<!DOCTYPE html>
<html>
<head>
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <script
        src="https://api.lyra.com/static/js/krypton-client/V4.0/stable/kr-payment-form.min.js"
        kr-public-key="{{ $publicKey }}"
        kr-post-url-success="{{ $successUrl }}">
    </script>

    <link rel="stylesheet"
          href="https://api.lyra.com/static/js/krypton-client/V4.0/ext/classic-reset.css">
    <script
        src="https://api.lyra.com/static/js/krypton-client/V4.0/ext/classic.js">
    </script>
</head>
<body>

<div class="kr-embedded"  kr-form-token="{{ $formToken }}">

    <div class="kr-pan"></div>
    <div class="kr-expiry"></div>
    <div class="kr-security-code"></div>

    <button class="kr-payment-button"></button>

    <div class="kr-form-error"></div>
</div>
</body>
</html>
