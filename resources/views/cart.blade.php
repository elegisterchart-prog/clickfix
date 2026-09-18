@extends('layouts.app')

@section('content')
<div class="card">
    <p class="section-heading">ตะกร้าสินค้า</p>
    <p class="spec-summary">ดูรายการสเป็คที่คุณเพิ่มในตะกร้าและลบรายการที่ไม่ต้องการได้</p>

    @if (count($items))
        <div class="summary-card">
            <h2>สเป็คในตะกร้า</h2>
            <ul class="summary-list">
                @foreach ($items as $item)
                    <li>
                        <div>
                            <strong>{{ $item['name'] }}</strong>
                            <span>เพิ่มเมื่อ {{ $item['created_at'] }}</span>
                            @if (! empty($item['components']) && is_array($item['components']))
                                <div style="margin-top:8px; color:#475569; font-size:0.95rem;">
                                    @foreach ($item['components'] as $component)
                                        <div>{{ $component['section'] ?? '' }}: {{ $component['option']['name'] ?? '' }}</div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="summary-meta">
                            <span>{{ number_format($item['total']) }} บาท</span>
                            <form method="POST" action="{{ route('cart.remove') }}" style="display:inline;">
                                @csrf
                                <input type="hidden" name="id" value="{{ $item['id'] }}">
                                <button type="submit" class="button-secondary" style="padding: 10px 16px;">ลบ</button>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>
            <div class="summary-total">
                <span>รวมทั้งหมด</span>
                <span>{{ number_format($total) }} บาท</span>
            </div>
        </div>
        <div class="form-actions">
            <a href="{{ route('products.category', ['section' => $sections[0]['key']]) }}" class="button-secondary">เลือกสินค้าเพิ่ม</a>
            <form method="POST" action="{{ route('cart.checkout') }}" style="display:inline;">
                @csrf
                <button type="submit" class="button-primary">สั่งซื้อและชำระเงิน</button>
            </form>
            <form method="POST" action="{{ route('cart.clear') }}" style="display:inline;">
                @csrf
                <button type="submit" class="button-secondary">ล้างตะกร้า</button>
            </form>
        </div>
    @else
        <div class="alert">ไม่มีรายการในตะกร้า</div>
        <a href="{{ route('products.category', ['section' => $sections[0]['key']]) }}" class="button-primary">เลือกสินค้าเริ่มต้น</a>
    @endif
</div>

<div class="card" style="margin-top:16px;">
    <p class="section-heading">คำสั่งซื้อของฉัน</p>

    @if (isset($orders) && $orders->isEmpty())
        <div class="alert">ยังไม่มีคำสั่งซื้อ</div>
    @elseif (isset($orders))
        <div style="overflow-x:auto; margin-top:12px;">
            <table style="width:100%; border-collapse:collapse; min-width:720px;">
                <thead>
                    <tr style="text-align:left; border-bottom:1px solid #e2e8f0;">
                        <th>หมายเลข</th>
                        <th>ยอดรวม</th>
                        <th>สถานะ</th>
                        <th>วันที่</th>
                        <th>การจัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $o)
                        <tr style="border-bottom:1px solid #f1f5f9;">
                            <td>#{{ $o->id }}</td>
                            <td>{{ number_format($o->total, 2) }} บาท</td>
                            <td>{{ $o->status }}</td>
                            <td>{{ $o->created_at?->format('Y-m-d H:i') ?? '-' }}</td>
                            <td><a href="{{ route('orders.show', ['id' => $o->id]) }}" class="button-secondary">ดู</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="margin-top:12px;">
            <a href="{{ route('orders.index') }}" class="button-secondary">ดูคำสั่งซื้อทั้งหมด</a>
        </div>
    @endif
</div>

@endsection