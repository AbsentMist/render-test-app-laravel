<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">
    <h2>Bonjour {{ $fondateur->prenom }},</h2>
    
    <p>Nous vous informons que <strong>{{ $invite->prenom }} {{ $invite->nom }}</strong> a malheureusement refusé votre invitation à rejoindre l'équipe <strong>"{{ $groupe->nom }}"</strong>.</p>
    
    <p>Votre équipe est donc actuellement incomplète. Pas de panique ! Vous pouvez vous connecter à votre espace Running Geneva pour inviter un nouveau coéquipier.</p>
    
    <br>
    <a href="{{ config('app.frontend_url') }}/connexion" style="background-color: #d9f20b; color: #0e0f54; padding: 10px 20px; text-decoration: none; font-weight: bold; border-radius: 5px;">Accéder à mon compte</a>
    <br><br>
    
    <p>Sportivement,<br>L'équipe Running Geneva</p>
</body>
</html>