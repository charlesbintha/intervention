@props(['content' => '', 'label' => 'Contenu'])

@if($content)
<div class="bg-white rounded-lg shadow-lg overflow-hidden mb-6">
    <div class="bg-gradient-to-r from-gut-blue to-gut-orange text-white px-6 py-4">
        <h2 class="text-xl font-semibold">{{ $label }}</h2>
    </div>
    <div class="p-6">
        <div id="quill-viewer-{{ md5($content) }}" class="prose max-w-none bg-gray-50 p-4 rounded-lg min-h-[100px]">
            <p class="text-gray-500">Chargement du contenu...</p>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const viewerId = 'quill-viewer-{{ md5($content) }}';
        const viewer = document.getElementById(viewerId);

        if (!viewer) return;

        try {
            // Parse le contenu JSON
            let deltaContent = @json($content);

            // Si c'est une chaîne, essayer de la parser
            if (typeof deltaContent === 'string') {
                try {
                    deltaContent = JSON.parse(deltaContent);
                } catch (e) {
                    // Si ce n'est pas du JSON, afficher comme texte brut
                    viewer.innerHTML = '<div class="text-gray-700 whitespace-pre-wrap">' + deltaContent + '</div>';
                    return;
                }
            }

            // Créer un éditeur Quill temporaire en lecture seule
            const tempQuill = new Quill(viewer, {
                theme: 'snow',
                readOnly: true,
                modules: {
                    toolbar: false
                }
            });

            // Charger le contenu Delta
            tempQuill.setContents(deltaContent);

            // Masquer la barre d'outils (qui n'est pas utilisée)
            const toolbar = viewer.previousElementSibling;
            if (toolbar && toolbar.classList.contains('ql-toolbar')) {
                toolbar.style.display = 'none';
            }
        } catch (error) {
            console.error('Erreur lors du chargement du contenu Quill:', error);
            viewer.innerHTML = '<div class="text-red-500">Erreur lors du chargement du contenu formaté</div>';
        }
    });
</script>
@endpush
@endif
