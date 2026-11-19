<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class QuestionImportReader implements SkipsEmptyRows, ToCollection, WithHeadingRow
{
    /** @var Collection<int, Collection<string, mixed>> */
    public Collection $rows;

    private bool $captured = false;

    public function __construct()
    {
        $this->rows = collect();
    }

    public function collection(Collection $rows): void
    {
        // Import hanya membaca sheet pertama ("Soal"); sheet Contoh dan Petunjuk diabaikan.
        if ($this->captured) {
            return;
        }

        $this->rows = $rows;
        $this->captured = true;
    }
}
