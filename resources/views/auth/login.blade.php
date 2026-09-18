@extends('layouts.app')

@section('content')
<div class="auth-shell">
    <div class="auth-card">
        <div class="eyebrow">Welcome back</div>
        <h1>เข้าสู่ระบบ</h1>
        <p class="auth-subtitle">เข้าถึงระบบบริการซ่อม จัดสเป็คคอมพิวเตอร์ และติดตามประกันของคุณ</p>

        @if($errors->any())
            <div class="alert">
                <ul class="error-list">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-group">
                <label for="email">อีเมล</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" autofocus>
            </div>
            <div class="form-group">
                <label for="password">รหัสผ่าน</label>
                <input id="password" type="password" name="password">
            </div>
            <div class="form-group" style="display:flex; align-items:center; gap:10px;">
                <input id="remember" type="checkbox" name="remember" style="width:18px; height:18px; margin:0; accent-color:#2563eb;">
                <label for="remember" style="margin:0; font-weight:600; cursor:pointer;">จำฉันไว้ในระบบ</label>
            </div>
            <button type="submit" class="button-primary">เข้าสู่ระบบ</button>
        </form>

        <p class="auth-meta">ยังไม่มีบัญชี? <a href="{{ route('register') }}">ลงทะเบียนที่นี่</a></p>
    </div>
</div>
@endsection
