@extends('web.default2'.'.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/select2/select2.min.css">
    <style>
        .thank-you-container {
            text-align: center;
            padding: 40px 20px;
        }

        .success-icon {
            font-size: 80px;
            color: #28a745;
            margin-bottom: 20px;
            animation: scaleIn 0.5s ease-in-out;
        }

        .thank-you-title {
            font-size: 48px;
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
            animation: fadeInDown 0.6s ease-in-out;
        }

        .thank-you-message {
            font-size: 18px;
            color: #666;
            line-height: 1.8;
            margin-bottom: 30px;
            animation: fadeInUp 0.7s ease-in-out;
        }

        .consultation-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 25px;
            margin: 30px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            animation: fadeIn 0.8s ease-in-out;
        }

        .consultation-info h3 {
            color: #333;
            font-size: 22px;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .consultation-info p {
            color: #555;
            font-size: 16px;
            margin: 10px 0;
        }

        .action-buttons {
            margin-top: 40px;
            animation: fadeInUp 0.9s ease-in-out;
        }

        .btn-custom {
            padding: 12px 40px;
            font-size: 16px;
            border-radius: 25px;
            margin: 10px;
            transition: all 0.3s ease;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-outline-custom {
            border: 2px solid #667eea;
            color: #667eea;
            background: white;
        }

        .btn-outline-custom:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.3);
        }

        @keyframes scaleIn {
            0% {
                transform: scale(0);
                opacity: 0;
            }
            50% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        @keyframes fadeInDown {
            0% {
                opacity: 0;
                transform: translateY(-30px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            0% {
                opacity: 0;
                transform: translateY(30px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            0% {
                opacity: 0;
            }
            100% {
                opacity: 1;
            }
        }

        .checkmark-circle {
            width: 120px;
            height: 120px;
            margin: 0 auto 30px;
            border-radius: 50%;
            background: #28a745;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            animation: scaleIn 0.5s ease-in-out;
            box-shadow: 0 5px 20px rgba(40, 167, 69, 0.3);
        }

        .checkmark-circle::before {
            content: '';
            position: absolute;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 3px solid #28a745;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.1);
                opacity: 0.5;
            }
        }

        .checkmark-svg {
            width: 60px;
            height: 60px;
        }

        .checkmark-path {
            stroke-dasharray: 166;
            stroke-dashoffset: 166;
            animation: draw 0.8s ease-in-out 0.3s forwards;
        }

        @keyframes draw {
            to {
                stroke-dashoffset: 0;
            }
        }

        .checkmark-circle-svg {
            stroke-dasharray: 166;
            stroke-dashoffset: 166;
            animation: draw 0.6s ease-in-out forwards;
        }

        .info-item {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 15px 0;
        }

        .info-item i {
            font-size: 24px;
            color: #667eea;
            margin-right: 10px;
        }

        @media (max-width: 768px) {
            .thank-you-title {
                font-size: 36px;
            }
            .btn-custom {
                width: 100%;
                margin: 5px 0;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container">
        <div class="row login-container1">
            <div class="col-12 col-md-12">
                <div class="login-card">
                    <div class="thank-you-container">

                        <div class="checkmark-circle">
                            <svg class="checkmark-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                                <circle class="checkmark-circle-svg" cx="26" cy="26" r="25" fill="none" stroke="#fff" stroke-width="3"/>
                                <path class="checkmark-path" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                            </svg>
                        </div>

                        <h1 class="thank-you-title">Thank You!</h1>

                        <p class="thank-you-message">
                            Thank you for booking a consultation!<br>
                            We appreciate your interest and look forward to providing valuable insights during our upcoming session.
                        </p>

                        <div class="consultation-info">
                            <h3><i class="fa fa-info-circle"></i> What Happens Next?</h3>
                            <div class="info-item">
                                <i class="fa fa-envelope"></i>
                                <p>You will receive a confirmation email shortly</p>
                            </div>
                            <div class="info-item">
                                <i class="fa fa-calendar"></i>
                                <p>Our team will contact you to schedule the consultation</p>
                            </div>
                            <div class="info-item">
                                <i class="fa fa-phone"></i>
                                <p>Keep your phone handy for any updates</p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts_bottom')
    <script src="{{ config('app.js_css_url') }}/assets/default/vendors/select2/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Optional: Auto redirect after 10 seconds
            // setTimeout(function() {
            //     window.location.href = '/panel';
            // }, 10000);

            // Trigger animation on page load
            setTimeout(function() {
                $('.checkmark-circle').addClass('show');
            }, 100);
        });
    </script>
@endpush
