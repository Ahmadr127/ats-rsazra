<x-layouts.app title="Edit Soal DiSC - ATS RS Azra">

    <div class="mb-4">
        <a href="{{ route('soal-disc.index') }}" class="inline-flex items-center gap-1 text-xs text-gray-500 hover:text-primary transition-colors ease-out duration-150 mb-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Soal DiSC
        </a>
        <h1 class="text-xl font-semibold text-gray-900">Edit Soal DiSC <span class="text-sm font-normal text-gray-400">No. {{ $question->urutan }}</span></h1>
    </div>

    @if ($errors->any())
        <div class="mb-4 px-4 py-2.5 bg-red-50 border border-red-200 rounded text-xs text-red-700 max-w-4xl">
            <p class="font-medium mb-1">Terdapat kesalahan:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="max-w-4xl">
        <form method="POST" action="{{ route('soal-disc.update', $question) }}">
            @csrf
            @method('PUT')

            <div class="bg-white/80 border border-gray-200 rounded-md">
                @include('disc-questions._form', ['question' => $question])

                <div class="flex items-center gap-2 px-4 py-3 border-t border-gray-200 bg-gray-200/90 rounded-b-md">
                    <button type="submit"
                        class="px-4 py-1.5 bg-primary text-white text-xs font-medium rounded hover:bg-primary-dark transition-colors ease-out duration-150 cursor-pointer">
                        Simpan Perubahan
                    </button>
                    <a href="{{ route('soal-disc.index') }}"
                        class="px-4 py-1.5 text-xs text-gray-500 border border-gray-300 rounded bg-white hover:bg-gray-50 transition-colors ease-out duration-150">
                        Batal
                    </a>
                </div>
            </div>
        </form>
    </div>

</x-layouts.app>
