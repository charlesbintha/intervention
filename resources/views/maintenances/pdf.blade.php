<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance - {{ $maintenance->project_name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            line-height: 1.4;
            color: #333;
            font-size: 11pt;
        }
        .page-header {
            display: table;
            width: 100%;
            border: 2px solid #000;
            margin-bottom: 20px;
        }
        .page-header-row {
            display: table-row;
        }
        .page-header-cell {
            display: table-cell;
            padding: 15px;
            vertical-align: middle;
            border-right: 2px solid #000;
        }
        .page-header-cell:last-child {
            border-right: none;
        }
        .page-header-cell.logo {
            width: 25%;
            text-align: center;
        }
        .page-header-cell.title {
            width: 50%;
            text-align: center;
        }
        .page-header-cell.client-logo {
            width: 25%;
            text-align: center;
        }
        .logo img {
            max-width: 150px;
            max-height: 80px;
        }
        .title h1 {
            color: #0066CC;
            font-size: 14pt;
            font-weight: bold;
            line-height: 1.3;
        }
        .fiche-title {
            background-color: #fff;
            border: 3px solid #0066CC;
            padding: 10px;
            text-align: center;
            font-size: 16pt;
            font-weight: bold;
            color: #0066CC;
            margin: 20px 0;
        }
        .fiche-content {
            border: 2px solid #000;
            padding: 15px;
            margin-bottom: 20px;
        }
        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }
        .info-cell {
            display: table-cell;
            width: 50%;
            padding: 3px;
        }
        .info-label {
            font-weight: bold;
            text-decoration: underline;
        }
        .info-value {
            color: #0066CC;
            font-weight: bold;
        }
        .intervenants-section {
            margin: 10px 0;
        }
        .intervenants-list {
            color: #0066CC;
            font-weight: bold;
            margin-left: 15px;
        }
        .contact-info {
            text-align: right;
            margin-top: 15px;
        }
        .contact-info p {
            margin: 5px 0;
        }
        .contact-label {
            font-weight: bold;
        }
        .contact-value {
            color: #0066CC;
        }
        .pieces-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        .pieces-table th {
            background-color: #333;
            color: white;
            padding: 10px;
            text-align: center;
            border: 1px solid #000;
        }
        .pieces-table td {
            border: 1px solid #000;
            padding: 20px;
            min-height: 60px;
        }
        .nature-section {
            border: 2px solid #000;
            padding: 10px;
            margin: 15px 0;
        }
        .nature-title {
            font-weight: bold;
            margin-bottom: 8px;
        }
        .checkbox-line {
            margin: 5px 0;
        }
        .checkbox {
            display: inline-block;
            width: 15px;
            height: 15px;
            border: 2px solid #000;
            margin: 0 5px;
            vertical-align: middle;
            position: relative;
        }
        .checkbox.checked::after {
            content: '✓';
            position: absolute;
            top: -3px;
            left: 2px;
            font-size: 14px;
            font-weight: bold;
            color: #000;
        }
        .section-title {
            font-weight: bold;
            font-size: 13pt;
            margin: 20px 0 10px 0;
            text-decoration: underline;
        }
        .section-content {
            text-align: justify;
            margin: 10px 0;
            line-height: 1.6;
        }
        .work-list {
            margin: 10px 0;
        }
        .work-list h4 {
            font-weight: bold;
            margin: 15px 0 8px 0;
        }
        .work-list ul {
            margin-left: 20px;
        }
        .work-list li {
            margin: 5px 0;
        }
        .recommendations {
            margin: 20px 0;
        }
        .recommendations ul {
            margin-left: 20px;
        }
        .recommendations li {
            margin: 8px 0;
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }
        .signature-table th {
            background-color: #fff;
            color: #0066CC;
            border: 2px solid #000;
            padding: 10px;
            font-weight: bold;
        }
        .signature-table td {
            border: 2px solid #000;
            padding: 60px;
            height: 120px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ccc;
            text-align: center;
            font-size: 9pt;
            color: #666;
        }
        .footer-branding {
            background-color: #f5f5f5;
            padding: 10px;
            margin: 20px 0;
            text-align: center;
            font-style: italic;
            color: #666;
        }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <!-- En-tête avec 3 colonnes -->
    @php
        $logoPath = public_path('images/logo.png');
        $logoData = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : '';
    @endphp
    <div class="page-header">
        <div class="page-header-row">
            <div class="page-header-cell logo">
                @if($logoData)
                    <img src="data:image/png;base64,{{ $logoData }}" alt="GUT Logo">
                @endif
            </div>
            <div class="page-header-cell title">
                <h1>{{ strtoupper($maintenance->project_name) }}</h1>
            </div>
            <div class="page-header-cell client-logo">
                <!-- Espace pour logo client si nécessaire -->
            </div>
        </div>
    </div>

    <!-- Fiche de Maintenance -->
    <div class="fiche-title">Fiche de Maintenance :</div>

    <div class="fiche-content">
        <div class="info-row">
            <div class="info-cell">
                <span class="info-label">Nom de l'entreprise :</span>
                <span class="info-value">{{ $maintenance->company_name }}</span>
            </div>
            <div class="info-cell">
                <span class="info-label">Lieu :</span>
                <span class="info-value">{{ $maintenance->location }}</span>
            </div>
        </div>

        <div class="info-row">
            <div class="info-cell">
                <span class="info-label">Nom du contact :</span>
                <span class="info-value">{{ $maintenance->contact_name }}</span>
            </div>
            <div class="info-cell">
                <span class="info-label">Fonction :</span>
                <span class="info-value">{{ $maintenance->contact_function }}</span>
            </div>
        </div>

        <div class="info-row">
            <div class="info-cell">
                <span class="info-label">Date et heure de début de la maintenance :</span><br>
                <span class="info-value">{{ $maintenance->start_datetime->format('d F Y à H\h i\m\n') }}</span>
            </div>
            <div class="info-cell">
                <span class="info-label">Email :</span>
                <span class="info-value">{{ $maintenance->contact_email }}</span>
            </div>
        </div>

        <div class="info-row">
            <div class="info-cell">
                <span class="info-label">Date et heure de fin de la maintenance :</span><br>
                <span class="info-value">{{ $maintenance->end_datetime->format('d F Y à H\h i\m\n') }}</span>
            </div>
            <div class="info-cell"></div>
        </div>

        <div class="info-row">
            <div class="info-cell">
                <span class="info-label">But de l'intervention</span><br>
                <span class="info-value">{{ $maintenance->purpose }}</span>
            </div>
            <div class="info-cell"></div>
        </div>

        @if($maintenance->nature_intervention)
            <div class="info-row">
                <div class="info-cell">
                    <span class="info-label">Nature de l'intervention</span><br>
                    <span class="info-value">{{ $maintenance->nature_intervention }}</span>
                </div>
                <div class="info-cell"></div>
            </div>
        @endif

        @if($maintenance->type_intervention)
            <div class="info-row">
                <div class="info-cell">
                    <span class="info-label">Type d'intervention</span><br>
                    <span class="info-value">{{ $maintenance->type_intervention }}</span>
                </div>
                <div class="info-cell"></div>
            </div>
        @endif

        <div class="intervenants-section">
            <span class="info-label">Intervenants GUT :</span>
            @if($maintenance->intervenantsGut->count() > 0)
                <div class="intervenants-list">
                    @foreach($maintenance->intervenantsGut as $intervenant)
                        {{ $intervenant->prenom }} {{ $intervenant->nom }}<br>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="info-row" style="margin-top: 20px;">
            <div class="info-cell">
                <span class="info-label">Intervenants Externes :</span>
                @if($maintenance->intervenantsRencontres->count() > 0)
                    <div class="intervenants-list">
                        @foreach($maintenance->intervenantsRencontres as $intervenant)
                            {{ $intervenant->prenom }} {{ $intervenant->nom }}<br>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="info-cell"></div>
        </div>

        <div class="contact-info">
            <p><span class="contact-label">Mail support :</span> <span class="contact-value">support@universtelecom.net</span></p>
            <p><span class="contact-label">Téléphone GUT :</span> <span class="contact-value">33-865-61-61</span></p>
        </div>
    </div>

    <!-- Nature et Type d'intervention -->
    @php
        $natureValues = $maintenance->nature_intervention ? explode(', ', $maintenance->nature_intervention) : [];
        $typeValues = $maintenance->type_intervention ? explode(', ', $maintenance->type_intervention) : [];
    @endphp
    <div class="nature-section">
        <div class="nature-title">Nature de l'intervention :</div>
        <div class="checkbox-line">
            <span class="checkbox {{ in_array('Mécanique', $natureValues) ? 'checked' : '' }}"></span> Mécanique
            <span class="checkbox {{ in_array('Electrique', $natureValues) ? 'checked' : '' }}"></span> Electrique
            <span class="checkbox {{ in_array('Soudure', $natureValues) ? 'checked' : '' }}"></span> Soudure
            <span class="checkbox {{ in_array('Informatique', $natureValues) ? 'checked' : '' }}"></span> Informatique
            <span class="checkbox {{ in_array('Autre', $natureValues) ? 'checked' : '' }}"></span> Autre
        </div>

        <div class="nature-title" style="margin-top: 15px;">Type d'intervention :</div>
        <div class="checkbox-line">
            <span class="checkbox {{ in_array('Echange de composant', $typeValues) ? 'checked' : '' }}"></span> Echange de composant
            <span class="checkbox {{ in_array('Réglage nettoyage graissage', $typeValues) ? 'checked' : '' }}"></span> Réglage nettoyage graissage
            <span class="checkbox {{ in_array('Réparation', $typeValues) ? 'checked' : '' }}"></span> Réparation
            <span class="checkbox {{ in_array('Dépannage', $typeValues) ? 'checked' : '' }}"></span> Dépannage
        </div>
        <div class="checkbox-line">
            <span class="checkbox {{ in_array('Équipements', $typeValues) ? 'checked' : '' }}"></span> Équipements
            <span class="checkbox {{ in_array('Nettoyage et dépoussiérage préventif', $typeValues) ? 'checked' : '' }}"></span> Nettoyage et dépoussiérage préventif
        </div>
        <div class="checkbox-line">
            <span class="checkbox {{ in_array('Reconfiguration, Modification, Amélioration', $typeValues) ? 'checked' : '' }}"></span> Reconfiguration, Modification, Amélioration
            <span class="checkbox {{ in_array('Autre', $typeValues) ? 'checked' : '' }}"></span> Autre
        </div>
    </div>

    <!-- Saut de page -->
    <div style="page-break-after: always;"></div>

    <!-- Page 2 - En-tête répété -->
    <div class="page-header">
        <div class="page-header-row">
            <div class="page-header-cell logo">
                @if($logoData)
                    <img src="data:image/png;base64,{{ $logoData }}" alt="GUT Logo">
                @endif
            </div>
            <div class="page-header-cell title">
                <h1>{{ strtoupper($maintenance->project_name) }}</h1>
            </div>
            <div class="page-header-cell client-logo">
                <!-- Espace pour logo client -->
            </div>
        </div>
    </div>

    <!-- Travaux effectués -->
    @if($maintenance->layout_content)
    <div class="section-title">Travaux ou tâches effectués :</div>
    <div class="section-content">
        {!! quill_to_html($maintenance->layout_content) !!}
    </div>
    @endif

    <!-- Signatures -->
    <table class="signature-table">
        <thead>
            <tr>
                <th>Signature du client</th>
                <th>Signature Groupe Univers Telecom</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td></td>
                <td style="text-align: center; vertical-align: middle;">
                    @if($maintenance->status === 'validated' && $maintenance->user && $maintenance->user->signature)
                        @php
                            $signaturePath = public_path('storage/' . $maintenance->user->signature);
                            $signatureData = file_exists($signaturePath) ? base64_encode(file_get_contents($signaturePath)) : '';
                            $ext = pathinfo($maintenance->user->signature, PATHINFO_EXTENSION);
                        @endphp
                        @if($signatureData)
                            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%;">
                                <img src="data:image/{{ $ext }};base64,{{ $signatureData }}" alt="Signature" style="max-height: 80px; max-width: 200px; margin-bottom: 8px;">
                                <p style="margin: 0; font-size: 9pt;"><strong>{{ $maintenance->user->name }}</strong></p>
                                <p style="margin: 0; font-size: 8pt; color: #666;">Validé le {{ $maintenance->updated_at->format('d/m/Y à H:i') }}</p>
                            </div>
                        @endif
                    @endif
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Footer branding -->
    <div class="footer-branding">
        <p><strong>Le Développement</strong></p>
        <p><em>numérique en Afrique,</em></p>
        <p><em>pour l'Afrique</em></p>
    </div>

    <div class="footer">
        <p>26 Cité Comico VDN - Immeuble Farafan - 2ème étage ☎ +221 33 865 61 61 ✉ info@universtelecom.net 🌐 www.universtelecom.net</p>
        <p>R.C.: SN-DKR-2013 B 19 794 - NINEA : 0049455161 2R2</p>
        <p>Document généré le {{ now()->format('d/m/Y à H:i') }}</p>
    </div>

    <div class="no-print" style="margin-top: 30px; text-align: center;">
        <button onclick="window.print()" style="background-color: #FF8C00; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">
            Imprimer / Télécharger PDF
        </button>
    </div>
</body>
</html>
