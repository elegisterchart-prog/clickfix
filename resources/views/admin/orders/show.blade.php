@extends('layouts.app')

@section('content')
<div class="card">
    <a href="{{ route('admin.orders.index') }}" class="button-secondary" style="margin-bottom:12px; display:inline-block;">ย้อนกลับ</a>
    <h2>รายละเอียดคำสั่งซื้อ #{{ $order->id }}</h2>
    <div style="display:flex; gap:20px; flex-wrap:wrap; margin-top:12px;">
        <div style="flex:1; min-width:280px;">
            <h3>ข้อมูลลูกค้า</h3>
            <div>ชื่อ: {{ $order->name ?? ($order->user?->name ?? '-') }}</div>
            <div>อีเมล: {{ $order->email ?? ($order->user?->email ?? '-') }}</div>
            <div>เบอร์: {{ $order->phone ?? '-' }}</div>

            <h3 style="margin-top:16px;">รายการสินค้า</h3>
            <div style="background:#f8fafc; padding:16px; border-radius:16px; border:1px solid #e2e8f0;">
                @foreach ($order->items as $item)
                    <div style="margin-bottom:16px; padding-bottom:12px; border-bottom:1px solid #e2e8f0;">
                        <strong>{{ $item['name'] }}</strong>
                        <div style="font-size:0.95rem; color:#475569; margin-top:4px;">
                            จำนวน: {{ count($item['components']) }} ชิ้น · {{ number_format($item['total'], 2) }} บาท
                        </div>
                        @if (!empty($item['components']) && is_array($item['components']))
                            <div style="margin-top:10px; padding:12px; background:#ffffff; border-radius:12px; border:1px solid #e2e8f0;">
                                @foreach ($item['components'] as $component)
                                    <div style="margin-bottom:8px;">
                                        <span style="font-weight:600;">{{ $component['section'] ?? '-' }}</span>
                                        <span style="color:#475569;">: {{ $component['option']['name'] ?? '-' }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <div style="width:320px;">
            <h3>การจัดการ</h3>
            <div>สถานะ: <strong>{{ $order->status }}</strong></div>
            <div style="margin-top:8px;">มอบหมายให้: <strong>{{ $order->assigned_to ?? '-' }}</strong></div>
            <div style="margin-top:18px; padding:16px; border-radius:16px; background:#f8fafc; border:1px solid #e2e8f0;">
                <form method="POST" action="{{ route('admin.orders.assign', ['id' => $order->id]) }}">
                    @csrf
                    <div class="form-group">
                        <label>มอบหมายให้ (อีเมลช่าง)</label>
                        <input type="email" name="assigned_to" value="{{ $order->assigned_to ?? '' }}" placeholder="เช่น tech@example.com">
                    </div>
                    <button class="button-primary" type="submit">บันทึกการมอบหมาย</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
