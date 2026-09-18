@extends('layouts.app')

@section('content')
<div class="card">
    <a href="{{ route('repairs.index') }}" class="button-secondary" style="margin-bottom:12px; display:inline-block;">ย้อนกลับ</a>
    <h2>รายละเอียดแจ้งซ่อม #{{ $repair->id }}</h2>

    <div style="display:flex; flex-wrap:wrap; gap:16px; margin-top:16px;">
        <div style="flex:1; min-width:220px; padding:16px; border:1px solid #e2e8f0; border-radius:16px; background:#f8fafc;">
            <div style="font-weight:700; margin-bottom:8px;">สถานะล่าสุด</div>
            <div style="font-size:1.05rem; color:#111827;">{{ ucfirst(str_replace('_', ' ', $repair->status)) }}</div>
            <div style="margin-top:6px; color:#475569;">สถานะเสนอราคา: {{ $repair->quote_status }}</div>
            @if ($repair->quote_price !== null)
                <div style="margin-top:6px; color:#475569;">ราคาเสนอ: {{ number_format($repair->quote_price, 2) }} บาท</div>
            @endif
        </div>
        <div style="flex:1; min-width:260px; padding:16px; border:1px solid #e2e8f0; border-radius:16px; background:#eff6ff;">
            <div style="font-weight:700; margin-bottom:8px;">อัปเดตจากช่าง</div>
            @if ($repair->updates && $repair->updates->count())
                @php $latestUpdate = $repair->updates->last(); @endphp
                <div style="font-size:1rem; color:#111827;">{{ $latestUpdate->status ? ucfirst(str_replace('_', ' ', $latestUpdate->status)) : 'อัปเดตล่าสุด' }}</div>
                <div style="margin-top:6px; color:#475569;">{{ $latestUpdate->message ?? 'ไม่มีรายละเอียดเพิ่มเติม' }}</div>
                <div style="margin-top:10px; font-size:0.85rem; color:#475569;">โดย {{ $latestUpdate->user?->name ?? 'เจ้าหน้าที่' }} เมื่อ {{ $latestUpdate->created_at->format('Y-m-d H:i') }}</div>
            @else
                <div style="color:#475569;">ยังไม่มีการอัปเดตจากช่าง</div>
            @endif
        </div>
    </div>

    <div style="display:flex; gap:20px; margin-top:20px;">
        <div style="flex:1;">
            <h3>ข้อมูลผู้แจ้ง</h3>
            <div>ชื่อ: {{ $repair->name ?? ($repair->user->name ?? '-') }}</div>
            <div>อีเมล: {{ $repair->email ?? '-' }}</div>
            <div>เบอร์: {{ $repair->phone ?? '-' }}</div>

            <h3 style="margin-top:16px;">ข้อมูลอุปกรณ์</h3>
            <div>ประเภท: {{ $repair->device_type }}</div>
            <div>รุ่น: {{ $repair->device_model ?? '-' }}</div>
            <div>Serial: {{ $repair->serial_number ?? '-' }}</div>

            <h3 style="margin-top:16px;">รายละเอียด</h3>
            <div style="background:#f8fafc; padding:12px; border-radius:8px;">{{ $repair->details ?? '-' }}</div>

            <h3 style="margin-top:16px;">สถานะการเสนอราคา</h3>
            <div style="background:#f1f5f9; padding:12px; border-radius:8px;">
                <div>สถานะเสนอราคา: <strong>{{ $repair->quote_status }}</strong></div>
                <div>ราคาเสนอ: {{ $repair->quote_price !== null ? number_format($repair->quote_price, 2) . ' บาท' : 'ยังไม่มีการเสนอราคา' }}</div>
                <div>ข้อความจากเจ้าหน้าที่: {{ $repair->quote_message ?? 'ไม่มีข้อความ' }}</div>
            </div>

            <h3 style="margin-top:16px;">ที่อยู่ / เวลาติดต่อ</h3>
            <div>{{ $repair->address ?? '-' }}</div>
            <div>{{ $repair->preferred_contact_time ?? '-' }}</div>

            <h3 style="margin-top:16px;">ไฟล์แนบ</h3>
            <div>
                @if ($repair->attachments)
                    <ul>
                        @foreach ($repair->attachments as $i => $f)
                            <li><a href="{{ route('repairs.download', ['id' => $repair->id, 'index' => $i]) }}">ดาวน์โหลดไฟล์ #{{ $i+1 }}</a></li>
                        @endforeach
                    </ul>
                @else
                    <div>ไม่มีไฟล์แนบ</div>
                @endif
            </div>

            <h3 style="margin-top:16px;">ประวัติการอัปเดตงาน</h3>
            <div>
                @if ($repair->updates && $repair->updates->count())
                    <ul>
                        @foreach ($repair->updates as $u)
                            <li style="margin-bottom:12px; padding:8px; border:1px solid #eef2ff; border-radius:6px;">
                                <div style="font-weight:600;">@if($u->status) สถานะ: {{ $u->status }} @endif</div>
                                <div style="font-size:0.95rem; color:#374151; margin-top:6px;">{{ $u->message ?? '-' }}</div>
                                <div style="margin-top:6px;">
                                    @if ($u->attachments)
                                        <ul>
                                            @foreach ($u->attachments as $i => $p)
                                                <li><a href="{{ route('repairs.update.download', ['repairId' => $repair->id, 'updateId' => $u->id, 'index' => $i]) }}" target="_blank">ไฟล์อัปเดต #{{ $i+1 }}</a></li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                                <div style="font-size:0.85rem; color:#6b7280; margin-top:6px;">โดย: {{ $u->user?->name ?? 'เจ้าหน้าที่' }} เมื่อ {{ $u->created_at->format('Y-m-d H:i') }}</div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div>ยังไม่มีการอัปเดต</div>
                @endif
            </div>
 
            @if (auth()->check() && auth()->user()->id === $repair->user_id && $repair->quote_status === 'sent_to_customer')
                <div style="margin-top:18px; padding:14px; border:1px solid #dbeafe; border-radius:12px; background:#eff6ff;">
                    <h3>รอการยืนยันการซ่อม</h3>
                    <p>เจ้าหน้าที่ได้ส่งใบเสนอราคาให้คุณแล้ว กรุณาตรวจสอบและยืนยันการซ่อมเพื่อให้ระบบสามารถมอบหมายงานให้ช่างได้</p>
                    <p><strong>ราคาเสนอ:</strong> {{ number_format($repair->quote_price ?? 0, 2) }} บาท</p>
                    <p><strong>ข้อความจากเจ้าหน้าที่:</strong> {{ $repair->quote_message ?? 'ไม่มีข้อความ' }}</p>
                    <form method="POST" action="{{ route('repairs.accept', ['id' => $repair->id]) }}">
                        @csrf
                        <button type="submit" class="button-primary">ยืนยันการซ่อม</button>
                    </form>
                </div>
            @elseif (auth()->check() && auth()->user()->id === $repair->user_id && $repair->quote_status === 'awaiting_admin')
                <div style="margin-top:18px; padding:14px; border:1px solid #f8d7da; border-radius:12px; background:#fff1f2;">
                    <h3>รอใบเสนอราคา</h3>
                    <p>เราได้รับคำแจ้งซ่อมของคุณแล้ว เจ้าหน้าที่จะเสนอราคาให้ภายในเร็ว ๆ นี้</p>
                </div>
            @endif
 
        </div>

    </div>
</div>
@endsection
