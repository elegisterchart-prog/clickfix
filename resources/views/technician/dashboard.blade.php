@extends('layouts.app')

@section('content')
@php
    $items = $repairs->getCollection();
    $statusCounts = $items->groupBy(fn ($repair) => strtolower($repair->status ?? 'pending'))->map->count();
    $statusMap = [
        'pending' => 'รอรับงาน',
        'in_progress' => 'กำลังดำเนินการ',
        'completed' => 'เสร็จสิ้น',
        'closed' => 'ปิดงาน',
    ];
@endphp

<div class="technician-dashboard">
    <section class="tech-hero">
        <div>
            <div class="tech-eyebrow">พื้นที่ช่าง</div>
            <h2>ศูนย์ควบคุมงานซ่อม</h2>
            <p>สวัสดี {{ $technician ? $technician : 'ช่าง' }} — ตรวจสอบงานที่ถูกมอบหมาย ติดตามสถานะ และอัปเดตความคืบหน้าให้ลูกค้าทราบแบบเรียลไทม์</p>
        </div>
        <div class="tech-actions">
            <a href="{{ route('technician.logout') }}" class="button-secondary" onclick="event.preventDefault(); document.getElementById('technician-logout-form').submit();">ออกจากระบบ</a>
            <form id="technician-logout-form" method="POST" action="{{ route('technician.logout') }}" style="display:none;">@csrf</form>
        </div>
    </section>

    <section class="stats-grid">
        <div class="stat-card">
            <span class="stat-label">งานทั้งหมด</span>
            <strong>{{ $repairs->total() }}</strong>
        </div>
        <div class="stat-card">
            <span class="stat-label">รอรับงาน</span>
            <strong>{{ $statusCounts['pending'] ?? 0 }}</strong>
        </div>
        <div class="stat-card">
            <span class="stat-label">กำลังดำเนินการ</span>
            <strong>{{ $statusCounts['in_progress'] ?? 0 }}</strong>
        </div>
        <div class="stat-card">
            <span class="stat-label">เสร็จสิ้น/ปิดงาน</span>
            <strong>{{ ($statusCounts['completed'] ?? 0) + ($statusCounts['closed'] ?? 0) }}</strong>
        </div>
    </section>

    <div class="card tech-card">
        <div class="card-header">
            <div>
                <h3>รายการซ่อมที่มอบหมาย</h3>
                <p>คลิกปุ่ม “เปิดงาน” เพื่ออัปเดตสถานะและแนบรูปภาพ/วิดีโอ</p>
            </div>
        </div>

        @if ($repairs->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">🛠️</div>
                <h4>ยังไม่มีงานซ่อมที่ได้รับมอบหมาย</h4>
                <p>เมื่อมีงานใหม่จะปรากฏที่นี่โดยอัตโนมัติ</p>
            </div>
        @else
            <div class="table-wrap">
                <table class="tech-table">
                    <thead>
                        <tr>
                            <th>หมายเลข</th>
                            <th>ผู้แจ้ง</th>
                            <th>เบอร์</th>
                            <th>สถานะ</th>
                            <th>วันที่</th>
                            <th>การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($repairs as $r)
                            <tr>
                                <td>#{{ $r->id }}</td>
                                <td>{{ $r->name ?? ($r->user?->name ?? '-') }}</td>
                                <td>{{ $r->phone ?? '-' }}</td>
                                <td>
                                    <span class="status-pill {{ strtolower($r->status ?? 'pending') }}">
                                        {{ $statusMap[strtolower($r->status ?? 'pending')] ?? ucfirst($r->status ?? 'pending') }}
                                    </span>
                                </td>
                                <td>{{ $r->created_at?->format('Y-m-d H:i') ?? '-' }}</td>
                                <td>
                                    <a class="button-primary compact-button" href="{{ route('technician.repair.show', ['id' => $r->id]) }}">เปิดงาน</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrap">
                {{ $repairs->links() }}
            </div>
        @endif
    </div>

    {{-- Orders awaiting dispatch --}}
    <div class="card tech-card" style="margin-top:18px;">
        <div class="card-header">
            <div>
                <h3>คำสั่งซื้อที่รอจัดส่ง</h3>
                <p>รายการคำสั่งซื้อที่คุณต้องจัดเตรียมและยืนยันว่าส่งแล้ว</p>
            </div>
        </div>

        @if (empty($orders) || $orders->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">📦</div>
                <h4>ยังไม่มีคำสั่งซื้อที่รอจัดส่ง</h4>
                <p>เมื่อมีคำสั่งซื้อที่มอบหมายและพร้อมให้จัดส่ง จะปรากฏที่นี่</p>
            </div>
        @else
            <div class="table-wrap">
                <table class="tech-table">
                    <thead>
                        <tr>
                            <th>คำสั่งซื้อ</th>
                            <th>ลูกค้า</th>
                            <th>ยอดรวม</th>
                            <th>วันที่</th>
                            <th>การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $o)
                            <tr>
                                <td>#{{ $o->id }}</td>
                                <td>{{ $o->name ?? ($o->user?->name ?? '-') }}</td>
                                <td>{{ number_format($o->total, 2) }} บาท</td>
                                <td>{{ $o->created_at?->format('Y-m-d H:i') ?? '-' }}</td>
                                <td style="display:flex; gap:8px;">
                                    <a class="button-secondary compact-button" href="{{ route('technician.order.show', ['id' => $o->id]) }}">ดู</a>
                                    <form method="POST" action="{{ route('technician.order.dispatch', ['id' => $o->id]) }}" style="display:inline-block;">
                                        @csrf
                                        <button class="button-primary compact-button" type="submit">ยืนยันจัดส่ง</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<style>
    .technician-dashboard {
        display: grid;
        gap: 24px;
    }
    .tech-hero {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        padding: 28px 32px;
        border-radius: 28px;
        background: linear-gradient(135deg, #eff6ff 0%, #f8fafc 100%);
        border: 1px solid #dbeafe;
        box-shadow: 0 20px 50px rgba(37, 99, 235, 0.08);
    }
    .tech-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 7px 12px;
        border-radius: 999px;
        background: #dbeafe;
        color: #1d4ed8;
        font-weight: 700;
        font-size: 0.86rem;
        margin-bottom: 10px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .tech-hero h2 {
        margin: 0 0 8px;
        font-size: clamp(1.5rem, 2.5vw, 2.2rem);
        color: #0f172a;
    }
    .tech-hero p {
        margin: 0;
        color: #475569;
        line-height: 1.75;
        max-width: 700px;
    }
    .tech-actions {
        display: flex;
        gap: 12px;
        align-items: center;
    }
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 16px;
    }
    .stat-card {
        padding: 22px;
        border-radius: 22px;
        background: white;
        border: 1px solid #e2e8f0;
        box-shadow: 0 16px 36px rgba(15, 23, 42, 0.06);
    }
    .stat-label {
        display: block;
        color: #64748b;
        font-size: 0.95rem;
        margin-bottom: 10px;
    }
    .stat-card strong {
        font-size: 1.65rem;
        color: #0f172a;
    }
    .tech-card {
        padding: 28px;
    }
    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 18px;
    }
    .card-header h3 {
        margin: 0 0 6px;
        font-size: 1.2rem;
        color: #0f172a;
    }
    .card-header p {
        margin: 0;
        color: #64748b;
    }
    .empty-state {
        text-align: center;
        padding: 40px 20px;
        border-radius: 22px;
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
    }
    .empty-icon {
        font-size: 2.2rem;
        margin-bottom: 8px;
    }
    .empty-state h4 {
        margin: 0 0 8px;
        color: #0f172a;
    }
    .empty-state p {
        margin: 0;
        color: #64748b;
    }
    .table-wrap {
        overflow-x: auto;
    }
    .tech-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 720px;
    }
    .tech-table th,
    .tech-table td {
        padding: 14px 12px;
        text-align: left;
        border-bottom: 1px solid #e2e8f0;
        vertical-align: middle;
    }
    .tech-table th {
        color: #64748b;
        font-size: 0.9rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .tech-table tbody tr:hover {
        background: #f8fafc;
    }
    .status-pill {
        display: inline-flex;
        align-items: center;
        padding: 7px 12px;
        border-radius: 999px;
        font-size: 0.88rem;
        font-weight: 700;
        white-space: nowrap;
    }
    .status-pill.pending {
        background: #fef3c7;
        color: #92400e;
    }
    .status-pill.in_progress {
        background: #dbeafe;
        color: #1d4ed8;
    }
    .status-pill.completed,
    .status-pill.closed {
        background: #dcfce7;
        color: #166534;
    }
    .compact-button {
        padding: 10px 16px;
        font-size: 0.95rem;
    }
    .pagination-wrap {
        margin-top: 16px;
    }
    @media (max-width: 768px) {
        .tech-hero {
            flex-direction: column;
            align-items: flex-start;
        }
        .tech-actions {
            width: 100%;
        }
        .tech-actions a {
            width: 100%;
            justify-content: center;
        }
        .tech-card {
            padding: 22px;
        }
    }
</style>
@endsection
