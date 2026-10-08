<x-layouts.app title="Soal MBTI - ATS RS Azra">

    <div class="flex items-center justify-between mb-5">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Soal MBTI</h1>
            <p class="text-xs text-gray-500 mt-0.5">Kelola bank soal tes MBTI (70 soal standar)</p>
        </div>
        @permission('mbti-question.create')
        <a
            href="{{ route('soal-mbti.create') }}"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors ease-out duration-150"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Buat Soal
        </a>
        @endpermission
    </div>

    @if (session('success'))
        <div class="mb-4 px-4 py-2.5 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 px-4 py-2.5 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="mb-3">
        <form method="GET" action="{{ route('soal-mbti.index') }}">
            <div class="flex flex-wrap items-center gap-2">
                <div class="relative flex-1 min-w-52">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input
                        type="text"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Cari pernyataan..."
                        class="w-full pl-8 pr-3 py-1.5 text-sm border border-gray-200 rounded-md focus-ring bg-white placeholder:text-gray-400"
                    >
                </div>
                <select
                    name="dikotomi"
                    onchange="this.form.requestSubmit()"
                    class="py-1.5 pl-3 pr-8 text-sm border border-gray-200 rounded-md bg-white text-gray-600 focus-ring cursor-pointer"
                >
                    <option value="">Semua Dikotomi</option>
                    @foreach (['EI' => 'E / I', 'SN' => 'S / N', 'TF' => 'T / F', 'JP' => 'J / P'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('dikotomi') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-3.5 py-1.5 bg-primary text-white text-sm font-medium rounded-md hover:bg-primary-dark transition-colors ease-out duration-150 cursor-pointer">
                    Cari
                </button>
                @if (request()->anyFilled(['q', 'dikotomi']))
                    <a href="{{ route('soal-mbti.index') }}" class="py-1.5 text-xs text-gray-400 hover:text-gray-600 transition-colors ease-out duration-150">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-primary border-b border-primary/10 text-white">
                        <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider w-16">No.</th>
                        <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider w-24">Dikotomi</th>
                        <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider">Pernyataan A / B</th>
                        <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider w-40">Kutub</th>
                        <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider w-24">Terjawab</th>
                        <th class="w-20 px-3 py-2.5"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($questions as $question)
                        <tr class="odd:bg-white even:bg-primary/5 hover:bg-primary/10 transition-colors ease-out duration-100">
                            <td class="px-3 py-1.5 text-xs text-gray-400 tabular-nums">{{ $question->urutan }}</td>
                            <td class="px-3 py-1.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-primary/10 text-primary">
                                    {{ $question->dikotomi }}
                                </span>
                            </td>
                            <td class="px-3 py-1.5">
                                <p class="text-xs text-gray-800"><span class="font-semibold text-secondary-dark">A.</span> {{ $question->pernyataan_a }}</p>
                                <p class="text-xs text-gray-500 mt-0.5"><span class="font-semibold">B.</span> {{ $question->pernyataan_b }}</p>
                            </td>
                            <td class="px-3 py-1.5 text-xs text-gray-600">
                                A: {{ $question->kutub_a->value }} ({{ $question->kutub_a->shortLabel() }})
                                <span class="block text-[10px] text-gray-400">B: {{ $question->kutubB()->value }} ({{ $question->kutubB()->shortLabel() }})</span>
                            </td>
                            <td class="px-3 py-1.5 text-xs text-gray-600 tabular-nums">{{ $question->answers_count }}x</td>
                            <td class="px-3 py-1.5">
                                <div class="flex items-center justify-end gap-0.5">
                                    @permission('mbti-question.update')
                                    <a
                                        href="{{ route('soal-mbti.edit', $question) }}"
                                        class="p-1.5 rounded text-amber-400/60 hover:text-amber-500 hover:bg-amber-50 transition-colors ease-out duration-150"
                                        title="Edit soal"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                        </svg>
                                    </a>
                                    @endpermission
                                    @permission('mbti-question.delete')
                                    <form method="POST" action="{{ route('soal-mbti.destroy', $question) }}" onsubmit="return confirm('Hapus soal nomor {{ $question->urutan }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="p-1.5 rounded text-red-400/60 hover:text-red-500 hover:bg-red-50 transition-colors ease-out duration-150 cursor-pointer"
                                            title="Hapus soal"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                    @endpermission
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-14 text-center">
                                <div class="flex flex-col items-center gap-2.5 max-w-xs mx-auto">
                                    <div>
                                        <p class="text-sm font-medium text-gray-700">Belum ada soal</p>
                                        <p class="text-xs text-gray-400 mt-0.5">Buat soal MBTI pertama</p>
                                    </div>
                                    @permission('mbti-question.create')
                                    <a
                                        href="{{ route('soal-mbti.create') }}"
                                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors ease-out duration-150"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        Buat Soal
                                    </a>
                                    @endpermission
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($questions->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $questions->links() }}
            </div>
        @endif
    </div>

</x-layouts.app>
