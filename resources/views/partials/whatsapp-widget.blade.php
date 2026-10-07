@php
    $rawPhone = \App\Models\Setting::get('whatsapp_number', \App\Models\Setting::get('contact_phone', '03555123456'));
    $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
    if (str_starts_with($cleanPhone, '0')) {
        $cleanPhone = '92' . substr($cleanPhone, 1);
    } elseif (!str_starts_with($cleanPhone, '92') && strlen($cleanPhone) === 10) {
        $cleanPhone = '92' . $cleanPhone;
    }
    $defaultMsg = rawurlencode("Hello Organic Dairy Gilgit-Baltistan! I'm interested in ordering fresh mountain dairy products (Cow/Yak milk, Desi Ghee, Butter, Cheese).");
    $waUrl = "https://wa.me/{$cleanPhone}?text={$defaultMsg}";
@endphp

<style>
    .wa-floating-btn {
        position: fixed;
        bottom: 26px;
        right: 26px;
        z-index: 1040;
        display: flex;
        align-items: center;
        gap: 10px;
        text-decoration: none;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .wa-btn-circle {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
        box-shadow: 0 8px 24px rgba(37, 211, 102, 0.4), 0 2px 6px rgba(0, 0, 0, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        font-size: 30px;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
        position: relative;
    }

    .wa-btn-circle::before {
        content: '';
        position: absolute;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background: inherit;
        top: 0;
        left: 0;
        z-index: -1;
        animation: waPulse 2s infinite cubic-bezier(0.4, 0, 0.2, 1);
    }

    @keyframes waPulse {
        0% {
            transform: scale(1);
            opacity: 0.8;
        }
        70% {
            transform: scale(1.4);
            opacity: 0;
        }
        100% {
            transform: scale(1.4);
            opacity: 0;
        }
    }

    .wa-floating-btn:hover .wa-btn-circle {
        transform: scale(1.08);
        box-shadow: 0 12px 28px rgba(37, 211, 102, 0.55);
    }

    .wa-pill-label {
        background: #ffffff;
        color: #1F3A27;
        font-size: 0.82rem;
        font-weight: 700;
        padding: 8px 16px;
        border-radius: 50px;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
        border: 1px solid #E5DFC9;
        display: flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
        opacity: 0;
        transform: translateX(10px);
        transition: all 0.25s ease;
        pointer-events: none;
    }

    .wa-floating-btn:hover .wa-pill-label {
        opacity: 1;
        transform: translateX(0);
    }

    @media (max-width: 768px) {
        .wa-floating-btn {
            bottom: 20px;
            right: 20px;
        }
        .wa-btn-circle {
            width: 50px;
            height: 50px;
            font-size: 26px;
        }
        .wa-pill-label {
            display: none;
        }
    }
</style>

<a href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer" class="wa-floating-btn" title="Chat with us on WhatsApp" aria-label="Chat on WhatsApp">
    <span class="wa-pill-label">
        <i class="bi bi-chat-dots-fill text-success"></i> Chat on WhatsApp
    </span>
    <div class="wa-btn-circle">
        <i class="bi bi-whatsapp"></i>
    </div>
</a>
