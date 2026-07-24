<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Normalize existing installations to the supported regional settings.
     */
    public function up(): void
    {
        $allowedCurrencies = ['USD', 'SAR', 'YER'];
        $allowedTimezones = ['Asia/Aden', 'Asia/Riyadh', 'Asia/Qatar'];

        DB::table('configs')
            ->where('name', 'system')
            ->get(['id', 'value'])
            ->each(function (object $config) use ($allowedCurrencies, $allowedTimezones): void {
                $value = json_decode($config->value, true) ?: [];

                if (! in_array($value['timezone'] ?? null, $allowedTimezones, true)) {
                    $value['timezone'] = 'Asia/Aden';
                }

                $value['currencies'] = implode(',', $allowedCurrencies);

                if (! in_array($value['currency'] ?? null, $allowedCurrencies, true)) {
                    $value['currency'] = 'USD';
                }

                DB::table('configs')
                    ->where('id', $config->id)
                    ->update(['value' => json_encode($value, JSON_UNESCAPED_UNICODE)]);
            });
    }

    public function down(): void
    {
        // Existing custom values cannot be reconstructed safely.
    }
};
