@extends('layouts.app')

@section('content')
<div class="card">
    <h2>ตรวจสอบประกัน</h2>
    <p>ค้นหาประกันโดยใช้ อีเมล, เบอร์, ชื่อ หรือหมายเลขเครื่อง (serial)</p>

    <form method="GET" action="{{ route('admin.warranty') }}" style="display:flex; gap:8px; margin-top:12px;">
        <input type="text" name="q" placeholder="ค้นหา (email / phone / name / serial)" value="{{ $q ?? '' }}" style="flex:1; padding:10px 12px; border-radius:8px; border:1px solid #cbd5e1;">
        <button class="button-primary">ค้นหา</button>
    </form>

    <div style="margin-top:18px;">
        @if(isset($results) && $results->isEmpty())
            <div>ไม่พบผลลัพธ์</div>
        @elseif(isset($results))
            <h3>ผลลัพธ์ ({{ $results->count() }})</h3>
            <ul>
                @foreach($results as $w)
                    <li style="padding:10px 0; border-bottom:1px solid #f1f5f9;">
                        <strong>{{ $w->product_name }}</strong> — Serial: {{ $w->serial_number }} — หมดประกัน: {{ optional($w->warranty_expires_at)->format('Y-m-d') }}
                        <div>ลูกค้า: {{ $w->user?->name ?? 'ไม่ระบุ' }} ({{ $w->user?->email ?? $w->user_id }})</div>
                        <div style="margin-top:6px;">Notes: {{ $w->notes ?? '-' }}</div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
@endsection
