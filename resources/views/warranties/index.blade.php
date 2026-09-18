@extends('layouts.app')

@section('content')
<div class="card">
    <h2>รายการประกันของฉัน</h2>

    @if ($warranties->isEmpty())
        <div>ไม่มีข้อมูลประกัน</div>
    @else
        <table style="width:100%; border-collapse:collapse;">
            <thead>
                <tr style="text-align:left; border-bottom:1px solid #e2e8f0;">
                    <th>สินค้า</th>
                    <th>SKU / Serial</th>
                    <th>วันที่ซื้อ</th>
                    <th>หมดประกัน</th>
                    <th>สถานะ</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($warranties as $w)
                    <tr style="border-bottom:1px solid #f1f5f9;">
                        <td>{{ $w->product_name }}</td>
                        <td>{{ $w->product_sku }} @if($w->serial_number) / {{ $w->serial_number }} @endif</td>
                        <td>{{ $w->purchase_date?->format('Y-m-d') ?? '-' }}</td>
                        <td>{{ $w->warranty_expires_at?->format('Y-m-d') ?? 'ไม่มี' }}</td>
                        <td>
                            @if ($w->warranty_expires_at && $w->warranty_expires_at->isPast())
                                <span style="color:#ef4444;">หมดประกัน</span>
                            @elseif (! $w->warranty_expires_at)
                                <span>ไม่มีประกัน</span>
                            @else
                                <span style="color:#16a34a;">ยังอยู่ในประกัน</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

</div>
@endsection
