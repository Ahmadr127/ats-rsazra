<x-layouts.app title="Hak Akses - ATS RS Azra">

    <div class="flex items-center justify-between mb-5">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Hak Akses</h1>
            <p class="text-xs text-gray-500 mt-0.5">Satu tombol / sidemenu = satu permission. Centang permission untuk tiap peran.</p>
        </div>
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input
                id="permission-search"
                type="text"
                placeholder="Cari permission..."
                class="w-56 pl-8 pr-3 py-1.5 text-sm border border-gray-200 rounded-md focus-ring bg-white placeholder:text-gray-400"
            >
        </div>
    </div>

    @if ($errors->any())
        <div class="mb-4 px-4 py-2.5 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('pengaturan.hak-akses.update') }}">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-xl border border-gray-100 overflow-hidden mb-4">
            <div class="overflow-auto max-h-[65vh] hak-scroll">
                <table class="w-full text-sm" id="matrix">
                    <thead class="sticky top-0 z-10 shadow-sm">
                        <tr class="border-b border-gray-100 bg-gray-50">
                            <th class="text-left px-4 py-2.5 text-[11px] font-medium text-gray-500 uppercase tracking-wide">Permission</th>
                            @foreach ($roles as $role)
                                <th class="px-3 py-2.5 text-center">
                                    <label class="inline-flex flex-col items-center gap-1 cursor-pointer">
                                        <span class="text-[11px] font-medium text-gray-700">{{ $role->label }}</span>
                                        <input type="checkbox" class="w-4 h-4 accent-primary col-toggle" data-role="{{ $role->id }}" title="Centang semua {{ $role->label }}">
                                    </label>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($grouped as $group => $permissions)
                            <tr class="bg-gray-50/60" data-group="{{ $group }}">
                                <td colspan="{{ count($roles) + 1 }}" class="px-4 py-2 text-[11px] font-semibold text-gray-600 uppercase tracking-wide">{{ $group }}</td>
                            </tr>
                            @foreach ($permissions as $permission)
                                <tr class="border-t border-gray-100 hover:bg-gray-50/50" data-permission data-search="{{ mb_strtolower($permission->label.' '.$permission->key) }}">
                                    <td class="px-4 py-2">
                                        <p class="text-sm text-gray-800">{{ $permission->label }}</p>
                                        <p class="text-[11px] text-gray-400 font-mono">{{ $permission->key }}</p>
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

        <div class="flex justify-end">
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
