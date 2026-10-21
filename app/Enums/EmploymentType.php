<?php

namespace App\Enums;

enum EmploymentType: string
{
    case FullTime = 'full_time';
    case PartTime = 'part_time';
    case Contract = 'contract';
    case Internship = 'internship';

    public function label(): string
    {
        return match ($this) {
            self::FullTime => 'Penuh Waktu',
            self::PartTime => 'Paruh Waktu',
            self::Contract => 'Kontrak',
            self::Internship => 'Magang',
        };
    }

    /**
     * Nilai employmentType untuk schema.org JobPosting.
     */
    public function schemaOrgType(): string
    {
        return match ($this) {
            self::FullTime => 'FULL_TIME',
            self::PartTime => 'PART_TIME',
            self::Contract => 'CONTRACTOR',
            self::Internship => 'INTERN',
        };
    }
}
