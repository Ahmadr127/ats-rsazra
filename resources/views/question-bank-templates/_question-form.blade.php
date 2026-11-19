<div class="px-4 py-5">
    <div class="flex items-center justify-between mb-3">
        <div>
            <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Daftar Soal</p>
            <p class="text-[10px] text-gray-400 mt-0.5"><span x-text="questions.length"></span> soal &mdash; <span x-text="totalPoin"></span> poin total</p>
        </div>
        <button type="button" @click="addQuestion()"
            class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium border border-gray-300 text-gray-600 rounded bg-white hover:bg-gray-50 transition-colors ease-out duration-150">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Soal
        </button>
    </div>

    <div class="space-y-4">
        <template x-for="(question, qIndex) in questions" :key="question.uid">
            <div class="border border-gray-200 rounded p-3 bg-gray-50/50">
                <div class="flex items-start justify-between gap-3 mb-3">
                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-primary/10 text-primary text-[10px] font-bold shrink-0" x-text="qIndex + 1"></span>
                    <div class="flex items-center gap-1">
                        <button type="button" @click="moveUp(qIndex)" x-show="qIndex > 0"
                            class="p-1 text-gray-400 hover:text-primary rounded transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>
                        </button>
                        <button type="button" @click="moveDown(qIndex)" x-show="qIndex < questions.length - 1"
                            class="p-1 text-gray-400 hover:text-primary rounded transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <button type="button" @click="removeQuestion(qIndex)" x-show="questions.length > 1"
                            class="p-1 text-red-400 hover:text-red-600 rounded transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-3">
                    <div class="md:col-span-1">
                        <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-1">Tipe</label>
                        <div class="flex flex-wrap gap-1.5">
                            <button type="button" @click="question.tipe = 'mc'"
                                :class="question.tipe === 'mc' ? 'bg-primary text-white border-primary hover:bg-primary' : 'bg-white text-gray-600 border-gray-200 hover:border-gray-300 hover:bg-gray-50'"
                                class="px-3 py-1.5 rounded border text-xs font-medium transition-colors ease-out duration-150 cursor-pointer">
                                Pilihan Ganda
                            </button>
                            <button type="button" @click="question.tipe = 'essay'"
                                :class="question.tipe === 'essay' ? 'bg-primary text-white border-primary hover:bg-primary' : 'bg-white text-gray-600 border-gray-200 hover:border-gray-300 hover:bg-gray-50'"
                                class="px-3 py-1.5 rounded border text-xs font-medium transition-colors ease-out duration-150 cursor-pointer">
                                Esai
                            </button>
                            <button type="button" @click="question.tipe = 'image'"
                                :class="question.tipe === 'image' ? 'bg-primary text-white border-primary hover:bg-primary' : 'bg-white text-gray-600 border-gray-200 hover:border-gray-300 hover:bg-gray-50'"
                                class="px-3 py-1.5 rounded border text-xs font-medium transition-colors ease-out duration-150 cursor-pointer">
                                Unggah Gambar
                            </button>
                        </div>
                    </div>
                    <div class="md:col-span-1">
                        <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-1">Poin</label>
                        <input type="number" x-model.number="question.nilai_poin" min="1" max="100"
                            class="w-full text-xs border border-gray-200 rounded px-2.5 py-1.5 bg-white focus-ring">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-1">Pertanyaan</label>
                    <textarea x-model="question.pertanyaan" rows="2"
                        class="w-full text-xs border border-gray-200 rounded px-2.5 py-1.5 bg-white focus-ring"
                        placeholder="Tulis pertanyaan..."></textarea>
                </div>

                <div class="mb-3">
                    <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-1">Gambar Soal <span class="text-gray-400 normal-case font-normal">(opsional, satu gambar)</span></label>
                    <input type="file" name="pending-file" :data-uid="question.uid"
                        accept="image/jpeg,image/png,image/webp"
                        @change="onImageChange($event, question)"
                        class="w-full text-[11px] border border-gray-200 rounded px-2.5 py-1.5 bg-white focus-ring file:mr-2 file:border-0 file:bg-primary/10 file:text-primary file:text-[10px] file:font-medium file:px-2 file:py-1 file:rounded">
                    <template x-if="question.gambar_url && !question.gambar_baru">
                        <div class="mt-2 flex items-center gap-3">
                            <img :src="question.gambar_url" alt="Pratinjau gambar soal" class="h-16 w-16 rounded border border-gray-200 object-cover">
                            <label class="flex items-center gap-1.5 text-[11px] text-gray-600 cursor-pointer">
                                <input type="checkbox" x-model="question.hapus_gambar" class="w-3.5 h-3.5 text-red-500 focus:ring-red-400/40">
                                Hapus gambar
                            </label>
                        </div>
                    </template>
                    <template x-if="question.gambar_baru_url">
                        <div class="mt-2">
                            <img :src="question.gambar_baru_url" alt="Pratinjau gambar baru" class="h-16 w-16 rounded border border-gray-200 object-cover">
                        </div>
                    </template>
                    <p class="mt-1 text-[10px] text-gray-400">JPG, JPEG, PNG, atau WEBP &mdash; maksimal 5 MB.</p>
                </div>

                <div x-show="question.tipe === 'mc'" x-cloak>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-[10px] font-medium text-gray-500 uppercase tracking-wide">Opsi Jawaban</label>
                        <button type="button" @click="addOption(qIndex)"
                            class="text-[10px] text-primary hover:underline">+ Tambah Opsi</button>
                    </div>
                    <div class="space-y-1.5">
                        <template x-for="(option, oIndex) in question.options" :key="oIndex">
                            <div class="flex items-center gap-2">
                                <input type="radio" :name="'q_correct_' + qIndex" :value="oIndex"
                                    x-model.number="question.correct_option"
                                    class="w-3.5 h-3.5 text-primary focus:ring-primary/40">
                                <input type="text" x-model="option.teks_opsi" placeholder="Teks opsi..."
                                    class="flex-1 text-xs border border-gray-200 rounded px-2.5 py-1.5 bg-white focus-ring">
                                <button type="button" @click="removeOption(qIndex, oIndex)"
                                    x-show="question.options.length > 2"
                                    class="text-[10px] text-red-400 hover:text-red-600">Hapus</button>
                            </div>
                        </template>
                    </div>
                    <p class="mt-1 text-[10px] text-gray-400">Pilih radio untuk menandai jawaban benar.</p>
                </div>
            </div>
        </template>
    </div>
