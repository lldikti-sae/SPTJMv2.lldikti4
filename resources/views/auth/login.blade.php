@extends('layouts/blankLayout')

@section('title', 'SPTJM Online - Login')

@section('page-style')
<style>
    /* ============================================================
       SPTJM LOGIN PAGE — Figma Redesign Implementation
       Hubungan: blankLayout → commonMaster → styles.blade.php
       commonMaster inject: Bootstrap 5, Boxicons, jQuery
       Semua form attribute (action, name, csrf) TIDAK diubah.
    ============================================================ */

    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

    *, *::before, *::after {
        box-sizing: border-box;
    }

    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
        font-family: 'Inter', 'Public Sans', sans-serif;
    }

    /* ── Background Utama ── */
    @php
        $bgUrl = \App\Services\LoginBackgroundService::getAssetUrl();
        $headerMode = \App\Services\LoginBackgroundService::getHeaderMode();
    @endphp
    .sptjm-login-page {
        min-height: 100vh;
        width: 100%;
        background: url('{{ $bgUrl }}') no-repeat center center;
        background-size: cover;
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: stretch;
    }

    /* Gradient overlay to ensure form readability over any image */
    .sptjm-login-page::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: linear-gradient(135deg, rgba(21, 101, 192, 0.40) 0%, rgba(30, 136, 229, 0.25) 100%);
        z-index: 0;
    }

    /* ── Wave Decoration (SVG) ── */
    .sptjm-wave-bottom {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        pointer-events: none;
        z-index: 0;
    }

    .sptjm-wave-mid {
        position: absolute;
        bottom: 60px;
        left: 0;
        width: 100%;
        pointer-events: none;
        z-index: 0;
        opacity: 0.5;
    }

    /* ── Layout Container ── */
    .sptjm-login-container {
        position: relative;
        z-index: 1;
        width: 100%;
        max-width: 1280px;
        margin: 0 auto;
        display: flex;
        align-items: center;
        min-height: 100vh;
        padding: 2rem 3rem;
        gap: 2rem;
    }

    /* ── Kolom Kiri (Branding) ── */
    .sptjm-left-col {
        flex: 1.2;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: flex-start;
        padding: 2rem 1rem 2rem 2rem;
    }

    /* Logo Row */
    .sptjm-logo-row {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 2.5rem;
    }

    .sptjm-logo-row img {
        height: 52px;
        width: auto;
        object-fit: contain;
        filter: drop-shadow(0 2px 8px rgba(0,0,0,0.2));
    }

    .sptjm-logo-divider {
        width: 2px;
        height: 40px;
        background: rgba(255,255,255,0.4);
        border-radius: 2px;
    }

    /* Welcome Text */
    .sptjm-welcome-title {
        font-size: clamp(2rem, 3.5vw, 2.8rem);
        font-weight: 800;
        color: #ffffff;
        line-height: 1.2;
        margin: 0 0 1rem 0;
        text-shadow: 0 2px 12px rgba(0,0,0,0.15);
        letter-spacing: -0.5px;
    }

    .sptjm-welcome-subtitle {
        font-size: clamp(0.9rem, 1.4vw, 1.05rem);
        font-weight: 400;
        color: rgba(255,255,255,0.80);
        margin: 0;
        letter-spacing: 0.01em;
    }

    /* ── Kolom Kanan (Form Card) ── */
    .sptjm-right-col {
        flex: 0 0 420px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 1rem;
    }

    /* ── Glassmorphism Card ── */
    .sptjm-card {
        background: rgba(255, 255, 255, 0.10) !important;
        backdrop-filter: blur(20px) !important;
        -webkit-backdrop-filter: blur(20px) !important;
        border: 1px solid rgba(255, 255, 255, 0.25) !important;
        border-radius: 24px !important;
        box-shadow:
            0 20px 40px rgba(0, 0, 0, 0.15),
            0 2px 8px rgba(0, 0, 0, 0.05) !important;
        padding: 2.5rem 2.25rem 2rem !important;
        width: 100% !important;
        max-width: 400px;
    }

    /* ── Card Header ── */
    .sptjm-card-header {
        text-align: center;
        margin-bottom: 1.75rem;
    }

    .sptjm-brand-title {
        font-size: clamp(1.6rem, 2.5vw, 1.9rem);
        font-weight: 800;
        line-height: 1;
        letter-spacing: 0.5px;
        margin-bottom: 0.4rem;
    }

    .sptjm-brand-title .brand-sptjm {
        color: #ffffff;
        text-shadow: 0 2px 10px rgba(0,0,0,0.20);
    }

    .sptjm-brand-title .brand-online {
        color: #FFB300;
        text-shadow: 0 2px 10px rgba(255,179,0,0.30);
    }

    .sptjm-brand-subtitle {
        font-size: 0.75rem;
        font-weight: 400;
        color: rgba(255, 255, 255, 0.85);
        margin: 0;
        letter-spacing: 0.02em;
    }

    /* ── Form Labels ── */
    .sptjm-form-label {
        display: block;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #ffffff;
        margin-bottom: 0.4rem;
    }

    /* ── Form Group Spacing ── */
    .sptjm-form-group {
        margin-bottom: 1.1rem;
    }

    /* ── Input Fields (Pill Glassmorphism) ── */
    .sptjm-input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .sptjm-input-icon {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #93c5fd;
        font-size: 1rem;
        z-index: 2;
        pointer-events: none;
    }

    .sptjm-input-icon-right {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #93c5fd;
        font-size: 1rem;
        z-index: 2;
        cursor: pointer;
    }

    .sptjm-input {
        width: 100%;
        height: 48px;
        background: rgba(255, 255, 255, 0.05) !important;
        border: 1.5px solid #60a5fa !important;
        border-radius: 12px !important;
        padding: 0 42px 0 42px !important;
        font-size: 0.875rem !important;
        font-weight: 400 !important;
        color: #ffffff !important;
        transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
        -webkit-text-fill-color: #ffffff !important;
        outline: none;
        box-shadow: none;
    }

    .sptjm-input::placeholder {
        color: rgba(255, 255, 255, 0.5) !important;
        opacity: 1 !important;
    }

    .sptjm-input:focus {
        background: rgba(255, 255, 255, 0.1) !important;
        border-color: #ffffff !important;
        box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.2) !important;
        outline: none !important;
    }

    /* Select */
    .sptjm-select {
        width: 100%;
        height: 48px;
        background: #ffffff !important;
        border: 1.5px solid #ffffff !important;
        border-radius: 12px !important;
        padding: 0 42px 0 42px !important;
        font-size: 0.875rem !important;
        font-weight: 400 !important;
        color: #1e293b !important;
        transition: border-color 0.2s, box-shadow 0.2s;
        -webkit-appearance: none;
        appearance: none;
        cursor: pointer;
        outline: none;
        box-shadow: inset 0 1px 2px rgba(0,0,0,0.02);
    }

    .sptjm-select:focus {
        background: #ffffff !important;
        border-color: #ffffff !important;
        box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.3) !important;
        outline: none !important;
    }

    .sptjm-select option {
        background: #ffffff;
        color: #1e293b;
    }

    /* Target icons inside the select wrapper specifically */
    .sptjm-input-wrapper .sptjm-select ~ .sptjm-input-icon-right {
        color: #475569 !important;
    }
    .sptjm-input-wrapper:has(.sptjm-select) .sptjm-input-icon {
        color: #475569 !important;
    }

    /* Password toggle */
    .sptjm-input.with-right-icon {
        padding-right: 42px !important;
    }

    /* ── Login Button ── */
    .sptjm-btn-login {
        width: 100%;
        height: 48px;
        background: #28C76F;
        border: none;
        border-radius: 12px;
        color: #ffffff;
        font-size: 0.95rem;
        font-weight: 700;
        letter-spacing: 0.03em;
        cursor: pointer;
        transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
        box-shadow: 0 4px 16px rgba(40, 199, 111, 0.40);
        margin-top: 0.5rem;
    }

    .sptjm-btn-login:hover {
        background: #22B164;
        box-shadow: 0 6px 20px rgba(40, 199, 111, 0.55);
        transform: translateY(-1px);
    }

    .sptjm-btn-login:active {
        transform: translateY(0);
        box-shadow: 0 2px 8px rgba(40, 199, 111, 0.35);
    }

    /* ── Alert Error ── */
    .sptjm-alert-error {
        background: rgba(255, 77, 79, 0.18);
        border: 1px solid rgba(255, 77, 79, 0.4);
        border-radius: 10px;
        color: #FFD0D0;
        padding: 0.65rem 1rem;
        font-size: 0.82rem;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* ── Card Footer ── */
    .sptjm-card-footer {
        text-align: center;
        margin-top: 1.5rem;
        font-size: 0.72rem;
        color: rgba(255, 255, 255, 0.55);
        letter-spacing: 0.01em;
    }

    /* ============================================================
       RESPONSIVE
    ============================================================ */
    @media (max-width: 991px) {
        .sptjm-login-container {
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.5rem;
            gap: 1.5rem;
        }

        .sptjm-left-col {
            flex: none;
            width: 100%;
            max-width: 480px;
            align-items: center;
            text-align: center;
            padding: 1rem 1rem 0;
        }

        .sptjm-logo-row {
            justify-content: center;
        }

        .sptjm-right-col {
            flex: none;
            width: 100%;
            max-width: 480px;
            padding: 0 1rem 2rem;
        }
    }

    @media (max-width: 576px) {
        .sptjm-logo-row img {
            height: 38px;
        }

        .sptjm-card {
            padding: 2rem 1.5rem 1.75rem;
            border-radius: 20px;
        }

        .sptjm-welcome-title {
            font-size: 1.6rem;
        }

        .sptjm-left-col {
            padding-top: 1.5rem;
        }
    }
</style>
@endsection

@section('content')
<div class="sptjm-login-page">

    {{-- Wave Decorations --}}
    <svg class="sptjm-wave-bottom" viewBox="0 0 1440 200" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M0,160 C360,220 1080,80 1440,160 L1440,200 L0,200 Z" fill="rgba(255,255,255,0.08)"/>
    </svg>
    <svg class="sptjm-wave-mid" viewBox="0 0 1440 200" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M0,120 C300,200 900,40 1440,120 L1440,200 L0,200 Z" fill="rgba(255,255,255,0.05)"/>
    </svg>

    <div class="sptjm-login-container">

        {{-- ===== Kolom Kiri: Branding ===== --}}
        <div class="sptjm-left-col">

            {{-- Logo Row --}}
            <div class="sptjm-logo-row" style="{{ $headerMode === 'hide' ? 'display: none !important;' : ($headerMode === 'corner' ? 'position: absolute; top: 2rem; left: 2rem; margin-bottom: 0;' : '') }}">
                <img src="{{ asset('assets/img/favicon/logo-lldikti-4.png') }}" alt="Logo LLDIKTI 4">
                <div class="sptjm-logo-divider"></div>
                <img src="{{ asset('logo_berdampak.png') }}" alt="Logo Berdampak DIKTISAINTEK">
            </div>

            {{-- Welcome Text --}}
            <div style="{{ $headerMode === 'hide' || $headerMode === 'corner' ? 'display: none !important;' : '' }}">
                <h1 class="sptjm-welcome-title">
                    Selamat Datang di<br>SPTJM Online
                </h1>
                <p class="sptjm-welcome-subtitle">
                    Silahkan Masuk untuk Memulai Aplikasi
                </p>
            </div>

        </div>

        {{-- ===== Kolom Kanan: Login Card ===== --}}
        <div class="sptjm-right-col">
            <div class="sptjm-card">

                {{-- Card Header --}}
                <div class="sptjm-card-header">
                    <div class="sptjm-brand-title">
                        <span class="brand-sptjm">SPTJM</span>&nbsp;<span class="brand-online">ONLINE</span>
                    </div>
                    <p class="sptjm-brand-subtitle">Sistem Pernyataan Tanggung Jawab Mutlak</p>
                </div>

                {{-- Error Alert --}}
                @if (session('error'))
                <div class="sptjm-alert-error">
                    <i class="bx bx-error-circle" style="font-size:1rem; flex-shrink:0;"></i>
                    <span>{{ session('error') }}</span>
                </div>
                @endif

                {{-- ===== FORM (semua attribute TIDAK berubah) ===== --}}
                <form id="formAuthentication" action="{{ route('login') }}" method="POST" autocomplete="on">
                    @csrf

                    {{-- USERNAME --}}
                    <div class="sptjm-form-group">
                        <label class="sptjm-form-label" for="login">Username</label>
                        <div class="sptjm-input-wrapper">
                            <i class="bx bx-user sptjm-input-icon"></i>
                            <input
                                type="text"
                                class="sptjm-input"
                                id="login"
                                name="login"
                                placeholder=""
                                required
                                autocomplete="username"
                                autocapitalize="none"
                                spellcheck="false"
                            >
                        </div>
                    </div>

                    {{-- PASSWORD --}}
                    <div class="sptjm-form-group">
                        <label class="sptjm-form-label" for="password">Password</label>
                        <div class="sptjm-input-wrapper">
                            <i class="bx bx-lock-alt sptjm-input-icon"></i>
                            <input
                                type="password"
                                id="password"
                                class="sptjm-input with-right-icon"
                                name="password"
                                placeholder=""
                                required
                                autocomplete="current-password"
                            >
                            <i class="bx bx-hide sptjm-input-icon-right" id="togglePassword"></i>
                        </div>
                    </div>

                    {{-- TAHUN --}}
                    <div class="sptjm-form-group">
                        <label class="sptjm-form-label" for="tahun">Tahun</label>
                        <div class="sptjm-input-wrapper">
                            <i class="bx bx-calendar sptjm-input-icon"></i>
                            <select id="tahun" name="tahun" class="sptjm-select" required>
                                <option value="">Pilih Tahun</option>
                                @foreach($tahun_versi as $th)
                                    <option value="{{ $th }}">{{ $th }}</option>
                                @endforeach
                            </select>
                            <i class="bx bx-chevron-down sptjm-input-icon-right" style="pointer-events:none;"></i>
                        </div>
                    </div>

                    {{-- LOGIN BUTTON --}}
                    <button type="submit" class="sptjm-btn-login">
                        Login
                    </button>

                </form>

                {{-- Card Footer --}}
                <div class="sptjm-card-footer">
                    &copy; 2026 SPTJM Online &middot; LLDIKTI Wilayah IV
                </div>

            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Toggle password visibility
    const toggleBtn = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');

    if (toggleBtn && passwordInput) {
        toggleBtn.addEventListener('click', function () {
            const isHidden = passwordInput.type === 'password';
            passwordInput.type = isHidden ? 'text' : 'password';
            this.classList.toggle('bx-hide', !isHidden);
            this.classList.toggle('bx-show', isHidden);
        });
    }
});
</script>
@endsection