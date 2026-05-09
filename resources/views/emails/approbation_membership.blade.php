<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votre membership a été approuvé</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #374151;
        }
    </style>
</head>
<body>
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="text-align: center; padding: 20px 0; border-bottom: 2px solid #d9f20b;">
            <h1 style="margin: 0; font-size: 28px; color: #111827;">🎉 Membership Approuvé</h1>
        </div>

        <div style="padding: 30px 0;">
            <p>Bonjour <strong>{{ $demande->prenom }} {{ $demande->nom }}</strong>,</p>

            <p>Excellente nouvelle ! Votre demande de membership à Running Geneva Association a été <strong>approuvée</strong>.</p>

            <p>Votre compte existant est maintenant membre. Vous pouvez vous connecter avec vos identifiants habituels.</p>

            <div style="background-color: #f3f4f6; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #d9f20b;">
                <p style="margin: 5px 0;"><strong>Email :</strong> {{ $demande->email }}</p>
            </div>

            <div style="text-align: center; margin: 40px 0;">
                <a href="{{ config('app.frontend_url') }}/connexion"
                   style="background-color: #d9f20b; color: #111827; padding: 14px 30px; text-decoration: none; font-weight: 700; font-size: 16px; border-radius: 50px; display: inline-block;">
                    Se connecter
                </a>
            </div>

            <p>Bienvenue dans notre communauté ! 🏃‍♂️</p>

            <p>En cas de question, n'hésitez pas à nous contacter.</p>
        </div>

        <div style="background-color: #f9fafb; padding: 20px; text-align: center; border-top: 1px solid #e5e7eb;">
            <p style="margin: 0; font-size: 12px; color: #9ca3af;">
                © {{ date('Y') }} Running Geneva Association. Tous droits réservés.<br>
                <a href="https://runningeneva.ch" style="color: #6b7280; text-decoration: none;">runningeneva.ch</a>
            </p>
        </div>
    </div>
</body>
</html>
