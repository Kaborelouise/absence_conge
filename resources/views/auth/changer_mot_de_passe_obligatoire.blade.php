<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Pour des raisons de sécurité, vous devez définir un nouveau mot de passe avant de continuer.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('mot_de_passe.changer_obligatoire.update') }}">
        @csrf
        @method('PUT')

        <!-- Nouveau mot de passe -->
        <div>
            <x-input-label for="password" :value="__('Nouveau mot de passe')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autofocus autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirmation -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirmer le nouveau mot de passe')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Définir le mot de passe') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>