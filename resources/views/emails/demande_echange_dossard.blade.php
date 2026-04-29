<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demande d'échange de dossard</title>
</head>
<body style="font-family: 'Helvetica Neue', Arial, sans-serif; background-color: #f3f4f6; margin: 0; padding: 40px 20px; color: #1f2937;">

    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">

        <div style="background-color: #0e0f54; padding: 30px 20px; text-align: center;">
            <h1 style="color: #ffffff; margin: 0; font-size: 26px; font-weight: 700; letter-spacing: 1px;">
                RUNNING GENEVA
            </h1>
        </div>

        <div style="padding: 40px 30px;">
            <h2 style="margin-top: 0; color: #111827; font-size: 20px;">Bonjour {{ $inscription->participant->prenom }},</h2>

            <p style="line-height: 1.6; font-size: 16px; color: #4b5563;">
                Une demande d'échange de dossard vient d'être déposée pour votre inscription.
            </p>

            <div style="background-color: #f9fafb; border-left: 4px solid #d9f20b; padding: 20px; margin: 30px 0; border-radius: 0 8px 8px 0;">
                <p style="margin: 0 0 12px 0; font-weight: 600; color: #111827;">Détails de la demande :</p>
                <ul style="margin: 0; padding-left: 20px; line-height: 1.8; color: #374151;">
                    <li><strong>Évènement :</strong> {{ $inscription->course->evenement->nom ?? '—' }}</li>
                    <li><strong>Course :</strong> {{ $inscription->course->nom ?? '—' }}</li>
                    <li><strong>Dossard :</strong> n°{{ $inscription->ancienneInscription->dossard->numero ?? '—' }}</li>
                    <li><strong>Demandé par :</strong> {{ $inscription->ancienneInscription->participant->prenom ?? '—' }} {{ $inscription->ancienneInscription->participant->nom ?? '' }}</li>
                </ul>
            </div>

            <div style="text-align: center; margin: 40px 0;">
                <a href="{{ config('app.frontend_url') }}/connexion"
                   style="background-color: #d9f20b; color: #111827; padding: 14px 30px; text-decoration: none; font-weight: 700; font-size: 16px; border-radius: 50px; display: inline-block;">
                    Se connecter et répondre
                </a>
            </div>

            <p style="font-size: 13px; color: #6b7280; line-height: 1.5; text-align: center; font-style: italic;">
                Une notification "Demande échange dossard" vous attend également dans votre espace.
            </p>
        </div>

        <div style="background-color: #f9fafb; padding: 20px; text-align: center; border-top: 1px solid #e5e7eb;">
            <p style="margin: 0; font-size: 12px; color: #9ca3af;">
                © {{ date('Y') }} Running Geneva Association. Tous droits réservés.<br>
                Ceci est un email automatique, merci de ne pas y répondre.
            </p>
        </div>

    </div>
</body>
</html>