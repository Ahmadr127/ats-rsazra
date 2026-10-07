<x-layouts.public title="Penawaran Sudah Direspon - RS Azra" main-class="w-full bg-paper">

<style>
    .offer-done { width: 100%; padding: 56px 4vw 96px; }
    .offer-eyebrow {
        font-family: "IBM Plex Mono", monospace;
        font-size: 12px; color: #5a6864;
        text-transform: uppercase; letter-spacing: 0.14em; font-weight: 500;
        margin-bottom: 16px; display: flex; align-items: center; gap: 10px;
    }
    .offer-eyebrow::before { content: ""; width: 28px; height: 1px; background: #8a948f; }
    .offer-h1 {
        font-family: "IBM Plex Serif", Georgia, serif; font-weight: 500;
        font-size: clamp(34px, 4.6vw, 58px); line-height: 1.04; letter-spacing: -0.02em;
        margin: 0 0 14px; color: #0d1614; text-wrap: balance; max-width: 20ch;
    }
    .offer-lede { font-size: 17px; line-height: 1.6; color: #2a3835; max-width: 62ch; margin: 0; }
    @media (max-width: 900px) {
        .offer-done { padding: 36px 4vw 64px; }
    }
</style>

<div class="offer-done">
    <div class="offer-eyebrow">Surat Penawaran · Selesai</div>
    <h1 class="offer-h1">Anda Sudah Merespon</h1>
    <p class="offer-lede">Anda sudah merespon penawaran kerja ini sebelumnya. Respon tidak dapat diubah.</p>
</div>

</x-layouts.public>
