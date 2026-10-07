<x-layouts.public title="Tes DiSC - {{ $submission->application->vacancy->judul_posisi }} - RS Azra" main-class="w-full bg-paper">

<style>
    .exam-bar {
        position: sticky; top: 80px; z-index: 40;
        background: #0d1614; color: #fff;
        margin-top: 40px;
    }
    .exam-bar-inner { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 14px 20px; }
    .exam-bar-title { font-size: 15px; font-weight: 600; }
    .exam-bar-sub { font-family: "IBM Plex Mono", monospace; font-size: 11px; color: #b8c0bd; letter-spacing: 0.06em; text-transform: uppercase; margin-top: 2px; }
    .exam-count { font-family: "IBM Plex Mono", monospace; font-size: 20px; font-weight: 600; }
    .exam-progress { height: 3px; background: rgba(255,255,255,0.15); }
    .exam-progress > div { height: 100%; background: rgb(129,189,65); transition: width 0.3s; }
    .exam-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-top: 40px; }
    .exam-q-head { display: flex; gap: 16px; align-items: baseline; margin-bottom: 6px; }
    .exam-q-num {
        font-family: "IBM Plex Serif", serif; font-size: 26px; font-weight: 500;
        color: rgb(0,119,116); line-height: 1; flex-shrink: 0; min-width: 44px;
    }
    .exam-q-hint { font-size: 13px; color: #5a6864; margin: 0 0 18px; }
    .exam-q-hint .like { color: #4d7e1c; font-weight: 600; }
    .exam-q-hint .unlike { color: #b54327; font-weight: 600; }
    .exam-word {
        display: flex; align-items: center; gap: 16px;
        border: 1px solid #d9ddd9; padding: 14px 16px; margin-bottom: 10px;
        transition: border-color 0.15s, background 0.15s;
    }
    .exam-word:last-child { margin-bottom: 0; }
    .exam-word.is-most { border-color: #5e9425; background: #f4f8ec; }
    .exam-word.is-least { border-color: #b54327; background: #fdf4f2; }
    .exam-word-text { flex: 1; font-size: 17px; font-weight: 500; color: #0d1614; }
    .exam-pick { display: flex; align-items: center; gap: 8px; cursor: pointer; flex-shrink: 0; }
    .exam-pick input { width: 20px; height: 20px; cursor: pointer; }
    .exam-pick input[value]:checked { accent-color: rgb(0,119,116); }
    .exam-pick.most input { accent-color: #5e9425; }
    .exam-pick.least input { accent-color: #b54327; }
    .exam-pick span { font-size: 12px; font-weight: 600; }
    .exam-pick.most span { color: #4d7e1c; }
    .exam-pick.least span { color: #b54327; }
    .exam-pick input:disabled { cursor: not-allowed; opacity: 0.35; }
    .exam-pick:has(input:disabled) span { opacity: 0.35; }
    .exam-foot {
        display: flex; align-items: center; justify-content: space-between; gap: 16px;
        margin-top: 40px; border-top: 2px solid #0d1614; padding-top: 24px; flex-wrap: wrap;
    }
    .exam-hint { font-size: 14px; color: #5a6864; margin: 0; }
    .exam-dl { margin: 0; }
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
        .exam-word { flex-wrap: wrap; }
        .exam-word-text { width: 100%; }
        .exam-foot { flex-direction: column; align-items: stretch; }
    }
</style>

@if ($submission->isSubmitted())
    <div class="container-public section">
        <div class="badge badge-primary">Tes DiSC · Terkirim</div>
        <h1 class="offer-h1">Jawaban Anda sudah kami terima.</h1>
        <p class="offer-lede">Tim HR akan meninjau hasil asesmen dan menghubungi Anda melalui email.</p>
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
    <div class="container-public section" x-data="discEngine()" x-init="init()">
        <div class="badge badge-primary">Tes DiSC · RS Azra</div>
        <h1 class="offer-h1">{{ $submission->application->vacancy->judul_posisi }}</h1>
        <p class="offer-lede">
            Untuk setiap kelompok kata di bawah ini, pilih kata yang
            <strong>paling mencerminkan diri Anda</strong> dan kata yang
            <strong>paling tidak mencerminkan diri Anda</strong>.
            Setiap kata hanya boleh dipilih untuk satu kolom per soal.
        </p>

        <div class="exam-bar">
            <div class="exam-bar-inner">
                <div>
                    <div class="exam-bar-title">{{ $questions->count() }} Kelompok Kata</div>
                    <div class="exam-bar-sub">Tes DiSC</div>
                </div>
                <div class="exam-count"><span x-text="answered"></span> / {{ $questions->count() }}</div>
            </div>
            <div class="exam-progress"><div x-bind:style="'width: ' + (answered / {{ $questions->count() }} * 100) + '%'"></div></div>
        </div>

        <form id="disc-form" method="POST" action="{{ route('tes-disc.submit', $submission->token) }}">
            @csrf

            <div class="exam-grid">
                @foreach ($questions as $index => $question)
                    <div class="card"
                         x-data="discQuestion({{ $question->id }})"
                         @answer-change="updateAnswered()">
                        <div class="exam-q-head">
                            <span class="exam-q-num">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                        </div>
                        <p class="exam-q-hint">Pilih satu <span class="like">Paling Mirip</span> dan satu <span class="unlike">Paling Tidak Mirip</span></p>

                        <div>
                            @foreach ($question->words as $word)
                                <div class="exam-word"
                                     :class="{
                                        'is-most': most === {{ $word->id }},
                                        'is-least': least === {{ $word->id }}
                                     }">
                                    <span class="exam-word-text">{{ $word->teks }}</span>
                                    <label class="exam-pick most">
                                        <input type="radio"
                                            name="most[{{ $question->id }}]"
                                            value="{{ $word->id }}"
                                            x-model.number="most"
                                            @change="onMostChange({{ $word->id }})"
                                            :disabled="least === {{ $word->id }}">
                                        <span>Mirip</span>
                                    </label>
                                    <label class="exam-pick least">
                                        <input type="radio"
                                            name="least[{{ $question->id }}]"
                                            value="{{ $word->id }}"
                                            x-model.number="least"
                                            @change="onLeastChange({{ $word->id }})"
                                            :disabled="most === {{ $word->id }}">
                                        <span>Tidak</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="exam-foot">
                <p class="exam-hint">Semua kelompok kata wajib dijawab sebelum mengirim.</p>
                <button type="submit"
                    @click="confirmSubmit($event)"
                    :disabled="answered < {{ $questions->count() }}"
                    class="btn btn-primary">
                    Kirim Jawaban
                    <small x-show="answered < {{ $questions->count() }}"><span x-text="{{ $questions->count() }} - answered"></span> belum dijawab</small>
                </button>
            </div>
        </form>
    </div>

    <script>
        function discQuestion(questionId) {
            return {
                questionId,
                most: null,
                least: null,

                onMostChange(wordId) {
                    if (this.most === wordId && this.least === wordId) {
                        this.least = null;
                    }
                    this.$dispatch('answer-change');
                },

                onLeastChange(wordId) {
                    if (this.least === wordId && this.most === wordId) {
                        this.most = null;
                    }
                    this.$dispatch('answer-change');
                },
            };
        }

        function discEngine() {
            return {
                answered: 0,
                submitted: false,

                init() {
                    this.updateAnswered();
                },

                updateAnswered() {
                    // Count questions where both most and least are set
                    let count = 0;
                    document.querySelectorAll('[x-data*="discQuestion"]').forEach(el => {
                        const mostSelected = el.querySelector('input[name^="most"]:checked');
                        const leastSelected = el.querySelector('input[name^="least"]:checked');
                        if (mostSelected && leastSelected) {
                            count++;
                        }
                    });
                    this.answered = count;
                },

                confirmSubmit(event) {
                    if (this.submitted) {
                        event.preventDefault();
                        return;
                    }
                    if (this.answered < {{ $questions->count() }}) {
                        event.preventDefault();
                        return;
                    }
                    if (!confirm('Anda yakin ingin mengirim jawaban sekarang? Tes tidak dapat diulang.')) {
                        event.preventDefault();
                        return;
                    }
                    this.submitted = true;
                },
            };
        }
    </script>
@endif

</x-layouts.public>
