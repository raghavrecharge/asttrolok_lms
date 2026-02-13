<?php

namespace App\PaymentChannels\Drivers\Apple;

use App\Models\PaymentChannel;
use App\PaymentChannels\IChannel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\TransactionHistory;
use App\Models\Api\User;
use App\Models\OrderItem;
use Carbon\Carbon;

class Channel implements IChannel
{
    private $APPLE_PROD_URL = "https://buy.itunes.apple.com/verifyReceipt";
    private $APPLE_SANDBOX_URL = "https://sandbox.itunes.apple.com/verifyReceipt";
    private $paymentChannel;

    public function __construct(PaymentChannel $paymentChannel)
    {
        $this->paymentChannel = $paymentChannel;
    }

    public function paymentRequest(Order $order)
    {
       
        return [
            'status' => true,
            'message' => 'Initiate payment from iOS app',
            'order_id' => $order->id
        ];
    }

    /**
     * Verify Apple In-App purchase
     */
    public function verify(Request $request)
    {
        $receipt = $request->input('transactionReceipt');
        $orderId = $request->input('order_id');
        $transactionId = $request->input('transactionId');
        $productId = $request->input('productId');

        if (!$receipt) {
            return [
                'status' => false,
                'message' => 'Missing receipt data'
            ];
        }

        try {
            $appleResponse = $this->callAppleVerify($receipt);
            
            if (!$this->isValidAppleResponse($appleResponse)) {
                return [
                    'status' => false,
                    'message' => 'Apple verification failed: ' . $this->getAppleErrorMessage($appleResponse['status'] ?? 0),
                    'error_code' => $appleResponse['status'] ?? 'unknown',
                    'apple_response' => $appleResponse
                ];
            }

            $validationResult = $this->validateTransaction($appleResponse, $orderId, $transactionId, $productId);
            if (!$validationResult['valid']) {
                return [
                    'status' => false,
                    'message' => $validationResult['message']
                ];
            }

            return [
                'status' => true,
                'message' => 'Payment verified successfully',
                'order_id' => $orderId,
                'transaction_id' => $transactionId,
                'product_id' => $productId,
                'apple_response' => $appleResponse
            ];

        } catch (\Exception $e) {
           
            return [
                'status' => false,
                'message' => 'Verification failed: ' . $e->getMessage()
            ];
        }
    }
    
    public function verifyApi1($input)
    {
        $orderId = $input['order_id'] ?? null;
        $receiptData = $input['transactionReceipt'] ?? null;
        $transactionId = $input['transactionId'] ?? null;
        $productId = $input['productId'] ?? null;

        try {
            $user = apiAuth();
            if (empty($user)) {
                $order = new \stdClass();
                $order->error = 'User authentication failed';
                return $order;
            }

            if (!$receiptData) {
                $order = new \stdClass();
                $order->error = 'Missing transaction receipt';
                return $order;
            }

            if (!$orderId) {
                $order = new \stdClass();
                $order->error = 'Missing order ID';
                return $order;
            }

            $order = Order::where('id', $orderId)
                         ->where('user_id', $user->id)
                         ->first();
                         
            if (!$order) {
                $errorOrder = new \stdClass();
                $errorOrder->error = 'Order not found or unauthorized';
                return $errorOrder;
            }

            $existingTransaction = TransactionHistory::where('order_id', $orderId)
                                                  ->where('status', 'successful')
                                                  ->first();
            
            if ($existingTransaction) {
                $order->error = 'Transaction already processed';
                $order->existing_transaction = $existingTransaction;
                return $order;
            }

            $appleResponse = $this->callAppleVerify($receiptData);
            
            if (!$this->isValidAppleResponse($appleResponse)) {
                Log::error('Apple verification failed', [
                    'order_id' => $orderId,
                    'apple_response' => $appleResponse,
                    'user_id' => $user->id,
                    'status_code' => $appleResponse['status'] ?? 'unknown'
                ]);

                $order->error = 'Apple verification failed: ' . $this->getAppleErrorMessage($appleResponse['status'] ?? 0);
                $order->status_code = $appleResponse['status'] ?? 'unknown';
                if (config('app.debug')) {
                    $order->apple_details = $appleResponse;
                }
                return $order;
            }

            $validationResult = $this->validateTransaction($appleResponse, $orderId, $transactionId, $productId);
            if (!$validationResult['valid']) {
                $order->error = $validationResult['message'];
                return $order;
            }

            $appleTransactionData = $this->extractTransactionData($appleResponse, $transactionId);
            
            // Update order status
            $order->status = Order::$paying;  
            $order->payment_method = Order::$credit;
            $order->save();

            $transactionHistory = TransactionHistory::create([
                'user_id'          => $user->id,
                'order_id'         => $orderId,
                'transaction_type' => 'credit',
                'transaction_id'   => $transactionId,
                'product_id'       => $productId,
                'amount'           => $order->amount,
                'currency'         => $order->currency ?? 'USD',
                'status'           => 'successful',
                'description'      => 'Apple In-App Purchase',
                'transaction_date' => $appleTransactionData['purchase_date'] ?? Carbon::now(),
                'raw_response'     => json_encode($appleResponse),
                'payment_gateway'  => 'apple_iap',
                'razorpay_payment_id' => $transactionId
            ]);

            return $order;

        } catch (\Exception $e) {
            
            $errorOrder = new \stdClass();
            $errorOrder->error = config('app.debug') 
                ? 'An internal error occurred: ' . $e->getMessage()
                : 'An internal error occurred during verification. Please try again later.';
            return $errorOrder;
        }
    }

