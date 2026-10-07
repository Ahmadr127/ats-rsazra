<x-layouts.app title="Hak Akses - ATS RS Azra">

    <div class="flex items-center justify-between mb-5">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Hak Akses</h1>
            <p class="text-xs text-gray-500 mt-0.5">Satu tombol / sidemenu = satu permission. Centang permission untuk tiap peran.</p>
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
            <div class="overflow-x-auto">
                <table class="w-full text-sm" id="matrix">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/60">
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
                            <tr class="bg-gray-50/60">
                                <td colspan="{{ count($roles) + 1 }}" class="px-4 py-2 text-[11px] font-semibold text-gray-600 uppercase tracking-wide">{{ $group }}</td>
                            </tr>
                            @foreach ($permissions as $permission)
                                <tr class="border-t border-gray-100 hover:bg-gray-50/50">
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

    <script>
        document.querySelectorAll('.col-toggle').forEach(function (toggle) {
            toggle.addEventListener('change', function () {
                document.querySelectorAll('.cell[data-role="' + toggle.dataset.role + '"]').forEach(function (cell) {
                    cell.checked = toggle.checked;
                });
            });
        });
    </script>

</x-layouts.app>
