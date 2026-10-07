<x-layouts.app title="Peran - ATS RS Azra">

    <div class="flex items-center justify-between mb-5">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Peran</h1>
            <p class="text-xs text-gray-500 mt-0.5">Kelola peran pengguna. Key tidak dapat diubah setelah dibuat.</p>
        </div>
        @permission('role.create')
        <a
            href="{{ route('pengaturan.peran.create') }}"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors ease-out duration-150"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Peran
        </a>
        @endpermission
    </div>

    <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/60">
                        <th class="text-left px-4 py-2.5 text-[11px] font-medium text-gray-500 uppercase tracking-wide">Label</th>
                        <th class="text-left px-4 py-2.5 text-[11px] font-medium text-gray-500 uppercase tracking-wide">Key</th>
                        <th class="text-left px-4 py-2.5 text-[11px] font-medium text-gray-500 uppercase tracking-wide">Pengguna</th>
                        <th class="w-24 px-4 py-2.5"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($roles as $role)
                        <tr class="odd:bg-white even:bg-primary/5 hover:bg-primary/10 transition-colors ease-out duration-100">
                            <td class="px-4 py-2.5 font-medium text-gray-800">{{ $role->label }}</td>
                            <td class="px-4 py-2.5"><code class="text-xs font-mono text-primary bg-primary/5 px-1.5 py-0.5 rounded">{{ $role->key }}</code></td>
                            <td class="px-4 py-2.5 text-xs text-gray-500">{{ $role->users_count }} pengguna</td>
                            <td class="px-4 py-2.5">
                                <div class="flex items-center justify-end gap-0.5">
                                    @permission('role.update')
                                    <a
                                        href="{{ route('pengaturan.peran.edit', $role) }}"
                                        class="p-1.5 rounded text-amber-400/60 hover:text-amber-500 hover:bg-amber-50 transition-colors ease-out duration-150"
                                        title="Edit peran"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                        </svg>
                                    </a>
                                    @endpermission
                                    @permission('role.delete')
                                    <form method="POST" action="{{ route('pengaturan.peran.destroy', $role) }}" onsubmit="return confirm('Hapus peran {{ $role->label }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="p-1.5 rounded text-red-400/60 hover:text-red-500 hover:bg-red-50 transition-colors ease-out duration-150 cursor-pointer"
                                            title="Hapus peran"
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
                            <td colspan="4" class="px-4 py-14 text-center text-sm text-gray-400">Belum ada peran.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</x-layouts.app>
