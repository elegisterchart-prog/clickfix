@extends('layouts.app')

@section('content')
<div class="card">
    <a href="{{ route('orders.index') }}" class="button-secondary" style="margin-bottom:12px; display:inline-block;">ย้อนกลับ</a>
    <h2>รายละเอียดคำสั่งซื้อ #{{ $order->id }}</h2>

    <div style="display:flex; gap:20px; margin-top:12px; flex-wrap:wrap;">
        <div style="flex:1; min-width:280px;">
            <h3>ข้อมูลลูกค้า</h3>
            <div>ชื่อ: {{ $order->name ?? ($order->user?->name ?? '-') }}</div>
            <div>อีเมล: {{ $order->email ?? ($order->user?->email ?? '-') }}</div>
            <div>เบอร์: {{ $order->phone ?? '-' }}</div>

            <h3 style="margin-top:16px;">รายการ</h3>
            <div style="background:#f8fafc; padding:12px; border-radius:8px;">
                @foreach ($order->items as $item)
                    <div style="margin-bottom:16px; padding-bottom:12px; border-bottom:1px solid #dbeafe;">
                        <strong>{{ $item['name'] }}</strong>
                        <div style="color:#475569; font-size:0.95rem; margin-top:4px;">จำนวน: {{ count($item['components']) }} · {{ number_format($item['total'], 2) }} บาท</div>
                        @if (!empty($item['components']) && is_array($item['components']))
                            <div style="margin-top:10px; margin-left:6px;">
                                @foreach ($item['components'] as $component)
                                    <div style="margin-bottom:6px;">
                                        <span style="font-weight:600;">{{ $component['section'] ?? '-' }}</span>
                                        <span style="color:#475569;">: {{ $component['option']['name'] ?? '-' }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <h3 style="margin-top:16px;">ไฟล์ที่อัปโหลด</h3>
            <div>
                @if ($order->attachments && count($order->attachments))
                    <ul>
                        @foreach ($order->attachments as $i => $p)
                            <li><a href="{{ Storage::url($p) }}" target="_blank">ไฟล์ที่ {{ $i + 1 }}</a></li>
                        @endforeach
                    </ul>
                @else
                    <div>ยังไม่มีไฟล์</div>
                @endif
            </div>
        </div>

        <div style="width:360px;">
            <h3>สถานะคำสั่งซื้อ</h3>
            <div style="padding:12px; border-radius:8px; background:#fff; border:1px solid #e2e8f0;">
                <strong>{{ $order->status }}</strong>
                <div style="margin-top:8px; color:#64748b;">วันที่สั่งซื้อ: {{ $order->created_at?->format('Y-m-d H:i') ?? '-' }}</div>
                <div style="margin-top:8px;">หมายเหตุ: {{ $order->notes ?? '-' }}</div>

                @if (($order->status ?? '') !== 'cancelled')
                    <form method="POST" action="{{ route('orders.cancel', ['id' => $order->id]) }}" style="margin-top:16px;">
                        @csrf
                        <button type="submit" class="button-danger" onclick="return confirm('คุณต้องการยกเลิกคำสั่งซื้อนี้หรือไม่?')">
                            ยกเลิกการสั่งซื้อ
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
