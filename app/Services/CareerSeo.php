<?php

namespace App\Services;

use App\Models\Vacancy;
use Illuminate\Support\Collection;

class CareerSeo
{
    public const DEFAULT_TITLE = 'Karier - RS Azra';

    public const DEFAULT_HERO = 'Jelajahi {jumlah_posisi} posisi terbuka di {jumlah_unit} unit RS Azra, termasuk {lowongan_terbaru}. Pilih lowongan, baca detailnya, dan lamar langsung dari halaman ini.';

    public const LATEST_FALLBACK = 'lowongan terbaru';

    /**
     * Nilai token berdasarkan lowongan yang benar-benar tampil di halaman
     * karier: published, belum kedaluwarsa, dan memiliki flyer.
     *
     * @return array{jumlah_posisi:int,jumlah_unit:int,lowongan_terbaru:string}
     */
    public function publicTokenValues(): array
    {
        $query = Vacancy::query()->publiclyListed();

        $latestTitles = (clone $query)
            ->orderByRaw('COALESCE(published_at, created_at) DESC')
            ->limit(3)
            ->pluck('judul_posisi');

        return $this->values(
            (clone $query)->count(),
            (clone $query)->distinct()->count('unit_id'),
            $latestTitles,
        );
    }

    /**
     * @param  Collection<int, string>  $latestTitles
     * @return array{jumlah_posisi:int,jumlah_unit:int,lowongan_terbaru:string}
     */
    public function values(int $totalRoles, int $unitCount, Collection $latestTitles): array
    {
        return [
            'jumlah_posisi' => $totalRoles,
            'jumlah_unit' => $unitCount,
            'lowongan_terbaru' => $latestTitles->isNotEmpty()
                ? $latestTitles->join(', ', ' dan ')
                : self::LATEST_FALLBACK,
        ];
    }

    /**
     * @param  array<string, int|string>  $values
     */
    public function compose(string $template, array $values): string
    {
        return strtr($template, [
            '{jumlah_posisi}' => (string) ($values['jumlah_posisi'] ?? 0),
            '{jumlah_unit}' => (string) ($values['jumlah_unit'] ?? 0),
            '{lowongan_terbaru}' => (string) ($values['lowongan_terbaru'] ?? self::LATEST_FALLBACK),
        ]);
    }

    /**
     * @param  array<string, int|string>  $values
     */
    public function hero(string $template, array $values): string
    {
        $template = trim($template);

        return $this->compose($template !== '' ? $template : self::DEFAULT_HERO, $values);
    }

    /**
     * @param  array<string, int|string>  $values
     */
    public function title(string $template, array $values): string
    {
        $template = trim($template);

        return $this->compose($template !== '' ? $template : self::DEFAULT_TITLE, $values);
    }

    /**
     * @param  array<string, int|string>  $values
     */
    public function description(string $template, string $heroLede, array $values): string
    {
        $template = trim($template);

        return $template !== '' ? $this->compose($template, $values) : $heroLede;
    }
}
