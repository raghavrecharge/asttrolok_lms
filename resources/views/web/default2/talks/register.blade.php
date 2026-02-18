@extends('web.default2.layouts.app')

@section('content')

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Program Registration</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css?family=Inter:400,600,700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@400;700&family=Material+Symbols+Rounded:wght@400;700&display=swap" rel="stylesheet">
  <style>
    body {
      margin: 0;
      background: #f8f8f8;
      font-family: 'Inter', Arial, sans-serif;
      color: #222;
      min-height: 100vh;
    }

  .main-flex {
  display: flex;
  align-items: flex-start;
  gap: 24px;
  padding:10px;
  margin-top: 40px;
  flex-wrap: wrap;
}

    .sidebar-card {
      width: 400px;
      background: #fff;
      min-width: 320px;
      border-right: 1px solid #eee;
      display: flex;
      flex-direction: column;
      align-items: stretch;
      box-shadow: 0 0 28px #0001;
      margin: 0;
      padding-top: 0;
      border-radius: 11px;
      align-self: flex-start;
    }

    .sidebar-image {
      width: 100%;
      height: 210px;
      object-fit: cover;
      border-top-left-radius: 9px;
      border-top-right-radius: 9px;
      margin-bottom: 0;
    }

    .sidebar-table {
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: flex-start;
      align-items: center;
      padding: 36px 28px;
    }

    .program-heading {
      font-size: 1.17rem;
      font-weight: 700;
      margin-bottom: 16px;
      text-align: center;
    }

    .program-details {
      font-size: 1rem;
      color: #757575;
      margin-bottom: 12px;
      text-align: center;
    }

    .details-table {
      width: 100%;
      max-width: 320px;
      margin-top: 18px;
    }

    .details-row {
      display: flex;
      align-items: flex-start;
      gap: 10px;
      margin-bottom: 19px;
      font-size: 1.05rem;
      color: #474747;
      justify-content: center;
    }

    .details-row .material-symbols-outlined {
      font-size: 1.33rem;
      color: #eab676;
    }

    .contact-info, .email {
      text-align: left;
      font-size: 0.99rem;
      color: #2562b2;
    }

    .form-section {
      flex: 1;
      background-size: cover;
      display: flex;
      flex-direction: column;
      justify-content: center;
      padding: 0 44px;
      min-width: 300px;
      position: relative;
    }

    .personal-form {
      background: rgba(255,255,255,0.97);
      padding: 38px 38px 24px 38px;
      max-width: 600px;
      margin: auto;
      border-radius: 12px;
      box-shadow: 0 0 22px #00000013;
    }

    .form-header {
      font-size: 1.32rem;
      font-weight: 700;
      margin-bottom: 18px;
      border-bottom: 2px solid #eab676;
      display: inline-block;
      padding-bottom: 6px;
    }

    .form-group {
      display: flex;
      gap: 18px;
      margin-bottom: 18px;
    }

    .form-group input {
      flex: 1;
      background: #f7f7fa;
      border: 1px solid #d8d8d8;
      border-radius: 6px;
      padding: 12px;
      font-size: 1rem;
      margin-top: 6px;
      outline: none;
    }

    .form-group input:focus {
      border-color: #eab676;
    }

    .checkbox-row {
      margin: 7px 0 0 0;
      font-size: 0.95rem;
      color: #535353;
    }

    .checkbox-row label {
      margin-left: 6px;
    }

    .submit-row {
      display: flex;
      justify-content: center;
      align-items: center;
      margin-top: 16px;
    }

    .register-btn {
      background: linear-gradient(90deg, #ffd083 0%, #e1a65c 100%);
      color: #232323;
      font-size: 1.09rem;
      font-weight: 600;
      border: none;
      border-radius: 28px;
      padding: 13px 38px;
      cursor: pointer;
      box-shadow: 0 2px 9px #00000018;
      transition: background 0.23s;
    }

    .register-btn:hover {
      background: linear-gradient(90deg, #fde1b6 0%, #ecc283 100%);
    }

    .quote-area {
      position: absolute;
      right: 34px;
      bottom: 26px;
      color: #fff;
      font-size: 1.08rem;
      max-width: 340px;
      opacity: 0.84;
      font-family: 'Inter', Arial, sans-serif;
      text-align: right;
      line-height: 1.5;
      font-style: italic;
      text-shadow: 0 0 4px #6662;
    }

    .quote-area strong {
      font-weight: 700;
      font-style: normal;
      color: #fff;
    }

   @media (max-width:900px) {
  .main-flex {
    flex-direction: column;
    gap: 16px;
  }

      .sidebar-card {
        width: 100vw;
        min-width: unset;
        border-right: none;
        border-bottom: 1.1px solid #eee;
        border-radius: 0;
      }

      .sidebar-image {
        height: 110px;
      }

      .sidebar-table {
        padding: 16px 4vw;
      }

      .details-table {
        max-width: 98vw;
      }

      .quote-area {
        display: none;
      }
    }
  </style>
</head>
<body>
<div class="main-flex">

  <div class="sidebar-card">
    <img src="{{ $talk->getImage() ?? 'https://aartofliving.org/sites/default/files/styles/medium/public/artofliving_logo_0.png' }}"
         alt="{{ $talk->topic ?? 'Program' }}" class="sidebar-image">
    <div class="sidebar-table">
      <div class="program-heading">{{ $talk->topic ?? 'Program' }}</div>
      @if(!empty($talk->description))
        <div class="program-details">{{ $talk->description }}</div>
      @elseif(!empty($talk->contribution))
        <div class="program-details">Contribution = ₹ {{ $talk->contribution }}</div>
      @endif
      <div class="details-table">
        @if(isset($talk->teacher))
        <div class="details-row">
          <span class="material-symbols-outlined">account_circle</span>
          <span>
            {{ $talk->teacher->name ?? '' }}
            @if($talk->teacher->city || $talk->teacher->state)
              <br>
              ({{ $talk->teacher->city ?? '' }}
              {{ ($talk->teacher->city && $talk->teacher->state) ? ', ' : '' }}
              {{ $talk->teacher->state ?? '' }})
            @endif
          </span>
        </div>
        @endif
        <div class="details-row">
          <span class="material-symbols-outlined">calendar_today</span>
          <span>
            {{ $talk->date_time ? \Carbon\Carbon::parse($talk->date_time)->format('d M Y') : 'TBA' }}
          </span>
        </div>
        <div class="details-row">
          <span class="material-symbols-outlined">schedule</span>
          <span>
            {{ $talk->date_time ? \Carbon\Carbon::parse($talk->date_time)->format('h:i A') : 'TBA' }}
          </span>
        </div>
        @if(isset($talk->teacher) && $talk->teacher->phone)
        <div class="details-row">
          <span class="material-symbols-outlined">call</span>
          <span class="contact-info">
            {{ $talk->teacher->name ?? '' }},
            <a href="tel:{{ $talk->teacher->phone ?? '' }}">{{ $talk->teacher->phone ?? '' }}</a>
          </span>
        </div>
        @endif
        @if(isset($talk->teacher) && $talk->teacher->email)
        <div class="details-row">
          <span class="material-symbols-outlined">mail</span>
          <span class="email">
            {{ $talk->teacher->email ?? '' }}
          </span>
        </div>
        @endif
        @if($talk->location)
        <div class="details-row">
          <span class="material-symbols-outlined">location_on</span>
          <span>{{ $talk->location }}</span>
        </div>
        @endif
      </div>
    </div>
  </div>

  <div class="form-section">
    <form action="{{ route('talks.register', $talk->slug) }}" method="POST" class="personal-form">
      @csrf
      <div class="form-header">Personal Details</div>

      <div class="form-group">
        <input type="text" name="first_name" placeholder="First Name *" required>
        <input type="text" name="last_name" placeholder="Last Name *" required>
      </div>

      <div class="form-group">
        <input type="text" name="phone" placeholder="Mobile *" required>
        <input type="email" name="email" placeholder="Email *" required>
      </div>

      <div class="form-group">
        <input type="number" name="age" placeholder="Age *" required>
        <input type="text" name="postal_code" placeholder="Postal Code *" required>
      </div>

      <div class="checkbox-row">
        <input type="checkbox" name="health" id="health" required>
        <label for="health">I have read and agree to the <a href="#">health declaration</a></label>
      </div>

      <div class="checkbox-row">
        <input type="checkbox" name="terms" id="terms" required>
        <label for="terms">I agree to the <a href="#">Terms & Conditions</a> of my program participation.</label>
      </div>

      <div class="checkbox-row">
        <input type="checkbox" name="promo" id="promo">
        <label for="promo">I agree to receive information from The Art of Living and its affiliate organizations...</label>
      </div>

      <div class="submit-row">
        <button type="submit" class="register-btn">Register</button>
      </div>
    </form>

    <div class="quote-area">
      "If you want to make sense it has to come from Silence"<br>
      <strong>– Asttrolok</strong>
    </div>
  </div>
</div>
</body>
</html>

@endsection
