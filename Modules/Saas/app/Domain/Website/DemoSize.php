<?php

namespace Modules\Saas\Domain\Website;

/** Stable, locale-independent school-size codes stored with demo leads. */
enum DemoSize: string
{
    case UpTo300 = 'up_to_300';
    case From300To800 = '300_to_800';
    case From800To2000 = '800_to_2000';
    case Over2000 = 'over_2000';

    public function labelKey(): string
    {
        return "saas::marketing.form.size_options.{$this->value}";
    }

    /** @return list<array{value: string, label: string}> */
    public static function localizedOptions(): array
    {
        return array_map(
            static fn (self $size): array => [
                'value' => $size->value,
                'label' => __($size->labelKey()),
            ],
            self::cases(),
        );
    }
}
