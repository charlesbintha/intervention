@extends('layouts.app')

@section('title', 'Gérer ma Signature')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <a href="{{ route('home') }}" class="text-gut-blue hover:text-gut-orange transition inline-flex items-center mb-4">
            <i class="fas fa-arrow-left mr-2"></i>Retour à l'accueil
        </a>
        <h1 class="text-3xl font-bold text-gray-900">
            <i class="fas fa-signature text-gut-blue mr-2"></i>
            Gérer ma Signature
        </h1>
        <p class="mt-2 text-sm text-gray-600">Téléchargez votre signature pour qu'elle apparaisse automatiquement sur les documents PDF validés</p>
    </div>

    <div class="bg-white rounded-lg shadow-lg p-8">
        @if($user->signature)
            <div class="mb-8 p-6 bg-gray-50 border border-gray-200 rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    <i class="fas fa-check-circle text-green-600 mr-2"></i>Signature actuelle
                </h3>
                <div class="flex items-center justify-center p-4 bg-white border-2 border-dashed border-gray-300 rounded-lg">
                    <img src="{{ asset('storage/' . $user->signature) }}" alt="Signature actuelle" class="max-h-32 border border-gray-200 rounded">
                </div>
                <p class="mt-3 text-sm text-gray-600 text-center">
                    <i class="fas fa-info-circle mr-1"></i>Téléchargez une nouvelle signature ci-dessous pour remplacer celle-ci
                </p>
            </div>
        @else
            <div class="mb-8 p-6 bg-blue-50 border-l-4 border-gut-blue rounded">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-gut-blue text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-gray-800">Aucune signature enregistrée</h3>
                        <p class="mt-2 text-sm text-gray-700">
                            Téléchargez votre signature pour qu'elle apparaisse automatiquement sur les documents PDF que vous validez (Maintenances, Surveys, etc.)
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <form action="{{ route('profile.signature.update') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="space-y-6">
                <!-- Upload de la signature -->
                <div>
                    <label for="signature" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-upload mr-2 text-gut-blue"></i>
                        {{ $user->signature ? 'Télécharger une nouvelle signature' : 'Télécharger votre signature' }} *
                    </label>
                    <input type="file" name="signature" id="signature" accept="image/png,image/jpeg,image/jpg" required
                        class="block w-full text-sm text-gray-700 file:mr-4 file:py-3 file:px-6 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-gut-blue file:text-white hover:file:bg-gut-orange transition @error('signature') border-red-500 @enderror">
                    @error('signature')
                        <p class="mt-2 text-sm text-red-600">
                            <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                        </p>
                    @enderror
                    <div class="mt-3 space-y-2 text-xs text-gray-600">
                        <p><i class="fas fa-check text-green-600 mr-1"></i>Formats acceptés: PNG, JPG, JPEG</p>
                        <p><i class="fas fa-check text-green-600 mr-1"></i>Taille maximale: 2 Mo</p>
                        <p><i class="fas fa-check text-green-600 mr-1"></i>Recommandation: Signature sur fond blanc ou transparent</p>
                    </div>
                </div>

                <!-- Informations supplémentaires -->
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-lightbulb text-yellow-600 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-gray-800">Conseils pour votre signature</h3>
                            <ul class="mt-2 text-sm text-gray-700 space-y-1 list-disc list-inside">
                                <li>Utilisez une signature claire et lisible</li>
                                <li>Privilégiez un fond blanc ou transparent</li>
                                <li>Évitez les images trop grandes (dimensions recommandées: 200x80 pixels)</li>
                                <li>Votre signature apparaîtra automatiquement sur les PDF une fois validés</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Prévisualisation (optionnel avec JavaScript) -->
                <div id="preview-container" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-eye mr-2 text-gut-blue"></i>Prévisualisation
                    </label>
                    <div class="p-4 bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg text-center">
                        <img id="preview-image" src="" alt="Prévisualisation" class="max-h-32 mx-auto border border-gray-200 rounded">
                    </div>
                </div>
            </div>

            <!-- Boutons d'action -->
            <div class="mt-8 flex items-center justify-end space-x-4">
                <a href="{{ route('home') }}" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition font-medium">
                    <i class="fas fa-times mr-2"></i>Annuler
                </a>
                <button type="submit" class="bg-gut-blue hover:bg-gut-orange text-white px-8 py-3 rounded-lg font-medium transition shadow-lg">
                    <i class="fas fa-save mr-2"></i>{{ $user->signature ? 'Mettre à Jour' : 'Enregistrer' }} la Signature
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Prévisualisation de l'image avant upload
    document.getElementById('signature').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('preview-image').src = e.target.result;
                document.getElementById('preview-container').classList.remove('hidden');
            }
            reader.readAsDataURL(file);
        } else {
            document.getElementById('preview-container').classList.add('hidden');
        }
    });
</script>
@endpush
@endsection
