<x-layouts.public title="Penawaran Ditolak - RS Azra" main-class="w-full bg-paper">

<style>
    .offer-actions { margin-top: 40px; border-top: 2px solid #0d1614; padding-top: 24px; }
    .offer-link {
        font-size: 15px; font-weight: 600; color: rgb(0,119,116);
        text-decoration: none; display: inline-flex; align-items: center; gap: 8px;
    }
    .offer-link:hover { color: rgb(0,88,85); }
</style>

<div class="container-public section">
    <div class="badge badge-primary">Surat Penawaran · Ditolak</div>
    <h1 class="offer-h1">Penawaran Ditolak</h1>
    <p class="offer-lede">Anda telah menolak penawaran kerja dari RS Azra. Terima kasih atas waktu dan pertimbangan Anda.</p>
    <div class="offer-actions">
        <a href="{{ route('karier.index') }}" class="btn btn-outline">
            Lihat lowongan lainnya
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M13 6l6 6-6 6"/></svg>
        </a>
    </div>
</div>

</x-layouts.public>
