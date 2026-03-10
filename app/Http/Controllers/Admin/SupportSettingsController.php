<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SupportSettingsController extends Controller
{
    public function index()
    {
        $this->authorize('admin_support_manage');
        
        // Load existing settings
        $settings = $this->loadSettings();
        
        return view('admin.support-settings.index', compact('settings'));
    }
    
    public function loadSettings()
    {
        $defaultSettings = [
            'sms' => [
                'enabled' => false,
                'critical' => true,
                'newTickets' => false,
                'escalation' => true,
                'afterHours' => false,
                'phoneNumbers' => []
            ],
            'email' => [
                'enabled' => true,
                'assignment' => true,
                'status' => true,
                'digest' => true,
                'weekly' => false,
                'recipients' => []
            ],
            'general' => [
                'businessHoursStart' => '09:00',
                'businessHoursEnd' => '18:00',
                'quietHoursEnabled' => false,
                'weekendNotifications' => false,
                'maxFrequency' => '10',
                'priorityHigh' => true,
                'priorityMedium' => true,
                'priorityLow' => false
            ]
        ];
        
        // Try to load from database
        try {
            $dbSettings = DB::table('support_settings')
                ->where('key', 'notification_settings')
                ->first();
                
            if ($dbSettings) {
                return array_merge($defaultSettings, json_decode($dbSettings->value, true) ?: []);
            }
        } catch (\Exception $e) {
            // Log error but continue with defaults
            \Log::error('Error loading support settings: ' . $e->getMessage());
        }
        
        return $defaultSettings;
    }
    
    public function saveSettings(Request $request)
    {
        $this->authorize('admin_support_manage');
        
        try {
            $settings = $request->all();
            
            // Validate settings
            $validated = $this->validateSettings($settings);
            
            // Save to database
            DB::table('support_settings')->updateOrInsert(
                ['key' => 'notification_settings'],
                [
                    'value' => json_encode($validated),
                    'updated_by' => Auth::id(),
                    'updated_at' => now()
                ]
            );
            
            // Log the change
            $this->logSettingsChange($validated);
            
            return response()->json([
                'success' => true,
                'message' => 'Settings saved successfully'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error saving support settings: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error saving settings: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function getSettings()
    {
        $this->authorize('admin_support_manage');
        
        $settings = $this->loadSettings();
        
        return response()->json([
            'success' => true,
            'settings' => $settings
        ]);
    }
    
    private function validateSettings($settings)
    {
        $validated = [];
        
        // Validate SMS settings
        if (isset($settings['sms'])) {
            $validated['sms'] = [
                'enabled' => filter_var($settings['sms']['enabled'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'critical' => filter_var($settings['sms']['critical'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'newTickets' => filter_var($settings['sms']['newTickets'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'escalation' => filter_var($settings['sms']['escalation'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'afterHours' => filter_var($settings['sms']['afterHours'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'phoneNumbers' => $this->validatePhoneNumbers($settings['sms']['phoneNumbers'] ?? [])
            ];
        }
        
        // Validate Email settings
        if (isset($settings['email'])) {
            $validated['email'] = [
                'enabled' => filter_var($settings['email']['enabled'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'assignment' => filter_var($settings['email']['assignment'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'status' => filter_var($settings['email']['status'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'digest' => filter_var($settings['email']['digest'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'weekly' => filter_var($settings['email']['weekly'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'recipients' => $this->validateEmailRecipients($settings['email']['recipients'] ?? [])
            ];
        }
        
        // Validate General settings
        if (isset($settings['general'])) {
            $validated['general'] = [
                'businessHoursStart' => $this->validateTime($settings['general']['businessHoursStart'] ?? '09:00'),
                'businessHoursEnd' => $this->validateTime($settings['general']['businessHoursEnd'] ?? '18:00'),
                'quietHoursEnabled' => filter_var($settings['general']['quietHoursEnabled'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'weekendNotifications' => filter_var($settings['general']['weekendNotifications'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'maxFrequency' => $this->validateFrequency($settings['general']['maxFrequency'] ?? '10'),
                'priorityHigh' => filter_var($settings['general']['priorityHigh'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'priorityMedium' => filter_var($settings['general']['priorityMedium'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'priorityLow' => filter_var($settings['general']['priorityLow'] ?? false, FILTER_VALIDATE_BOOLEAN)
            ];
        }
        
        return $validated;
    }
    
    private function validatePhoneNumbers($phoneNumbers)
    {
        $validated = [];
        
        foreach ($phoneNumbers as $phone) {
            $phone = trim($phone);
            if (!empty($phone) && $this->isValidPhoneNumber($phone)) {
                $validated[] = $phone;
            }
        }
        
        return array_unique($validated);
    }
    
    private function validateEmailRecipients($recipients)
    {
        $validated = [];
        
        foreach ($recipients as $recipient) {
            if (isset($recipient['email']) && filter_var($recipient['email'], FILTER_VALIDATE_EMAIL)) {
                $validated[] = [
                    'email' => $recipient['email'],
                    'type' => in_array($recipient['type'], ['All Notifications', 'Critical Only', 'Digest Only']) 
                        ? $recipient['type'] 
                        : 'All Notifications'
                ];
            }
        }
        
        return $validated;
    }
    
    private function validateTime($time)
    {
        // Validate time format HH:MM
        if (preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $time)) {
            return $time;
        }
        
        return '09:00'; // Default fallback
    }
    
    private function validateFrequency($frequency)
    {
        $validFrequencies = ['unlimited', '5', '10', '20'];
        return in_array($frequency, $validFrequencies) ? $frequency : '10';
    }
    
    private function isValidPhoneNumber($phone)
    {
        // Basic phone number validation - can be enhanced
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        return strlen($phone) >= 10 && strlen($phone) <= 15;
    }
    
    private function logSettingsChange($settings)
    {
        try {
            DB::table('support_settings_audit')->insert([
                'settings' => json_encode($settings),
                'changed_by' => Auth::id(),
                'changed_at' => now(),
                'ip_address' => request()->ip()
            ]);
        } catch (\Exception $e) {
            \Log::error('Error logging settings change: ' . $e->getMessage());
        }
    }
    
    /**
     * Send test notification
     */
    public function sendTestNotification(Request $request)
    {
        $this->authorize('admin_support_manage');
        
        $type = $request->get('type', 'email'); // email or sms
        $recipient = $request->get('recipient');
        
        try {
            if ($type === 'email') {
                $this->sendTestEmail($recipient);
            } elseif ($type === 'sms') {
                $this->sendTestSMS($recipient);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Test notification sent successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error sending test notification: ' . $e->getMessage()
            ], 500);
        }
    }
    
    private function sendTestEmail($email)
    {
        // Implementation for sending test email
        // This would integrate with your email service
        \Log::info("Test email sent to: $email");
    }
    
    private function sendTestSMS($phone)
    {
        // Implementation for sending test SMS
        // This would integrate with your SMS service
        \Log::info("Test SMS sent to: $phone");
    }
    
    /**
     * Get notification history
     */
    public function getNotificationHistory(Request $request)
    {
        $this->authorize('admin_support_manage');
        
        $limit = $request->get('limit', 50);
        $offset = $request->get('offset', 0);
        
        try {
            $history = DB::table('notification_history')
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->offset($offset)
                ->get();
            
            return response()->json([
                'success' => true,
                'history' => $history
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading notification history'
            ], 500);
        }
    }
}
