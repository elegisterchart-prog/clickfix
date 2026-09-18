@extends('layouts.app')

@section('content')
<div class="card" style="max-width:420px; margin: 0 auto;">
    <h2>เข้าสู่ระบบช่าง</h2>

    @if (session('status'))
        <div class="alert">{{ session('status') }}</div>
    @endif

    @if ($errors->has('access_code'))
        <div class="alert">{{ $errors->first('access_code') }}</div>
    @endif

    <form method="POST" action="{{ route('technician.login') }}">
        @csrf
        <div class="form-group">
            <label>รหัสช่าง</label>
            <input type="password" name="access_code" required>
        </div>
        <div style="margin-top:12px;">
            <button class="button-primary" type="submit">เข้าสู่ระบบ</button>
        </div>
    </form>
</div>
@endsection
