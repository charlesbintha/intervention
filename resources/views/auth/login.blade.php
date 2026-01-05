<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Portail Intervention</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .gradient-gut {
            background: linear-gradient(135deg, #0099CC 0%, #FF8C00 100%);
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Logo et titre -->
            <div class="text-center">
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
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <!-- Formulaire de connexion -->
            <form class="mt-8 space-y-6 bg-white p-8 rounded-lg shadow-lg" action="{{ route('login') }}" method="POST">
                @csrf
                <div class="rounded-md shadow-sm space-y-4">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-envelope mr-2"></i>Email
                        </label>
                        <input id="email" name="email" type="email" required
                            value="{{ old('email') }}"
                            class="appearance-none rounded-lg relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="votreemail@exemple.com">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-lock mr-2"></i>Mot de passe
                        </label>
                        <input id="password" name="password" type="password" required
                            class="appearance-none rounded-lg relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Votre mot de passe">
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center">
                    <input id="remember" name="remember" type="checkbox"
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="remember" class="ml-2 block text-sm text-gray-900">
                        Se souvenir de moi
                    </label>
                </div>

                <div>
                    <button type="submit"
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white gradient-gut hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        Se connecter
                    </button>
                </div>
            </form>

            <div class="text-center text-sm text-gray-600">
                <p>Besoin d'aide ? Contactez votre administrateur</p>
            </div>
        </div>
    </div>
</body>
</html>
