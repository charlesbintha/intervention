<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Portail Intervention</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-cover bg-center bg-no-repeat bg-fixed" style="background-image: url('{{ asset('images/background.jpg') }}');">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-black/40">
        <div class="max-w-md w-full bg-white rounded-2xl shadow-2xl p-8">
            <!-- Logo et titre -->
            <div class="text-center mb-6">
                @if(file_exists(public_path('images/logo.png')))
                    <img src="{{ asset('images/logo.png') }}" alt="Logo GUT" class="mx-auto h-24 w-auto mb-4">
                @endif
                <h2 class="text-3xl font-extrabold text-gray-900">
                    Portail Intervention
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Connectez-vous à votre compte
                </p>
            </div>

            <!-- Messages d'erreur ou de succès -->
            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <a href="{{ route('auth.microsoft.redirect') }}"
                class="flex w-full items-center justify-center gap-3 rounded-lg bg-[#2F2F2F] px-4 py-3 font-semibold text-white transition hover:bg-black focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                <svg aria-hidden="true" viewBox="0 0 23 23" class="h-5 w-5">
                    <path fill="#f35325" d="M1 1h10v10H1z"/><path fill="#81bc06" d="M12 1h10v10H12z"/><path fill="#05a6f0" d="M1 12h10v10H1z"/><path fill="#ffba08" d="M12 12h10v10H12z"/>
                </svg>
                Se connecter avec Microsoft
            </a>

            <p class="mt-4 text-center text-xs text-gray-500">Utilisez votre compte professionnel Microsoft autorisé.</p>

            <div class="text-center text-sm text-gray-500 mt-6 pt-4 border-t border-gray-200">
                <p>Besoin d'aide ? Contactez votre administrateur</p>
            </div>
        </div>
    </div>
</body>
</html>
