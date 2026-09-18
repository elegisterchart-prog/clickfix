@extends('layouts.app')

@section('content')
<div class="auth-shell">
    <div class="auth-card">
        <div class="eyebrow">Create account</div>
        <h1>ลงทะเบียน</h1>
        <p class="auth-subtitle">เริ่มต้นใช้งาน ClickFix ได้ทันที พร้อมระบบแจ้งซ่อม จัดสเป็ค และดูประกันแบบครบวงจร</p>

        @if($errors->any())
            <div class="alert">
                <ul class="error-list">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf
            <div class="form-group">
                <label for="name">ชื่อ</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus>
            </div>
            <div class="form-group">
                <label for="email">อีเมล</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required>
            </div>
            <div class="form-group">
                <label for="password">รหัสผ่าน</label>
                <input id="password" type="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="password_confirmation">ยืนยันรหัสผ่าน</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required>
            </div>
            <button type="submit" class="button-primary">สร้างบัญชี</button>
        </form>

        <p class="auth-meta">ถ้าคุณมีบัญชีอยู่แล้ว <a href="{{ route('login') }}">เข้าสู่ระบบ</a></p>
    </div>
</div>
@endsection
