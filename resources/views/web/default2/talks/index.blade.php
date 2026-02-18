@extends('web.default2.layouts.app')

@section('content')
<div class="talk-container">
    <div class="talk-inner">
        <div class="left-section">
            <img src="{{ $talk->getImage() }}" class="talk-image" alt="{{ $talk->topic }}">
        </div>

        <div class="right-section">
            <h1 class="event-title">{{ $talk->topic }}</h1>

            <div class="event-teacher">
                With <span class="teacher-name">{{ $talk->speaker->full_name ?? 'TBA' }}</span>
            </div>

            <div class="event-meta-row">
                <span class="material-symbols-rounded event-icon">schedule</span>
                {{ date('h:i A', strtotime($talk->date_time)) }}
                <span class="material-symbols-rounded event-icon" style="margin-left:16px;">calendar_month</span>
                {{ date('d M', strtotime($talk->date_time)) }}
                @php $endDate = $talk->end_date ?? null; @endphp
                @if($endDate && $endDate !== $talk->date_time)
                    - {{ date('d M', strtotime($endDate)) }}
                @endif
            </div>

            @if(!empty($talk->language))
            <div class="event-meta-row">
                <span class="material-symbols-rounded event-icon">language</span>
                {{ $talk->language }}
            </div>
            @endif

            @if(!empty($talk->location))
            <div class="event-meta-row">
                <span class="material-symbols-rounded event-icon">place</span>
                {{ $talk->location }}
            </div>
            @endif

            @if(!empty($talk->price))
            <div class="event-price">INR {{ number_format($talk->price, 2) }}</div>
            @endif

            @if(!empty($talk->description))
            <div class="event-note">{!! $talk->description !!}</div>
            @endif

            @php
                $user = auth()->user();
                $hasRegistered = $user ? $talk->registrations()->where('user_id', $user->id)->exists() : false;
                $isAdmin = $user && method_exists($user, 'isAdmin') ? $user->isAdmin() : false;
                $isTeacher = $user && method_exists($user, 'isTeacher') ? $user->isTeacher() : false;
            @endphp

            <div class="btn-wrap">
                @if(!$user || $isAdmin || $isTeacher)
                    <a href="/talks/{{ $talk->slug }}" class="reg-btn">View Details</a>
                @elseif($hasRegistered)
                    <button class="reg-btn" disabled>Registered</button>
                @else
                    <a href="{{ route('talks.showRegistrationForm', $talk->slug) }}" class="reg-btn">Register</a>
                @endif
            </div>
        </div>
    </div>
</div>

@push('styles_top')
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:wght@400;700&family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<style>
body {
  margin: 0;
  font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
  background: #fff;
  overflow-x: hidden;
}

.talk-container {
  display: flex;
  width: 100%;
  height: calc(80vh - 60px);
  margin: 0;
  padding: 0;
}

.talk-inner {
  display: flex;
  width: 100%;
  height: 100%;
}

.left-section {
  flex: 1;
  display: flex;
  justify-content: center;
  align-items: stretch;
  padding: 0;
  overflow: hidden;
}

.talk-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.right-section {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  padding: 20px;
  height: 100%;
  box-sizing: border-box;
  text-align: center;
}

.event-title { font-size: 1.8rem; font-weight: 700; color: #232323; margin-bottom: 12px; }
.event-teacher { color: #888; font-size: 1rem; margin-bottom: 15px; }
.teacher-name { color: #232323; font-weight: 600; }
.event-meta-row { display: flex; align-items: center; font-size: 0.95rem; color: #444; margin-bottom: 8px; }
.event-meta-row .event-icon { font-family: 'Material Symbols Rounded'; font-size: 1.1rem; margin-right: 6px; color: #f7b750; }
.event-price { margin-top: 12px; font-size: 1.15rem; color: #232323; font-weight: 700; }
.event-note { font-size: 0.9rem; color: #666; line-height: 1.4; margin-top: 12px; }
.btn-wrap { margin-top: 20px; }
.reg-btn { background: linear-gradient(90deg,#ffd083 0%,#e1a65c 100%); color: #232323; font-size: 1.05rem; border: none; border-radius: 25px; padding: 12px 30px; font-weight: 700; cursor: pointer; box-shadow: 0 2px 8px #00000017; transition: all 0.2s; }
.reg-btn:hover { background: linear-gradient(90deg,#fde1b6 0%,#ecc283 100%); }

@media (max-width: 900px) {
  .talk-inner { flex-direction: column; height: auto; }
  .left-section, .right-section { width: 100%; flex: none; }
  .talk-image { max-height: 200px; object-fit: cover; }
  .right-section { padding: 15px; justify-content: flex-start; }
  .event-title { font-size: 1.3rem; }
}
</style>
@endpush
@endsection
