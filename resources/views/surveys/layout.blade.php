@extends('layouts.app')

@section('title', 'Mise en page Survey - Portail Intervention')

@push('styles')
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
@endpush

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Mise en page du Survey</h1>
        <p class="text-gray-600 mt-2">Complétez la mise en page avec les détails de votre survey</p>
    </div>

    <div class="bg-white rounded-lg shadow-lg p-8">
        <div class="border-l-4 border-gut-blue pl-4 mb-6">
            <h2 class="text-xl font-semibold text-gray-800">Informations du Survey</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8 bg-gray-50 p-6 rounded-lg">
            <div>
                <p class="text-sm text-gray-600">Opportunité</p>
                <p class="font-semibold text-gray-800">{{ $survey->opportunity_name }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Entreprise</p>
                <p class="font-semibold text-gray-800">{{ $survey->company_name }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Lieu</p>
                <p class="font-semibold text-gray-800">{{ $survey->location }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Date</p>
                <p class="font-semibold text-gray-800">{{ $survey->start_datetime->format('d/m/Y H:i') }}</p>
            </div>
        </div>

        <form action="{{ route('surveys.layout.store', $survey) }}" method="POST" id="layoutForm">
            @csrf

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Contenu de la mise en page *</label>
                <div id="editor" style="height: 400px;" class="bg-white"></div>
                <input type="hidden" name="layout_content" id="layout_content">
                @error('layout_content')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="bg-blue-50 border-l-4 border-gut-blue p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-gut-blue"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-gray-700">
                            Utilisez l'éditeur ci-dessus pour mettre en forme le contenu de votre survey.
                            Vous pouvez ajouter des titres, des listes, du texte en gras ou en italique, etc.
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-4 pt-6 border-t">
                <a href="{{ route('surveys.index') }}" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                    Annuler
                </a>
                <button type="submit" class="px-6 py-3 bg-gut-blue hover:bg-opacity-90 text-white rounded-lg transition">
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
        placeholder: 'Écrivez le contenu de votre survey ici...',
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

    @if($survey->layout_content)
        quill.root.innerHTML = {!! json_encode($survey->layout_content) !!};
    @endif

    document.getElementById('layoutForm').onsubmit = function() {
        document.getElementById('layout_content').value = quill.root.innerHTML;
    };
</script>
@endpush
@endsection
