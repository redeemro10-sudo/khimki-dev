@php
    $wa = preg_replace('/\D+/', '', (string) get_option('site_whatsapp', ''));
    $tg = ltrim((string) get_option('site_telegram', ''), '@');
    $viber = preg_replace('/\D+/', '', (string) get_option('site_viber', ''));
    $mail = sanitize_email((string) get_option('site_email_public', ''));

    $links = [
        'wa' => $wa ? 'https://api.whatsapp.com/send?phone=' . $wa : null,
        'tg' => $tg ? 'https://t.me/' . $tg : null,
        'viber' => $viber ? 'viber://chat?number=%2B' . $viber : null,
        'mail' => $mail ? 'mailto:' . $mail : null,
    ];
@endphp

<div id="fab-box"
    class="fixed bottom-4 right-4 z-[120] flex items-center gap-2 md:flex-col md:items-end pointer-events-none">

    {{-- WhatsApp --}}
    @if ($links['wa'])
        <a href="{{ $links['wa'] }}"
            class="fab-btn pointer-events-auto inline-flex items-center justify-center rounded-full border bg-white shadow p-3 hover:shadow-md"
            target="_blank" rel="noopener" aria-label="WhatsApp">
            <svg viewBox="0 0 32 32" class="h-5 w-5" fill="currentColor">
                <path
                    d="M19.11 17.07c-.27-.13-1.58-.78-1.82-.86-.24-.09-.42-.13-.6.13-.18.27-.69.86-.84 1.04-.15.18-.31.2-.58.07-.27-.13-1.12-.41-2.14-1.3-.79-.7-1.32-1.57-1.47-1.84-.15-.27-.02-.42.11-.55.12-.12.27-.31.4-.47.13-.16.18-.27.27-.45.09-.18.04-.34-.02-.47-.07-.13-.6-1.44-.82-1.98-.22-.53-.45-.46-.6-.47-.16-.01-.34-.01-.52-.01-.18 0-.47.07-.72.34-.25.27-.95.93-.95 2.26s.97 2.62 1.11 2.8c.13.18 1.91 2.91 4.63 4.08.65.28 1.16.44 1.56.56.66.21 1.26.18 1.74.11.53-.08 1.58-.65 1.8-1.27.22-.62.22-1.15.15-1.27-.06-.12-.24-.2-.52-.34z" />
                <path
                    d="M26.75 5.25C23.87 2.37 20.1 1 16.12 1 8.24 1 1.94 7.29 1.94 15.17c0 2.49.65 4.92 1.89 7.06L1 31l8.98-2.77c2.08 1.14 4.41 1.74 6.84 1.74h.01c7.88 0 14.17-6.29 14.17-14.17 0-3.76-1.46-7.3-4.25-10.09zm-10.63 23.6h-.01c-2.12 0-4.19-.58-5.98-1.68l-.43-.26-5.33 1.64 1.71-5.2-.28-.46c-1.17-1.93-1.79-4.15-1.79-6.44 0-6.92 5.63-12.56 12.56-12.56 3.36 0 6.52 1.31 8.89 3.69 2.37 2.37 3.68 5.53 3.68 8.89 0 6.93-5.63 12.56-12.56 12.56z" />
            </svg>
        </a>
    @endif

    {{-- Telegram --}}
    @if ($links['tg'])
        <a href="{{ $links['tg'] }}"
            class="fab-btn pointer-events-auto inline-flex items-center justify-center rounded-full border bg-white shadow p-3 hover:shadow-md"
            target="_blank" rel="noopener" aria-label="Telegram">
            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="currentColor">
                <path
                    d="M9.04 15.34l-.38 5.39c.54 0 .77-.23 1.05-.5l2.53-2.42 5.25 3.85c.96.53 1.64.25 1.9-.89l3.44-16.1h.01c.31-1.45-.52-2.02-1.47-1.67L1.3 9.4c-1.4.54-1.38 1.32-.24 1.66l5.04 1.57L18.9 6.45c.89-.58 1.7-.26 1.03.33" />
            </svg>
        </a>
    @endif

    {{-- Кнопка "наверх" --}}
    <button id="backToTop"
        class="fab-btn pointer-events-auto inline-flex items-center justify-center rounded-full border bg-white shadow p-3 hover:shadow-md opacity-0 translate-y-2 transition md:translate-y-0"
        aria-label="Наверх">
        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="currentColor">
            <path d="M12 4l-8 8h6v8h4v-8h6z" />
        </svg>
    </button>

    {{-- Доп.: Viber / Email (если понадобятся) --}}
    @if ($links['viber'])
        <a href="{{ $links['viber'] }}"
            class="fab-btn pointer-events-auto hidden md:inline-flex items-center justify-center rounded-full border bg-white shadow p-3 hover:shadow-md"
            aria-label="Viber"><svg viewBox="0 0 24 24" class="h-5 w-5" fill="currentColor">
                <path
                    d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.472-.148-.671.149-.198.297-.769.967-.942 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.479-1.761-1.652-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.447-.52.149-.173.198-.297.298-.495.099-.198.05-.372-.025-.52-.075-.149-.671-1.611-.92-2.207-.242-.579-.487-.5-.671-.51-.173-.009-.372-.009-.57-.009-.198 0-.52.074-.795.372-.273.297-1.048 1.024-1.048 2.498s1.073 2.894 1.224 3.092c.149.198 2.112 3.232 5.12 4.53.716.307 1.274.49 1.713.627.72.229 1.378.196 1.903.118.579-.087 1.742-.716 1.989-1.397.248-.681.248-1.257.173-1.38-.074-.124-.223-.198-.495-.347z" />
                <path
                    d="M20.487 3.515C18.23 1.258 15.22 0 11.999 0 5.728 0 .6 5.127.6 11.4c0 2.01.525 3.969 1.53 5.696L0 24l6.996-2.116c1.657.9 3.533 1.372 5.503 1.372 6.273 0 11.4-5.128 11.4-11.4 0-3.221-1.259-6.231-3.516-8.488zM12.5 21.4c-1.762 0-3.49-.47-5.004-1.361l-.359-.213-4.154 1.257 1.33-4.045-.223-.366C2.197 15.01 1.7 13.23 1.7 11.4 1.7 6.074 6.274 1.5 11.6 1.5c2.992 0 5.804 1.165 7.926 3.287S22.8 8.408 22.8 11.4c0 5.326-4.574 9.9-10.3 9.9z" />
            </svg></a>
    @endif
    @if ($links['mail'])
        <a href="{{ $links['mail'] }}"
            class="fab-btn pointer-events-auto hidden md:inline-flex items-center justify-center rounded-full border bg-white shadow p-3 hover:shadow-md"
            aria-label="Email"><svg viewBox="0 0 24 24" class="h-5 w-5" fill="currentColor">
                <path d="M12 13L2 6.76V18h20V6.76z" />
                <path d="M12 11L2 4h20z" />
            </svg></a>
    @endif
</div>
