<?php

namespace App;

class ContactData
{
    public static function value(string $key, ?string $fallback = null): ?string
    {
        $value = get_theme_mod('contact_' . $key);
        if (is_string($value) && trim($value) !== '') {
            return trim($value);
        }

        if ($value !== false && $value !== null && !is_string($value)) {
            $value = (string) $value;
            if (trim($value) !== '') {
                return trim($value);
            }
        }

        $value = get_option('contact_' . $key);
        if (is_string($value) && trim($value) !== '') {
            return trim($value);
        }

        if ($value !== false && $value !== null && !is_string($value)) {
            $value = (string) $value;
            if (trim($value) !== '') {
                return trim($value);
            }
        }

        return $fallback !== null && trim($fallback) !== '' ? trim($fallback) : null;
    }

    public static function encoded(?string $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value !== '' ? base64_encode($value) : null;
    }

    public static function footerContacts(): array
    {
        $telegramUrl = self::value('telegram_url', 'https://t.me/elllie_mng');
        $telegramLabel = self::value('telegram');

        if ($telegramLabel === null && $telegramUrl !== null) {
            $telegramLabel = self::telegramHandleFromUrl($telegramUrl);
        }

        $email = self::value('email', 'info@prostitutkikhimki.com');
        $city = self::value('city', 'РҐРёРјРєРё, РњРѕСЃРєРѕРІСЃРєР°СЏ РѕР±Р»Р°СЃС‚СЊ');
        $hours = self::value('hours', '24/7 (РљСЂСѓРіР»РѕСЃСѓС‚РѕС‡РЅРѕ)');

        return [
            [
                'label' => 'РўРµР»РµРіСЂР°Рј',
                'encoded_value' => self::encoded($telegramLabel),
                'encoded_url' => self::encoded($telegramUrl),
            ],
            [
                'label' => 'Email',
                'encoded_value' => self::encoded($email),
                'encoded_url' => self::encoded($email ? 'mailto:' . $email : null),
            ],
            [
                'label' => null,
                'value' => $city,
            ],
            [
                'label' => null,
                'value' => $hours,
            ],
        ];
    }

    public static function fabLinks(): array
    {
        return array_filter([
            'wa' => self::encoded(self::value('whatsapp_url', 'https://wa.me/79879815874')),
            'tg' => self::encoded(self::value('telegram_url', 'https://t.me/elllie_mng')),
            'max' => self::encoded(self::value('max_url', 'https://max.ru/u/f9LHodD0cOKe3IufFQsYRevc9Xg5C9Ti1M8oCrvpFvP3YizC1L0e0bBa5VU')),
        ]);
    }

    public static function modelLinks(): array
    {
        $phone = self::value('phone', '79879815874');

        return [
            'encoded_phone' => self::encoded(self::phoneHref($phone)),
            'encoded_tg' => self::encoded(self::value('telegram_url', 'https://t.me/elllie_mng')),
            'encoded_wa' => self::encoded(self::value('whatsapp_url', 'https://wa.me/79879815874')),
            'encoded_max' => self::encoded(self::value('max_url', 'https://max.ru/u/f9LHodD0cOKe3IufFQsYRevc9Xg5C9Ti1M8oCrvpFvP3YizC1L0e0bBa5VU')),
        ];
    }

    private static function telegramHandleFromUrl(string $url): ?string
    {
        $path = trim((string) parse_url($url, PHP_URL_PATH), '/');

        return $path !== '' ? '@' . $path : null;
    }

    private static function phoneHref(?string $phone): ?string
    {
        if (!is_string($phone)) {
            return null;
        }

        $phone = trim($phone);
        if ($phone === '') {
            return null;
        }

        if (str_starts_with($phone, 'tel:')) {
            return $phone;
        }

        $normalized = preg_replace('/[^0-9+]/', '', $phone);

        return $normalized !== null && $normalized !== '' ? 'tel:' . $normalized : null;
    }
}
