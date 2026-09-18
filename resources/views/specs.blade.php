@extends('layouts.app')

@section('content')
    <div class="card">
        <p class="section-heading">จัดสเป็คคอมพิวเตอร์</p>
        <p class="spec-summary">เลือกแต่ละหมวดทีละหน้า เพื่อให้การจัดสเป็คชัดเจนและไม่รก</p>

        @php $selection = $selection ?? []; @endphp

        <div class="spec-layout">
            <aside class="section-nav">
                <h3>หัวข้อสเป็ค</h3>
                <ol class="section-list">
                    @foreach ($sections as $section)
                        @php $selectedOption = isset($selection[$section['key']]) ? collect($section['options'])->firstWhere('id', $selection[$section['key']]) : null; @endphp
                        <li>
                            <a href="{{ route('specs.section', ['section' => $section['key']]) }}" class="section-link {{ $section['key'] === $currentSection['key'] ? 'selected' : '' }}">
                                <div>
                                    <strong>{{ $section['title'] }}</strong>
                                    <span class="section-choice">{{ $selectedOption ? 'เลือกแล้ว: '.$selectedOption['name'] : 'ยังไม่เลือก' }}</span>
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ol>

                <div class="summary-card" style="margin-top: 20px;">
                    <h2>สรุปปัจจุบัน</h2>
                    <ul class="summary-list">
                        @foreach ($sections as $section)
                            @php $selectedOption = isset($selection[$section['key']]) ? collect($section['options'])->firstWhere('id', $selection[$section['key']]) : null; @endphp
                            <li>
                                <span>{{ $section['title'] }}</span>
                                <span>{{ $selectedOption ? $selectedOption['name'] : 'ยังไม่เลือก' }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <a href="{{ route('specs.summary') }}" class="button-primary" style="width:100%; margin-top: 16px; display: inline-flex; justify-content: center;">ดูสรุปทั้งหมด</a>
                </div>
            </aside>

            <form method="POST" action="{{ route('specs.submit', ['section' => $currentSection['key']]) }}" id="spec-step-form">
                @csrf
                <div class="component-card">
                    <div class="section-header">
                        <div>
                            <h2>{{ $currentSection['title'] }}</h2>
                            <p>{{ $currentSection['description'] }}</p>
                        </div>
                        <div class="step-indicator">หมวดที่ {{ $currentStep }} / {{ $totalSteps }}</div>
                    </div>

                    @if ($selectedItem)
                        <div class="selected-summary">
                            <div>
                                <strong>สิ่งที่เลือกแล้ว</strong>
                                <span>{{ $selectedItem['name'] }}</span>
                            </div>
                            <span class="option-price">{{ $selectedItem['price'] > 0 ? number_format($selectedItem['price']).' บาท' : 'รวมในแพ็กเกจ' }}</span>
                        </div>
                    @endif

                    <div class="option-grid">
                        @foreach ($currentSection['options'] as $option)
                            <label class="option-card {{ $selectedValue === $option['id'] ? 'option-selected' : '' }}">
                                <input type="radio"
                                       name="{{ $currentSection['key'] }}"
                                       value="{{ $option['id'] }}"
                                       {{ $selectedValue === $option['id'] ? 'checked' : '' }}
                                       {{ $option['stock'] <= 0 && $selectedValue !== $option['id'] ? 'disabled' : '' }}>
                                <span class="option-marker"></span>
 
                                <img class="option-thumb" src="{{ $option['image'] }}" alt="{{ $option['name'] }}">
 
                                <div class="option-info">
                                    <strong>{{ $option['name'] }}</strong>
                                    <span>{{ $option['details'] }}</span>
                                    <span class="option-stock {{ $option['stock'] <= 0 ? 'out-of-stock' : '' }}">
                                        {{ $option['stock'] > 0 ? 'สต๊อก ' . $option['stock'] . ' ชิ้น' : 'สินค้าหมด' }}
                                    </span>
                                </div>
 
                                <span class="option-price">{{ $option['price'] > 0 ? number_format($option['price']).' บาท' : 'รวมในแพ็กเกจ' }}</span>
                            </label>
                        @endforeach
                    </div>

                    @error($currentSection['key'])
                        <p class="alert">{{ $message }}</p>
                    @enderror

                    <div class="form-actions">
                        @if ($previousSection)
                            <a href="{{ route('specs.section', ['section' => $previousSection]) }}" class="button-secondary">กลับ</a>
                        @endif
                        <button type="submit" class="button-primary">{{ $nextSection ? 'ถัดไป' : 'สรุปทั้งหมด' }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('spec-step-form');
            if (!form) return;

            document.querySelectorAll('input[type="radio"]').forEach(function (input) {
                input.addEventListener('change', function () {
                    form.submit();
                });
            });
        });
    </script>
</div>
@endsection
