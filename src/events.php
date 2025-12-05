<?php

// Fichier centralisant les événements narratifs du jeu.
// Chaque événement contient :
// - un titre
// - une description
// - des choix, chacun avec :
//   - un texte descriptif
//   - un message de résultat
//   - des effets sur les ressources du joueur

function get_all_events() {
    return [
        // --- Événement 1 : Panne Matérielle ---
        'EVT001' => [
            'title' => 'Panne du Serveur de Fichiers',
            'description' => 'Le vieux serveur de fichiers, qui héberge les documents administratifs et les travaux d\'élèves, vient de tomber en panne. C\'est la panique au secrétariat.',
            'choices' => [
                [
                    'text' => 'Contacter le support propriétaire (Intervention rapide et garantie)',
                    'result_message' => 'Un technicien est intervenu en 24h. Le service est rétabli, mais la facture est salée.',
                    'effects' => [
                        'budget' => -4000,
                        'satisfaction' => +10,
                        'autonomy' => -2,
                    ]
                ],
                [
                    'text' => 'Mobiliser l\'équipe technique interne pour tenter une réparation',
                    'result_message' => 'Après deux jours d\'effort, l\'équipe a réussi à remplacer le disque défaillant et à restaurer les données. Quelques sueurs froides, mais une belle économie.',
                    'effects' => [
                        'budget' => -300, // Coût des pièces
                        'satisfaction' => -5, // L\'indisponibilité a agacé
                        'autonomy' => +5,
                    ]
                ],
            ]
        ],

        // --- Événement 2 : Demande de Logiciels ---
        'EVT002' => [
            'title' => 'Besoin d\'un Logiciel de PAO',
            'description' => 'Les professeurs d\'arts plastiques demandent des licences pour une suite de création graphique professionnelle (type Adobe) pour un nouveau projet pédagogique.',
            'choices' => [
                [
                    'text' => 'Acheter les licences de la suite logicielle leader du marché',
                    'result_message' => 'Les professeurs sont ravis et les élèves ont accès aux outils standards de l\'industrie. Le budget, lui, fait grise mine.',
                    'effects' => [
                        'budget' => -8000,
                        'satisfaction' => +15,
                        'autonomy' => -5,
                    ]
                ],
                [
                    'text' => 'Proposer une formation sur des alternatives Libres (GIMP, Inkscape)',
                    'result_message' => 'L\'adoption est mitigée. Certains professeurs sont curieux, d\'autres se plaignent du changement. C\'est un pari sur l\'avenir.',
                    'effects' => [
                        'budget' => -1000, // Coût de la formation
                        'satisfaction' => -10,
                        'autonomy' => +10,
                    ]
                ],
                 [
                    'text' => 'Refuser la demande pour des raisons budgétaires',
                    'result_message' => 'La décision est mal perçue par l\'équipe pédagogique, qui se sent bridée dans ses projets.',
                    'effects' => [
                        'budget' => 0,
                        'satisfaction' => -15,
                        'autonomy' => 0,
                    ]
                ],
            ]
        ],
        
        // --- Événement 3 : Fin de support OS ---
        'EVT003' => [
            'title' => 'Fin de Support de l\'OS',
            'description' => 'Le système d\'exploitation de 150 postes de la salle informatique arrive en fin de support. Ils ne recevront plus de mises à jour de sécurité.',
            'choices' => [
                [
                    'text' => 'Payer la mise à niveau vers la nouvelle version de l\'OS propriétaire',
                    'result_message' => 'Les postes sont à jour et sécurisés, mais cela représente un coût de licence très important.',
                    'effects' => [
                        'budget' => -15000,
                        'satisfaction' => +5,
                        'autonomy' => -5,
                    ]
                ],
                [
                    'text' => 'Lancer un projet de migration vers une distribution Linux Éducative',
                    'result_message' => 'Le projet est ambitieux. Il faudra du temps pour former tout le monde, mais c\'est un grand pas vers l\'autonomie.',
                    'effects' => [
                        'budget' => -2500, // Formation et support
                        'satisfaction' => -10, // Résistance au changement
                        'autonomy' => +15,
                    ]
                ],
            ]
        ],
    ];
}

