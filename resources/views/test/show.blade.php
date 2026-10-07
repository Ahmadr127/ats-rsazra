<x-layouts.public title="Tes Kompetensi - {{ $submission->application->vacancy->judul_posisi }} - RS Azra" main-class="w-full bg-paper">

<style>
    .exam-bar {
        position: sticky; top: 80px; z-index: 40;
        background: #0d1614; color: #fff;
        margin-top: 40px;
    }
    .exam-bar-inner {
        display: flex; align-items: center; justify-content: space-between; gap: 16px;
        padding: 14px 20px;
    }
    .exam-bar-title { font-size: 15px; font-weight: 600; }
    .exam-bar-sub { font-family: "IBM Plex Mono", monospace; font-size: 11px; color: #b8c0bd; letter-spacing: 0.06em; text-transform: uppercase; margin-top: 2px; }
    .exam-timer {
        font-family: "IBM Plex Mono", monospace; font-size: 22px; font-weight: 600;
        letter-spacing: 0.04em; display: inline-flex; align-items: center; gap: 10px;
    }
    .exam-timer svg { width: 18px; height: 18px; }
    .exam-progress { height: 3px; background: rgba(255,255,255,0.15); }
    .exam-progress > div { height: 100%; background: rgb(129,189,65); transition: width 0.3s; }
    .exam-grid {
        display: grid; grid-template-columns: 1fr 1fr; gap: 24px;
        margin-top: 40px;
    }
    .exam-q-head { display: flex; gap: 16px; align-items: flex-start; }
    .exam-q-num {
        font-family: "IBM Plex Serif", serif; font-size: 26px; font-weight: 500;
        color: rgb(0,119,116); line-height: 1; flex-shrink: 0; min-width: 44px;
    }
    .exam-q-text { font-size: 17px; line-height: 1.55; color: #0d1614; margin: 0; }
    .exam-q-meta {
        display: flex; gap: 10px; align-items: center; margin-top: 8px;
        font-family: "IBM Plex Mono", monospace; font-size: 11px;
        text-transform: uppercase; letter-spacing: 0.08em; color: #8a948f;
    }
    .exam-q-meta .tag-mc { color: #005f5c; }
    .exam-q-meta .tag-essay { color: #7a5a1a; }
    .exam-opt {
        display: flex; align-items: flex-start; gap: 14px;
        border: 1px solid #d9ddd9; padding: 14px 16px; cursor: pointer;
        transition: border-color 0.15s, background 0.15s;
    }
    .exam-opt:hover { border-color: rgb(0,119,116); background: #f4faf9; }
    .exam-opt input { width: 20px; height: 20px; margin-top: 1px; accent-color: rgb(0,119,116); flex-shrink: 0; cursor: pointer; }
    .exam-opt span { font-size: 16px; line-height: 1.5; color: #2a3835; }
    .exam-foot {
        display: flex; align-items: center; justify-content: space-between; gap: 16px;
        margin-top: 40px; border-top: 2px solid #0d1614; padding-top: 24px; flex-wrap: wrap;
    }
    .exam-hint { font-size: 14px; color: #5a6864; margin: 0; }
    .exam-dl { margin: 28px 0 0; }
    .exam-dl-row { display: flex; gap: 16px; padding: 12px 0; border-bottom: 1px solid #ebeeea; }
    .exam-dl-row:last-child { border-bottom: 0; }
    .exam-dl-label {
        font-family: "IBM Plex Mono", monospace; font-size: 11px; color: #5a6864;
        text-transform: uppercase; letter-spacing: 0.08em; width: 160px; flex-shrink: 0; padding-top: 3px;
    }
    .exam-dl-value { font-size: 16px; font-weight: 500; color: #0d1614; }
    @media (max-width: 1100px) {
        .exam-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 640px) {
        .exam-bar { top: 80px; }
        .exam-timer { font-size: 18px; }
        .exam-foot { flex-direction: column; align-items: stretch; }
    }
</style>

@if ($submission->isSubmitted())
    <div class="container-public section">
        <div class="badge badge-primary">Tes Kompetensi · Terkirim</div>
        <h1 class="offer-h1">Jawaban Anda sudah kami terima.</h1>
        <p class="offer-lede">Tim HR akan meninjau hasil tes dan menghubungi Anda melalui email.</p>
        <div class="card" style="margin-top: 32px;">
            <dl class="exam-dl">
                <div class="exam-dl-row">
                    <dt class="exam-dl-label">Posisi</dt>
                    <dd class="exam-dl-value">{{ $submission->application->vacancy->judul_posisi }}</dd>
                </div>
                <div class="exam-dl-row">
                    <dt class="exam-dl-label">Waktu Pengiriman</dt>
                    <dd class="exam-dl-value">{{ $submission->submitted_at->format('d M Y, H:i') }}</dd>
                </div>
            </dl>
        </div>
        <p class="exam-hint" style="margin-top: 20px;">Halaman ini dapat ditutup.</p>
    </div>
@else
    <div class="container-public section" x-data="testEngine({{ $submission->remainingSeconds() }})">
        <div class="badge badge-primary">Tes Kompetensi · RS Azra</div>
        <h1 class="offer-h1">{{ $submission->application->vacancy->judul_posisi }}</h1>
        <p class="offer-lede">Jawab semua pertanyaan di bawah ini. Tes akan otomatis terkirim saat waktu habis.</p>

        <div class="exam-bar">
            <div class="exam-bar-inner">
                <div>
                    <div class="exam-bar-title">{{ $questions->count() }} Soal</div>
                    <div class="exam-bar-sub">Tes Kompetensi</div>
                </div>
                <div class="exam-timer"
                     :style="timeLeft <= 60 ? 'color:#f0a390' : (timeLeft <= 300 ? 'color:#e8c26a' : 'color:#fff')">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span x-text="formatTime(timeLeft)"></span>
                </div>
            </div>
            <div class="exam-progress"><div style="width: 100%" x-bind:style="'width: ' + (timeLeft / {{ $submission->remainingSeconds() }} * 100) + '%'"></div></div>
        </div>

        <form id="test-form" method="POST" action="{{ route('tes.submit', $submission->token) }}">
            @csrf

            <div class="exam-grid">
                @foreach ($questions as $index => $question)
                    <div class="card">
                        <div class="exam-q-head">
                            <span class="exam-q-num">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <div>
                                <p class="exam-q-text">{{ $question->pertanyaan }}</p>
                                <div class="exam-q-meta">
                                    <span class="{{ $question->tipe->value === 'mc' ? 'tag-mc' : 'tag-essay' }}">{{ $question->tipe->label() }}</span>
                                    <span>·</span>
                                    <span>{{ $question->nilai_poin }} poin</span>
                                </div>
                            </div>
                        </div>

                        @if ($question->tipe->value === 'mc')
                            <div style="display:flex;flex-direction:column;gap:10px;">
                                @foreach ($question->options as $option)
                                    <label class="exam-opt">
                                        <input type="radio"
                                            name="answers[{{ $question->id }}]"
                                            value="{{ $option->id }}">
                                        <span>{{ $option->teks_opsi }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @else
                            <textarea
                                name="answers[{{ $question->id }}]"
                                rows="5"
                                placeholder="Tulis jawaban Anda di sini..."
                                class="form-input"
                            ></textarea>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="exam-foot">
                <p class="exam-hint">Periksa kembali sebelum mengirim. Jawaban yang terkirim tidak dapat diubah.</p>
                <button type="submit"
                    @click="confirmSubmit($event)"
                    class="btn btn-primary">
                    Kirim Jawaban
                </button>
            </div>
        </form>
    </div>

    <script>
        function testEngine(initialSeconds) {
            return {
                timeLeft: initialSeconds,
                timer: null,
                submitted: false,
                csrfInterval: null,

                init() {
                    this.timer = setInterval(() => {
                        if (this.timeLeft <= 0) {
                            this.autoSubmit();
                            return;
                        }
                        this.timeLeft--;
                    }, 1000);

                    this.csrfInterval = setInterval(() => this.refreshCsrf(), 60 * 60 * 1000);
                },

                async refreshCsrf() {
                    try {
                        const resp = await fetch('{{ route("tes.show", $submission->token) }}', {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' },
                            credentials: 'same-origin',
                        });
                        const html = await resp.text();
                        const match = html.match(/name="_token"[^>]*value="([^"]+)"/);
                        if (match) {
                            document.querySelector('#test-form input[name="_token"]').value = match[1];
                        }
                    } catch (e) {}
                },

                formatTime(seconds) {
                    const m = Math.floor(seconds / 60).toString().padStart(2, '0');
                    const s = (seconds % 60).toString().padStart(2, '0');
                    return `${m}:${s}`;
                },

                autoSubmit() {
                    if (this.submitted) return;
                    this.submitted = true;
                    clearInterval(this.timer);
                    document.getElementById('test-form').submit();
                },

                confirmSubmit(event) {
                    if (this.submitted) {
                        event.preventDefault();
                        return;
                    }
                    if (!confirm('Anda yakin ingin mengirim jawaban sekarang?')) {
                        event.preventDefault();
                        return;
                    }
                    this.submitted = true;
                    clearInterval(this.timer);
                },
            };
        }
    </script>
@endif

</x-layouts.public>
