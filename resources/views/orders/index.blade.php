@extends('layouts.app')

@section('content')
<div class="card">
    <h2>คำสั่งซื้อของฉัน</h2>

    @if ($orders->isEmpty())
        <div>ยังไม่มีคำสั่งซื้อ</div>
    @else
        <div style="overflow-x:auto; margin-top:12px;">
            <table style="width:100%; border-collapse:collapse; min-width:720px;">
                <thead>
                    <tr style="text-align:left; border-bottom:1px solid #e2e8f0;">
                        <th>หมายเลข</th>
                        <th>ยอดรวม</th>
                        <th>สถานะ</th>
                        <th>วันที่</th>
                        <th>การจัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $o)
                        <tr style="border-bottom:1px solid #f1f5f9;">
                            <td>#{{ $o->id }}</td>
                            <td>{{ number_format($o->total, 2) }} บาท</td>
                            <td>{{ $o->status }}</td>
                            <td>{{ $o->created_at?->format('Y-m-d H:i') ?? '-' }}</td>
                            <td style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                                <a href="{{ route('orders.show', ['id' => $o->id]) }}" class="button-secondary">ดู</a>
                                @if (($o->status ?? '') !== 'cancelled')
                                    <form method="POST" action="{{ route('orders.cancel', ['id' => $o->id]) }}" style="display:inline; margin:0;">
                                        @csrf
                                        <button type="submit" class="button-danger" onclick="return confirm('ยกเลิกคำสั่งซื้อนี้หรือไม่?')">ยกเลิก</button>
                                    </form>
                                @endif
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
