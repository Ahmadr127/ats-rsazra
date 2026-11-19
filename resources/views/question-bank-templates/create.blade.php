<x-layouts.app title="Buat Template Bank Soal - ATS RS Azra">

    <div class="mb-4">
        <a href="{{ route('template-bank-soal.index') }}" class="inline-flex items-center gap-1 text-xs text-gray-500 hover:text-primary transition-colors ease-out duration-150 mb-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Template Bank Soal
        </a>
        <h1 class="text-xl font-semibold text-gray-900">Buat Template Bank Soal</h1>
    </div>

    @if ($errors->any())
        <div class="mb-4 px-4 py-2.5 bg-red-50 border border-red-200 rounded text-xs text-red-700">
            <p class="font-medium mb-1">Terdapat kesalahan:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (! empty($importErrors))
        <div class="mb-4 px-4 py-2.5 bg-red-50 border border-red-200 rounded text-xs text-red-700">
            <p class="font-medium mb-1">Isi Excel tidak dapat diproses:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($importErrors as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div x-data="templateForm(@js($initialQuestions ?? []))" class="max-w-4xl">
        <form method="POST" action="{{ route('template-bank-soal.import.parse') }}" enctype="multipart/form-data" class="mb-4">
            @csrf
            <input type="hidden" name="nama" id="import-nama" value="{{ old('nama', $importNama ?? '') }}">
            <div class="bg-white/80 border border-gray-200 rounded-md px-4 py-3">
                <div class="flex flex-wrap items-end gap-3">
                    <div class="min-w-52 flex-1">
                        <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-1">Import dari Excel</label>
                        <input type="file" name="file" accept=".xlsx"
                            class="w-full text-[11px] border border-gray-200 rounded px-2.5 py-1.5 bg-white focus-ring">
                    </div>
                    <button type="submit"
                        class="px-3.5 py-1.5 bg-white border border-primary/30 text-primary text-xs font-medium rounded hover:bg-primary/5 transition-colors cursor-pointer">
                        Isi dari Excel
                    </button>
                    <a href="{{ route('template-bank-soal.import.template') }}"
                        class="px-3.5 py-1.5 text-xs text-primary hover:underline">Unduh Template Excel</a>
                </div>
                <p class="mt-1.5 text-[10px] text-gray-400">Gambar tidak dikirim dari Excel. Tambahkan gambar pada soal setelah form terisi, lalu simpan.</p>
            </div>
        </form>

        <form method="POST" action="{{ route('template-bank-soal.store') }}" enctype="multipart/form-data" @submit="prepareSubmit($event)">
            @csrf

            <div class="bg-white/80 border border-gray-200 rounded-md">
                <div class="px-4 pt-4 pb-5">
                    <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-3">Informasi Template</p>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Nama Template <span class="text-red-500">*</span></label>
                        <input type="text" name="nama" value="{{ old('nama', $importNama ?? '') }}" required
                            oninput="document.getElementById('import-nama').value = this.value"
                            class="w-full px-2.5 py-1.5 text-xs border border-gray-200 rounded bg-white focus-ring"
                            placeholder="Contoh: Tes Kompetensi Perawat">
                    </div>
                </div>

                <hr class="border-t border-gray-300/80">

                @include('question-bank-templates._question-form')

                <div class="flex items-center gap-2 px-4 py-3 border-t border-gray-200 bg-gray-200/90 rounded-b-md">
                    <button type="submit"
                        class="px-4 py-1.5 bg-primary text-white text-xs font-medium rounded hover:bg-primary-dark transition-colors ease-out duration-150 cursor-pointer">
                        Simpan Template
                    </button>
                    <a href="{{ route('template-bank-soal.index') }}"
                        class="px-4 py-1.5 text-xs text-gray-500 border border-gray-300 rounded bg-white hover:bg-gray-50 transition-colors ease-out duration-150">
                        Batal
                    </a>
                </div>
            </div>
        </form>
    </div>

</x-layouts.app>
