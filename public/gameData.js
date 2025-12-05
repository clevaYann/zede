const gameData = {
    // Définition des types de tuiles
    tiles: {
        0: { name: "Sol", color: '#e2e8f0', walkable: true, decorator: (ctx, x, y, size) => {
            ctx.strokeStyle = '#cbd5e0';
            ctx.strokeRect(x, y, size, size);
        }},
        1: { name: "Mur", color: '#2c3e50', walkable: false, decorator: (ctx, x, y, size) => {
            ctx.fillStyle = '#1a252f';
            ctx.fillRect(x, y + size - 10, size, 10);
        }},
        2: { name: "Porte", color: '#d69e2e', walkable: true, decorator: (ctx, x, y, size) => {
            ctx.fillStyle = '#e2e8f0'; // Sol dessous
            ctx.fillRect(x,y,size,size);
            ctx.fillStyle = '#d69e2e'; // Bois
            ctx.fillRect(x + 5, y, size - 10, size);
        }},
        3: { name: "Casier", color: '#4a5568', walkable: false, decorator: (ctx, x, y, size) => {
             ctx.fillStyle = '#2d3748';
             ctx.fillRect(x + 10, y + 10, size - 20, 5);
             ctx.fillRect(x + 10, y + 20, size - 20, 5);
        }},
        4: { name: "Serveur", color: '#1a202c', walkable: false, decorator: (ctx, x, y, size) => {
            ctx.fillStyle = (Date.now() % 1000 < 500) ? '#48bb78' : '#276749';
            ctx.beginPath();
            ctx.arc(x + 15, y + 15, 3, 0, Math.PI*2);
            ctx.fill();
            ctx.fillStyle = (Date.now() % 800 < 400) ? '#f56565' : '#9b2c2c';
            ctx.beginPath();
            ctx.arc(x + 25, y + 15, 3, 0, Math.PI*2);
            ctx.fill();
        }},
        // Les tuiles 5, 6, 7 sont des marqueurs de position pour les PNJ. Ils sont marchables.
        5: { name: "Sol NPC Technicien", color: '#e2e8f0', walkable: true, decorator: (ctx, x, y, size) => {
            ctx.strokeStyle = '#cbd5e0';
            ctx.strokeRect(x, y, size, size);
        }},
        6: { name: "Sol NPC Enseignant", color: '#e2e8f0', walkable: true, decorator: (ctx, x, y, size) => {
            ctx.strokeStyle = '#cbd5e0';
            ctx.strokeRect(x, y, size, size);
        }},
        7: { name: "Sol NPC Eleve", color: '#e2e8f0', walkable: true, decorator: (ctx, x, y, size) => {
            ctx.strokeStyle = '#cbd5e0';
            ctx.strokeRect(x, y, size, size);
        }},
    },
    // Carte du jeu
    maps: {
        school: [
            [1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1],
            [1,3,3,1,0,0,0,1,6,0,1,0,0,0,0,0,1],
            [1,0,0,1,0,0,0,2,0,0,2,0,0,0,0,0,1],
            [1,3,3,1,0,0,0,1,0,0,1,0,7,0,0,0,1],
            [1,1,1,1,1,1,1,1,0,0,1,1,1,1,1,1,1],
            [1,0,0,0,0,0,0,0,0,0,2,4,4,1,1,1,1],
            [1,0,0,0,0,0,0,0,0,0,1,5,4,1,1,1,1],
            [1,0,0,0,0,0,0,0,0,0,1,4,4,1,1,1,1],
            [1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1]
        ]
    },
    // Personnages non-joueurs
    npcs: [
        { id: 'tech_guy_1', name: "Technicien", color: '#2ecc71', tileId: 5, eventId: 'event_office_migration' },
        { id: 'teacher_1', name: "Enseignant", color: '#3498db', tileId: 6, eventId: 'event_teacher_complaint' },
        { id: 'student_1', name: "Élève Club Info", color: '#9b59b6', tileId: 7, eventId: 'event_student_initiative' }
    ],
    // Événements et choix
    events: {
        'event_office_migration': {
            message: "La licence de notre suite bureautique proprio expire. Renouvellement : 10 000€. Alternative : migrer vers LibreOffice.",
            choices: [
                { id: 'choice_renew', text: "Renouveler (10 000€)", consequences: { budget: -10000, autonomie: -5 }, outcome_message: "Licence renouvelée. Le budget et l'autonomie en pâtissent." },
                { id: 'choice_migrate', text: "Migrer (3 000€)", consequences: { budget: -3000, satisfaction: -15, autonomie: 20 }, outcome_message: "Migration lancée ! L'autonomie grimpe mais la satisfaction baisse." },
            ]
        },
        'event_teacher_complaint': {
            condition: 'event_office_migration',
            message: "Ce nouveau logiciel est une catastrophe ! Mes anciens documents ne s'ouvrent pas bien. Il faut faire quelque chose !",
            choices: [
                { id: 'choice_train_more', text: "Formation avancée (1 500€)", consequences: { budget: -1500, satisfaction: 10, autonomie: 5 }, outcome_message: "La formation a apaisé les tensions." },
                { id: 'choice_ignore_teacher', text: "Ignorer les plaintes", consequences: { satisfaction: -10 }, outcome_message: "Le manque de soutien a déplu aux enseignants." },
            ]
        },
        'event_student_initiative': {
            message: "Avec le club info, on peut monter un cloud local (type Nextcloud) pour se libérer des GAFAM. On a juste besoin d'un serveur.",
            choices: [
                { id: 'choice_fund_server', text: "Acheter un serveur (5 000€)", consequences: { budget: -5000, satisfaction: 10, autonomie: 30 }, outcome_message: "L'école a son propre cloud ! L'autonomie fait un bond." },
                { id: 'choice_recycle_server', text: "Utiliser du matériel de récup (500€)", consequences: { budget: -500, satisfaction: 5, autonomie: 15 }, outcome_message: "Le système D fonctionne. C'est un premier pas vers l'autonomie." },
            ]
        }
    }
};

export default gameData;
