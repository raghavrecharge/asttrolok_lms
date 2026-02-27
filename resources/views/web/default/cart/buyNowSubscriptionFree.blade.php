@extends('web.default.layouts.app')
@push('styles_top')
<style>
.form-control.is-invalid {
    border-color: #dc3545;
}

.form-control.is-valid {
    border-color: #28a745;
}

.form-control.is-invalid:focus {
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

.form-control.is-valid:focus {
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
}
</style>
@endpush
@section('content')
<section class="container mt-45">
    <h2 class="section-title">{{ $subscription->title }}</h2>
    
    <form action="/subscriptions/{{$subscription->slug}}/free" method="get">
        @csrf
        <input type="text" name="name" id="customer_name" 
               value="{{ auth()->check() ? auth()->user()->full_name : '' }}" 
               placeholder="Name" class="form-control mt-25" required>
        
        <input type="email" name="email" id="customer_email" 
               value="{{ auth()->check() ? auth()->user()->email : '' }}" 
               placeholder="Email" class="form-control mt-25" required>
        @if(!auth()->check())
        <input type="password" 
       name="password" 
       id="customer_password" 
       placeholder="Create Password" 
       class="form-control mt-25" 
       required>

<input type="password" 
       name="password_confirmation" 
       id="customer_password_confirmation" 
       placeholder="Confirm Password" 
       class="form-control mt-25" 
       required>
<div class="invalid-feedback">
    Passwords do not match!
</div>
        @endif
        <input type="number" name="number" id="customer_number" 
               value="{{ auth()->check() ? auth()->user()->mobile : '' }}" 
               placeholder="Mobile" class="form-control mt-25 mb-25" required>
        
        <input type="hidden" name="subscription_id" id="subscription_id" 
               value="{{ $subscription->id }}" 
               placeholder="Mobile" class="form-control mt-25 mb-25" required>

        <button type="submit" class="btn btn-primary">
            Enroll Now
        </button>
    </form>

    <center>
        <div class="loader mt-50" id="loader" style="display:none;">
            <img width="80px" height="80px" src="{{ asset('assets/default/img/loading.gif') }}">
            <h3>Processing payment...</h3>
        </div>
    </center>
</section>
@endsection

@push('scripts_bottom')
<script>
// Password confirmation validation
const passwordField = document.getElementById('customer_password');
const confirmPasswordField = document.getElementById('customer_password_confirmation');

function validatePasswordMatch() {
    const password = passwordField.value;
    const confirmPassword = confirmPasswordField.value;
    
    // अगर confirm password खाली है तो कुछ नहीं करो
    if (confirmPassword === '') {
        confirmPasswordField.classList.remove('is-invalid', 'is-valid');
        return true;
    }
    
    // तभी validate करो जब confirm password की length >= password की length हो
    if (confirmPassword.length >= password.length) {
        if (password === confirmPassword) {
            confirmPasswordField.classList.remove('is-invalid');
            confirmPasswordField.classList.add('is-valid');
            return true;
        } else {
            confirmPasswordField.classList.remove('is-valid');
            confirmPasswordField.classList.add('is-invalid');
            return false;
        }
    }
    
    return true;
}

// Input event - sirf tabhi check karo jab puri length match kare
confirmPasswordField.addEventListener('input', validatePasswordMatch);

// Blur event - jab user field se bahar jaye tab bhi check karo
confirmPasswordField.addEventListener('blur', function() {
    if (confirmPasswordField.value !== '') {
        validatePasswordMatch();
    }
});

// Jab password field change ho, confirm password ko bhi revalidate karo
passwordField.addEventListener('input', function() {
    if (confirmPasswordField.value !== '' && 
        confirmPasswordField.value.length >= passwordField.value.length) {
        validatePasswordMatch();
    }
});

// Form submission validation
document.querySelector('form').addEventListener('submit', function(e) {
    const password = document.getElementById('customer_password').value;
    const confirmPassword = document.getElementById('customer_password_confirmation').value;
    const name = document.getElementById('customer_name').value;
    const email = document.getElementById('customer_email').value;
    const number = document.getElementById('customer_number').value;

    // Check if all fields are filled
    if (!name || !email || !number || !password || !confirmPassword) {
        e.preventDefault();
        alert('Please fill in all required fields!');
        return false;
    }

    // Check if passwords match
    if (password !== confirmPassword) {
        e.preventDefault();
        confirmPasswordField.classList.add('is-invalid');
        alert('Passwords do not match!');
        return false;
    }

    // Show loader
    document.getElementById('loader').style.display = 'block';
    return true;
});
</script>
@endpush