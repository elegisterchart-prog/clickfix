@extends('layouts.app')

@section('content')
<div class="card">
    <h1>ส่งเรื่องซ่อม</h1>
    <p>กรอกข้อมูลเพื่อส่งคำขอซ่อมอุปกรณ์ของคุณให้ทางทีมงานตรวจสอบและติดต่อกลับ.</p>
    <div class="alert">ฟีเจอร์นี้พร้อมพัฒนาเพิ่มเติมในเวอร์ชันถัดไป.</div>
    <a href="{{ route('dashboard') }}" class="button-secondary">กลับไปหน้าหลัก</a>
</div>
@endsection
