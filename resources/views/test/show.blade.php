<x-layouts.test
    title="Tes Kompetensi - {{ $submission->application->vacancy->judul_posisi }}"
    heading="Tes Kompetensi"
    position="{{ $submission->application->vacancy->judul_posisi }}"
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
                Tes berhasil dikirim!
            </div>

            <x-ui.card class="text-center">
                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-[#f0f7e6] text-[20px] font-bold text-secondary-dark">✓</span>
                <h1 class="ui-section-title mt-4">Tes Berhasil Dikirim</h1>
                <p class="ui-help mx-auto mt-2 max-w-md">Jawaban Anda telah diterima. Tim HR akan meninjau hasil tes dan menghubungi Anda melalui email.</p>
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
        <div x-data="testEngine({{ $submission->remainingSeconds() }})" class="w-full">
            <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                <p class="ui-help">Jawab semua pertanyaan di bawah ini. Tes akan otomatis terkirim saat waktu habis.</p>
                <p class="flex items-center gap-2 text-[14px] font-semibold" :class="timeLeft <= 60 ? 'text-red-600' : (timeLeft <= 300 ? 'text-amber-600' : 'text-ink-2')">
                    <span x-text="formatTime(timeLeft)"></span>
                </p>
            </div>

            <form id="test-form" method="POST" action="{{ route('tes.submit', $submission->token) }}">
                @csrf

                <div class="grid w-full gap-4 lg:grid-cols-2">
                    @foreach ($questions as $index => $question)
                        <x-ui.card>
                            <div class="mb-4 flex items-start gap-3">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary/10 text-[12px] font-semibold text-primary">{{ $index + 1 }}</span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-[14px] text-ink">{{ $question->pertanyaan }}</p>
                                    <div class="mt-1.5 flex items-center gap-2">
                                        <x-ui.badge tone="{{ $question->tipe->value === 'mc' ? 'info' : 'warning' }}">{{ $question->tipe->label() }}</x-ui.badge>
                                        <span class="text-[12px] text-ink-4">{{ $question->nilai_poin }} poin</span>
                                    </div>
                                </div>
                            </div>

                            @if ($question->tipe->value === 'mc')
                                <div class="space-y-2">
                                    @foreach ($question->options as $option)
                                        <label class="flex cursor-pointer items-center gap-2.5 rounded-lg border border-line-2 px-3 py-2.5 text-[14px] text-ink-2 hover:border-line">
                                            <input type="radio" name="answers[{{ $question->id }}]" value="{{ $option->id }}" class="h-4 w-4 shrink-0 text-primary focus:ring-primary/30">
                                            <span>{{ $option->teks_opsi }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            @else
                                <textarea name="answers[{{ $question->id }}]" rows="4" placeholder="Tulis jawaban Anda di sini..." class="ui-textarea focus-ring"></textarea>
                            @endif
                        </x-ui.card>
                    @endforeach
                </div>

                <div class="sticky bottom-0 mt-6 border-t border-line bg-paper/95 py-4 backdrop-blur">
                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end">
                        <x-ui.button type="submit" x-on:click="confirmSubmit($event)" class="w-full sm:w-auto">Kirim Jawaban</x-ui.button>
                    </div>
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
                            const resp = await fetch('{{ route('tes.show', $submission->token) }}', {
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
</x-layouts.test>
