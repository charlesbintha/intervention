<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #0099CC 0%, #FF6600 100%);
            padding: 30px 20px;
            text-align: center;
        }
        .logo {
            max-width: 200px;
            height: auto;
        }
        .content {
            padding: 30px 20px;
        }
        .greeting {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #0099CC;
        }
        .credentials-box {
            background-color: #f8f9fa;
            border-left: 4px solid #0099CC;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .credential-item {
            margin: 10px 0;
            font-size: 14px;
        }
        .credential-label {
            font-weight: bold;
            color: #555;
        }
        .credential-value {
            color: #0099CC;
            font-weight: 600;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #0099CC 0%, #FF6600 100%);
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: opacity 0.3s;
        }
        .button:hover {
            opacity: 0.9;
        }
        .warning {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            font-size: 14px;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #dee2e6;
        }
        .footer-links {
            margin-top: 10px;
        }
        .footer-links a {
            color: #0099CC;
            text-decoration: none;
            margin: 0 10px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header avec logo -->
        <div class="header">
            <img src="{{ asset('images/logo.png') }}" alt="Groupe Univers Telecom" class="logo">
        </div>

        <!-- Contenu -->
        <div class="content">
            <div class="greeting">{{ $greeting }}</div>

            <p>{{ $intro }}</p>

            <!-- Identifiants -->
            <div class="credentials-box">
                <div class="credential-item">
                    <span class="credential-label">Email :</span>
                    <span class="credential-value">{{ $user->email }}</span>
                </div>
                <div class="credential-item">
                    <span class="credential-label">Mot de passe :</span>
                    <span class="credential-value">{{ $password }}</span>
                </div>
                <div class="credential-item">
                    <span class="credential-label">Rôle :</span>
                    <span class="credential-value">{{ ucfirst($user->role) }}</span>
                </div>
            </div>

            <!-- Bouton de connexion -->
            <div class="button-container">
                <a href="{{ $loginUrl }}" class="button">Se connecter</a>
            </div>

            <!-- Avertissement de sécurité -->
            <div class="warning">
                <strong>⚠️ Important :</strong> Pour des raisons de sécurité, veuillez changer votre mot de passe après votre première connexion.
            </div>

            <p style="margin-top: 20px; font-size: 14px;">
                Si vous rencontrez des difficultés pour vous connecter, n'hésitez pas à contacter l'administrateur.
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>Groupe Univers Telecom</strong></p>
            <p>Portail d'Intervention</p>
            <div class="footer-links">
                <a href="https://groupe-universtelecom.com">Site Web</a>
                <a href="mailto:support@groupe-universtelecom.com">Support</a>
            </div>
            <p style="margin-top: 15px; color: #999;">
                Cet email a été envoyé automatiquement, merci de ne pas y répondre.
            </p>
        </div>
    </div>
</body>
</html>
