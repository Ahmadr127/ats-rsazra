{{-- Variables: $question (?DiscQuestion with words) --}}
@php
    $editing = isset($question);
    $existingWords = $editing ? $question->words->values() : collect();
    $oldWords = old('words');
@endphp

<div class="px-4 pt-4 pb-5">
    <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-3">Isi Soal</p>
    <div class="mb-3 max-w-xs">
        <label class="block text-xs font-medium text-gray-700 mb-1">Nomor Urut <span class="text-red-500">*</span></label>
        <input type="number" name="urutan" value="{{ old('urutan', $question?->urutan ?? '') }}" min="1" max="9999" required
            class="w-full px-2.5 py-1.5 text-xs border border-gray-200 rounded bg-white focus-ring"
            placeholder="Contoh: 29">
    </div>
    <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-2">4 Kata (tepat satu D, satu I, satu S, satu C)</p>
    <div class="space-y-2">
        @for ($i = 0; $i < 4; $i++)
            @php
                if (is_array($oldWords) && isset($oldWords[$i])) {
                    $wordId = $oldWords[$i]['id'] ?? null;
                    $wordTeks = $oldWords[$i]['teks'] ?? '';
                    $wordDimensi = $oldWords[$i]['dimensi'] ?? '';
                } else {
                    $word = $existingWords->get($i);
                    $wordId = $word?->id;
                    $wordTeks = $word?->teks ?? '';
                    $wordDimensi = $word?->dimensi?->value ?? '';
                }
            @endphp
            <div class="flex items-center gap-2">
                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary/10 text-[12px] font-semibold text-primary">{{ $i + 1 }}</span>
                @if ($wordId)
                    <input type="hidden" name="words[{{ $i }}][id]" value="{{ $wordId }}">
                @endif
                <input type="text" name="words[{{ $i }}][teks]" value="{{ $wordTeks }}" required maxlength="255"
                    class="flex-1 px-2.5 py-1.5 text-xs border border-gray-200 rounded bg-white focus-ring placeholder:text-gray-400"
                    placeholder="Kata {{ $i + 1 }}, contoh: {{ ['Tegas', 'Antusias', 'Sabar', 'Teliti'][$i] }}">
                <select name="words[{{ $i }}][dimensi]" required
                    class="w-36 shrink-0 px-2.5 py-1.5 text-xs border border-gray-200 rounded bg-white focus-ring cursor-pointer">
                    <option value="">Dimensi...</option>
                    @foreach (\App\Enums\DiscDimension::cases() as $dimension)
                        <option value="{{ $dimension->value }}" @selected($wordDimensi === $dimension->value)>{{ $dimension->value }} — {{ $dimension->shortLabel() }}</option>
                    @endforeach
                </select>
            </div>
        @endfor
    </div>
</div>
