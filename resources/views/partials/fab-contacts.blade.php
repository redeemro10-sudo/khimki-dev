@php
    $links = [
        'wa' => base64_encode('https://wa.me/79879815874'),
        'tg' => base64_encode('https://t.me/elllie_mng'),
        'max' => base64_encode('https://max.ru/u/f9LHodD0cOKe3IufFQsYRevc9Xg5C9Ti1M8oCrvpFvP3YizC1L0e0bBa5VU'),
    ];
@endphp

<div id="fab-box"
    class="fixed bottom-4 right-4 z-[120] flex items-center gap-2 md:flex-col md:items-end pointer-events-none">

    <a data-contact-link="{{ $links['wa'] }}"
        class="fab-btn pointer-events-auto inline-flex items-center justify-center rounded-full border bg-white shadow p-3 hover:shadow-md"
        aria-label="WhatsApp">
        <svg viewBox="0 0 32 32" class="h-5 w-5" fill="currentColor">
            <path
                d="M19.11 17.07c-.27-.13-1.58-.78-1.82-.86-.24-.09-.42-.13-.6.13-.18.27-.69.86-.84 1.04-.15.18-.31.2-.58.07-.27-.13-1.12-.41-2.14-1.3-.79-.7-1.32-1.57-1.47-1.84-.15-.27-.02-.42.11-.55.12-.12.27-.31.4-.47.13-.16.18-.27.27-.45.09-.18.04-.34-.02-.47-.07-.13-.6-1.44-.82-1.98-.22-.53-.45-.46-.6-.47-.16-.01-.34-.01-.52-.01-.18 0-.47.07-.72.34-.25.27-.95.93-.95 2.26s.97 2.62 1.11 2.8c.13.18 1.91 2.91 4.63 4.08.65.28 1.16.44 1.56.56.66.21 1.26.18 1.74.11.53-.08 1.58-.65 1.8-1.27.22-.62.22-1.15.15-1.27-.06-.12-.24-.2-.52-.34z" />
            <path
                d="M26.75 5.25C23.87 2.37 20.1 1 16.12 1 8.24 1 1.94 7.29 1.94 15.17c0 2.49.65 4.92 1.89 7.06L1 31l8.98-2.77c2.08 1.14 4.41 1.74 6.84 1.74h.01c7.88 0 14.17-6.29 14.17-14.17 0-3.76-1.46-7.3-4.25-10.09zm-10.63 23.6h-.01c-2.12 0-4.19-.58-5.98-1.68l-.43-.26-5.33 1.64 1.71-5.2-.28-.46c-1.17-1.93-1.79-4.15-1.79-6.44 0-6.92 5.63-12.56 12.56-12.56 3.36 0 6.52 1.31 8.89 3.69 2.37 2.37 3.68 5.53 3.68 8.89 0 6.93-5.63 12.56-12.56 12.56z" />
        </svg>
    </a>

    <a data-contact-link="{{ $links['tg'] }}"
        class="fab-btn pointer-events-auto inline-flex items-center justify-center rounded-full border bg-white shadow p-3 hover:shadow-md"
        aria-label="Telegram">
        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="currentColor">
            <path
                d="M9.04 15.34l-.38 5.39c.54 0 .77-.23 1.05-.5l2.53-2.42 5.25 3.85c.96.53 1.64.25 1.9-.89l3.44-16.1h.01c.31-1.45-.52-2.02-1.47-1.67L1.3 9.4c-1.4.54-1.38 1.32-.24 1.66l5.04 1.57L18.9 6.45c.89-.58 1.7-.26 1.03.33" />
        </svg>
    </a>

    <a data-contact-link="{{ $links['max'] }}"
        class="fab-btn pointer-events-auto inline-flex items-center justify-center rounded-full border bg-white shadow p-3 hover:shadow-md"
        aria-label="MAX">
        <svg viewBox="0 0 1000 1000" class="h-5 w-5" fill="currentColor">
            <path d="M508.2 878.3c-75 0-109.8-11-170.4-54.7-38.3 49.3-159.7 87.8-165 21.9 0-49.5-11-91.2-23.4-136.9-14.8-56.2-31.6-118.8-31.6-209.5 0-216.6 177.8-379.6 388.4-379.6 210.8 0 376 171 376 381.6.7 207.3-166.6 376.1-374 377.2m3.1-571.6c-102.6-5.3-182.5 65.7-200.2 177-14.6 92.2 11.3 204.4 33.4 210.2 10.6 2.6 37.2-19 53.8-35.6a190 190 0 0 0 92.7 33c106.3 5.1 197.1-75.8 204.2-182 4.2-106.3-77.7-196.5-184-202.6Z" />
        </svg>
    </a>

    <button id="backToTop"
        class="fab-btn pointer-events-auto inline-flex items-center justify-center rounded-full border bg-white shadow p-3 hover:shadow-md opacity-0 translate-y-2 transition md:translate-y-0"
        aria-label="Наверх">
        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="currentColor">
            <path d="M12 4l-8 8h6v8h4v-8h6z" />
        </svg>
    </button>
</div>
