<?php
session_start();

// --- Configuration Initiale ---
define('INITIAL_BUDGET', 50000); // en €
define('INITIAL_SATISFACTION', 70); // en %
define('INITIAL_AUTONOMY', 5); // en points
define('MAX_WEEKS', 36);
define('RUNNING_COSTS_PER_WEEK', 500); // Coûts de fonctionnement hebdomadaires
define('EVENT_CHANCE_PERCENT', 75); // Pourcentage de chance qu'un événement se produise

// --- Chargement des données ---
require_once '../src/events.php';
$all_events = get_all_events();

// --- Actions du joueur ---
if (isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'start_game') {
        $_SESSION['game_state'] = [
            'week' => 1,
            'budget' => INITIAL_BUDGET,
            'satisfaction' => INITIAL_SATISFACTION,
            'autonomy' => INITIAL_AUTONOMY,
            'message' => 'L\'année scolaire commence. Votre mission : renforcer l\'autonomie numérique de l\'établissement !',
            'current_event_id' => null,
        ];
    }
    elseif ($action === 'next_week' && isset($_SESSION['game_state'])) {
        // Avancer la semaine et appliquer les coûts fixes
        $_SESSION['game_state']['week']++;
        $_SESSION['game_state']['budget'] -= RUNNING_COSTS_PER_WEEK;
        $_SESSION['game_state']['message'] = 'Une nouvelle semaine commence.';
        $_SESSION['game_state']['current_event_id'] = null;

        // Logique de déclenchement d'un événement
        if (rand(1, 100) <= EVENT_CHANCE_PERCENT) {
            $event_keys = array_keys($all_events);
            $random_event_key = $event_keys[array_rand($event_keys)];
            $_SESSION['game_state']['current_event_id'] = $random_event_key;
        }
    }
    elseif ($action === 'resolve_event' && isset($_SESSION['game_state'])) {
        $event_id = $_POST['event_id'];
        $choice_index = $_POST['choice_index'];
        
        if (isset($all_events[$event_id]) && isset($all_events[$event_id]['choices'][$choice_index])) {
            $choice = $all_events[$event_id]['choices'][$choice_index];

            // Appliquer les effets
            foreach ($choice['effects'] as $resource => $value) {
                $_SESSION['game_state'][$resource] += $value;
            }

            // Limiter la satisfaction à 100
            if ($_SESSION['game_state']['satisfaction'] > 100) {
                $_SESSION['game_state']['satisfaction'] = 100;
            }
             if ($_SESSION['game_state']['satisfaction'] < 0) {
                $_SESSION['game_state']['satisfaction'] = 0;
            }

            $_SESSION['game_state']['message'] = $choice['result_message'];
        }
        $_SESSION['game_state']['current_event_id'] = null; // L'événement est résolu
    }
}

// Récupérer l'état du jeu pour l'affichage
$gameState = $_SESSION['game_state'] ?? null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZEDE - Simulateur de Résistance Numérique</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="container">
        <header>
            <h1>ZEDE - Le Simulateur</h1>
            <p>Votre mission : Guider votre établissement vers l'autonomie numérique.</p>
        </header>

        <?php if ($gameState && $gameState['week'] <= MAX_WEEKS): ?>
            <div id="dashboard">
                <div class="resource">
                    <span class="label">Semaine</span>
                    <span class="value"><?= $gameState['week'] ?> / <?= MAX_WEEKS ?></span>
                </div>
                <div class="resource">
                    <span class="label">Budget</span>
                    <span class="value"><?= number_format($gameState['budget'], 0, ',', ' ') ?> €</span>
                </div>
                <div class="resource">
                    <span class="label">Satisfaction</span>
                    <span class="value"><?= $gameState['satisfaction'] ?> %</span>
                </div>
                <div class="resource">
                    <span class="label">Autonomie</span>
                    <span class="value"><?= $gameState['autonomy'] ?> pts</span>
                </div>
            </div>

            <main id="game-content">
                <?php 
                $current_event_id = $gameState['current_event_id'] ?? null;
                if ($current_event_id && isset($all_events[$current_event_id])):
                    $event = $all_events[$current_event_id];
                ?>
                    <div class="event-container">
                        <h3>Événement : <?= htmlspecialchars($event['title']) ?></h3>
                        <p class="event-description"><?= htmlspecialchars($event['description']) ?></p>
                        <form method="POST" action="index.php" class="event-choices">
                            <input type="hidden" name="action" value="resolve_event">
                            <input type="hidden" name="event_id" value="<?= $current_event_id ?>">
                            <?php foreach ($event['choices'] as $index => $choice): ?>
                                <button type="submit" name="choice_index" value="<?= $index ?>" class="choice-button">
                                    <?= htmlspecialchars($choice['text']) ?>
                                </button>
                            <?php endforeach; ?>
                        </form>
                    </div>

                <?php else: ?>
                    <div class="event-message">
                        <p><?= htmlspecialchars($gameState['message']) ?></p>
                    </div>

                    <div class="actions">
                        <form method="POST" action="index.php">
                            <input type="hidden" name="action" value="next_week">
                            <button type="submit">Passer à la semaine suivante</button>
                        </form>
                    </div>
                <?php endif; ?>
            </main>

        <?php elseif ($gameState && $gameState['week'] > MAX_WEEKS): ?>
            <div class="game-over">
                <h2>Fin de l'année scolaire !</h2>
                <p>Le temps est écoulé. Voyons votre bilan.</p>
                <p><strong>Score d'Autonomie final :</strong> <?= $gameState['autonomy'] ?> points</p>
                <!-- Autres statistiques finales ici -->
                <form method="POST" action="index.php">
                    <input type="hidden" name="action" value="start_game">
                    <button type="submit">Recommencer une partie</button>
                </form>
            </div>
        <?php else: ?>
            <div class="start-screen">
                <h2>Bienvenue</h2>
                <p>Vous êtes à la tête d'un établissement scolaire dépendant des GAFAM. Votre objectif est de regagner votre souveraineté numérique en 36 semaines, sans sacrifier votre budget ni la satisfaction de vos équipes.</p>
                <form method="POST" action="index.php">
                    <input type="hidden" name="action" value="start_game">
                    <button type="submit">Commencer la simulation</button>
                </form>
            </div>
        <?php endif; ?>

        <footer>
            <p>Un projet pour la Nuit de l'Info 2025.</p>
        </footer>
    </div>

</body>
</html>
