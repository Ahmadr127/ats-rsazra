<x-layouts.public title="Terima Penawaran - RS Azra" main-class="w-full bg-paper">

<style>
    .offer-grid {
        display: grid; grid-template-columns: 1.2fr 1fr; gap: 48px;
        margin-top: 48px; align-items: start;
    }
    .offer-section-h {
        font-family: "IBM Plex Mono", monospace; font-size: 12px; text-transform: uppercase;
        letter-spacing: 0.1em; font-weight: 600; color: #0d1614; margin: 0 0 18px;
        border-top: 2px solid #0d1614; padding-top: 14px;
    }
    .offer-dl { margin: 0; }
    .offer-dl-row { display: flex; gap: 20px; padding: 14px 0; border-bottom: 1px solid #ebeeea; }
    .offer-dl-row:last-child { border-bottom: 0; }
    .offer-dl-label {
        font-family: "IBM Plex Mono", monospace; font-size: 11px; color: #5a6864;
        text-transform: uppercase; letter-spacing: 0.08em; width: 150px; flex-shrink: 0; padding-top: 4px;
    }
    .offer-dl-value { font-size: 17px; font-weight: 500; color: #0d1614; }
    @media (max-width: 900px) {
        .offer-grid { grid-template-columns: 1fr; gap: 32px; }
    }
</style>

<div class="container-public section">
    <div class="badge badge-primary">Surat Penawaran · RS Azra</div>
    <h1 class="offer-h1">Terima Penawaran Kerja</h1>
    <p class="offer-lede">Anda akan menerima penawaran kerja dari RS Azra. Tindakan ini tidak dapat dibatalkan.</p>

    <div class="offer-grid">
        <div>
            <h2 class="offer-section-h">Ringkasan Penawaran</h2>
            <dl class="offer-dl">
                <div class="offer-dl-row">
                    <dt class="offer-dl-label">Posisi</dt>
                    <dd class="offer-dl-value">{{ $offering->jabatan_ditawarkan }}</dd>
                </div>
                <div class="offer-dl-row">
                    <dt class="offer-dl-label">Gaji</dt>
                    <dd class="offer-dl-value">{{ $offering->gaji }}</dd>
                </div>
                <div class="offer-dl-row">
                    <dt class="offer-dl-label">Tanggal Mulai</dt>
                    <dd class="offer-dl-value">{{ $offering->tanggal_mulai->format('d M Y') }}</dd>
                </div>
            </dl>
        </div>

        <div class="card" style="position: sticky; top: 100px;">
            <p style="font-family: 'IBM Plex Serif', serif; font-size: 24px; font-weight: 500; margin: 0 0 8px;">Konfirmasi Penerimaan</p>
            <p style="font-size: 15px; line-height: 1.6; color: #5a6864; margin: 0 0 24px;">Dengan menekan tombol di bawah, Anda menyatakan menerima penawaran ini dan bersedia bergabung sesuai tanggal mulai.</p>
            <form method="POST" action="{{ request()->fullUrl() }}">
                @csrf
                <button type="submit" class="btn btn-primary" style="width: 100%;">Konfirmasi Penerimaan</button>
            </form>
        </div>
    </div>
</div>

</x-layouts.public>
