{{-- Variables: $question (?MbtiQuestion) --}}
@php
    $selectedDikotomi = old('dikotomi', $question?->dikotomi ?? 'EI');
    $selectedKutub = old('kutub_a', $question?->kutub_a?->value);
@endphp

<div class="px-4 pt-4 pb-5" x-data="{ dikotomi: '{{ $selectedDikotomi }}' }">
    <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-3">Isi Soal</p>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Nomor Urut <span class="text-red-500">*</span></label>
            <input type="number" name="urutan" value="{{ old('urutan', $question?->urutan ?? '') }}" min="1" max="9999" required
                class="w-full px-2.5 py-1.5 text-xs border border-gray-200 rounded bg-white focus-ring"
                placeholder="Contoh: 71">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Dikotomi <span class="text-red-500">*</span></label>
            <select name="dikotomi" x-model="dikotomi" required
                class="w-full px-2.5 py-1.5 text-xs border border-gray-200 rounded bg-white focus-ring cursor-pointer">
                @foreach (['EI' => 'E / I (Ekstrovert − Introvert)', 'SN' => 'S / N (Penginderaan − Intuisi)', 'TF' => 'T / F (Pemikiran − Perasaan)', 'JP' => 'J / P (Terstruktur − Fleksibel)'] as $value => $label)
                    <option value="{{ $value }}" @selected($selectedDikotomi === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Kutub Pernyataan A <span class="text-red-500">*</span></label>
            <select name="kutub_a" required
                class="w-full px-2.5 py-1.5 text-xs border border-gray-200 rounded bg-white focus-ring cursor-pointer">
                <option value="">Pilih kutub...</option>
                @foreach (\App\Enums\MbtiPole::cases() as $pole)
                    @php
                        $poleDikotomi = in_array($pole->value, ['E', 'I'], true) ? 'EI' : (in_array($pole->value, ['S', 'N'], true) ? 'SN' : (in_array($pole->value, ['T', 'F'], true) ? 'TF' : 'JP'));
                    @endphp
                    <option value="{{ $pole->value }}" x-show="dikotomi === '{{ $poleDikotomi }}'" @selected($selectedKutub === $pole->value)>{{ $pole->value }} — {{ $pole->shortLabel() }}</option>
                @endforeach
            </select>
            <p class="text-[10px] text-gray-400 mt-1">Kutub A harus pasangan dikotomi terpilih. Kutub B otomatis lawannya.</p>
        </div>
    </div>
    <div class="space-y-3">
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Pernyataan A <span class="text-red-500">*</span></label>
            <textarea name="pernyataan_a" rows="2" required
                class="w-full px-2.5 py-1.5 text-xs border border-gray-200 rounded bg-white focus-ring resize-none"
                placeholder="Tulis pernyataan pilihan A...">{{ old('pernyataan_a', $question?->pernyataan_a ?? '') }}</textarea>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Pernyataan B <span class="text-red-500">*</span></label>
            <textarea name="pernyataan_b" rows="2" required
                class="w-full px-2.5 py-1.5 text-xs border border-gray-200 rounded bg-white focus-ring resize-none"
                placeholder="Tulis pernyataan pilihan B (kutub lawan)...">{{ old('pernyataan_b', $question?->pernyataan_b ?? '') }}</textarea>
        </div>
    </div>
</div>