</div>

<div id="hidden-fields"></div>

<script>
    function templateForm(initialQuestions) {
        return {
            questions: initialQuestions && initialQuestions.length ? initialQuestions.map(normalizeQuestion) : [makeQuestion()],
            get totalPoin() {
                return this.questions.reduce((sum, q) => sum + (parseInt(q.nilai_poin) || 0), 0);
            },
            addQuestion() {
                this.questions.push(makeQuestion());
            },
            removeQuestion(index) {
                if (this.questions.length <= 1) return;
                this.questions.splice(index, 1);
            },
            moveUp(index) {
                if (index <= 0) return;
                [this.questions[index - 1], this.questions[index]] = [this.questions[index], this.questions[index - 1]];
            },
            moveDown(index) {
                if (index >= this.questions.length - 1) return;
                [this.questions[index + 1], this.questions[index]] = [this.questions[index], this.questions[index + 1]];
            },
            addOption(qIndex) {
                this.questions[qIndex].options.push({ teks_opsi: '' });
            },
            removeOption(qIndex, oIndex) {
                if (this.questions[qIndex].options.length <= 2) return;
                this.questions[qIndex].options.splice(oIndex, 1);
                if (this.questions[qIndex].correct_option >= this.questions[qIndex].options.length) {
                    this.questions[qIndex].correct_option = 0;
                }
            },
            onImageChange(event, question) {
                const file = event.target.files[0];
                question.gambar_baru = !!file;
                question.hapus_gambar = false;
                question.gambar_baru_url = file ? URL.createObjectURL(file) : null;
            },
            prepareSubmit() {
                const container = document.getElementById('hidden-fields');
                container.innerHTML = '';
                const fileInputs = Array.from(document.querySelectorAll('input[type="file"][data-uid]'));

                this.questions.forEach((q, qi) => {
                    if (q.id) {
                        addHidden(container, `questions[${qi}][id]`, q.id);
                    }
                    addHidden(container, `questions[${qi}][tipe]`, q.tipe);
                    addHidden(container, `questions[${qi}][pertanyaan]`, q.pertanyaan);
                    addHidden(container, `questions[${qi}][nilai_poin]`, q.nilai_poin);
                    addHidden(container, `questions[${qi}][hapus_gambar]`, q.hapus_gambar ? '1' : '0');
                    if (q.gambar_path) {
                        addHidden(container, `questions[${qi}][gambar_lama]`, q.gambar_path);
                    }
                    if (q.tipe === 'mc') {
                        addHidden(container, `questions[${qi}][correct_option]`, q.correct_option);
                        q.options.forEach((o, oi) => {
                            addHidden(container, `questions[${qi}][options][${oi}][teks_opsi]`, o.teks_opsi);
                        });
                    }

                    const fileInput = fileInputs.find((input) => input.dataset.uid === q.uid);
                    if (fileInput) {
                        if (q.gambar_baru) {
                            fileInput.name = `questions[${qi}][gambar]`;
                            fileInput.disabled = false;
                        } else {
                            fileInput.disabled = true;
                        }
                    }
                });
            },
        };
    }

    function normalizeQuestion(question) {
        return {
            id: question.id ?? null,
            uid: question.uid ?? makeUid(),
            tipe: question.tipe ?? 'mc',
            pertanyaan: question.pertanyaan ?? '',
            nilai_poin: question.nilai_poin ?? 1,
            gambar_path: question.gambar_path ?? null,
            gambar_url: question.gambar_url ?? null,
            gambar_baru: false,
            gambar_baru_url: null,
            hapus_gambar: false,
            correct_option: question.correct_option ?? 0,
            options: question.options && question.options.length
                ? question.options
                : [{ teks_opsi: '' }, { teks_opsi: '' }, { teks_opsi: '' }, { teks_opsi: '' }],
        };
    }

    function makeQuestion() {
        return normalizeQuestion({
            uid: makeUid(),
            tipe: 'mc',
            nilai_poin: 1,
        });
    }

    function makeUid() {
        if (typeof crypto !== 'undefined' && crypto.randomUUID) {
            return crypto.randomUUID();
        }
        return 'q-' + Date.now() + '-' + Math.random().toString(36).slice(2);
    }

    function addHidden(container, name, value) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value ?? '';
        container.appendChild(input);
    }
</script>
