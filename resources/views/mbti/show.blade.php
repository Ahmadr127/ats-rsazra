<x-layouts.public title="Tes MBTI - {{ $submission->application->vacancy->judul_posisi }} - RS Azra" main-class="w-full bg-paper">

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
    .exam-q-head { display: flex; gap: 16px; align-items: baseline; margin-bottom: 18px; }
    .exam-q-num {
        font-family: "IBM Plex Serif", serif; font-size: 26px; font-weight: 500;
        color: rgb(0,119,116); line-height: 1; flex-shrink: 0; min-width: 44px;
    }
    .exam-q-hint { font-size: 13px; color: #5a6864; margin: 0; }
    .exam-choice {
        display: flex; align-items: flex-start; gap: 14px;
        border: 1px solid #d9ddd9; padding: 16px; cursor: pointer;
        transition: border-color 0.15s, background 0.15s; margin-bottom: 10px;
    }
    .exam-choice:last-child { margin-bottom: 0; }
    .exam-choice:hover { border-color: rgb(0,119,116); }
    .exam-choice.is-selected { border-color: rgb(0,119,116); background: #f4faf9; }
    .exam-choice input { width: 20px; height: 20px; margin-top: 2px; accent-color: rgb(0,119,116); flex-shrink: 0; cursor: pointer; }
    .exam-choice .choice-key {
        font-family: "IBM Plex Mono", monospace; font-size: 12px; font-weight: 600;
        color: rgb(0,119,116); border: 1px solid rgb(0,119,116);
        width: 28px; height: 28px; display: grid; place-items: center; flex-shrink: 0;
    }
    .exam-choice span:last-child { font-size: 16px; line-height: 1.55; color: #0d1614; }
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
        .exam-foot { flex-direction: column; align-items: stretch; }
    }
</style>

@if ($submission->isSubmitted())
    <div class="container-public section">
        <div class="badge badge-primary">Tes MBTI · Terkirim</div>
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
    <div class="container-public section" x-data="mbtiEngine()">
        <div class="badge badge-primary">Tes MBTI · RS Azra</div>
        <h1 class="offer-h1">{{ $submission->application->vacancy->judul_posisi }}</h1>
        <p class="offer-lede">
            Untuk setiap pasangan pernyataan di bawah ini, pilih yang
            <strong>paling mencerminkan diri Anda</strong>.
            Tidak ada jawaban benar atau salah — jawablah dengan jujur sesuai kepribadian Anda.
        </p>

        <div class="exam-bar">
            <div class="exam-bar-inner">
                <div>
                    <div class="exam-bar-title">{{ $questions->count() }} Pasangan Pernyataan</div>
                    <div class="exam-bar-sub">Tes MBTI</div>
                </div>
                <div class="exam-count"><span x-text="answered"></span> / {{ $questions->count() }}</div>
            </div>
            <div class="exam-progress"><div x-bind:style="'width: ' + (answered / {{ $questions->count() }} * 100) + '%'"></div></div>
        </div>

        <form id="mbti-form" method="POST" action="{{ route('tes-mbti.submit', $submission->token) }}">
            @csrf

            <div class="exam-grid">
                @foreach ($questions as $index => $question)
                    <div class="card"
                         x-data="mbtiQuestion({{ $question->id }})"
                         @answer-change="$dispatch('mbti-answered', { id: {{ $question->id }}, answered: selected !== null })">
                        <div class="exam-q-head">
                            <span class="exam-q-num">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <p class="exam-q-hint">Pilih salah satu pernyataan yang lebih mencerminkan diri Anda</p>
                        </div>

                        <div>
                            <label class="exam-choice"
                                   :class="selected === 'A' ? 'is-selected' : ''">
                                <input type="radio"
                                       name="jawaban[{{ $question->id }}]"
                                       value="A"
                                       x-model="selected"
                                       @change="$dispatch('answer-change')">
                                <span class="choice-key">A</span>
                                <span>{{ $question->pernyataan_a }}</span>
                            </label>

                            <label class="exam-choice"
                                   :class="selected === 'B' ? 'is-selected' : ''">
                                <input type="radio"
                                       name="jawaban[{{ $question->id }}]"
                                       value="B"
                                       x-model="selected"
                                       @change="$dispatch('answer-change')">
                                <span class="choice-key">B</span>
                                <span>{{ $question->pernyataan_b }}</span>
                            </label>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="exam-foot">
                <p class="exam-hint">Semua pasangan pernyataan wajib dijawab sebelum mengirim.</p>
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
        function mbtiQuestion(questionId) {
            return {
                questionId,
                selected: null,
            };
        }

        function mbtiEngine() {
            return {
                answered: 0,
                submitted: false,

                init() {
                    this.$el.addEventListener('mbti-answered', (e) => {
                        this.recountAnswered();
                    });
                },

                recountAnswered() {
                    let count = 0;
                    document.querySelectorAll('input[type="radio"]:checked').forEach(input => {
                        if (input.name.startsWith('jawaban[')) {
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
