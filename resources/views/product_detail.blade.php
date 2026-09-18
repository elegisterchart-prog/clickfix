@extends('layouts.app')

@section('content')
<div class="card">
    <a href="{{ url()->previous() }}" class="button-secondary" style="margin-bottom:12px; display:inline-block;">ย้อนกลับ</a>

    <h2 style="margin-top:0;">{{ $item['name'] }}</h2>
    <div style="display:flex; gap:20px; align-items:flex-start; flex-wrap:wrap; margin-top:12px;">
        <div style="flex:1; min-width:320px;">
            <div style="padding:18px; border:1px solid #e2e8f0; border-radius:16px; background:#f8fafc;">
                <div style="font-size:1.7rem; font-weight:800; margin-bottom:10px;">{{ $item['name'] }}</div>
                <div style="font-weight:700; color:#0f766e; margin-bottom:10px;">ราคา {{ number_format($item['price'] ?? 0, 2) }} บาท</div>
                <div style="color:#475569; margin-bottom:10px;">{{ $item['details'] ?? 'ไม่มีรายละเอียดเพิ่มเติม' }}</div>
                <div style="font-weight:700; color:{{ isset($item['stock']) && $item['stock'] > 0 ? '#065f46' : '#b91c1c' }};">
                    {{ isset($item['stock']) ? ($item['stock'] > 0 ? 'สต๊อค: '.$item['stock'].' ชิ้น' : 'สินค้าหมด') : 'จำนวนสินค้าไม่แน่นอน' }}
                </div>
            </div>
        </div>

        <div style="flex:1; min-width:320px;">
            <h3>สเป็คสินค้า</h3>
            <div style="background:#ffffff; padding:18px; border:1px solid #e2e8f0; border-radius:16px;">
                @php
                    $keys = ['cores','threads','base_clock','boost_clock','socket','cache','chipset','vram','tdp','type','speed','capacity','read','write','wattage','efficiency'];
                    $hasSpecs = false;
                @endphp

                @foreach ($keys as $k)
                    @if (isset($item[$k]))
                        @php $hasSpecs = true; @endphp
                        <div style="padding:8px 0; border-bottom:1px solid #f1f5f9;">
                            <strong>{{ ucfirst(str_replace('_',' ',$k)) }}:</strong> {{ is_array($item[$k]) ? implode(', ', (array)$item[$k]) : $item[$k] }}
                        </div>
                    @endif
                @endforeach

                @if (! $hasSpecs)
                    <div style="color:#475569;">ไม่มีข้อมูลสเป็คเพิ่มเติมสำหรับสินค้ารายการนี้</div>
                @endif
            </div>
        </div>
    </div>

    <div style="margin-top:18px; display:flex; gap:12px; flex-wrap:wrap;">
        <form method="POST" action="{{ route('cart.add.product') }}">
            @csrf
            <input type="hidden" name="product_id" value="{{ $item['id'] }}">
            <button type="submit" class="button-primary" {{ (isset($item['stock']) && $item['stock'] <= 0) ? 'disabled' : '' }}>เพิ่มในตะกร้า</button>
        </form>
        <a href="{{ route('products.category', ['section' => $item['category'] ?? 'cpu']) }}" class="button-secondary">กลับไปหน้าหมวด</a>
    </div>
</div>
@endsection
