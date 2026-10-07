<x-layouts.public title="Tolak Penawaran - RS Azra" main-class="w-full bg-paper">

<style>
    .offer-wrap { width: 100%; padding: 56px 4vw 96px; }
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
    .offer-panel { background: #fff; border: 1px solid #0d1614; padding: 36px; position: sticky; top: 100px; }
    .offer-panel-title { font-family: "IBM Plex Serif", serif; font-size: 24px; font-weight: 500; margin: 0 0 8px; }
    .offer-panel-desc { font-size: 15px; line-height: 1.6; color: #5a6864; margin: 0 0 24px; }
    .offer-field label {
        display: block;
        font-family: "IBM Plex Mono", monospace; font-size: 11px; text-transform: uppercase;
        letter-spacing: 0.08em; color: #5a6864; font-weight: 600; margin-bottom: 8px;
    }
    .offer-field textarea {
        width: 100%; border: 1px solid #d9ddd9; background: #fafaf9;
        padding: 14px 16px; font-size: 16px; line-height: 1.6; color: #0d1614;
        font-family: "IBM Plex Sans", system-ui, sans-serif; outline: none; resize: vertical;
    }
    .offer-field textarea:focus { border-color: #b54327; background: #fff; }
    .offer-submit {
        width: 100%; background: #b54327; color: #fff; border: 0; margin-top: 20px;
        padding: 16px; font-size: 16px; font-weight: 600; cursor: pointer;
        font-family: "IBM Plex Sans", system-ui, sans-serif; transition: background 0.15s;
    }
    .offer-submit:hover { background: #93351f; }
    @media (max-width: 900px) {
        .offer-wrap { padding: 36px 4vw 64px; }
        .offer-grid { grid-template-columns: 1fr; gap: 32px; }
        .offer-panel { position: static; padding: 24px; }
    }
</style>

<div class="offer-wrap">
    <div class="offer-eyebrow">Surat Penawaran · RS Azra</div>
    <h1 class="offer-h1">Tolak Penawaran Kerja</h1>
    <p class="offer-lede">Anda akan menolak penawaran kerja dari RS Azra. Tindakan ini tidak dapat dibatalkan.</p>

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

        <div class="offer-panel">
            <p class="offer-panel-title">Konfirmasi Penolakan</p>
            <p class="offer-panel-desc">Ceritakan alasan Anda bila berkenan — masukan ini membantu kami berbenah.</p>
            <form method="POST" action="{{ request()->fullUrl() }}">
                @csrf
                <div class="offer-field">
                    <label for="rejection_reason">Alasan Penolakan (opsional)</label>
                    <textarea
                        id="rejection_reason"
                        name="rejection_reason"
                        rows="4"
                        placeholder="Mohon beri tahu alasan Anda menolak penawaran ini..."
                    >{{ old('rejection_reason') }}</textarea>
                </div>
                <button type="submit" class="offer-submit">Konfirmasi Penolakan</button>
            </form>
        </div>
    </div>
</div>

</x-layouts.public>
