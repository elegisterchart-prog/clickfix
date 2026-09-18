@extends('layouts.app')

@section('content')
<div class="card" style="padding:28px;">
    <div style="display:flex; flex-wrap:wrap; justify-content:space-between; gap:18px; align-items:flex-start;">
        <div style="min-width:240px; flex:1;">
            <h2 style="margin-top:0;">งานซ่อม #{{ $repair->id }}</h2>
            <div style="margin-top:8px; color:#475569; line-height:1.7;">
                <div><strong>ผู้แจ้ง:</strong> {{ $repair->name ?? ($repair->user?->name ?? '-') }}</div>
                <div><strong>เบอร์:</strong> {{ $repair->phone ?? '-' }}</div>
                <div><strong>สภาพปัจจุบัน:</strong> <span style="color:#0f172a; font-weight:700;">{{ ucfirst(str_replace('_', ' ', $repair->status)) }}</span></div>
            </div>
        </div>

        <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
            <div style="padding:12px 16px; border-radius:9999px; background:#eff6ff; color:#1d4ed8; font-weight:700;">{{ ucfirst(str_replace('_', ' ', $repair->status)) }}</div>
            <form method="POST" action="{{ route('technician.logout') }}">
                @csrf
                <button class="button-secondary" type="submit">ออกจากระบบช่าง</button>
            </form>
        </div>
    </div>

    @if (session('status'))
        <div class="alert" style="margin-top:20px;">{{ session('status') }}</div>
    @endif

    <div style="display:grid; gap:20px; margin-top:20px;">
        <div style="display:grid; gap:20px; grid-template-columns:repeat(auto-fit,minmax(280px,1fr));">
            <div style="padding:22px; border:1px solid #e2e8f0; border-radius:22px; background:#ffffff;">
                <h3 style="margin-top:0;">รายละเอียดแจ้งซ่อม</h3>
                <p style="margin:10px 0 0; color:#334155; line-height:1.8;">{{ $repair->details ?? '-' }}</p>
            </div>

            <div style="padding:22px; border:1px solid #e2e8f0; border-radius:22px; background:#ffffff;">
                <h3 style="margin-top:0;">ไฟล์แนบลูกค้า</h3>
                @if ($repair->attachments)
                    <ul style="margin:12px 0 0; padding-left:18px; color:#334155;">
                        @foreach ($repair->attachments as $i => $path)
                            <li style="margin-bottom:8px;"><a href="{{ route('repairs.download', ['id' => $repair->id, 'index' => $i]) }}" style="color:#2563eb; text-decoration:none;">ไฟล์ลูกค้า #{{ $i+1 }}</a></li>
                        @endforeach
                    </ul>
                @else
                    <div style="margin-top:12px; color:#64748b;">ไม่มีไฟล์แนบ</div>
                @endif
            </div>
        </div>

        <div style="display:grid; gap:20px; grid-template-columns:1fr 1fr; align-items:start;">
            <div style="padding:22px; border:1px solid #e2e8f0; border-radius:22px; background:#ffffff;">
                <h3 style="margin-top:0;">ประวัติการอัปเดต</h3>
                @if ($repair->updates && $repair->updates->count())
                    <div style="display:grid; gap:14px; margin-top:12px;">
                        @foreach ($repair->updates as $update)
                            <div style="padding:16px; border:1px solid #dbeafe; border-radius:18px; background:#f8fafc;">
                                <div style="display:flex; justify-content:space-between; gap:8px; align-items:flex-start; flex-wrap:wrap;">
                                    <div style="font-weight:700; color:#0f172a;">{{ $update->status ? ucfirst(str_replace('_', ' ', $update->status)) : 'อัปเดตล่าสุด' }}</div>
                                    <div style="color:#475569; font-size:0.95rem;">{{ $update->created_at->format('Y-m-d H:i') }}</div>
                                </div>
                                <p style="margin:10px 0 0; color:#334155; line-height:1.7;">{{ $update->message ?? 'ไม่มีรายละเอียดเพิ่มเติม' }}</p>
                                <div style="margin-top:12px; color:#475569; font-size:0.95rem;">โดย {{ $update->user?->name ?? 'เจ้าหน้าที่' }}</div>
                                @if ($update->attachments)
                                    <div style="margin-top:12px;">
                                        <strong style="color:#0f172a;">ไฟล์อัปเดต:</strong>
                                        <ul style="margin:8px 0 0; padding-left:18px; color:#334155;">
                                            @foreach ($update->attachments as $i => $path)
                                                <li style="margin-bottom:6px;"><a href="{{ Storage::url($path) }}" target="_blank" style="color:#2563eb; text-decoration:none;">ข้อมูลงาน #{{ $i+1 }}</a></li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div style="margin-top:12px; color:#64748b;">ยังไม่มีการอัปเดต</div>
                @endif
            </div>

            <form method="POST" action="{{ route('technician.repair.update', ['id' => $repair->id]) }}" enctype="multipart/form-data" style="padding:22px; border:1px solid #e2e8f0; border-radius:22px; background:#ffffff;">
                @csrf
                <h3 style="margin-top:0;">ส่งอัปเดตงานซ่อม</h3>
                <div class="form-group">
                    <label>สถานะงาน</label>
                    <select name="status" style="width:100%;">
                        <option value="">ไม่เปลี่ยนสถานะ</option>
                        <option value="confirmed">ยืนยันการซ่อม</option>
                        <option value="in_progress">กำลังดำเนินการ</option>
                        <option value="closed">เสร็จสิ้น</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>ข้อความอัปเดต</label>
                    <textarea name="message" rows="5" placeholder="อธิบายงานที่ทำ หรือสภาพล่าสุด"></textarea>
                </div>
                <div class="form-group">
                    <label>แนบรูปหรือวิดีโอ</label>
                    <input type="file" name="attachments[]" accept="image/*,video/*" multiple>
                </div>
                <button class="button-primary" type="submit" style="width:100%;">ส่งอัปเดต</button>
            </form>
        </div>
    </div>
</div>
@endsection
