@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/welcome.css') }}">

<div class="bg-slideshow" aria-hidden="true">
    <div class="slide"></div>
    <div class="slide"></div>
    <div class="slide"></div>
    <div class="slide"></div>
</div>

<div class="welcome-hero">
    <div class="welcome-shell">
        <div class="logo-box">C</div>
        <div class="brand-large">ClickFix</div>
        <div class="welcome-tagline">ซ่อมคอมพิวเตอร์ • จำหน่ายอะไหล่ • บริการครบวงจร</div>

        <div class="welcome-actions">
            @guest
                <a href="{{ route('login') }}" class="btn btn-primary">Login</a>
                <a href="{{ route('register') }}" class="btn btn-secondary">Sign in</a>
            @else
                <a href="{{ route('dashboard') }}" class="btn btn-primary">Go to dashboard</a>
            @endguest
        </div>

        <div class="welcome-pills">
            <span class="pill">ซ่อมด่วน</span>
            <span class="pill">จำหน่ายชิ้นส่วน</span>
            <span class="pill">ประกันชัดเจน</span>
        </div>
    </div>
</div>
@endsection
