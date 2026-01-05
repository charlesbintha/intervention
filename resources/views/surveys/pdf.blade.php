<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Survey - {{ $survey->opportunity_name }}</title>
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
            position: relative;
        }
        .page-wrapper {
            position: relative;
            min-height: 100vh;
            padding-right: 60px;
        }
        .vertical-sidebar {
            position: fixed;
            right: 0;
            top: 0;
            bottom: 0;
            width: 50px;
            background: linear-gradient(to bottom, #0066CC 0%, #0066CC 85%, #FF8C00 85%, #FF8C00 100%);
            z-index: 1000;
        }
        .sidebar-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-90deg);
            white-space: nowrap;
            color: white;
            font-size: 14pt;
            font-weight: bold;
            letter-spacing: 3px;
        }
        .page-header {
            margin-bottom: 20px;
            padding: 10px 0;
        }
        .logo {
            max-width: 250px;
            max-height: 60px;
            margin-bottom: 5px;
        }
        .header-tagline {
            color: #666;
            font-size: 12pt;
            font-style: italic;
            margin-bottom: 20px;
        }
        .fiche-title {
            border: 3px solid #000;
            padding: 12px;
            text-align: left;
            font-size: 18pt;
            font-weight: bold;
            margin: 20px 0;
        }
        .fiche-content {
            border: 2px solid #000;
            padding: 15px;
            margin-bottom: 20px;
        }
        .info-grid {
            display: table;
            width: 100%;
        }
        .info-row {
            display: table-row;
        }
        .info-cell {
            display: table-cell;
            width: 50%;
            padding: 5px 10px;
            vertical-align: top;
        }
        .info-label {
            font-weight: normal;
            text-decoration: underline;
            margin-bottom: 3px;
        }
        .info-value {
            color: #0066CC;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .section-box {
            border: 2px solid #000;
            padding: 15px;
            margin: 15px 0;
        }
        .section-header {
            color: #0066CC;
            font-weight: bold;
            font-style: italic;
            margin-bottom: 10px;
            text-decoration: underline;
        }
        .section-content {
            line-height: 1.6;
        }
        .table-container {
            margin: 15px 0;
        }
        .spec-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        .spec-table th {
            background-color: #0066CC;
            color: white;
            padding: 8px;
            text-align: center;
            border: 1px solid #000;
            font-weight: bold;
        }
        .spec-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
        }
        .table-title {
            color: #0066CC;
            font-weight: bold;
            margin: 15px 0 8px 0;
            text-decoration: underline;
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
            text-align: center;
        }
        .signature-table td {
            border: 2px solid #000;
            padding: 80px;
            height: 150px;
        }
        .footer {
            margin-top: 30px;
            padding: 15px 0;
            text-align: center;
        }
        .footer-branding {
            background-color: #f5f5f5;
            padding: 10px;
            margin: 20px 0;
            text-align: center;
            font-style: italic;
            color: #666;
        }
        .footer-info {
            font-size: 9pt;
            color: #666;
            line-height: 1.4;
        }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="vertical-sidebar">
        <div class="sidebar-text">CONNECTER LA VIE...</div>
    </div>

    <div class="page-wrapper">
        <!-- En-tête -->
        <div class="page-header">
            @php
                $logoPath = public_path('images/logo.png');
                $logoData = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : '';
            @endphp
            @if($logoData)
                <img src="data:image/png;base64,{{ $logoData }}" alt="GUT Logo" class="logo">
            @endif
            <div class="header-tagline">CONNECTER LA VIE…</div>
        </div>

        <!-- Fiche de Survey -->
        <div class="fiche-title">Fiche de Survey :</div>

        <div class="fiche-content">
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-cell">
                        <div class="info-label">Nom de l'entreprise :</div>
                        <div class="info-value">{{ strtoupper($survey->company_name) }}</div>
                    </div>
                    <div class="info-cell">
                        <div class="info-label">Localisation :</div>
                        <div class="info-value">{{ strtoupper($survey->location) }}</div>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-cell">
                        <div class="info-label">Nom du contact :</div>
                        <div class="info-value">{{ strtoupper($survey->contact_name) }}</div>
                    </div>
                    <div class="info-cell">
                        <div class="info-label">Fonction :</div>
                        <div class="info-value">{{ strtoupper($survey->contact_function) }}</div>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-cell">
                        <div class="info-label">Téléphone :</div>
                        <div class="info-value">{{ $survey->contact_phone }}</div>
                    </div>
                    <div class="info-cell">
                        <div class="info-label">Courriel :</div>
                        <div class="info-value">{{ $survey->contact_email }}</div>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-cell">
                        <div class="info-label">Date et heure de début du Survey :</div>
                        <div class="info-value">{{ $survey->start_datetime->format('d/m/Y à H\\h i\\m\\n') }}</div>
                    </div>
                    <div class="info-cell">
                        <div class="info-label">Date et heure de fin du Survey :</div>
                        <div class="info-value">{{ $survey->end_datetime->format('d/m/Y à H\\h i\\m\\n') }}</div>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-cell" colspan="2" style="display: table-cell; width: 100%;">
                        <div class="info-label">But du Survey :</div>
                        <div class="info-value">{{ strtoupper($survey->purpose) }}</div>
                    </div>
                </div>
            </div>

            <div style="margin-top: 20px;">
                <div class="info-grid">
                    <div class="info-row">
                        <div class="info-cell">
                            <div class="info-label">Intervenants Externes :</div>
                            @if($survey->intervenantsRencontres->count() > 0)
                                <div class="info-value">
                                    @foreach($survey->intervenantsRencontres as $intervenant)
                                        {{ $intervenant->prenom }} {{ $intervenant->nom }}<br>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="info-cell">
                            <div class="info-label">Intervenants GUT :</div>
                            @if($survey->intervenantsGut->count() > 0)
                                <div class="info-value">
                                    @foreach($survey->intervenantsGut as $intervenant)
                                        {{ $intervenant->prenom }} {{ $intervenant->nom }}<br>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-cell">
                            <div class="info-label">Mail support :</div>
                            <div class="info-value">support@universtelecom.net</div>
                        </div>
                        <div class="info-cell">
                            <div class="info-label">Téléphone UT :</div>
                            <div class="info-value">33-865-61-61</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenu de la mise en page -->
        @if($survey->layout_content)
            <div class="section-box">
                <div class="section-header">Contenu du plan :</div>
                <div class="section-content">
                    {!! quill_to_html($survey->layout_content) !!}
                </div>
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
                        @if($survey->status === 'validated' && $survey->user && $survey->user->signature)
                            @php
                                $signaturePath = public_path('storage/' . $survey->user->signature);
                                $signatureData = file_exists($signaturePath) ? base64_encode(file_get_contents($signaturePath)) : '';
                                $ext = pathinfo($survey->user->signature, PATHINFO_EXTENSION);
                            @endphp
                            @if($signatureData)
                                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%;">
                                    <img src="data:image/{{ $ext }};base64,{{ $signatureData }}" alt="Signature" style="max-height: 80px; max-width: 200px; margin-bottom: 8px;">
                                    <p style="margin: 0; font-size: 9pt;"><strong>{{ $survey->user->name }}</strong></p>
                                    <p style="margin: 0; font-size: 8pt; color: #666;">Validé le {{ $survey->updated_at->format('d/m/Y à H:i') }}</p>
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
            <div class="footer-info">
                <p>📍 26 Cité Comico VDN - Immeuble Farafan - 2ème étage ☎ +221 33 865 61 61 ✉ info@universtelecom.net 🌐 www.universtelecom.net</p>
                <p>R.C.: SN-DKR-2013 B 19 794 - NINEA : 0049455161 2R2</p>
                <p>Document généré le {{ now()->format('d/m/Y à H:i') }}</p>
            </div>
        </div>
    </div>

    <div class="no-print" style="margin-top: 30px; text-align: center;">
        <button onclick="window.print()" style="background-color: #0066CC; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">
            Imprimer / Télécharger PDF
        </button>
    </div>
</body>
</html>
