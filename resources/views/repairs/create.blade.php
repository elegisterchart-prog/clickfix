@extends('layouts.app')

@section('content')
<div class="card">
    <h2>แจ้งซ่อม</h2>

    @if (session('status'))
        <div class="alert">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('repairs.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="form-group">
            <label>ชื่อผู้แจ้ง</label>
            <input type="text" name="name" value="{{ old('name', auth()->user()->name ?? '') }}">
        </div>

        <div class="form-group">
            <label>เบอร์ติดต่อ</label>
            <input type="text" name="phone" value="{{ old('phone') }}" required>
        </div>

        <div class="form-group">
            <label>รายละเอียด</label>
            <input type="text" name="details" value="{{ old('details') }}" required placeholder="อธิบายอาการโดยย่อ เช่น หน้าจอดับ / มีเสียงดัง / ติดตั้งโปรแกรมไม่ได้">
        </div>

        <div class="form-group">
            <label>แนบรูปภาพหรือวิดีโอ (jpg, png, mp4, mov, mkv)</label>
            <input type="file" name="attachments[]" accept="image/*,video/*" multiple>
            <div style="margin-top:6px; color:#64748b; font-size:0.9rem;">แนะนำขนาดไฟล์ไม่เกิน 50MB ต่อไฟล์</div>
        </div>

        <div style="display:flex; gap:12px;">
            <button class="button-primary" type="submit">ส่งแจ้งซ่อม</button>
            <a href="{{ route('dashboard') }}" class="button-secondary">ยกเลิก</a>
        </div>

    </form>
</div>
@endsection
