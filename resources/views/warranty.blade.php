@extends('layouts.app')

@section('content')
<div class="card">
    <h1>เช็คประกัน</h1>
    <p>ตรวจสอบสถานะการรับประกันสำหรับอุปกรณ์ของคุณ โดยระบุหมายเลขเครื่องหรือข้อมูลที่เกี่ยวข้อง.</p>
    <div class="alert">ฟีเจอร์นี้พร้อมพัฒนาเพิ่มเติมในเวอร์ชันถัดไป.</div>
    <a href="{{ route('dashboard') }}" class="button-secondary">กลับไปหน้าหลัก</a>
</div>
@endsection
