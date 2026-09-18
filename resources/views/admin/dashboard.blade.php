@extends('layouts.app')

@section('content')
<div class="card">
    <h2>แผงควบคุมผู้ดูแลระบบ</h2>
    <p>มอบหมายงานให้ช่าง และตรวจสอบประกันลูกค้า</p>

    <div style="margin-top:18px; display:flex; gap:12px; flex-wrap:wrap;">
        <a href="{{ route('admin.warranty') }}" class="button-secondary">ตรวจสอบประกัน</a>
        <a href="{{ route('admin.users') }}" class="button-secondary">จัดการสมาชิก</a>
        <a href="{{ route('admin.products') }}" class="button-secondary">จัดการสินค้า</a>
        <a href="{{ route('admin.orders.index') }}" class="button-secondary">คำสั่งซื้อ</a>
    </div>

    <div style="margin-top:16px; padding:16px; border-radius:16px; background:#f8fafc; border:1px solid #dbeafe;">
        <strong>หมายเหตุ:</strong> ระบุชื่อช่างในช่องมอบหมายงานให้ตรงกับชื่อที่ช่างใช้ในการเข้าสู่ระบบ.
    </div>

    @if($assignedTechnicians->isNotEmpty())
        <div style="margin-top:16px; padding:16px; border-radius:16px; background:#ffffff; border:1px solid #e2e8f0;">
            <strong>ช่างที่เคยถูกมอบหมาย:</strong>
            <div style="margin-top:8px; display:flex; flex-wrap:wrap; gap:8px;">
                @foreach($assignedTechnicians as $technician)
                    <span style="padding:6px 10px; border-radius:999px; background:#eff6ff; color:#1d4ed8; font-size:0.95rem;">{{ $technician }}</span>
                @endforeach
            </div>
        </div>
    @endif

    <hr style="margin:18px 0;">

    <h3>รายการซ่อมล่าสุด</h3>

    @if($repairs->isEmpty())
        <div>ไม่มีรายการซ่อม</div>
    @else
        <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; min-width:720px;">
            <thead>
                <tr style="text-align:left; border-bottom:1px solid #e2e8f0;">
                    <th>หมายเลข</th>
                    <th>ผู้แจ้ง</th>
                    <th>เบอร์</th>
                    <th>สถานะ</th>
                    <th>มอบหมายให้</th>
                    <th>ตัวอย่างรูป</th>
                    <th>การจัดการ</th>
                </tr>
            </thead>
            <tbody>
                @foreach($repairs as $r)
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td>#{{ $r->id }}</td>
                    <td>{{ $r->name ?? ($r->user?->name ?? '-') }}</td>
                    <td>{{ $r->phone ?? '-' }}</td>
                    <td>{{ $r->status }} / {{ $r->quote_status }}</td>
                    <td>{{ $r->assigned_to ?? '-' }}</td>
                    <td>
                        @php
                            $imagePath = collect($r->attachments ?? [])->first(function ($path) {
                                return in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ['jpg','jpeg','png','webp','heic']);
                            });
                        @endphp
                        @if($imagePath)
                            <a href="{{ route('admin.repairs.show', ['id' => $r->id]) }}" target="_blank">
                                <img src="{{ Storage::url($imagePath) }}" alt="attachment-{{ $r->id }}" style="max-width:80px; max-height:60px; object-fit:cover; border-radius:6px; border:1px solid #d1d5db;">
                            </a>
                        @else
                            <span style="font-size:0.85rem; color:#6b7280;">ไม่มีภาพ</span>
                        @endif
                    </td>
                    <td>
                        <div style="display:flex; flex-direction:column; gap:8px;">
                            <a href="{{ route('admin.repairs.show', ['id' => $r->id]) }}" class="button-secondary" style="padding:8px 10px; display:inline-block;">รายละเอียด</a>
                            <form method="POST" action="{{ route('admin.repairs.assign', ['id' => $r->id]) }}" style="display:flex; flex-direction:column; gap:8px;">
                                @csrf
                                <input type="email" name="assigned_to" placeholder="อีเมลช่าง" style="padding:8px 10px; border-radius:8px; border:1px solid #cbd5e1;">
                                <button class="button-primary" type="submit">มอบหมาย</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>

        <div style="margin-top:12px;">{{ $repairs->links() }}</div>
    @endif
</div>
@endsection
