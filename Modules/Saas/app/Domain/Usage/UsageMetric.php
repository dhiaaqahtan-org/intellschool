<?php

namespace Modules\Saas\Domain\Usage;

enum UsageMetric: string
{
    case ActiveStudents = 'active_students';
    case ActiveStaff = 'active_staff';
    case Campuses = 'campuses';
    case StorageBytes = 'storage_bytes';
    case ApiCallsMonth = 'api_calls_month';

    public function featureCode(): string
    {
        return match ($this) {
            self::ActiveStudents => 'students.core',
            self::ActiveStaff => 'hr.employees',
            self::Campuses => 'campuses.max',
            self::StorageBytes => 'storage.gb',
            self::ApiCallsMonth => 'api.access',
        };
    }
}
