@extends('layouts.app')

@section('content')
<div class="card">
    <p class="section-heading">หมวด {{ $categoryLabel }}</p>
    <p>{{ $description }}</p>

    @if ($section === 'prebuilt' && ! empty($vendor))
        <div style="margin-bottom:16px; display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
            <span style="font-weight:700; color:#2563eb;">กรอง vendor:</span>
            <span style="background:#eff6ff; color:#1d4ed8; padding:6px 10px; border-radius:999px;">{{ strtoupper($vendor) }}</span>
        </div>
    @endif

    @if ($products->isEmpty())
        <div style="padding:24px; border:1px dashed #cbd5e1; border-radius:12px; color:#475569;">ยังไม่มีสินค้าที่ตรงกับหมวดนี้ในระบบ</div>
    @else
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:18px; margin-top:18px;">
            @foreach ($products as $product)
                <div style="border:1px solid #e2e8f0; border-radius:14px; padding:18px; background:#ffffff; box-shadow:0 8px 20px rgba(15,23,42,0.04); display:flex; flex-direction:column; justify-content:space-between;">
                    <div>
                        <div style="font-size:1.1rem; font-weight:800; margin-bottom:10px;">{{ $product->name }}</div>
                        <div style="color:#475569; line-height:1.6; min-height:60px;">{{ $product->details ?? '-' }}</div>
                    </div>

                    <div style="margin-top:16px; display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap;">
                        <div>
                            <div style="font-weight:700; color:#0f766e;">{{ $product->stock > 0 ? 'สต๊อค ' . $product->stock . ' ชิ้น' : 'สินค้าหมด' }}</div>
                            <div style="color:#334155; margin-top:4px;">ราคา {{ number_format($product->price, 2) }} บาท</div>
                        </div>
                        <div style="display:flex; gap:8px; flex-wrap:wrap;">
                            <a href="{{ route('products.item', ['id' => $product->id]) }}" class="button-secondary">ดูรายละเอียด</a>
                            <form method="POST" action="{{ route('cart.add.product') }}" style="margin:0;">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <button type="submit" class="button-primary" {{ $product->stock <= 0 ? 'disabled' : '' }}>เพิ่มในตะกร้า</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="form-actions" style="margin-top:22px; display:flex; gap:12px; justify-content:flex-end; flex-wrap:wrap;">
        <a href="{{ route('dashboard') }}" class="button-secondary">กลับไปหน้าหมวดหมู่</a>
        <a href="{{ route('cart') }}" class="button-primary">ไปยังตะกร้า (ดู/ชำระเงิน)</a>
    </div>
</div>
@endsection
