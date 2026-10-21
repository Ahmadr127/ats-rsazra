<x-layouts.public
    :title="$vacancy->judul_posisi.' — '.$vacancy->unit->nama.' | Karier RS Azra'"
    :metaDescription="$vacancy->seoDescription()"
    :canonical="route('karier.show', $vacancy)"
    :ogImage="$vacancy->flyer_path ? url($vacancy->flyerUrl()) : asset('images/logo.png')"
    :ogImageAlt="'Flyer lowongan '.$vacancy->judul_posisi"
    ogType="article"
    robots="index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1"
    main-class="w-full bg-paper"
>

<x-ui.page-hero
    eyebrow="{{ $vacancy->unit->nama }}"
    title="{{ $vacancy->judul_posisi }}"
    lede="Tenggat lamaran {{ $vacancy->tenggat_lamaran->format('d M Y') }} · {{ $vacancy->jenis_pekerjaan->label() }}"
>
    <div class="mt-4 flex flex-wrap gap-2">
        <x-ui.badge tone="info">{{ $vacancy->unit->nama }}</x-ui.badge>
        <x-ui.badge tone="neutral">{{ $vacancy->jenis_pekerjaan->label() }}</x-ui.badge>
        @if ($vacancy->lulusan)
            <x-ui.badge tone="neutral">Pendidikan Minimal {{ $vacancy->lulusan->label() }}</x-ui.badge>
        @endif
        @if ($vacancy->created_at->gte(now()->subDays(3)))
            <x-ui.badge tone="success">Baru</x-ui.badge>
        @endif
        @if ($vacancy->tenggat_lamaran->lte(now()->addDays(7)))
            <x-ui.badge tone="warning">Mendesak</x-ui.badge>
        @endif
    </div>
</x-ui.page-hero>

<div class="ui-shell grid w-full items-start gap-6 py-6 lg:grid-cols-[1fr_300px]">
    <div class="min-w-0">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-[13px] font-semibold text-primary hover:text-primary-dark">&larr; Kembali ke Lowongan</a>

        <x-ui.card id="deskripsi" class="scroll-mt-20 mt-4">
            <h2 class="ui-section-title">Deskripsi Pekerjaan</h2>
            @if ($vacancy->lulusan)
                <p class="mt-1 text-[13px] text-ink-3">Pendidikan Minimal: <span class="font-medium text-ink">{{ $vacancy->lulusan->label() }}</span></p>
            @endif
            <div class="mt-3 whitespace-pre-line text-[14px] leading-relaxed text-ink-2">{{ $vacancy->deskripsi_pekerjaan }}</div>
        </x-ui.card>

        <x-ui.card class="mt-4">
            <h2 class="ui-section-title">Kualifikasi</h2>
            <div class="mt-3 whitespace-pre-line text-[14px] leading-relaxed text-ink-2">{{ $vacancy->kualifikasi }}</div>
        </x-ui.card>
    </div>

    <aside class="w-full lg:sticky lg:top-20">
        <x-ui.card class="p-0!">
            <div class="border-b border-line bg-primary px-5 py-3 text-[12px] font-semibold uppercase tracking-wide text-white">Detail Posisi</div>
            <dl class="space-y-4 px-5 py-5">
                @if ($vacancy->lulusan)
                    <div>
                        <dt class="ui-label">Pendidikan Minimal</dt>
                        <dd class="mt-1 text-[14px] font-medium text-ink">{{ $vacancy->lulusan->label() }}</dd>
                    </div>
                @endif
                <div>
                    <dt class="ui-label">Jenis Pekerjaan</dt>
                    <dd class="mt-1 text-[14px] font-medium text-ink">{{ $vacancy->jenis_pekerjaan->label() }}</dd>
                </div>
                <div>
                    <dt class="ui-label">Jumlah Posisi</dt>
                    <dd class="mt-1 text-[14px] font-medium text-ink">{{ $vacancy->jumlah_posisi }}</dd>
                </div>
                <div>
                    <dt class="ui-label">Tenggat Lamaran</dt>
                    <dd class="mt-1 text-[14px] font-medium text-ink">{{ $vacancy->tenggat_lamaran->format('d M Y') }}</dd>
                </div>
                <div>
                    <dt class="ui-label">Ditayangkan</dt>
                    <dd class="mt-1 text-[14px] font-medium text-ink">{{ $vacancy->created_at->locale('id')->diffForHumans() }}</dd>
                </div>
            </dl>
            <div class="px-5 pb-5">
                <x-ui.button href="{{ route('karier.lamar', $vacancy) }}" class="w-full">Lamar Sekarang &rarr;</x-ui.button>
            </div>
        </x-ui.card>
    </aside>
</div>

@push('structured_data')
    @php
        $vacancyUrl = route('karier.show', $vacancy);
        $postedAt = ($vacancy->published_at ?? $vacancy->created_at)->toIso8601String();
        $validThrough = $vacancy->tenggat_lamaran->copy()->endOfDay()->toIso8601String();
        $jobDescription = trim(preg_replace('/\s+/', ' ', strip_tags(($vacancy->deskripsi_pekerjaan ?? '').' '.($vacancy->kualifikasi ?? ''))) ?? '');
        $jobDescription = $jobDescription !== '' ? $jobDescription : $vacancy->judul_posisi;
        $alamat = \App\Models\SiteSetting::get(
            'kontak_alamat',
            'Jl. Pintu Air No.1, Sempur, Bogor Tengah, Kota Bogor 16112',
        );

        $structuredData = json_encode([
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'BreadcrumbList',
                    'itemListElement' => [
                        [
                            '@type' => 'ListItem',
                            'position' => 1,
                            'name' => 'Lowongan',
                            'item' => route('home'),
                        ],
                        [
                            '@type' => 'ListItem',
                            'position' => 2,
                            'name' => $vacancy->judul_posisi,
                            'item' => $vacancyUrl,
                        ],
                    ],
                ],
                [
                    '@type' => 'JobPosting',
                    'title' => $vacancy->judul_posisi,
                    'description' => $jobDescription,
                    'datePosted' => $postedAt,
                    'validThrough' => $validThrough,
                    'employmentType' => $vacancy->jenis_pekerjaan->schemaOrgType(),
                    'identifier' => [
                        '@type' => 'PropertyValue',
                        'name' => 'RS Azra',
                        'value' => $vacancy->slug ?: (string) $vacancy->id,
                    ],
                    'url' => $vacancyUrl,
                    'directApply' => true,
                    'hiringOrganization' => [
                        '@type' => 'Organization',
                        'name' => 'RS Azra',
                        'sameAs' => ['https://rsazra.co.id'],
                        'logo' => asset('images/logo.png'),
                    ],
                    'jobLocation' => [
                        '@type' => 'Place',
                        'address' => [
                            '@type' => 'PostalAddress',
                            'streetAddress' => $alamat,
                            'addressLocality' => 'Bogor',
                            'addressRegion' => 'Jawa Barat',
                            'postalCode' => '16112',
                            'addressCountry' => 'ID',
                        ],
                    ],
                ],
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE);
    @endphp
    <script type="application/ld+json">{!! $structuredData !!}</script>
@endpush

</x-layouts.public>
