<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .cart-banner {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 60px 0;
            margin-bottom: 40px;
        }
        
        .section-title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #333;
        }
        
        .form-control {
            height: 45px;
            border-radius: 8px;
            border: 1px solid #ddd;
            padding: 0 15px;
        }
        
        .btn-primary {
            background: #667eea;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 500;
        }
        
        .btn-primary:hover {
            background: #5568d3;
        }
        
        .mt-45 {
            margin-top: 45px;
        }
        
        .mt-25 {
            margin-top: 25px;
        }
        
        .mb-25 {
            margin-bottom: 25px;
        }
    </style>
</head>
<body>
    <section class="cart-banner position-relative text-center">
        <h1 class="font-30 text-white font-weight-bold">Checkout</h1>
    </section>
@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

    <section class="container mt-45">
        <h2 class="section-title">Please Fill The Form</h2>
        
        <form action="/verifysubscriptionaccess" method="get" class="mt-25">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            
            <input type="text" 
                   name="name" 
                   id="customer_name" 
                   placeholder="Name" 
                   class="form-control mt-25" 
                   required>
            
            <input type="email" 
                   name="email" 
                   id="customer_email" 
                   placeholder="Email" 
                   class="form-control mt-25" 
                   required>
            
            <input type="number" 
                   name="mobile" 
                   id="customer_number" 
                   placeholder="Contact Number" 
                   class="form-control mt-25" 
                   required>
            
            <input type="text" 
                   name="subscription_id" 
                   id="subscription_id" 
                   placeholder="Subscription ID" 
                   class="form-control mt-25" 
                   value="{{ $subscription->id }}"
                   readonly
                   >
            
            <input type="text" 
                   name="order_id" 
                   id="order_id" 
                   placeholder="Order ID  (Optional)" 
                   class="form-control mt-25" 
                   >
            
            <input type="text" 
                   name="discount_id" 
                   id="discount_id" 
                   placeholder="Discount ID (Optional)" 
                   class="form-control mt-25 mb-25">

            <div class="d-flex align-items-center justify-content-between mt-45">
                <span class="font-16 font-weight-500 text-gray">Total Amount: ₹{{ number_format($total, 2) }}</span>
                <button type="submit" class="btn btn-sm btn-primary">Start Payment</button>
            </div>
        </form>
    </section>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Mobile number validation - only 10 digits
        $('#customer_number').on('keypress', function(e) {
            var $this = $(this);
            var regex = new RegExp("^[0-9\b]+$");
            var str = String.fromCharCode(!e.charCode ? e.which : e.charCode);
            
            // Max 10 digits
            if ($this.val().length > 9) {
                e.preventDefault();
                return false;
            }
            
            // First digit should not be 0-5
            if (e.charCode < 54 && e.charCode > 47) {
                if ($this.val().length == 0) {
                    e.preventDefault();
                    return false;
                }
            }
            
            if (regex.test(str)) {
                return true;
            }
            
            e.preventDefault();
            return false;
        });
    </script>
</body>
</html>