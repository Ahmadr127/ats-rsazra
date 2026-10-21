<x-layouts.guest title="Server Error - ATS RS Azra">
    <x-ui.card class="text-center">
        <p class="text-[25px] font-bold text-red-600">500</p>
        <h1 class="ui-section-title mt-2">Server Error</h1>
        <p class="ui-help mx-auto mt-2 max-w-sm">Terjadi kesalahan pada server. Silakan coba lagi nanti.</p>
        <x-ui.button href="{{ route('dashboard') }}" class="mt-6 w-full">Kembali ke Dashboard</x-ui.button>
    </x-ui.card>
</x-layouts.guest>
