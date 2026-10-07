<x-layouts.guest title="Akses Ditolak - ATS RS Azra">
    <x-ui.card class="text-center">
        <p class="text-[25px] font-bold text-red-600">403</p>
        <h1 class="ui-section-title mt-2">Akses Ditolak</h1>
        <p class="ui-help mx-auto mt-2 max-w-sm">Anda tidak memiliki izin untuk mengakses halaman ini.</p>
        <x-ui.button href="{{ route('dashboard') }}" class="mt-6 w-full">Kembali ke Dashboard</x-ui.button>
    </x-ui.card>
</x-layouts.guest>
