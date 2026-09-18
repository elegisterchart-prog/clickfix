@extends('layouts.app')

@section('content')
<div class="card">
    <a href="{{ route('admin.dashboard') }}" class="button-secondary" style="margin-bottom:12px; display:inline-block;">ย้อนกลับ</a>
    <h2>รายการคำสั่งซื้อ</h2>
    <p>คำสั่งซื้อจากตะกร้าจะถูกส่งให้ผู้ดูแลเพื่อตรวจสอบและมอบหมายงานให้ช่าง</p>

    @if($orders->isEmpty())
        <div>ยังไม่มีคำสั่งซื้อ</div>
    @else
        <div style="overflow-x:auto; margin-top:18px;">
            <table style="width:100%; border-collapse:collapse; min-width:720px;">
                <thead>
                    <tr style="text-align:left; border-bottom:1px solid #e2e8f0;">
                        <th>หมายเลข</th>
                        <th>ลูกค้า</th>
                        <th>ยอดรวม</th>
                        <th>สถานะ</th>
                        <th>มอบหมายให้</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                        <tr style="border-bottom:1px solid #f1f5f9;">
                            <td>#{{ $order->id }}</td>
                            <td>{{ $order->name ?? ($order->user?->name ?? '-') }}</td>
                            <td>{{ number_format($order->total, 2) }} บาท</td>
                            <td>{{ $order->status }}</td>
                            <td>{{ $order->assigned_to ?? '-' }}</td>
                            <td>
                                <div style="display:flex; flex-direction:column; gap:8px;">
                                    <a href="{{ route('admin.orders.show', ['id' => $order->id]) }}" class="button-secondary" style="padding:8px 10px; display:inline-block;">ดู</a>
                                    <form method="POST" action="{{ route('admin.orders.assign', ['id' => $order->id]) }}" style="display:flex; flex-direction:column; gap:8px;">
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

        <div style="margin-top:12px;">{{ $orders->links() }}</div>
    @endif
</div>
@endsection
