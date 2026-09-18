@extends('layouts.app')

@section('content')
<div class="card">
    <a href="{{ route('technician.dashboard') }}" class="button-secondary" style="margin-bottom:12px; display:inline-block;">ย้อนกลับ</a>
    <h2>รายละเอียดคำสั่งซื้อ (งานช่าง) #{{ $order->id }}</h2>

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
            <h3>การส่งคลิปเทส</h3>
            <p>อัปโหลดคลิปทดสอบหรือวิดีโอสรุปงานเพื่อให้แอดมิน/ลูกค้าตรวจสอบ</p>

            <form method="POST" action="{{ route('technician.order.upload', ['id' => $order->id]) }}" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label>ไฟล์คลิป (วิดีโอ)</label>
                    <input type="file" name="clip" accept="video/*" required>
                </div>
                <div class="form-group">
                    <label>ข้อความอธิบาย (ไม่บังคับ)</label>
                    <textarea name="message" rows="3"></textarea>
                </div>
                <div style="margin-top:8px;"><button class="button-primary" type="submit">อัปโหลดคลิป</button></div>
            </form>
        </div>
    </div>
</div>
@endsection
