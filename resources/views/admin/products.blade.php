@extends('layouts.app')

@section('content')
<div class="card">
    <h2>จัดการสินค้า</h2>
    <p>จัดการสินค้าตามหมวดหมู่จริงของระบบ (CPU, Motherboard, GPU, RAM, Storage, PSU, คอมเซ็ต)</p>

    <form method="POST" action="{{ route('admin.products.add') }}" style="display:grid; gap:10px; margin-top:12px;">
        @csrf
        <div style="display:flex; gap:8px; flex-wrap:wrap;">
            <input type="text" name="name" placeholder="ชื่อสินค้า" style="flex:2; min-width:220px; padding:10px 12px; border-radius:8px; border:1px solid #cbd5e1;">
            <input type="text" name="sku" placeholder="SKU (ไม่บังคับ)" style="flex:1; min-width:180px; padding:10px 12px; border-radius:8px; border:1px solid #cbd5e1;">
        </div>
        <div style="display:flex; gap:8px; flex-wrap:wrap;">
            <select name="category" style="flex:1; min-width:180px; padding:10px 12px; border-radius:8px; border:1px solid #cbd5e1;">
                @foreach($categories as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
            <input type="number" step="0.01" min="0" name="price" placeholder="ราคา" style="flex:1; min-width:120px; padding:10px 12px; border-radius:8px; border:1px solid #cbd5e1;">
            <input type="number" min="0" name="stock" placeholder="จำนวนสต็อก" style="flex:1; min-width:120px; padding:10px 12px; border-radius:8px; border:1px solid #cbd5e1;">
        </div>
        <textarea name="details" placeholder="คำอธิบายสินค้า (ไม่บังคับ)" rows="2" style="width:100%; padding:10px 12px; border-radius:8px; border:1px solid #cbd5e1;"></textarea>
        <button class="button-primary" style="max-width:180px;">เพิ่มสินค้า</button>
    </form>

    <div style="margin-top:18px;">
        @if($products->isEmpty())
            <div>ยังไม่มีสินค้าในระบบ</div>
        @else
            <div style="display:grid; gap:12px;">
                @foreach($products as $product)
                    <div style="padding:14px; border:1px solid #e2e8f0; border-radius:12px; display:grid; gap:8px;">
                        <div style="display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap; align-items:center;">
                            <div>
                                <strong style="font-size:1.05rem;">{{ $product->name }}</strong>
                                <div style="color:#64748b; font-size:0.95rem;">หมวดหมู่: {{ $categories[$product->category] ?? $product->category }} • SKU: {{ $product->sku ?? '-' }}</div>
                            </div>
                            <form method="POST" action="{{ route('admin.products.remove', ['id' => $product->id]) }}">
                                @csrf
                                <button class="button-secondary">ลบ</button>
                            </form>
                        </div>
                        <div style="display:flex; gap:12px; flex-wrap:wrap; color:#334155;">
                            <div style="min-width:120px;">ราคา: {{ number_format($product->price, 2) }}</div>
                            <div style="min-width:120px;">สต็อก: {{ $product->stock }}</div>
                        </div>
                        @if($product->details)
                            <div style="color:#475569;">{{ $product->details }}</div>
                        @endif
                        <div style="color:#94a3b8; font-size:0.9rem;">เพิ่มเมื่อ: {{ $product->created_at->format('Y-m-d H:i') }}</div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
