<x-layouts.app title="Hak Akses - ATS RS Azra">

    <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Hak Akses</h1>
            <p class="text-xs text-gray-500 mt-0.5">Atur izin (permission) tiap peran (role). Satu tombol / aksi = satu permission.</p>
        </div>
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input
                id="permission-search"
                type="text"
                placeholder="Cari permission... (mis. form pipeline)"
                class="w-72 pl-8 pr-3 py-1.5 text-sm border border-gray-200 rounded-md focus-ring bg-white placeholder:text-gray-400"
            >
        </div>
    </div>

    @php
        // Permissions that gate the action forms on the candidate pipeline page.
        // Logic is untouched — this is purely a labeling helper for the UI.
        $formPipelineKeys = [
            'application.advance',
            'application.fail',
            'screening.decide-hr',
            'screening.decide-user',
            'interview.schedule',
            'interview.reschedule',
            'interview.decide-user',
            'interview.decide-manager',
            'interview.decide-director',
            'test.review-essay',
            'test.decide',
            'offering.manage',
            'mcu.schedule',
            'mcu.decide',
            'onboarding.invite',
            'onboarding.complete',
        ];
        $pipelinePageKeys = ['vacancy.candidate-detail'];
        $pipelineGroups = ['Pipeline', 'Wawancara', 'Tes', 'Offering & MCU & Onboarding'];

        $groupDescriptions = [
            'Menu' => 'Membuka/menutup akses menu di sidebar.',
            'Dasbor' => 'Cakupan data yang boleh dilihat di Beranda.',
            'Akun Pengguna' => 'Kelola akun login karyawan (buat, ubah, aktif/nonaktif).',
            'Karyawan' => 'Kelola direktori data karyawan.',
            'Unit' => 'Kelola daftar unit kerja.',
            'Template Alur' => 'Kelola template alur kerja (urutan tahap rekrutmen).',
            'Template Lowongan' => 'Kelola template lowongan & penerbitan lowongan.',
            'Bank Soal' => 'Kelola bank soal (template soal tes).',
            'Tes MBTI' => 'Kelola daftar soal tes MBTI.',
            'Tes DiSC' => 'Kelola daftar soal tes DiSC.',
            'Template Wawancara' => 'Kelola template wawancara (kriteria & pertanyaan).',
            'Template Email' => 'Kelola teks template email notifikasi.',
            'Lowongan' => 'Kelola lowongan & halaman Pipeline kandidat.',
            'Pipeline' => 'Aksi tahap kandidat pada halaman Pipeline.',
            'Wawancara' => 'Penjadwalan & penilaian wawancara kandidat.',
            'Offering & MCU & Onboarding' => 'Surat penawaran, MCU, dan onboarding karyawan baru.',
            'Tes' => 'Penilaian jawaban esai & keputusan hasil tes kompetensi.',
            'Hak Akses' => 'Kelola matriks hak akses ini sendiri.',
            'Peran' => 'Kelola peran (contoh: Admin HR, Manajer HR).',
            'Notifikasi' => 'Siapa saja yang menerima notifikasi internal.',
        ];

        $isFormPipeline = fn ($key) => in_array($key, $formPipelineKeys, true);
        $isPipelinePage = fn ($key) => in_array($key, $pipelinePageKeys, true);
    @endphp

    <x-ui.alert tone="info" title="Cara membaca halaman ini" class="mb-3">
        Centang kotak pada kolom peran untuk memberikan izin. Kolom di kepala tabel bisa dipakai
        mencentang/menghapus semua permission yang <strong>terlihat</strong> untuk peran tersebut.
        Izin dengan chip <span class="inline-flex items-center px-1.5 py-0.5 mx-0.5 text-[10px] font-semibold rounded border border-violet-200 bg-violet-50 text-violet-700 align-middle">Form Pipeline</span>
        mengontrol <strong>panel aksi (form)</strong> pada halaman Pipeline kandidat (mis. tombol lolos/gagal,
        form skrining, form penilaian wawancara, tes, offering, MCU, onboarding) — serta menolak aksinya di server bila tidak dicentang.
        Chip <span class="inline-flex items-center px-1.5 py-0.5 mx-0.5 text-[10px] font-semibold rounded border border-sky-200 bg-sky-50 text-sky-700 align-middle">Halaman Pipeline</span>
        mengontrol akses membuka halaman Pipeline &amp; detail kandidat. Perubahan berlaku setelah disimpan.
    </x-ui.alert>

    @if ($errors->any())
        <div class="mb-4 px-4 py-2.5 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('pengaturan.hak-akses.update') }}">
        @csrf
        @method('PUT')

        <div class="flex flex-wrap items-center gap-x-4 gap-y-1.5 mb-2 px-1 text-[11px] text-gray-500">
            <span class="inline-flex items-center gap-1.5">
                <span class="inline-block w-2.5 h-2.5 rounded-sm bg-violet-400"></span>
                <span><strong class="text-gray-700">Ungu</strong> = form aksi Pipeline</span>
            </span>
            <span class="inline-flex items-center gap-1.5">
                <span class="inline-block w-2.5 h-2.5 rounded-sm bg-sky-400"></span>
                <span><strong class="text-gray-700">Biru</strong> = membuka halaman Pipeline</span>
            </span>
            <span class="inline-flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9.414l-1-1H17V8z"/></svg>
                <span><strong class="text-gray-700">Perhatian:</strong> minimal satu peran wajib memegang <em>menu.rbac</em> + <em>rbac.manage</em> agar tidak terkunci</span>
            </span>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 overflow-hidden mb-4">
            <div class="overflow-auto max-h-[62vh] hak-scroll">
                <table class="w-full text-sm" id="matrix">
                    <thead class="sticky top-0 z-10 shadow-sm">
                        <tr class="border-b border-gray-100 bg-gray-50">
                            <th class="text-left px-4 py-2.5 text-[11px] font-medium text-gray-500 uppercase tracking-wide">Permission</th>
                            @foreach ($roles as $role)
                                <th class="px-3 py-2.5 text-center">
                                    <label class="inline-flex flex-col items-center gap-1 cursor-pointer">
                                        <span class="text-[11px] font-medium text-gray-700">{{ $role->label }}</span>
                                        <input type="checkbox" class="w-4 h-4 accent-primary col-toggle" data-role="{{ $role->id }}" title="Centang semua {{ $role->label }} (yang sedang terlihat)">
                                    </label>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($grouped as $group => $permissions)
                            @php
                                $isPipelineGroup = in_array($group, $pipelineGroups, true);
                                $description = $groupDescriptions[$group] ?? null;
                            @endphp
                            <tr class="bg-gray-50/60 border-t border-gray-100" data-group="{{ $group }}">
                                <td colspan="{{ count($roles) + 1 }}" class="px-4 py-2">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="text-[11px] font-semibold text-gray-700 uppercase tracking-wide">{{ $group }}</span>
                                        @if ($isPipelineGroup)
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 text-[10px] font-semibold rounded border border-violet-200 bg-violet-50 text-violet-700">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                                Form Pipeline
                                            </span>
                                        @endif
                                        @if ($description)
                                            <span class="text-[11px] text-gray-400 font-normal normal-case tracking-normal">— {{ $description }}</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @foreach ($permissions as $permission)
                                @php
                                    $formPipeline = $isFormPipeline($permission->key);
                                    $pipelinePage = $isPipelinePage($permission->key);
                                    $searchExtra = $formPipeline ? ' form pipeline' : ($pipelinePage ? ' halaman pipeline' : '');
                                @endphp
                                <tr class="border-t border-gray-100 hover:bg-gray-50/50 {{ $formPipeline ? 'bg-violet-50/30' : '' }}" data-permission data-search="{{ mb_strtolower($permission->label.' '.$permission->key.$searchExtra) }}">
                                    <td class="px-4 py-2">
                                        <div class="flex items-start gap-2">
                                            @if ($formPipeline)
                                                <span class="mt-0.5 inline-block w-2 h-2 rounded-sm bg-violet-400 shrink-0" title="Form aksi Pipeline"></span>
                                            @elseif ($pipelinePage)
                                                <span class="mt-0.5 inline-block w-2 h-2 rounded-sm bg-sky-400 shrink-0" title="membuka halaman Pipeline"></span>
                                            @else
                                                <span class="mt-0.5 inline-block w-2 h-2 rounded-sm bg-gray-200 shrink-0"></span>
                                            @endif
                                            <div class="min-w-0">
                                                <p class="text-sm text-gray-800 leading-snug">{{ $permission->label }}</p>
                                                <div class="flex flex-wrap items-center gap-1.5 mt-0.5">
                                                    <p class="text-[10px] text-gray-400 font-mono">{{ $permission->key }}</p>
                                                    @if ($formPipeline)
                                                        <span class="inline-flex items-center px-1.5 py-0.5 text-[9px] font-semibold rounded border border-violet-200 bg-violet-50 text-violet-700">Form Pipeline</span>
                                                    @elseif ($pipelinePage)
                                                        <span class="inline-flex items-center px-1.5 py-0.5 text-[9px] font-semibold rounded border border-sky-200 bg-sky-50 text-sky-700">Halaman Pipeline</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    @foreach ($roles as $role)
                                        <td class="px-3 py-2 text-center">
                                            <input
                                                type="checkbox"
                                                name="permissions[{{ $role->id }}][]"
                                                value="{{ $permission->id }}"
                                                class="w-4 h-4 accent-primary cell"
                                                data-role="{{ $role->id }}"
                                                @if (in_array($permission->id, $granted[$role->id] ?? [], true)) checked @endif
                                            >
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        @endforeach
                        <tr id="matrix-empty" class="hidden">
                            <td colspan="{{ count($roles) + 1 }}" class="px-4 py-10 text-center text-xs text-gray-400">Tidak ada permission yang cocok dengan pencarian.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3">
            <p class="text-[11px] text-gray-400 max-w-md">Menyimpan akan menggantikan seluruh centangan dengan kondisi yang tampil di layar ini. Izin yang tidak dicentang akan dicabut.</p>
            <button type="submit" class="px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors ease-out duration-150 cursor-pointer">
                Simpan Hak Akses
            </button>
        </div>
    </form>

    <style>
        .hak-scroll {
            scrollbar-width: thin;
            scrollbar-color: rgba(0, 0, 0, .35) transparent;
        }
        .hak-scroll::-webkit-scrollbar { width: 10px; height: 10px; }
        .hak-scroll::-webkit-scrollbar-track { background: transparent; }
        .hak-scroll::-webkit-scrollbar-thumb { background: rgba(0, 0, 0, .3); border-radius: 999px; }
        .hak-scroll::-webkit-scrollbar-thumb:hover { background: rgba(0, 0, 0, .5); }
        .hak-scroll::-webkit-scrollbar-corner { background: transparent; }
    </style>
    <script>
        document.querySelectorAll('.col-toggle').forEach(function (toggle) {
            toggle.addEventListener('change', function () {
                document.querySelectorAll('.cell[data-role="' + toggle.dataset.role + '"]').forEach(function (cell) {
                    var row = cell.closest('tr');
                    if (row && row.style.display === 'none') return;
                    cell.checked = toggle.checked;
                });
            });
        });

        var permissionSearch = document.getElementById('permission-search');
        var matrixEmpty = document.getElementById('matrix-empty');
        function filterMatrix() {
            var q = permissionSearch.value.trim().toLowerCase();
            var visible = 0;
            document.querySelectorAll('#matrix tbody tr[data-permission]').forEach(function (row) {
                var match = !q || (row.dataset.search || '').indexOf(q) !== -1;
                row.style.display = match ? '' : 'none';
                if (match) visible++;
            });
            document.querySelectorAll('#matrix tbody tr[data-group]').forEach(function (groupRow) {
                var hasVisible = false;
                var next = groupRow.nextElementSibling;
                while (next && !next.hasAttribute('data-group') && next.id !== 'matrix-empty') {
                    if (next.hasAttribute('data-permission') && next.style.display !== 'none') {
                        hasVisible = true;
                        break;
                    }
                    next = next.nextElementSibling;
                }
                groupRow.style.display = (!q || hasVisible) ? '' : 'none';
            });
            if (matrixEmpty) matrixEmpty.classList.toggle('hidden', !(q && visible === 0));
        }
        if (permissionSearch) permissionSearch.addEventListener('input', filterMatrix);
    </script>

</x-layouts.app>
