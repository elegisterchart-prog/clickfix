@extends('layouts.app')

@section('content')
<style>
    .home-page {
        padding: 18px 0 60px;
    }

    .home-hero {
        display: grid;
        grid-template-columns: minmax(0, 1.2fr) minmax(320px, 0.8fr);
        gap: 26px;
        align-items: center;
        padding: 22px 0 8px;
    }

    .hero-copy {
        padding: 10px 0;
    }

    .eyebrow {
        display: inline-flex;
        align-items: center;
        padding: 7px 12px;
        border-radius: 999px;
        background: rgba(37,99,235,0.08);
        border: 1px solid rgba(37,99,235,0.12);
        color: #1d4ed8;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    .hero-title {
        margin: 18px 0 16px;
        font-size: clamp(2.6rem, 4.5vw, 4.4rem);
        line-height: 1.04;
        letter-spacing: -0.06em;
        color: #0f172a;
    }

    .hero-subtitle {
        margin: 0;
        max-width: 700px;
        color: #475569;
        line-height: 1.8;
        font-size: 1.02rem;
    }

    .hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 26px;
    }

    .hero-actions .button-primary,
    .hero-actions .button-secondary {
        min-width: 180px;
    }

    .mini-summary {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
        margin-top: 28px;
    }

    .mini-stat {
        background: rgba(255,255,255,0.7);
        border: 1px solid rgba(148,163,184,0.18);
        border-radius: 20px;
        padding: 18px 16px;
        box-shadow: 0 16px 30px rgba(15, 23, 42, 0.04);
    }

    .mini-stat strong {
        display: block;
        font-size: 1.8rem;
        letter-spacing: -0.05em;
        color: #0f172a;
        margin-bottom: 6px;
    }

    .mini-stat span {
        color: #475569;
        font-size: 0.88rem;
    }

    .hero-panel {
        background: linear-gradient(180deg, #1d2a3a 0%, #243546 100%);
        color: #edf8ff;
        border: 1px solid rgba(125,211,252,0.15);
        border-radius: 30px;
        padding: 20px;
        box-shadow: 0 28px 56px rgba(15, 23, 42, 0.12);
    }

    .panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 18px;
    }

    .panel-header strong {
        font-size: 1.05rem;
        letter-spacing: -0.04em;
    }

    .panel-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 10px;
        border-radius: 999px;
        background: rgba(255,255,255,0.08);
        color: #dbeafe;
        font-size: 0.72rem;
        font-weight: 700;
    }

    .feature-stack {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .feature-box {
        background: rgba(255,255,255,0.04);
        border: 1px solid rgba(255,255,255,0.06);
        border-radius: 18px;
        padding: 14px 12px;
        min-height: 120px;
    }

    .feature-box .num {
        display: inline-grid;
        place-items: center;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: rgba(125,211,252,0.12);
        border: 1px solid rgba(125,211,252,0.2);
        color: #edf9ff;
        font-size: 0.8rem;
        font-weight: 800;
    }

    .feature-box h3 {
        margin: 10px 0 6px;
        font-size: 1.05rem;
        color: #f8fbff;
    }

    .feature-box p {
        margin: 0;
        color: rgba(191,219,254,0.8);
        font-size: 0.86rem;
        line-height: 1.6;
    }

    .home-section {
        margin-top: 42px;
    }

    .ai-home-shell {
        background: rgba(255,255,255,0.82);
        border: 1px solid rgba(148,163,184,0.18);
        border-radius: 28px;
        padding: 24px;
        box-shadow: 0 24px 42px rgba(15, 23, 42, 0.05);
    }

    .ai-home-header {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: center;
        flex-wrap: wrap;
        margin-bottom: 18px;
    }

    .ai-home-header h2 {
        margin: 0;
        font-size: clamp(1.8rem, 2vw, 2.4rem);
        letter-spacing: -0.05em;
        color: #0f172a;
    }

    .ai-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        border-radius: 999px;
        background: rgba(37,99,235,0.06);
        border: 1px solid rgba(37,99,235,0.12);
        color: #1d4ed8;
        font-size: 0.82rem;
        font-weight: 700;
    }

    .ai-home-chat {
        border: 1px solid rgba(148,163,184,0.25);
        border-radius: 20px;
        background: rgba(248,250,252,0.92);
        padding: 12px;
        display: flex;
        flex-direction: column;
        gap: 12px;
        min-height: 260px;
        max-height: 380px;
        overflow-y: auto;
    }

    .ai-bubble {
        max-width: 85%;
        padding: 12px 14px;
        border-radius: 14px;
        line-height: 1.6;
        white-space: pre-wrap;
        word-break: break-word;
    }

    .ai-bubble.user {
        margin-left: auto;
        background: linear-gradient(135deg, #1d4ed8, #3b82f6);
        color: white;
    }

    .ai-bubble.assistant {
        background: white;
        border: 1px solid rgba(148,163,184,0.18);
        color: #0f172a;
    }

    .ai-home-form {
        display: flex;
        gap: 10px;
        margin-top: 16px;
        flex-wrap: wrap;
    }

    .ai-home-input {
        flex: 1;
        min-width: 220px;
        border: 1px solid rgba(148,163,184,0.35);
        border-radius: 14px;
        background: #fff;
        padding: 14px 16px;
        font-size: 1rem;
    }

    .section-head {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        align-items: end;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }

    .section-head h2 {
        margin: 0;
        font-size: clamp(2rem, 2.3vw, 2.8rem);
        letter-spacing: -0.05em;
        color: #0f172a;
    }

    .section-head p {
        margin: 6px 0 0;
        color: #475569;
    }

    .category-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 18px;
    }

    .category-card {
        background: rgba(255,255,255,0.74);
        border: 1px solid rgba(148,163,184,0.18);
        border-radius: 24px;
        padding: 22px 20px;
        box-shadow: 0 18px 32px rgba(15, 23, 42, 0.04);
    }

    .category-card h3 {
        margin: 0 0 10px;
        color: #0f172a;
        font-size: 1.15rem;
    }

    .category-card p {
        margin: 0 0 16px;
        color: #475569;
        line-height: 1.7;
        min-height: 62px;
    }

    @media (max-width: 900px) {
        .home-hero {
            grid-template-columns: 1fr;
        }

        .mini-summary {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="home-page">
    <div class="home-hero">
        <div class="hero-copy">
            <div class="eyebrow">ClickFix</div>
            <h1 class="hero-title">ระบบซ่อมคอมพิวเตอร์และร้านอุปกรณ์ที่ใช้งานง่าย</h1>
            <p class="hero-subtitle">
                ซ่อมด่วน จัดสเป็คคอมพิวเตอร์ให้ตรงความต้องการ จำหน่ายชิ้นส่วนครบวงจร และให้บริการหลังการขายที่กระชับและมืออาชีพ
            </p>

            <div class="hero-actions">
                <a href="{{ route('dashboard') }}" class="button-primary">ดูสินค้า</a>
                <a href="{{ route('repair.request') }}" class="button-secondary">แจ้งซ่อม</a>
            </div>

            <div class="mini-summary">
                <div class="mini-stat">
                    <strong>24/7</strong>
                    <span>แจ้งซ่อมออนไลน์</span>
                </div>
                <div class="mini-stat">
                    <strong>120+</strong>
                    <span>รุ่นอุปกรณ์พร้อมจำหน่าย</span>
                </div>
                <div class="mini-stat">
                    <strong>1-3 วัน</strong>
                    <span>รับประกันการซ่อม</span>
                </div>
            </div>
        </div>

        <div class="hero-panel">
            <div class="panel-header">
                <strong>ประโยชน์ของระบบ</strong>
                <span class="panel-badge">New</span>
            </div>

            <div class="feature-stack">
                <div class="feature-box">
                    <span class="num">1</span>
                    <h3>เลือกสินค้า</h3>
                    <p>ค้นหาชิ้นส่วนและคอมพิวเตอร์ได้จากหมวดที่จัดไว้แบบชัดเจน</p>
                </div>
                <div class="feature-box">
                    <span class="num">2</span>
                    <h3>จัดสเป็ค</h3>
                    <p>รวมชิ้นส่วนให้ตรงงานและงบประมาณแบบทีละส่วน</p>
                </div>
                <div class="feature-box">
                    <span class="num">3</span>
                    <h3>แจ้งซ่อม</h3>
                    <p>ติดตามการซ่อมและประวัติการใช้บริการได้จากระบบเดียว</p>
                </div>
                <div class="feature-box">
                    <span class="num">4</span>
                    <h3>ประกันชัดเจน</h3>
                    <p>มีข้อมูลการซื้อและประกันที่ใช้งานง่ายและติดตามได้</p>
                </div>
            </div>
        </div>
    </div>

    <div class="home-section">
        <div class="ai-home-shell">
            <div class="ai-home-header">
                <h2>ultron</h2>
                <div class="ai-status-pill">พร้อมช่วยเลือกชิ้นส่วน</div>
            </div>

            <div id="home-ai-chat" class="ai-home-chat" aria-live="polite">
                <div class="ai-bubble assistant">สวัสดี! ผมช่วยแนะนำชิ้นส่วน คอมพิวเตอร์ และคำถามเรื่องซ่อมได้เลย</div>
            </div>

            <form id="home-ai-form" class="ai-home-form">
                @csrf
                <input id="home-ai-input" class="ai-home-input" type="text" placeholder="ถามเรื่องคอม / ชิ้นส่วน / ซ่อม..." />
                <button type="submit" class="button-primary" id="home-ai-send">ส่ง</button>
            </form>
        </div>
    </div>

    <div class="home-section">
        <div class="section-head">
            <div>
                <h2>หมวดหมู่หลัก</h2>
                <p>เลือกสิ่งที่ต้องการได้จากระบบที่ออกแบบให้ใช้งานง่าย</p>
            </div>
        </div>

        <div class="category-grid">
            <div class="category-card">
                <h3>คอมเซ็ต</h3>
                <p>ชุดคอมฯ พร้อมใช้งานที่คัดสรรจากประสิทธิภาพและความคุ้มค่า</p>
                <a href="{{ route('products.category', ['section' => 'prebuilt']) }}" class="button-secondary">เลือกคอมเซ็ต</a>
            </div>

            <div class="category-card">
                <h3>CPU</h3>
                <p>ชิปประมวลผลที่เหมาะกับงานทั่วไป เกม และงานกราฟิก</p>
                <a href="{{ route('products.category', ['section' => 'cpu']) }}" class="button-secondary">เลือก CPU</a>
            </div>

            <div class="category-card">
                <h3>GPU</h3>
                <p>การ์ดจอที่เพิ่มประสิทธิภาพและภาพสำหรับเกมและงานกราฟิก</p>
                <a href="{{ route('products.category', ['section' => 'gpu']) }}" class="button-secondary">เลือก GPU</a>
            </div>

            <div class="category-card">
                <h3>RAM</h3>
                <p>หน่วยความจำที่ช่วยด้านประสิทธิภาพและความเร็วของระบบ</p>
                <a href="{{ route('products.category', ['section' => 'ram']) }}" class="button-secondary">เลือก RAM</a>
            </div>

            <div class="category-card">
                <h3>Storage</h3>
                <p>SSD และฮาร์ดดิสก์ที่ให้ความเร็วและพื้นที่เก็บข้อมูล</p>
                <a href="{{ route('products.category', ['section' => 'storage']) }}" class="button-secondary">เลือก Storage</a>
            </div>

            <div class="category-card">
                <h3>PSU</h3>
                <p>แหล่งจ่ายไฟที่เสถียรและปลอดภัยสำหรับการใช้งานจริง</p>
                <a href="{{ route('products.category', ['section' => 'psu']) }}" class="button-secondary">เลือก PSU</a>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const chatBox = document.getElementById('home-ai-chat');
    const form = document.getElementById('home-ai-form');
    const input = document.getElementById('home-ai-input');
    const sendBtn = document.getElementById('home-ai-send');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    function addBubble(text, role) {
        const bubble = document.createElement('div');
        bubble.className = 'ai-bubble ' + role;
        bubble.textContent = text;
        chatBox.appendChild(bubble);
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    function setLoading(isLoading) {
        sendBtn.disabled = isLoading;
        sendBtn.textContent = isLoading ? 'กำลังตอบ...' : 'ส่ง';
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const message = input.value.trim();
        if (!message) return;

        addBubble(message, 'user');
        input.value = '';
        setLoading(true);

        fetch('/ai/chat/message', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-XSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ message: message })
        })
        .then(async response => {
            const text = await response.text();
            if (!response.ok) {
                throw new Error('HTTP ' + response.status + ': ' + text);
            }
            return JSON.parse(text);
        })
        .then(data => {
            addBubble((data && data.reply) ? data.reply : 'ขออภัยไม่มีคำตอบ', 'assistant');
        })
        .catch(error => {
            addBubble('เกิดข้อผิดพลาด: ' + error.message, 'assistant');
        })
        .finally(() => {
            setLoading(false);
            input.focus();
        });
    });
})();
</script>
@endsection
