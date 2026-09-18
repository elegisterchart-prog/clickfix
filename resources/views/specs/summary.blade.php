@extends('layouts.app')

@section('content')
<div class="card">
        <div style="display:flex; flex-wrap:wrap; justify-content:space-between; gap:12px; align-items:flex-start;">
            <div>
                <h2>สรุปสเปค</h2>
                <p style="max-width:620px; color:#475569;">ทำแพลนสเปคทีละขั้นตอน เลือกชิ้นส่วนหลักให้ครบ แล้วเพิ่มสเปคทั้งหมดลงตะกร้าเพื่อสั่งประกอบ</p>
            </div>
            <div style="background:#eef2ff; color:#3730a3; padding:10px 14px; border-radius:10px; min-width:200px; font-size:0.95rem;">
                เลือกทั้งหมด {{ count($sections) }} หมวด
            </div>
        </div>

        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:16px; margin-top:24px;">
            @foreach ($sections as $index => $s)
                <a href="{{ route('specs.section', ['section' => $s['key']]) }}" style="text-decoration:none; color:inherit;">
                    <div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:14px; padding:18px; box-shadow:0 8px 20px rgba(15,23,42,0.06); height:100%; display:flex; flex-direction:column; justify-content:space-between;">
                        <div>
                            <div style="display:inline-flex; align-items:center; gap:8px; margin-bottom:10px; font-size:0.9rem; color:#0f766e; font-weight:700;">
                                <span style="display:inline-flex; width:28px; height:28px; align-items:center; justify-content:center; background:#d1fae5; color:#065f46; border-radius:999px;">{{ $index + 1 }}</span>
                                หมวด {{ $index + 1 }}
                            </div>
                            <div style="font-size:1.1rem; font-weight:800; margin-bottom:10px;">{{ $s['title'] }}</div>
                            <div style="color:#64748b; line-height:1.6;">{{ $s['description'] ?? 'เลือกชิ้นส่วนหลักสำหรับประกอบเครื่องของคุณ' }}</div>
                        </div>
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-top:16px;">
                            <span style="font-size:0.9rem; color:#64748b;">{{ count($s['options'] ?? []) }} ตัวเลือก</span>
                            <span style="color:#2563eb; font-weight:700;">เริ่มเลือก</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
</div>
@endsection
