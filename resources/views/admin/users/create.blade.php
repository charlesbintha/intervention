@extends('layouts.app')

@section('title', 'Créer un Utilisateur')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <a href="{{ route('admin.users.index') }}" class="text-gut-blue hover:text-gut-orange transition inline-flex items-center mb-4">
            <i class="fas fa-arrow-left mr-2"></i>Retour à la liste
        </a>
        <h1 class="text-3xl font-bold text-gray-900">
            <i class="fas fa-user-plus text-gut-blue mr-2"></i>
            Créer un Nouvel Utilisateur
        </h1>
        <p class="mt-2 text-sm text-gray-600">L'utilisateur recevra un email avec ses identifiants de connexion</p>
    </div>

    <div class="bg-white rounded-lg shadow-lg p-8">
        <form action="{{ route('admin.users.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="space-y-6">
                <!-- Sélection d'un employé GUT -->
                <div>
                    <label for="employee_search" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-search mr-2 text-gut-blue"></i>Rechercher un employé GUT
                    </label>
                    <div class="relative mb-2">
                        <input type="text" id="employee_search" placeholder="Tapez pour rechercher un employé..."
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gut-blue focus:border-transparent"
                            oninput="filterEmployees()">
                        <i class="fas fa-search absolute right-3 top-3 text-gray-400"></i>
                    </div>
                    <select id="employee_select" size="5" onchange="fillEmployeeInfo()"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gut-blue focus:border-transparent">
                        <option value="">Sélectionnez un employé</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee['id'] }}"
                                data-prenom-nom="{{ $employee['prenom_nom'] }}"
                                data-email="{{ $employee['email'] ?? '' }}"
                                data-search-text="{{ strtolower($employee['prenom_nom']) }}">
                                {{ $employee['prenom_nom'] }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i>
                        <span id="employees-count">{{ count($employees) }}</span> employé(s) disponible(s)
                    </p>
                </div>

                <!-- Nom complet -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user mr-2 text-gut-blue"></i>Nom complet *
                    </label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gut-blue focus:border-transparent @error('name') border-red-500 @enderror"
                        placeholder="Ex: Amadou Diallo">
                    @error('name')
                        <p class="mt-2 text-sm text-red-600">
                            <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                        </p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i>Sélectionnez un employé ci-dessus pour remplir automatiquement
                    </p>
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-envelope mr-2 text-gut-blue"></i>Adresse email *
                    </label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gut-blue focus:border-transparent @error('email') border-red-500 @enderror"
                        placeholder="exemple@gut.sn">
                    @error('email')
                        <p class="mt-2 text-sm text-red-600">
                            <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                        </p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i>Un email sera envoyé à cette adresse avec les identifiants de connexion
                    </p>
                </div>

                <!-- Rôle -->
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-shield-alt mr-2 text-gut-blue"></i>Rôle *
                    </label>
                    <select name="role" id="role" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gut-blue focus:border-transparent @error('role') border-red-500 @enderror">
                        <option value="">Sélectionnez un rôle</option>
                        <option value="user" {{ old('role') === 'user' ? 'selected' : '' }}>
                            <i class="fas fa-user"></i> Utilisateur
                        </option>
                        <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>
                            <i class="fas fa-crown"></i> Administrateur
                        </option>
                    </select>
                    @error('role')
                        <p class="mt-2 text-sm text-red-600">
                            <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                        </p>
                    @enderror
                    <div class="mt-2 space-y-2 text-xs text-gray-600">
                        <p>
                            <i class="fas fa-user text-blue-500 mr-1"></i>
                            <strong>Utilisateur :</strong> Peut seulement soumettre les formulaires (Survey, Maintenance, Intervention)
                        </p>
                        <p>
                            <i class="fas fa-crown text-purple-500 mr-1"></i>
                            <strong>Administrateur :</strong> Accès complet incluant la gestion des utilisateurs
                        </p>
                    </div>
                </div>

                <!-- Signature -->
                <div>
                    <label for="signature" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-signature mr-2 text-gut-blue"></i>Signature (optionnel)
                    </label>
                    <input type="file" name="signature" id="signature" accept="image/png,image/jpeg,image/jpg"
                        class="block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-gut-blue file:text-white hover:file:bg-gut-orange @error('signature') border-red-500 @enderror">
                    @error('signature')
                        <p class="mt-2 text-sm text-red-600">
                            <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                        </p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i>Formats acceptés: PNG, JPG, JPEG. Taille maximale: 2 Mo
                    </p>
                </div>

                <!-- Informations -->
                <div class="bg-blue-50 border-l-4 border-gut-blue p-4 rounded">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-gut-blue text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-gray-800">Informations importantes</h3>
                            <div class="mt-2 text-sm text-gray-700 space-y-1">
                                <p><i class="fas fa-check text-green-600 mr-2"></i>Un mot de passe sera généré automatiquement</p>
                                <p><i class="fas fa-check text-green-600 mr-2"></i>Un email sera envoyé avec les identifiants de connexion</p>
                                <p><i class="fas fa-check text-green-600 mr-2"></i>Le compte sera activé par défaut</p>
                                <p><i class="fas fa-check text-green-600 mr-2"></i>L'utilisateur pourra se connecter immédiatement</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Boutons d'action -->
            <div class="mt-8 flex items-center justify-end space-x-4">
                <a href="{{ route('admin.users.index') }}" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition font-medium">
                    <i class="fas fa-times mr-2"></i>Annuler
                </a>
                <button type="submit" class="bg-gut-blue hover:bg-gut-orange text-white px-8 py-3 rounded-lg font-medium transition shadow-lg">
                    <i class="fas fa-user-plus mr-2"></i>Créer l'Utilisateur
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function filterEmployees() {
    const searchInput = document.getElementById('employee_search');
    const employeeSelect = document.getElementById('employee_select');
    const searchText = searchInput.value.toLowerCase().trim();
    let visibleCount = 0;

    for (let i = 1; i < employeeSelect.options.length; i++) {
        const option = employeeSelect.options[i];
        const optionSearchText = option.dataset.searchText || '';

        if (searchText === '' || optionSearchText.includes(searchText)) {
            option.style.display = '';
            visibleCount++;
        } else {
            option.style.display = 'none';
        }
    }

    document.getElementById('employees-count').textContent = visibleCount;

    // Auto-sélection si un seul résultat
    if (visibleCount === 1 && searchText !== '') {
        for (let i = 1; i < employeeSelect.options.length; i++) {
            if (employeeSelect.options[i].style.display !== 'none') {
                employeeSelect.selectedIndex = i;
                fillEmployeeInfo();
                break;
            }
        }
    }
}

function fillEmployeeInfo() {
    const employeeSelect = document.getElementById('employee_select');
    const selectedOption = employeeSelect.options[employeeSelect.selectedIndex];

    if (selectedOption && selectedOption.value) {
        const prenomNom = selectedOption.dataset.prenomNom || '';
        const email = selectedOption.dataset.email || '';

        // Remplir automatiquement le nom
        document.getElementById('name').value = prenomNom;

        // Remplir automatiquement l'email si disponible
        if (email) {
            document.getElementById('email').value = email;
        }
    }
}
</script>
@endpush
@endsection
