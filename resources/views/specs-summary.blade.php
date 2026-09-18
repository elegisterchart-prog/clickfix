@extends('layouts.app')

@section('content')
<div class="card">
    <p class="section-heading">สรุปสเป็คคอมพิวเตอร์</p>
    <p class="spec-summary">ตรวจสอบสเป็คที่คุณเลือกทั้งหมดก่อนส่งคำขอให้ทีมงาน</p>

    @if (count($results))
        <div class="summary-card">
            <h2>รายการที่เลือก</h2>
            <ul class="summary-list">
                @foreach ($results as $item)
                    <li>
                        <div>
                            <strong>{{ $item['section'] }}</strong>
                            <span>{{ $item['option']['name'] }}</span>
                        </div>
                        <div class="summary-meta">
                            <span>{{ number_format($item['option']['price']) }} บาท</span>
                            <a href="{{ route('specs.section', ['section' => $item['key']]) }}">แก้ไข</a>
                        </div>
                    </li>
                @endforeach
            </ul>
            <div class="summary-total">
                <span>รวมทั้งหมด</span>
                <span>{{ number_format($total) }} บาท</span>
            </div>
        </div>
        <form method="POST" action="{{ route('cart.add') }}" class="form-actions" style="margin-top: 20px;">
            @csrf
            <button type="submit" class="button-primary">เพิ่มสเป็คนี้ลงตะกร้า</button>
            <a href="{{ route('specs.section', ['section' => $sections[0]['key']]) }}" class="button-secondary">แก้ไขสเป็คเพิ่มเติม</a>
        </form>
    @else
        <div class="alert">ยังไม่มีสเป็คที่เลือก กรุณาเริ่มต้นเลือกหนึ่งหมวดก่อน</div>
        <a href="{{ route('specs.section', ['section' => $sections[0]['key']]) }}" class="button-primary">เริ่มจัดสเป็ค</a>
    @endif
</div>
@endsection