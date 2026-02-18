@extends('web.default2.layouts.app')

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

        <input type="number" name="number" id="customer_number" 
               value="{{ auth()->check() ? auth()->user()->mobile : '' }}" 
               placeholder="Mobile" class="form-control mt-25 mb-25" required>
        
        <input type="hidden" name="subscription_id" id="subscription_id" 
               value="{{ $subscription->id }}" 
               placeholder="Mobile" class="form-control mt-25 mb-25" required>

        <button type="submit" class="btn btn-primary" style="font-family: 'Inter', sans-serif !important;">
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

