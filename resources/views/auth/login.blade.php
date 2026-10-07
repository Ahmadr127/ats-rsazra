<x-layouts.guest title="Masuk - ATS RS Azra">
    <x-ui.card>
        <p class="ui-eyebrow">ATS RS Azra</p>
        <h1 class="ui-title mt-2">Masuk</h1>
        <p class="ui-help mt-1">Masukkan kredensial Anda untuk melanjutkan</p>

        @if (session('status'))
            <x-ui.alert tone="success" class="mt-5">{{ session('status') }}</x-ui.alert>
        @endif

        <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
            @csrf

            <x-ui.input
                label="Nama Pengguna"
                id="username"
                name="username"
                type="text"
                value="{{ old('username') }}"
                required
                autofocus
                placeholder="username"
            />

            <x-ui.input
                label="Kata Sandi"
                id="password"
                name="password"
                type="password"
                required
                placeholder="••••••••"
            />

            <x-ui.checkbox name="remember" label="Ingat saya" />

            <x-ui.button type="submit" class="w-full">Masuk</x-ui.button>
        </form>
    </x-ui.card>
</x-layouts.guest>
