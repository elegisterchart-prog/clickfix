@extends('layouts.app')

@section('content')
<div class="card" style="margin-top: 24px;">
    <div style="display:flex; justify-content:space-between; align-items:flex-end; gap:16px; flex-wrap:wrap; margin-bottom:20px;">
        <div>
            <div class="eyebrow" style="margin-bottom:12px;">Marketplace</div>
            <h1 style="margin:0; font-size:clamp(2rem,3vw,2.8rem); letter-spacing:-0.06em; color:#0f172a;">หมวดหมู่สินค้า</h1>
        </div>
        <a href="{{ route('specs') }}" class="button-primary">สร้างสเป็คคอมพิวเตอร์</a>
    </div>

    <p style="margin:0 0 24px; color:#475569; line-height:1.8; max-width:860px;">เลือกหมวดสินค้าที่ต้องการเพื่อดูราคาและสต็อก จากนั้นเพิ่มสินค้าลงตะกร้า หรือเริ่มสร้างสเป็คพรีเมียมที่ออกแบบให้ตรงกับการใช้งานของคุณ.</p>

    <div class="service-grid">
        @foreach ($sections as $section)
            <div class="service-card">
                <div>
                    <h2>{{ $section['title'] }}</h2>
                    <p>{{ $section['description'] }}</p>
                </div>
                <a href="{{ route('products.category', ['section' => $section['key']]) }}" class="button-primary">ดู {{ $section['title'] }}</a>
            </div>
        @endforeach
        <div class="service-card" style="background: linear-gradient(135deg, rgba(37,99,235,0.06), rgba(139,92,246,0.05)); border-color: rgba(37,99,235,0.14);">
            <div>
                <h2>สร้างสเป็คคอมพิวเตอร์</h2>
                <p>ใช้ระบบจัดสเป็คทีละส่วน เพื่อสร้างเครื่องที่ครบทุกหมวดตามความต้องการของคุณ.</p>
            </div>
            <a href="{{ route('specs') }}" class="button-secondary">จัดสเป็คเอง</a>
        </div>
    </div>
</div>
@endsection
