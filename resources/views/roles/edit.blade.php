<x-layouts.app title="Edit Peran - ATS RS Azra">

    <div class="mb-5">
        <a href="{{ route('pengaturan.peran.index') }}" class="inline-flex items-center gap-1 text-xs text-gray-500 hover:text-primary transition-colors ease-out duration-150 mb-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Daftar Peran
        </a>
        <h1 class="text-xl font-semibold text-gray-900">Edit Peran</h1>
        <p class="text-xs text-gray-500 mt-0.5">Key <code class="font-mono text-primary bg-primary/5 px-1 rounded">{{ $role->key }}</code> tidak dapat diubah.</p>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 p-5 max-w-xl">
        <form method="POST" action="{{ route('pengaturan.peran.update', $role) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Label <span class="text-red-500">*</span></label>
                <input type="text" name="label" required value="{{ old('label', $role->label) }}"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/40">
                @error('label')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors ease-out duration-150 cursor-pointer">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

</x-layouts.app>
