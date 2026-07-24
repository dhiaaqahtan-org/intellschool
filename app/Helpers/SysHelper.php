<?php

namespace App\Helpers;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class SysHelper
{
    /**
     * Determine whether write operations should be blocked for the demo/test mode.
     */
    public static function isTestMode(): bool
    {
        return config('app.mode') === 'test';
    }

    /**
     * Used to compare version
     */
    public static function versionComparison(string $ver1, string $ver2, ?string $operator = null): bool
    {
        $p = '#(\.0+)+($|-)#';
        $ver1 = preg_replace($p, '', $ver1);
        $ver2 = preg_replace($p, '', $ver2);

        return isset($operator) ?
            version_compare($ver1, $ver2, $operator) :
            version_compare($ver1, $ver2);
    }

    /**
     * Is key contains boolean value
     */
    public static function isBoolean($key): bool
    {
        if (Str::startsWith($key, ['enable', 'disable', 'allow', 'show', 'hide', 'should', 'is_', 'has_'])) {
            return true;
        }

        return false;
    }

    /**
     * Get post max size
     */
    public static function getPostMaxSize(): int
    {
        if (is_numeric($postMaxSize = ini_get('post_max_size'))) {
            return (int) $postMaxSize;
        }

        $metric = strtoupper(substr($postMaxSize, -1));
        $postMaxSize = (int) $postMaxSize;

        switch ($metric) {
            case 'K':
                return $postMaxSize * 1024;
            case 'M':
                return $postMaxSize * 1048576;
            case 'G':
                return $postMaxSize * 1073741824;
            default:
                return $postMaxSize;
        }
    }

    public static function fileSize($bytes): string
    {
        $i = floor(log($bytes) / log(1024));

        $sizes = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

        return sprintf('%.02F', $i > 0 ? ($bytes / pow(1024, $i)) : 0) * 1 .' '.$sizes[$i];
    }

    public static function isValidCurrency($currency): bool
    {
        if (! in_array($currency, explode(',', config('config.system.currencies')))) {
            return false;
        }

        return true;
    }

    public static function getAvailableCurrencies(): array
    {
        return explode(',', config('config.system.currencies'));
    }

    public static function getAvailableCurrencyList(array $selectedCurrencies = []): array
    {
        if (! count($selectedCurrencies)) {
            $selectedCurrencies = self::getAvailableCurrencies();
        }

        $currencies = Arr::where(ListHelper::getList('currencies', 'name'), function ($currency) use ($selectedCurrencies) {
            return in_array(Arr::get($currency, 'value'), $selectedCurrencies);
        });

        return array_values($currencies);
    }

    public static function getCurrencyDetail($currency = null): ?array
    {
        if (is_null($currency)) {
            $currency = config('config.system.currency');
        }

        return collect(Arr::getVar('currencies'))->firstWhere('name', $currency);
    }

    /**
     * Format currency
     */
    public static function formatAmount(mixed $amount, $currency = null): float
    {
        if (! is_numeric($amount)) {
            return 0;
        }

        if (is_null($currency)) {
            $currency = config('config.system.currency');
        }

        $currencyDetail = collect(Arr::getVar('currencies'))->firstWhere('name', $currency);

        return round($amount, Arr::get($currencyDetail, 'decimal', 2));
    }

    /**
     * Format currency
     */
    public static function formatCurrency(mixed $amount, $currency = null): string
    {
        if (! is_numeric($amount)) {
            return '-';
        }

        if (is_null($currency)) {
            $currency = config('config.system.currency');
        }

        $currencyDetail = collect(Arr::getVar('currencies'))->firstWhere('name', $currency);

        $amount = self::formatAmount($amount);

        if (Arr::get($currencyDetail, 'position') === 'prefix') {
            return Arr::get($currencyDetail, 'symbol').''.$amount;
        }

        return $amount.''.Arr::get($currencyDetail, 'symbol');
    }

    /**
     * Format percentage
     */
    public static function formatPercentage($value, $symbol = true): ?string
    {
        if (is_null($value)) {
            return $value;
        }

        return round($value, 2).($symbol ? '%' : '');
    }

    public static function getPercentageColor($percent = 0): string
    {
        return match (true) {
            $percent <= 20 => 'bg-danger',
            $percent > 20 && $percent <= 40 => 'bg-warning',
            $percent > 40 && $percent <= 80 => 'bg-info',
            $percent > 80 => 'bg-success',
        };
    }

    public static function getUsagePercentageColor($percent = 0): string
    {
        return match (true) {
            $percent <= 10 => 'bg-success',
            $percent > 10 && $percent <= 50 => 'bg-info',
            $percent > 50 && $percent <= 80 => 'bg-warning',
            $percent > 80 => 'bg-danger',
        };
    }

    /**
     * Calculate percentage
     */
    public static function calcPercentage($amount = 0, $percent = 0): float
    {
        return self::formatAmount(($amount * $percent) / 100);
    }

    public static function cleanInput($input): mixed
    {
        if (empty($input)) {
            return null;
        }

        return strip_tags($input);
    }

    /**
     * Set team for permission
     */
    public static function setTeam(?int $teamId = null): void
    {
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($teamId);
    }
}
