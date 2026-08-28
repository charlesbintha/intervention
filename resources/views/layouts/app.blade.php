<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Portail Intervention - GUT')</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --gut-blue: #0099CC;
            --gut-orange: #FF8C00;
            --gut-gray: #6B7280;
            --gut-white: #ffffff;
        }

        .bg-gut-blue {
            background-color: var(--gut-blue);
        }

        .bg-gut-orange {
            background-color: var(--gut-orange);
        }

        .text-gut-blue {
            color: var(--gut-blue);
        }

        .text-gut-white {
            color: var(--gut-white);
        }

        .text-gut-orange {
            color: var(--gut-orange);
        }

        .border-gut-blue {
            border-color: var(--gut-blue);
        }

        .hover\:bg-gut-blue:hover {
            background-color: var(--gut-blue);
        }

        .hover\:bg-gut-orange:hover {
            background-color: var(--gut-orange);
        }

        .gradient-gut {
            background: linear-gradient(135deg, var(--gut-blue) 0%, var(--gut-orange) 100%);
        }

        .card-shadow {
            box-shadow: 0 10px 30px rgba(0, 153, 204, 0.1);
        }

        .card-hover {
            transition: all 0.3s ease;
        }

        .card-hover:hover {
            box-shadow: 0 15px 40px rgba(0, 153, 204, 0.2);
        }

        main input:not([type="hidden"]):not([type="checkbox"]):not([type="radio"]):not([type="submit"]),
        main select,
        main textarea {
            border-width: 1px;
            border-style: solid;
            border-color: #cbd5e1;
            background-color: #ffffff;
        }

        main input:not([type="hidden"]):not([type="checkbox"]):not([type="radio"]):not([type="submit"]):focus,
        main select:focus,
        main textarea:focus {
            border-color: #0ea5e9;
            outline: 2px solid rgba(14, 165, 233, 0.18);
            outline-offset: 1px;
        }

        main input[readonly],
        main input:disabled,
        main select:disabled,
        main textarea:disabled {
            background-color: #f3f4f6;
            color: #4b5563;
        }

        .tracking-ui input:not([type="hidden"]):not([type="checkbox"]):not([type="radio"]):not([type="submit"]):not([type="file"]),
        .tracking-ui select {
            box-sizing: border-box;
            min-height: 46px;
            padding: 0.65rem 0.9rem;
            border-radius: 0.5rem;
            font-size: 0.95rem;
            line-height: 1.25rem;
        }

        .tracking-ui textarea {
            min-height: 96px;
            padding: 0.75rem 0.9rem;
            border-radius: 0.5rem;
            font-size: 0.95rem;
            line-height: 1.5rem;
        }

        .tracking-ui label {
            color: #374151;
            font-weight: 600;
        }

        .tracking-ui input::placeholder,
        .tracking-ui textarea::placeholder {
            color: #9ca3af;
        }

        .tracking-ui #project_search,
        .tracking-ui #agent-search {
            padding-left: 2.5rem;
        }

        /* Quill Editor Styles */
        .ql-container {
            font-family: inherit;
        }
        .ql-editor {
            min-height: 100px;
        }
    </style>
    <!-- Quill.js -->
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.0/dist/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.0/dist/quill.js"></script>
    @stack('styles')
</head>
<body class="bg-gray-50 flex flex-col min-h-screen">
    <nav class="gradient-gut shadow-lg">
        <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center">
                        <img src="{{ asset('images/logo.png') }}" alt="GUT Logo" class="h-12 w-auto bg-white rounded-lg p-1">
                        <span class="ml-3 text-white font-bold text-xl">Portail Intervention</span>
                    </a>
                </div>

                <div class="hidden md:flex items-center space-x-4">
                    <a href="{{ route('home') }}" class="text-white hover:bg-white hover:bg-opacity-20 px-3 py-2 rounded-md text-sm font-medium transition">
                        <i class="fas fa-home mr-2"></i>Accueil
                    </a>
                    <a href="{{ route('surveys.index') }}" class="text-white hover:bg-white hover:bg-opacity-20 px-3 py-2 rounded-md text-sm font-medium transition">
                        <i class="fas fa-search mr-2"></i>Surveys
                    </a>
                    <a href="{{ route('maintenances.index') }}" class="text-white hover:bg-white hover:bg-opacity-20 px-3 py-2 rounded-md text-sm font-medium transition">
                        <i class="fas fa-tools mr-2"></i>Maintenances
                    </a>
                    <a href="{{ route('intervention-utes.index') }}" class="text-white hover:bg-white hover:bg-opacity-20 px-3 py-2 rounded-md text-sm font-medium transition">
                        <i class="fas fa-wrench mr-2"></i>Interventions
                    </a>
                    <a href="{{ route('project-trackings.index') }}" class="text-white hover:bg-white hover:bg-opacity-20 px-3 py-2 rounded-md text-sm font-medium transition">
                        <i class="fas fa-list-check mr-2"></i>Suivi travaux
                    </a>

                    @auth
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.users.index') }}" class="text-white hover:bg-white hover:bg-opacity-20 px-3 py-2 rounded-md text-sm font-medium transition border-l border-white border-opacity-30 ml-4 pl-4">
                                <i class="fas fa-users-cog mr-2"></i>Utilisateurs
                            </a>
                        @endif

                        <div class="relative border-l border-white border-opacity-30 ml-4 pl-4">
                            <button id="profileDropdown" type="button" class="flex items-center text-white hover:bg-white hover:bg-opacity-20 px-3 py-2 rounded-md text-sm font-medium transition">
                                <i class="fas fa-user-circle mr-2"></i>{{ auth()->user()->name }}
                                <i class="fas fa-chevron-down ml-2 text-xs"></i>
                            </button>
                            <div id="profileMenu" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-md shadow-lg py-1 z-50">
                                <a href="{{ route('profile.signature.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition">
                                    <i class="fas fa-signature mr-2 text-gut-blue"></i>Ajouter Signature
                                </a>
                                @if(auth()->user()->isAdmin())
                                    <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition">
                                        <i class="fas fa-chart-line mr-2 text-gut-orange"></i>Dashboard
                                    </a>
                                @endif
                                <div class="border-t border-gray-100"></div>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition">
                                        <i class="fas fa-sign-out-alt mr-2 text-red-500"></i>Déconnexion
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    @if(session('success'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <main class="py-8 flex-grow">
        @yield('content')
    </main>

    <footer class="bg-gray-800 text-white mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="text-center">
                <p>&copy; {{ date('Y') }} Groupe Univers Telecom. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <!-- Custom Confirmations Script -->
    <script src="{{ asset('js/custom-confirmations.js') }}"></script>

    <!-- Profile Dropdown Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const profileDropdown = document.getElementById('profileDropdown');
            const profileMenu = document.getElementById('profileMenu');

            if (profileDropdown && profileMenu) {
                profileDropdown.addEventListener('click', function(e) {
                    e.stopPropagation();
                    profileMenu.classList.toggle('hidden');
                });

                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!profileDropdown.contains(e.target) && !profileMenu.contains(e.target)) {
                        profileMenu.classList.add('hidden');
                    }
                });
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