    private function callAppleVerify($receipt)
    {
        $cleanedReceipt = $this->cleanReceiptData($receipt);
        
        if (!$cleanedReceipt) {
            throw new \Exception('Invalid receipt data provided');
        }
        
        $payload = [
            'receipt-data' => $cleanedReceipt,
            'exclude-old-transactions' => true
        ];

        $sharedSecret = config('services.apple.shared_secret', env('APPLE_SHARED_SECRET'));
        if ($sharedSecret) {
            $payload['password'] = $sharedSecret;
        }

        try {
            $environment = config('app.env', 'production');
            
            //  production environment
            $response = $this->makeAppleRequest($this->APPLE_PROD_URL, $payload);
            
            if (isset($response['status']) && $response['status'] == 21007) {
                Log::info('Switching to sandbox due to 21007 error', ['order_context' => 'apple_verification']);
                $response = $this->makeAppleRequest($this->APPLE_SANDBOX_URL, $payload);
            }
            
            if (!$this->isValidAppleResponse($response) && in_array($environment, ['local', 'development', 'staging'])) {
                Log::info('Trying sandbox for development environment');
                $sandboxResponse = $this->makeAppleRequest($this->APPLE_SANDBOX_URL, $payload);
                if ($this->isValidAppleResponse($sandboxResponse)) {
                    return $sandboxResponse;
                }
            }
            
            return $response;

        } catch (\Exception $e) {
            Log::error('Apple verification HTTP error: ' . $e->getMessage(), [
                'receipt_length' => strlen($cleanedReceipt),
                'is_valid_base64' => $this->isValidBase64($cleanedReceipt)
            ]);
            throw $e;
        }
    }

    private function makeAppleRequest($url, $payload)
    {
        $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])
            ->timeout(30)
            ->retry(3, 1000)
            ->post($url, $payload);
        
        if (!$response->successful()) {
            throw new \Exception("HTTP {$response->status()}: Failed to connect to Apple servers");
        }

        $responseData = $response->json();
        
        if (!is_array($responseData)) {
            throw new \Exception('Invalid JSON response from Apple servers');
        }

        return $responseData;
    }

    private function cleanReceiptData($receiptData)
    {
        if (!$receiptData) {
            return null;
        }

        $cleaned = trim($receiptData);
        $cleaned = str_replace(['\n', '\r', '\t', ' '], '', $cleaned);
        $cleaned = str_replace(
            ['"', '"', "'", "'"], 
            ['"', '"', "'", "'"], 
            $cleaned
        );
        
        $cleaned = preg_replace('/[^A-Za-z0-9+\/=]/', '', $cleaned);
        
        // Validate base64
        if (!$this->isValidBase64($cleaned)) {
            Log::error('Invalid base64 receipt data', [
                'original_length' => strlen($receiptData),
                'cleaned_length' => strlen($cleaned),
                'sample' => substr($cleaned, 0, 50) . '...'
            ]);
            return null;
        }

        return $cleaned;
    }

    private function isValidBase64($data)
    {
        if (!$data) {
            return false;
        }
        
        return base64_encode(base64_decode($data, true)) === $data;
    }

    private function isValidAppleResponse($response)
    {
        return isset($response['status']) && $response['status'] === 0;
    }

    private function validateTransaction($appleResponse, $orderId, $transactionId, $productId)
    {
        if (!isset($appleResponse['receipt']['in_app']) || empty($appleResponse['receipt']['in_app'])) {
            return [
                'valid' => false,
                'message' => 'No transactions found in receipt'
            ];
        }

        $transactions = $appleResponse['receipt']['in_app'];
        
        foreach ($transactions as $transaction) {
            if (isset($transaction['transaction_id']) && 
                $transaction['transaction_id'] == $transactionId) {
                
                if ($productId && isset($transaction['product_id']) && 
                    $transaction['product_id'] !== $productId) {
                    continue;
                }

                if (isset($transaction['cancellation_date'])) {
                    return [
                        'valid' => false,
                        'message' => 'Transaction has been cancelled'
                    ];
                }
                
                return ['valid' => true, 'message' => 'Transaction validated'];
            }
        }

        return [
            'valid' => false,
            'message' => 'Transaction ID not found in receipt'
        ];
    }

    private function extractTransactionData($appleResponse, $transactionId)
    {
        $transactions = $appleResponse['receipt']['in_app'] ?? [];
        
        foreach ($transactions as $transaction) {
            if (isset($transaction['transaction_id']) && 
                $transaction['transaction_id'] == $transactionId) {
                
                return [
                    'transaction_id' => $transaction['transaction_id'],
                    'product_id' => $transaction['product_id'] ?? null,
                    'purchase_date' => isset($transaction['purchase_date_ms']) 
                        ? Carbon::createFromTimestampMs($transaction['purchase_date_ms'])
                        : Carbon::now(),
                    'quantity' => $transaction['quantity'] ?? 1,
                    'original_transaction_id' => $transaction['original_transaction_id'] ?? null,
                ];
            }
        }
        
        return [];
    }

    private function getAppleErrorMessage($statusCode)
    {
        $errorMessages = [
            21000 => 'The App Store could not read the JSON object you provided.',
            21002 => 'The data in the receipt-data property was malformed or missing.',
            21003 => 'The receipt could not be authenticated.',
            21004 => 'The shared secret you provided does not match the shared secret on file.',
            21005 => 'The receipt server is not currently available.',
            21006 => 'This receipt is valid but the subscription has expired.',
            21007 => 'This receipt is from the test environment, but it was sent to the production environment.',
            21008 => 'This receipt is from the production environment, but it was sent to the test environment.',
            21009 => 'Internal data access error. Try again later.',
            21010 => 'This receipt could not be authorized. Treat this the same as if a purchase was never made.'
        ];

        return $errorMessages[$statusCode] ?? "Unknown Apple verification error (Code: $statusCode)";
    }
}