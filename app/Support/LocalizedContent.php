<?php

namespace App\Support;

class LocalizedContent
{
    /**
     * Resolve CMS copy stored as "Arabic || English".
     *
     * Content without a separator remains valid and is shown in every locale.
     */
    public static function get(?string $value, ?string $locale = null): string
    {
        $value = trim((string) $value);

        if (! str_contains($value, '||')) {
            return $value;
        }

        [$arabic, $english] = array_pad(
            array_map('trim', explode('||', $value, 2)),
            2,
            ''
        );

        $locale ??= app()->getLocale();
        $preferred = str_starts_with($locale, 'ar') ? $arabic : $english;

        return $preferred ?: ($arabic ?: $english);
    }
}
