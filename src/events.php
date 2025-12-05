<?php
session_start();
header('Content-Type: application/json');

// --- Base de données du jeu (Événements, PNJ, Choix) ---
$gameData = [
    'npcs' => [
        'tech_guy_1' => ['event_id' => 'event_office_migration'],
        'teacher_1' => ['event_id' => 'event_teacher_complaint'],
        'student_1' => ['event_id' => 'event_student_initiative']
    ],
    'events' => [
        // --- Événement 1: Technicien et Suite Bureautique ---
        'event_office_migration' => [
            'message' => "Bonjour. La licence de notre suite bureautique propriétaire arrive à expiration. Le renouvellement coûte 10 000€. Nous pourrions aussi migrer vers une solution libre comme LibreOffice.",
            'choices' => [
                'choice_renew' => [
                    'text' => "Renouveler la licence propriétaire (10 000€).",
                    'consequences' => ['budget' => -10000, 'satisfaction' => 0, 'autonomie' => -5],
                    'outcome_message' => "Vous avez renouvelé la licence. Le budget prend un coup et notre dépendance augmente."
                ],
                'choice_migrate' => [
                    'text' => "Migrer et former le personnel (3 000€).",
                    'consequences' => ['budget' => -3000, 'satisfaction' => -15, 'autonomie' => 20],
                    'outcome_message' => "La migration est lancée ! L'autonomie fait un bond, mais il faut maintenant gérer le mécontentement des utilisateurs."
                ],
                 'choice_delay_office' => [
                    'text' => "Ne rien décider pour l'instant.",
                    'consequences' => [],
                    'outcome_message' => "Vous reportez la décision. Le problème reste entier."
                ]
            ]
        ],
        // --- Événement 2: Enseignant Réfractaire ---
        'event_teacher_complaint' => [
            'condition' => 'event_office_migration', // Se déclenche après la migration
            'message' => "Ce nouveau logiciel libre est une catastrophe ! Mes anciens documents ne s'affichent pas correctement et je perds un temps fou. Il faut faire quelque chose !",
            'choices' => [
                'choice_train_more' => [
                    'text' => "Organiser une formation avancée (1 500€).",
                    'consequences' => ['budget' => -1500, 'satisfaction' => 10, 'autonomie' => 5],
                    'outcome_message' => "La formation a apaisé les tensions et amélioré les compétences de chacun."
                ],
                'choice_ignore_teacher' => [
                    'text' => "Leur dire de s'adapter et lire la documentation.",
                    'consequences' => ['budget' => 0, 'satisfaction' => -10, 'autonomie' => 0],
                    'outcome_message' => "Votre manque de soutien a fortement déplu aux enseignants, qui se sentent abandonnés."
                ],
                'choice_partial_rollback' => [
                    'text' => "Acheter des licences propriétaires 'de secours' (5 000€).",
                    'consequences' => ['budget' => -5000, 'satisfaction' => 5, 'autonomie' => -10],
                    'outcome_message' => "Le retour en arrière calme les plaintes, mais il sape votre stratégie d'autonomie et coûte cher."
                ]
            ]
        ],
        // --- Événement 3: Initiative du Club Informatique ---
        'event_student_initiative' => [
            'message' => "Bonjour ! Avec le club informatique, on a monté un petit serveur 'cloud' avec Nextcloud. On pourrait l'étendre à toute l'école pour nos fichiers. On aurait juste besoin d'un vrai serveur.",
            'choices' => [
                'choice_fund_server' => [
                    'text' => "Investir dans un vrai serveur dédié (5 000€).",
                    'consequences' => ['budget' => -5000, 'satisfaction' => 10, 'autonomie' => 30],
                    'outcome_message' => "L'école dispose maintenant de son propre cloud ! L'autonomie grimpe en flèche."
                ],
                'choice_recycle_server' => [
                    'text' => "Fournir du matériel de récupération et un petit budget (500€).",
                    'consequences' => ['budget' => -500, 'satisfaction' => 5, 'autonomie' => 15],
                    'outcome_message' => "Le système D fonctionne ! C'est un premier pas important vers un cloud autonome, même s'il est modeste."
                ],
                'choice_refuse_student' => [
                    'text' => "Refuser, trop risqué de confier ça à des élèves.",
                    'consequences' => ['budget' => 0, 'satisfaction' => -5, 'autonomie' => 0],
                    'outcome_message' => "Les élèves du club sont déçus. Une belle opportunité d'innover a été manquée."
                ]
            ]
        ]
    ]
];

// --- Fonctions de l'API ---

function getEventForNpc(string $npcId, array $gameData) {
    if (isset($gameData['npcs'][$npcId])) {
        $eventId = $gameData['npcs'][$npcId]['event_id'];
        $event = $gameData['events'][$eventId];
        $completed_events = $_SESSION['completed_events'] ?? [];

        // Gérer les événements déjà terminés
        if (in_array($eventId, $completed_events)) {
            return ['message' => "Nous avons déjà traité ce sujet."];
        }

        // Gérer les dépendances d'événements
        if (isset($event['condition']) && !in_array($event['condition'], $completed_events)) {
             if ($npcId === 'teacher_1') return ['message' => "L'enseignant est en train de préparer ses cours."];
             return ['message' => "Ce personnage n'a rien de spécial à vous dire pour le moment."];
        }

        $choices_for_api = [];
        foreach ($event['choices'] as $id => $choice) {
            $choices_for_api[] = ['id' => $id, 'text' => $choice['text']];
        }
        return ['message' => $event['message'], 'choices' => $choices_for_api];
    }
    return ['error' => 'PNJ non trouvé'];
}

function applyChoice(string $choiceId, array $gameData) {
    foreach ($gameData['events'] as $eventId => $event) {
        if (isset($event['choices'][$choiceId])) {
            $choice = $event['choices'][$choiceId];
            
            foreach ($choice['consequences'] as $resource => $value) {
                if (isset($_SESSION['resources'][$resource])) {
                    $_SESSION['resources'][$resource] += $value;
                }
            }
            
            if (!isset($_SESSION['completed_events'])) $_SESSION['completed_events'] = [];
            $_SESSION['completed_events'][] = $eventId;
            $_SESSION['resources']['week']++;

            return [
                'outcome_message' => $choice['outcome_message'],
                'updated_resources' => $_SESSION['resources']
            ];
        }
    }
    return ['error' => 'Choix non valide'];
}

// --- Point d'entrée de l'API ---
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? null;
$response = [];

switch ($action) {
    case 'interact_npc':
        $npcId = $input['npcId'] ?? null;
        if ($npcId) $response = getEventForNpc($npcId, $gameData);
        else { http_response_code(400); $response = ['error' => 'ID de PNJ manquant.']; }
        break;
    case 'make_choice':
        $choiceId = $input['choiceId'] ?? null;
        if ($choiceId) $response = applyChoice($choiceId, $gameData);
        else { http_response_code(400); $response = ['error' => 'ID de choix manquant.']; }
        break;
    default:
        http_response_code(400);
        $response = ['error' => 'Action non reconnue.'];
        break;
}

echo json_encode($response);