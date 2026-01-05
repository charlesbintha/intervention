<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Intervention - {{ $interventionUte->company_name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #0099CC;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo {
            max-width: 200px;
            margin-bottom: 10px;
        }
        h1 {
            background: linear-gradient(135deg, #0099CC 0%, #FF8C00 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 10px 0;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            background: linear-gradient(135deg, #0099CC 0%, #FF8C00 100%);
            color: white;
            padding: 10px;
            margin-bottom: 15px;
            font-size: 18px;
            font-weight: bold;
        }
        .field-group {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        .field {
            display: table-row;
        }
        .field-label {
            display: table-cell;
            font-weight: bold;
            padding: 8px;
            background-color: #f5f5f5;
            width: 35%;
        }
        .field-value {
            display: table-cell;
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
        }
        .badge-blue {
            background-color: #DBEAFE;
            color: #1E40AF;
        }
        .badge-orange {
            background-color: #FFEDD5;
            color: #C2410C;
        }
        .intervenants-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        .intervenant-card {
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .intervenant-card h4 {
            margin: 0 0 8px 0;
            color: #0099CC;
        }
        .observations-box {
            background-color: #FEF3C7;
            border-left: 4px solid #F59E0B;
            padding: 15px;
            margin: 15px 0;
        }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 2px solid #0099CC;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
        @media print {
            body {
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        @php
            $logoPath = public_path('images/logo.png');
            $logoData = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : '';
        @endphp
        @if($logoData)
            <img src="data:image/png;base64,{{ $logoData }}" alt="GUT Logo" class="logo">
        @endif
        <h1>RAPPORT D'INTERVENTION UTE</h1>
        <p>{{ $interventionUte->company_name }}</p>
    </div>

    <div class="section">
        <div class="section-title">INFORMATIONS DE L'ENTREPRISE</div>
        <div class="field-group">
            <div class="field">
                <div class="field-label">Entreprise</div>
                <div class="field-value">{{ $interventionUte->company_name }}</div>
            </div>
            <div class="field">
                <div class="field-label">Lieu</div>
                <div class="field-value">{{ $interventionUte->location }}</div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">CONTACT</div>
        <div class="field-group">
            <div class="field">
                <div class="field-label">Nom</div>
                <div class="field-value">{{ $interventionUte->contact_name }}</div>
            </div>
            <div class="field">
                <div class="field-label">Fonction</div>
                <div class="field-value">{{ $interventionUte->contact_function }}</div>
            </div>
            <div class="field">
                <div class="field-label">Téléphone</div>
                <div class="field-value">{{ $interventionUte->contact_phone }}</div>
            </div>
            <div class="field">
                <div class="field-label">Courriel</div>
                <div class="field-value">{{ $interventionUte->contact_email }}</div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">DÉTAILS DE L'INTERVENTION</div>
        <div class="field-group">
            <div class="field">
                <div class="field-label">Date et heure de début</div>
                <div class="field-value">{{ $interventionUte->start_datetime->format('d/m/Y à H:i') }}</div>
            </div>
            <div class="field">
                <div class="field-label">Date et heure de fin</div>
                <div class="field-value">{{ $interventionUte->end_datetime->format('d/m/Y à H:i') }}</div>
            </div>
            <div class="field">
                <div class="field-label">Diagnostic</div>
                <div class="field-value">
                    <span class="badge badge-blue">{{ ucfirst($interventionUte->diagnostic) }}</span>
                </div>
            </div>
            <div class="field">
                <div class="field-label">Type d'intervention</div>
                <div class="field-value">
                    <span class="badge badge-orange">{{ ucfirst(str_replace('_', ' ', $interventionUte->type)) }}</span>
                </div>
            </div>
            <div class="field">
                <div class="field-label">But de l'intervention</div>
                <div class="field-value">{{ $interventionUte->purpose }}</div>
            </div>
        </div>
    </div>

    @if($interventionUte->observations)
    <div class="section">
        <div class="section-title">OBSERVATIONS / RECOMMANDATIONS</div>
        <div class="observations-box">
            {{ $interventionUte->observations }}
        </div>
    </div>
    @endif

    @if($interventionUte->intervenants->count() > 0)
    <div class="section">
        <div class="section-title">INTERVENANTS</div>

        @if($interventionUte->intervenantsGut->count() > 0)
            <h3 style="color: #0099CC; margin-bottom: 10px;">Intervenants GUT</h3>
            <div class="intervenants-grid">
                @foreach($interventionUte->intervenantsGut as $intervenant)
                    <div class="intervenant-card">
                        <h4>{{ $intervenant->prenom }} {{ $intervenant->nom }}</h4>
                        @if($intervenant->email)
                            <p style="margin: 4px 0;"><strong>Email:</strong> {{ $intervenant->email }}</p>
                        @endif
                        @if($intervenant->telephone)
                            <p style="margin: 4px 0;"><strong>Tél:</strong> {{ $intervenant->telephone }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        @if($interventionUte->intervenantsRencontres->count() > 0)
            <h3 style="color: #FF8C00; margin: 20px 0 10px 0;">Intervenants Rencontrés</h3>
            <div class="intervenants-grid">
                @foreach($interventionUte->intervenantsRencontres as $intervenant)
                    <div class="intervenant-card">
                        <h4>{{ $intervenant->prenom }} {{ $intervenant->nom }}</h4>
                        @if($intervenant->email)
                            <p style="margin: 4px 0;"><strong>Email:</strong> {{ $intervenant->email }}</p>
                        @endif
                        @if($intervenant->telephone)
                            <p style="margin: 4px 0;"><strong>Tél:</strong> {{ $intervenant->telephone }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
    @endif

    @if($interventionUte->status === 'validated' && $interventionUte->user && $interventionUte->user->signature)
    <div class="section">
        <div class="section-title">SIGNATURE GROUPE UNIVERS TELECOM</div>
        @php
            $signaturePath = public_path('storage/' . $interventionUte->user->signature);
            $signatureData = file_exists($signaturePath) ? base64_encode(file_get_contents($signaturePath)) : '';
            $ext = pathinfo($interventionUte->user->signature, PATHINFO_EXTENSION);
        @endphp
        @if($signatureData)
        <div style="margin-top: 20px; padding: 15px; background-color: #f9f9f9; border: 1px solid #ddd; border-radius: 5px; text-align: center;">
            <p style="margin-bottom: 10px; font-weight: bold;">Signé par: {{ $interventionUte->user->name }}</p>
            <img src="data:image/{{ $ext }};base64,{{ $signatureData }}" alt="Signature" style="max-width: 400px; max-height: 150px; border: 1px solid #ccc; background-color: white; padding: 10px;">
            <p style="margin-top: 10px; font-size: 10pt; color: #666;">Document validé le {{ $interventionUte->updated_at->format('d/m/Y à H:i') }}</p>
        </div>
        @endif
    </div>
    @endif

    <div class="footer">
        <p>&copy; {{ date('Y') }} Groupe Univers Telecom - Tous droits réservés</p>
        <p>Document généré le {{ now()->format('d/m/Y à H:i') }}</p>
    </div>

    <div class="no-print" style="margin-top: 30px; text-align: center;">
        <button onclick="window.print()" style="background: linear-gradient(135deg, #0099CC 0%, #FF8C00 100%); color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">
            Imprimer / Télécharger PDF
        </button>
    </div>
</body>
</html>
