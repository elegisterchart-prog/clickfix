@extends('layouts.app')

@section('content')
<div class="card">
    <a href="{{ route('specs.summary') }}" class="button-secondary">ย้อนกลับ</a>
    <h2>หมวด: {{ $section['title'] }}</h2>
    <p>{{ $section['description'] ?? '' }}</p>

    @php
        $builder = $builder ?? session('spec_builder', []);
        $keys = array_map(function($s) { return $s['key']; }, $sections);
        $index = array_search($section['key'], $keys, true);
        $prev = ($index > 0) ? $keys[$index - 1] : null;
        $next = ($index !== false && $index < count($keys) - 1) ? $keys[$index + 1] : null;
        $isLast = ($index !== false && $index === count($keys) - 1);
        $current_total = $current_total ?? 0;
    @endphp

    <div style="margin-top:20px; display:grid; grid-template-columns:1.55fr 0.95fr; gap:24px; align-items:start;">
        <div>
            <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
                <div>
                    <div style="font-size:0.95rem; color:#0f766e; font-weight:700;">ขั้นตอนที่ {{ $index + 1 }} จาก {{ count($keys) }}</div>
                    <h2 style="margin:8px 0 4px;">{{ $section['title'] }}</h2>
                    <p style="color:#475569; max-width:680px;">{{ $section['description'] ?? 'เลือกชิ้นส่วนนี้เพื่อให้เครื่องของคุณทำงานได้ครบตามการใช้งาน' }}</p>
                </div>
                <div style="background:#eef2ff; color:#1d4ed8; border-radius:999px; padding:10px 16px; font-weight:700;">เลือกทีละส่วน</div>
            </div>

            <form method="POST" action="{{ route('specs.submit', ['section' => $section['key']]) }}">
                @csrf
                <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); gap:16px; margin-top:18px;">
                    @foreach ($section['options'] as $opt)
                        @php $outOfStock = (isset($opt['stock']) && $opt['stock'] <= 0); @endphp
                        <label style="display:block; border:1px solid #e2e8f0; border-radius:14px; padding:18px; background:#ffffff; box-shadow:0 10px 24px rgba(15,23,42,0.05); cursor:pointer; position:relative; transition:transform .18s ease, box-shadow .18s ease;">
                            <input type="radio" name="option_id" value="{{ $opt['id'] }}" style="position:absolute; top:18px; right:18px; transform:scale(1.15);" {{ (isset($builder[$section['key']]) && $builder[$section['key']] === $opt['id']) ? 'checked' : '' }} {{ $outOfStock ? 'disabled' : '' }}>

                            <div style="font-weight:700; font-size:1rem; margin-bottom:10px;">{{ $opt['name'] }}</div>
                            <div style="color:#475569; font-size:0.95rem; line-height:1.6; min-height:70px;">{{ $opt['details'] ?? '' }}</div>

                            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:18px; gap:12px;">
                                        <span style="font-size:0.95rem; color:#64748b;">ราคา {{ number_format($opt['price'] ?? 0) }} บาท</span>
                                        @if($outOfStock)
                                            <span style="background:#fee2e2; color:#b91c1c; border-radius:999px; padding:6px 10px; font-size:0.85rem;">หมดสต็อก</span>
                                        @else
                                            <span style="background:#d1fae5; color:#065f46; border-radius:999px; padding:6px 10px; font-size:0.85rem;">พร้อมสต็อก</span>
                                        @endif
                            </div>
                        </label>
                    @endforeach
                </div>

                <div style="display:flex; justify-content:space-between; flex-wrap:wrap; gap:14px; margin-top:22px; align-items:center;">
                    <div style="display:flex; gap:10px; flex-wrap:wrap;">
                        <a href="{{ route('specs.summary') }}" class="button-secondary">กลับสรุป</a>
                        @if ($prev)
                            <a href="{{ route('specs.section', ['section' => $prev]) }}" class="button-secondary">ก่อนหน้า</a>
                        @endif
                    </div>
                    <button type="submit" class="button-primary" style="min-width:180px;">{{ $isLast ? 'เพิ่มลงตะกร้า' : 'เลือกและไปต่อ' }}</button>
                </div>
            </form>
        </div>

        <div style="position:sticky; top:20px; align-self:start;">
            <div style="background:#f8fafc; border:1px solid #dbeafe; border-radius:14px; padding:18px; box-shadow:0 10px 24px rgba(15,23,42,0.05);">
                <div style="font-weight:700; margin-bottom:12px;">สรุปสเปคที่เลือก</div>
                @if (empty($builder))
                    <div style="color:#475569;">ยังไม่ได้เลือกชิ้นส่วนใด</div>
                @else
                    <div style="display:grid; gap:14px;">
                        @foreach ($builder as $k => $optId)
                            @php
                                        $sec = collect($sections)->firstWhere('key', $k);
                                        $opt = $sec ? collect($sec['options'])->firstWhere('id', $optId) : null;
                            @endphp
                            <div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:12px; padding:12px;">
                                        <div style="font-weight:700;">{{ $sec['title'] ?? ucfirst($k) }}</div>
                                        <div style="color:#475569; font-size:0.95rem; margin-top:4px;">{{ $opt['name'] ?? '-' }}</div>
                                        <div style="color:#64748b; font-size:0.85rem; margin-top:4px;">{{ number_format($opt['price'] ?? 0) }} บาท</div>
                            </div>
                        @endforeach
                        <div style="border-top:1px dashed #e2e8f0; padding-top:12px; display:flex; justify-content:space-between; align-items:center; font-weight:700;">
                            <span>ยอดรวม</span>
                            <span>{{ number_format($current_total, 2) }} บาท</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
