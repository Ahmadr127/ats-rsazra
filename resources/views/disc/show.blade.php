<x-layouts.test
    title="Tes DiSC - {{ $submission->application->vacancy->judul_posisi }}"
    heading="Tes DiSC"
    position="{{ $submission->application->vacancy->judul_posisi }}"
    meta="{{ $questions->count() }} soal"
>
    @if ($submission->isSubmitted())
        <div class="mx-auto w-full max-w-2xl" x-data="{ showToast: true }">
            <div
                x-show="showToast"
                x-init="setTimeout(() => showToast = false, 4000)"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-2"
                class="fixed left-1/2 top-4 z-50 flex -translate-x-1/2 items-center gap-2 rounded-lg bg-secondary-dark px-5 py-3 text-[13px] font-semibold text-white shadow-lg"
            >
                Tes DiSC berhasil dikirim!
            </div>

            <x-ui.card class="text-center">
                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-[#f0f7e6] text-[20px] font-bold text-secondary-dark">✓</span>
                <h1 class="ui-section-title mt-4">Tes DiSC Berhasil Dikirim</h1>
                <p class="ui-help mx-auto mt-2 max-w-md">Jawaban Anda telah diterima. Tim HR akan meninjau hasil asesmen dan menghubungi Anda melalui email.</p>
                <div class="mt-6 rounded-xl bg-paper p-4 text-left">
                    <div class="flex justify-between gap-4 py-1 text-[14px]">
                        <span class="text-ink-3">Posisi</span>
                        <span class="text-right font-medium text-ink">{{ $submission->application->vacancy->judul_posisi }}</span>
                    </div>
                    <div class="flex justify-between gap-4 py-1 text-[14px]">
                        <span class="text-ink-3">Waktu Pengiriman</span>
                        <span class="font-medium text-ink">{{ $submission->submitted_at->format('d M Y, H:i') }}</span>
                    </div>
                </div>
                <p class="mt-6 text-[12px] text-ink-4">Halaman ini dapat ditutup.</p>
            </x-ui.card>
        </div>
    @else
        <div x-data="discEngine()" x-init="init()" class="w-full">
            <x-ui.alert tone="info" title="Petunjuk" class="mb-5">Untuk setiap kelompok kata, pilih kata yang <strong>paling mencerminkan diri Anda</strong> dan kata yang <strong>paling tidak mencerminkan diri Anda</strong>.</x-ui.alert>

            <form id="disc-form" method="POST" action="{{ route('tes-disc.submit', $submission->token) }}">
                @csrf

                <div class="grid w-full gap-4 lg:grid-cols-2">
                    @foreach ($questions as $index => $question)
                        <x-ui.card x-data="discQuestion({{ $question->id }})" @answer-change="updateAnswered()">
                            <div class="mb-4 flex items-center gap-2.5">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary/10 text-[12px] font-semibold text-primary">{{ $index + 1 }}</span>
                                <p class="text-[12px] text-ink-3">Pilih satu <span class="font-semibold text-secondary-dark">Paling Mirip</span> dan satu <span class="font-semibold text-red-600">Paling Tidak Mirip</span></p>
                            </div>

                            <div class="grid grid-cols-1 gap-2">
                                @foreach ($question->words as $word)
                                    <div class="flex flex-col gap-2 rounded-lg border border-line-2 bg-paper p-3 sm:flex-row sm:items-center">
                                        <span class="min-w-0 flex-1 text-[14px] text-ink">{{ $word->teks }}</span>
                                        <div class="flex shrink-0 items-center gap-3">
                                            <label class="flex cursor-pointer items-center gap-1.5 text-[13px] font-medium text-secondary-dark">
                                                <input type="radio" name="most[{{ $question->id }}]" value="{{ $word->id }}" x-model.number="most" @change="onMostChange({{ $word->id }})" :disabled="least === {{ $word->id }}" class="h-4 w-4 text-secondary-dark focus:ring-secondary/40">
                                                Mirip
                                            </label>
                                            <label class="flex cursor-pointer items-center gap-1.5 text-[13px] font-medium text-red-600">
                                                <input type="radio" name="least[{{ $question->id }}]" value="{{ $word->id }}" x-model.number="least" @change="onLeastChange({{ $word->id }})" :disabled="most === {{ $word->id }}" class="h-4 w-4 text-red-500 focus:ring-red-400/40">
                                                Tidak
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </x-ui.card>
                    @endforeach
                </div>

                <div class="sticky bottom-0 mt-6 border-t border-line bg-paper/95 py-4 backdrop-blur">
                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end">
                        <p class="text-[13px] text-ink-3 sm:mr-auto"><span x-text="answered"></span> / {{ $questions->count() }} dijawab</p>
                        <x-ui.button
                            type="submit"
                            x-on:click="confirmSubmit($event)"
                            x-bind:disabled="answered < {{ $questions->count() }}"
                            class="w-full sm:w-auto"
                        >Kirim Jawaban</x-ui.button>
                    </div>
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
</x-layouts.test>
