@extends('layouts.app')

@section('title', 'Mise en page Maintenance - Portail Intervention')

@push('styles')
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
@endpush

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-8">
        <a href="{{ route('maintenances.show', $maintenance) }}" class="text-gut-orange hover:text-opacity-75">
            <i class="fas fa-arrow-left mr-2"></i>Retour aux détails
        </a>
        <h1 class="text-3xl font-bold text-gray-800 mt-4">{{ $maintenance->layout_content ? 'Modifier' : 'Créer' }} la mise en page</h1>
        <p class="text-gray-600 mt-2">{{ $maintenance->layout_content ? 'Modifiez' : 'Complétez' }} la mise en page avec les détails de votre maintenance</p>
    </div>

    <div class="bg-white rounded-lg shadow-lg p-8">
        <div class="border-l-4 border-gut-orange pl-4 mb-6">
            <h2 class="text-xl font-semibold text-gray-800">Informations de la Maintenance</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8 bg-gray-50 p-6 rounded-lg">
            <div>
                <p class="text-sm text-gray-600">Projet</p>
                <p class="font-semibold text-gray-800">{{ $maintenance->project_name }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Entreprise</p>
                <p class="font-semibold text-gray-800">{{ $maintenance->company_name }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Lieu</p>
                <p class="font-semibold text-gray-800">{{ $maintenance->location }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Date</p>
                <p class="font-semibold text-gray-800">{{ $maintenance->start_datetime->format('d/m/Y H:i') }}</p>
            </div>
        </div>

        <form action="{{ route('maintenances.layout.store', $maintenance) }}" method="POST" id="layoutForm">
            @csrf

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Contenu de la mise en page *</label>
                <div id="editor" style="height: 400px;" class="bg-white"></div>
                <input type="hidden" name="layout_content" id="layout_content">
                @error('layout_content')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="bg-orange-50 border-l-4 border-gut-orange p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-gut-orange"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-gray-700">
                            Utilisez l'éditeur ci-dessus pour mettre en forme le contenu de votre maintenance.
                            Vous pouvez ajouter des titres, des listes, du texte en gras ou en italique, etc.
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-4 pt-6 border-t">
                <a href="{{ route('maintenances.show', $maintenance) }}" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                    Annuler
                </a>
                <button type="submit" class="px-6 py-3 bg-gut-orange hover:bg-opacity-90 text-white rounded-lg transition">
                    <i class="fas fa-save mr-2"></i>Enregistrer la mise en page
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script>
    var quill = new Quill('#editor', {
        theme: 'snow',
        placeholder: 'Écrivez le contenu de votre maintenance ici...',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'indent': '-1'}, { 'indent': '+1' }],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'align': [] }],
                ['link'],
                ['clean']
            ]
        }
    });

    @if($maintenance->layout_content)
        quill.root.innerHTML = {!! json_encode($maintenance->layout_content) !!};
    @endif

    document.getElementById('layoutForm').onsubmit = function() {
        document.getElementById('layout_content').value = quill.root.innerHTML;
    };
</script>
@endpush
@endsection
