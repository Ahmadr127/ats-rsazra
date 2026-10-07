<x-layouts.public title="Penawaran Ditolak - RS Azra" main-class="w-full bg-paper">

<style>
    .offer-done { width: 100%; padding: 56px 4vw 96px; }
    .offer-eyebrow {
        font-family: "IBM Plex Mono", monospace;
        font-size: 12px; color: #7a2d1a;
        text-transform: uppercase; letter-spacing: 0.14em; font-weight: 500;
        margin-bottom: 16px; display: flex; align-items: center; gap: 10px;
    }
    .offer-eyebrow::before { content: ""; width: 28px; height: 1px; background: #b54327; }
    .offer-h1 {
        font-family: "IBM Plex Serif", Georgia, serif; font-weight: 500;
        font-size: clamp(34px, 4.6vw, 58px); line-height: 1.04; letter-spacing: -0.02em;
        margin: 0 0 14px; color: #0d1614; text-wrap: balance; max-width: 20ch;
    }
    .offer-lede { font-size: 17px; line-height: 1.6; color: #2a3835; max-width: 62ch; margin: 0; }
    .offer-actions { margin-top: 40px; border-top: 2px solid #0d1614; padding-top: 24px; }
    .offer-link {
        font-size: 15px; font-weight: 600; color: rgb(0,119,116);
        text-decoration: none; display: inline-flex; align-items: center; gap: 8px;
    }
    .offer-link:hover { color: rgb(0,88,85); }
    @media (max-width: 900px) {
        .offer-done { padding: 36px 4vw 64px; }
    }
</style>

<div class="offer-done">
    <div class="offer-eyebrow">Surat Penawaran · Ditolak</div>
    <h1 class="offer-h1">Penawaran Ditolak</h1>
    <p class="offer-lede">Anda telah menolak penawaran kerja dari RS Azra. Terima kasih atas waktu dan pertimbangan Anda.</p>
    <div class="offer-actions">
        <a href="{{ route('karier.index') }}" class="offer-link">
            Lihat lowongan lainnya
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M13 6l6 6-6 6"/></svg>
        </a>
    </div>
</div>

</x-layouts.public>
