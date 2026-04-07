<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation d'inscription - Running Geneva</title>
</head>
<body style="font-family: 'Helvetica Neue', Arial, sans-serif; background-color: #f3f4f6; margin: 0; padding: 40px 20px; color: #1f2937;">

    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">

        <!-- Header -->
        <div style="background-color: #4b5563; padding: 30px 20px; text-align: center;">
            <h1 style="color: #ffffff; margin: 0; font-size: 26px; font-weight: 700; letter-spacing: 1px;">
                RUNNING GENEVA
            </h1>
        </div>

        <!-- Corps -->
        <div style="padding: 40px 30px;">
            <h2 style="margin-top: 0; color: #111827; font-size: 20px;">
                Bonjour {{ $inscription->participant->prenom }} {{ $inscription->participant->nom }},
            </h2>

            <p style="line-height: 1.6; font-size: 16px; color: #4b5563;">
                Votre inscription a bien été enregistrée. Nous sommes ravis de vous compter parmi les participants ! 🎉
            </p>

            <!-- Récapitulatif inscription -->
            <div style="background-color: #f9fafb; border-left: 4px solid #c2ed3f; padding: 20px; margin: 30px 0; border-radius: 0 8px 8px 0;">
                <p style="margin: 0 0 15px 0; font-weight: 700; color: #111827; font-size: 16px;">
                    Récapitulatif de votre inscription
                </p>
                <table style="width: 100%; border-collapse: collapse; font-size: 15px; color: #374151;">
                    <tr>
                        <td style="padding: 6px 0; font-weight: 600; width: 45%;">Évènement :</td>
                        <td style="padding: 6px 0;">{{ $inscription->course->evenement->nom }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; font-weight: 600;">Course :</td>
                        <td style="padding: 6px 0;">{{ $inscription->course->nom }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; font-weight: 600;">Type :</td>
                        <td style="padding: 6px 0;">{{ $inscription->course->type }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; font-weight: 600;">Date de la course :</td>
                        <td style="padding: 6px 0;">{{ \Carbon\Carbon::parse($inscription->course->date_debut)->format('d.m.Y') }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; font-weight: 600;">Tarif payé :</td>
                        <td style="padding: 6px 0;">CHF {{ number_format($inscription->tarif, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; font-weight: 600;">Statut :</td>
                        <td style="padding: 6px 0;">
                            <span style="background-color: #d1fae5; color: #065f46; padding: 3px 10px; border-radius: 20px; font-size: 13px; font-weight: 600;">
                                {{ $inscription->status_paiement }}
                            </span>
                        </td>
                    </tr>
                    @if($inscription->dossard)
                    <tr>
                        <td style="padding: 6px 0; font-weight: 600;">N° dossard :</td>
                        <td style="padding: 6px 0; font-weight: 700; color: #111827; font-size: 18px;">
                            {{ $inscription->dossard->numero }}
                        </td>
                    </tr>
                    @endif
                    @if($inscription->groupe)
                    <tr>
                        <td style="padding: 6px 0; font-weight: 600;">Équipe :</td>
                        <td style="padding: 6px 0;">{{ $inscription->groupe->nom }}</td>
                    </tr>
                    @endif
                    @if($inscription->participe_challenge && $inscription->equipe_challenge)
                    <tr>
                        <td style="padding: 6px 0; font-weight: 600;">Challenge :</td>
                        <td style="padding: 6px 0;">{{ $inscription->equipe_challenge }}</td>
                    </tr>
                    @endif
                </table>
            </div>

            <!-- Bouton accès espace -->
            <div style="text-align: center; margin: 40px 0;">
                <a href="{{ env('APP_FRONTEND_URL', 'http://localhost:5173') }}/inscriptions"
                   style="background-color: #c2ed3f; color: #111827; padding: 14px 30px; text-decoration: none; font-weight: 700; font-size: 16px; border-radius: 50px; display: inline-block;">
                    Voir mes inscriptions
                </a>
            </div>

            <p style="font-size: 14px; color: #6b7280; line-height: 1.5;">
                Si vous avez des questions concernant votre inscription, n'hésitez pas à contacter l'organisateur de l'évènement.
            </p>
        </div>

        <!-- Footer -->
        <div style="background-color: #f9fafb; padding: 20px; text-align: center; border-top: 1px solid #e5e7eb;">
            <p style="margin: 0; font-size: 12px; color: #9ca3af;">
                © {{ date('Y') }} Running Geneva Association. Tous droits réservés.<br>
                Ceci est un email automatique, merci de ne pas y répondre.
            </p>
        </div>

    </div>
</body>
</html>
