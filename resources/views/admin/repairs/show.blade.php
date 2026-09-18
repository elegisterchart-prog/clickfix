@extends('layouts.app')

@section('content')
<div class="card">
    <a href="{{ route('admin.dashboard') }}" class="button-secondary" style="margin-bottom:12px; display:inline-block;">ย้อนกลับ</a>
    <h2>รายละเอียดแจ้งซ่อม (สำหรับแอดมิน) #{{ $repair->id }}</h2>

    <div style="display:flex; gap:20px; margin-top:12px;">
        <div style="flex:1;">
            <h3>ข้อมูลผู้แจ้ง</h3>
            <div>ชื่อ: {{ $repair->name ?? ($repair->user->name ?? '-') }}</div>
            <div>อีเมล: {{ $repair->email ?? ($repair->user->email ?? '-') }}</div>
            <div>เบอร์: {{ $repair->phone ?? '-' }}</div>

            <h3 style="margin-top:16px;">ข้อมูลอุปกรณ์</h3>
            <div>ประเภท: {{ $repair->device_type ?? '-' }}</div>
            <div>รุ่น: {{ $repair->device_model ?? '-' }}</div>
            <div>Serial: {{ $repair->serial_number ?? '-' }}</div>

            <h3 style="margin-top:16px;">รายละเอียด</h3>
            <div style="background:#f8fafc; padding:12px; border-radius:8px;">{{ $repair->details ?? '-' }}</div>

            <h3 style="margin-top:16px;">ไฟล์แนบตัวอย่าง</h3>
            <div>
                @if ($repair->attachments && count($repair->attachments))
                    <div style="display:flex; flex-wrap:wrap; gap:10px; margin-top:8px;">
                        @foreach ($repair->attachments as $i => $path)
                            <div style="width:160px; border:1px solid #e6e6e6; padding:6px; border-radius:6px; background:#fff; text-align:center;">
                                @php $url = Storage::url($path); $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION)); @endphp
                                @if (in_array($ext, ['jpg','jpeg','png','webp','heic']))
                                    <a href="{{ route('repairs.download', ['id' => $repair->id, 'index' => $i]) }}" target="_blank"><img src="{{ $url }}" alt="attachment-{{ $i }}" style="max-width:100%; height:100px; object-fit:cover; border-radius:4px;"></a>
                                @else
                                    <div style="height:100px; display:flex; align-items:center; justify-content:center;">ไฟล์ {{ strtoupper($ext) }}</div>
                                @endif
                                <div style="margin-top:8px; font-size:0.9rem;"><a href="{{ route('repairs.download', ['id' => $repair->id, 'index' => $i]) }}">ดาวน์โหลด</a></div>
                            </div>
                        @endforeach
                    </div>
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
        </div>

        <div style="width:320px;">
            <h3>การจัดการ (Admin)</h3>
            <div>สถานะปัจจุบัน: <strong>{{ $repair->status }}</strong></div>
            <div style="margin-top:8px;">สถานะเสนอราคา: <strong>{{ $repair->quote_status }}</strong></div>
            <div style="margin-top:12px;">
                <form method="POST" action="{{ route('admin.repairs.quote', ['id' => $repair->id]) }}">
                    @csrf
                    <div class="form-group">
                        <label>ราคาเสนอ (บาท)</label>
                        <input type="number" step="0.01" min="0" name="quote_price" value="{{ old('quote_price', $repair->quote_price) }}" required>
                    </div>
                    <div class="form-group" style="margin-top:10px;">
                        <label>ข้อความถึงลูกค้า</label>
                        <textarea name="quote_message" rows="3">{{ old('quote_message', $repair->quote_message) }}</textarea>
                    </div>
                    <div style="margin-top:8px;"><button class="button-primary" type="submit">ส่งใบเสนอราคา</button></div>
                </form>
            </div>

            <div style="margin-top:16px;">
                <form method="POST" action="{{ route('admin.repairs.assign', ['id' => $repair->id]) }}">
                    @csrf
                    <div class="form-group" style="margin-top:10px;">
                        <label>มอบหมายให้ (อีเมลช่าง)</label>
                        <input type="email" name="assigned_to" value="{{ $repair->assigned_to ?? '' }}" placeholder="ใส่อีเมลช่าง เช่น tech@example.com">
                    </div>
                    <div style="margin-top:8px;"><button class="button-primary" type="submit">มอบหมายงาน</button></div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
