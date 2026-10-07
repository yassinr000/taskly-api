<?php

// Sanctum fusionne ce fichier avec sa configuration par défaut :
// seules les valeurs modifiées sont nécessaires.
return [
    // Durée de vie d'un token, en minutes (7 jours).
    'expiration' => (int) env('SANCTUM_TOKEN_EXPIRATION', 10080),
];
