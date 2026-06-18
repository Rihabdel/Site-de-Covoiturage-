<?php

namespace App\Enum;

enum Statut: string
{
    case PENDING = 'pending';       // En attente
    case PLANNED = 'planned';       // Planifié
    case FULL = 'full';             // Complet
    case CONFIRMED = 'confirmed';   // Confirmé
    case IN_PROGRESS = 'in_progress'; // En cours
    case COMPLETED = 'completed';   // Terminé
    case CANCELLED = 'cancelled';   // Annulé
    case POSTPONED = 'postponed';   // Reporté
}
