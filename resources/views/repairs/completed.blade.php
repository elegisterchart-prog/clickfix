@extends('layouts.app')

@section('content')
<div class="card" style="text-align:center; padding:24px;">
    <h2>แจ้งซ่อมเสร็จสิ้น</h2>
    <p>ขอบคุณที่แจ้งซ่อม เรื่องของคุณได้รับการบันทึกแล้ว</p>
    <p>หมายเลขแจ้งซ่อม: <strong>#{{ $repair->id }}</strong></p>
    <div style="margin-top:18px; display:flex; gap:12px; justify-content:center;">
        <a href="{{ route('repairs.show', ['id' => $repair->id]) }}" class="button-primary">ดูรายละเอียดแจ้งซ่อม</a>
        <a href="{{ route('dashboard') }}" class="button-secondary">กลับไปหน้าหลัก</a>
    </div>
</div>
@endsection
