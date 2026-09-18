@extends('layouts.app')

@section('content')
<div class="card">
    <h2>รายการแจ้งซ่อม</h2>

    <div style="margin-bottom:12px; display:flex; gap:8px;">
        <a href="{{ route('repairs.create') }}" class="button-primary">แจ้งซ่อมใหม่</a>
        <form method="GET" style="display:inline-block;">
            <select name="status" onchange="this.form.submit()">
                <option value="">สถานะ: ทั้งหมด</option>
                <option value="open" {{ request('status')=='open'? 'selected':'' }}>Open</option>
                <option value="in_progress" {{ request('status')=='in_progress'? 'selected':'' }}>In Progress</option>
                <option value="closed" {{ request('status')=='closed'? 'selected':'' }}>Closed</option>
            </select>
        </form>
    </div>

    <div style="display:grid; gap:14px;">
        @foreach ($repairs as $r)
            <a href="{{ route('repairs.show', ['id' => $r->id]) }}" style="display:block; padding:18px; border:1px solid #cbd5e1; border-radius:18px; background:#ffffff; text-decoration:none; color:inherit; box-shadow:0 10px 24px rgba(15,23,42,0.06); transition:transform 0.15s ease, box-shadow 0.15s ease;">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px; flex-wrap:wrap;">
                    <div>
                        <div style="font-size:1rem; font-weight:700; color:#111827;">แจ้งซ่อม #{{ $r->id }}</div>
                        <div style="margin-top:6px; color:#475569;">ผู้แจ้ง: {{ $r->name ?? ($r->user->name ?? 'ไม่ระบุ') }}</div>
                        <div style="margin-top:4px; color:#475569;">อุปกรณ์: {{ trim(($r->device_type ?? '') . ' ' . ($r->device_model ?? '')) ?: '-' }}</div>
                    </div>
                    <div style="text-align:right; min-width:160px;">
                        <div style="font-size:0.95rem; color:#64748b;">{{ $r->created_at->format('Y-m-d H:i') }}</div>
                        <div style="margin-top:8px; font-weight:700; color:#111827;">{{ $r->status }}</div>
                        @if(auth()->check() && auth()->user()->role === 'admin' && $r->quote_status !== 'accepted')
                            <div style="font-size:0.85rem; color:#475569;">({{ $r->quote_status }})</div>
                        @endif
                    </div>
                </div>
                <div style="margin-top:14px; display:flex; justify-content:space-between; gap:12px; align-items:center; flex-wrap:wrap;">
                    <div style="color:#334155;">
                        @if ($r->updates && $r->updates->count())
                            ล่าสุด: {{ $r->updates->last()->created_at->format('Y-m-d H:i') }} ({{ $r->updates->count() }} ครั้ง)
                        @else
                            ยังไม่มีการอัปเดต
                        @endif
                    </div>
                    <div style="padding:8px 14px; border-radius:9999px; background:#eff6ff; color:#1d4ed8; font-weight:700; white-space:nowrap;">ดูรายละเอียด</div>
                </div>
            </a>
        @endforeach
    </div>

    <div style="margin-top:16px;">{{ $repairs->links() }}</div>
</div>
@endsection
