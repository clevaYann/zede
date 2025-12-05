const canvas = document.getElementById('gameCanvas');
const ctx = canvas.getContext('2d');
const eventLog = document.getElementById('event-log');

// Ajuster la taille du canvas
const resizeCanvas = () => {
    const container = document.getElementById('game-container');
    canvas.width = container.clientWidth - 300;
    canvas.height = container.clientHeight;
    // Redessiner après redimensionnement
    draw(); 
};
window.addEventListener('resize', resizeCanvas);

// --- Actifs du jeu ---
const player = {
    x: 100, y: 50, width: 20, height: 20,
    color: '#e74c3c', speed: 4
};

const npcs = [
    { id: 'tech_guy_1', x: 0, y: 0, width: 25, height: 25, color: '#2ecc71', name: "Technicien" },
    { id: 'teacher_1', x: 0, y: 0, width: 25, height: 25, color: '#3498db', name: "Enseignant" },
    { id: 'student_1', x: 0, y: 0, width: 20, height: 20, color: '#9b59b6', name: "Élève Club Info" }
];

const scenery = [
    // Labels des salles
    { label: "Bureau Direction", x: 100, y: 40 },
    { label: "Salle Serveur", x: 100, y: 220 },
    { label: "Salle des Profs", x: 380, y: 40 },
    { label: "Salle de Classe", x: 380, y: 220 },

    // Murs exterieurs
    { type: 'wall', x: 0, y: 0, width: 10, height: 480, color: '#7f8c8d'}, // Gauche
    { type: 'wall', x: 590, y: 0, width: 10, height: 480, color: '#7f8c8d'},// Droite
    { type: 'wall', x: 0, y: 0, width: 600, height: 10, color: '#7f8c8d'},   // Haut
    { type: 'wall', x: 0, y: 470, width: 600, height: 10, color: '#7f8c8d'},  // Bas

    // Murs intérieurs
    // Boite Bureau
    { type: 'wall', x: 10, y: 150, width: 180, height: 10, color: '#7f8c8d'},
    { type: 'wall', x: 190, y: 10, width: 10, height: 140, color: '#7f8c8d'},
    // Boite Salle serveur
    { type: 'wall', x: 10, y: 310, width: 180, height: 10, color: '#7f8c8d'},
    { type: 'wall', x: 190, y: 310, width: 10, height: 160, color: '#7f8c8d'},
    // Boite Salle des profs
    { type: 'wall', x: 280, y: 10, width: 10, height: 140, color: '#7f8c8d'},
    { type: 'wall', x: 290, y: 150, width: 300, height: 10, color: '#7f8c8d'},
    // Boite Salle de classe
    { type: 'wall', x: 280, y: 310, width: 10, height: 160, color: '#7f8c8d'},
    { type: 'wall', x: 290, y: 310, width: 300, height: 10, color: '#7f8c8d'},
    
    // Portes
    { type: 'door', x: 150, y: 150, width: 40, height: 10, color: '#A0522D'}, // Porte Bureau
    { type: 'door', x: 150, y: 310, width: 40, height: 10, color: '#A0522D'}, // Porte Salle Serveur
    { type: 'door', x: 290, y: 150, width: 40, height: 10, color: '#A0522D'}, // Porte Salle des profs
    { type: 'door', x: 290, y: 310, width: 40, height: 10, color: '#A0522D'}, // Porte Salle de classe
];

// Placer les PNJ dans leurs zones
npcs.find(n => n.id === 'tech_guy_1').x = 60;
npcs.find(n => n.id === 'tech_guy_1').y = 240;
npcs.find(n => n.id === 'teacher_1').x = 330;
npcs.find(n => n.id === 'teacher_1').y = 60;
npcs.find(n => n.id === 'student_1').x = 330;
npcs.find(n => n.id === 'student_1').y = 240;


const keys = { ArrowUp: false, ArrowDown: false, ArrowLeft: false, ArrowRight: false };

// --- Fonctions de dessin ---
function drawScenery() {
    scenery.forEach(item => {
        if(item.type) { // Ne dessine que les murs et les portes
            ctx.fillStyle = item.color;
            ctx.fillRect(item.x, item.y, item.width, item.height);
        } else if (item.label) { // Dessine les labels
            ctx.fillStyle = '#2c3e50';
            ctx.font = '12px Arial';
            ctx.textAlign = 'center';
            ctx.fillText(item.label, item.x, item.y);
        }
    });
}

function drawPlayer() {
    ctx.fillStyle = player.color;
    ctx.fillRect(player.x, player.y, player.width, player.height);
}

function drawNPCs() {
    npcs.forEach(npc => {
        ctx.fillStyle = npc.color;
        ctx.fillRect(npc.x, npc.y, npc.width, npc.height);
        ctx.fillStyle = '#000';
        ctx.textAlign = 'center';
        ctx.fillText(npc.name, npc.x + npc.width / 2, npc.y - 10);

        // Afficher l'indicateur d'interaction
        if (checkCollision(player, { ...npc, width: npc.width + 40, height: npc.height + 40, x: npc.x - 20, y: npc.y - 20 })) {
             ctx.font = 'bold 14px Arial';
             ctx.fillStyle = '#333';
             ctx.fillText("[Entrée]", npc.x + npc.width / 2, npc.y + npc.height + 15);
        }
    });
}

// --- Logique de jeu ---
function update() {
    const originalX = player.x;
    const originalY = player.y;

    if (keys.ArrowUp) player.y -= player.speed;
    if (keys.ArrowDown) player.y += player.speed;
    if (keys.ArrowLeft) player.x -= player.speed;
    if (keys.ArrowRight) player.x += player.speed;

    // Gestion des collisions avec les murs
    for (const item of scenery) {
        if (item.type === 'wall' && checkCollision(player, item)) {
            // Collision détectée, on annule le mouvement
            player.x = originalX;
            player.y = originalY;
            // On sort de la boucle, une seule collision suffit
            break; 
        }
    }
}

function checkCollision(rect1, rect2) {
    return rect1.x < rect2.x + rect2.width && rect1.x + rect1.width > rect2.x &&
           rect1.y < rect2.y + rect2.height && rect1.y + rect1.height > rect2.y;
}

function draw() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    drawScenery();
    drawPlayer();
    drawNPCs();
}

function gameLoop() {
    update();
    draw();
    requestAnimationFrame(gameLoop);
}

// --- Gestion des entrées et Communication API ---
window.addEventListener('keydown', (e) => {
    if (keys.hasOwnProperty(e.key)) { e.preventDefault(); keys[e.key] = true; }
    if (e.key === 'Enter') {
        e.preventDefault();
        npcs.forEach(npc => {
            if (checkCollision(player, npc)) interactWithNpc(npc.id);
        });
    }
});
window.addEventListener('keyup', (e) => {
    if (keys.hasOwnProperty(e.key)) { e.preventDefault(); keys[e.key] = false; }
});

async function interactWithNpc(npcId) {
    eventLog.innerHTML = `<p><i>Communication...</i></p>`;
    const response = await fetch('../src/events.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'interact_npc', npcId: npcId })
    });
    const data = await response.json();
    updateUI(data);
}

async function makeChoice(choiceId) {
    eventLog.innerHTML = `<p><i>Décision en cours...</i></p>`;
    const response = await fetch('../src/events.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'make_choice', choiceId: choiceId })
    });
    const data = await response.json();
    updateUI(data);
}

function updateUI(data) {
    eventLog.innerHTML = ''; // Nettoyer le log

    if (data.message) {
        eventLog.innerHTML = `<p>${data.message}</p>`;
    }
    if (data.outcome_message) {
        eventLog.innerHTML = `<p>${data.outcome_message}</p>`;
    }

    if (data.choices) {
        const choiceContainer = document.createElement('div');
        choiceContainer.className = 'choices-container';
        data.choices.forEach(choice => {
            const btn = document.createElement('button');
            btn.textContent = choice.text;
            btn.onclick = () => makeChoice(choice.id);
            choiceContainer.appendChild(btn);
        });
        eventLog.appendChild(choiceContainer);
    }

    if (data.updated_resources) {
        const res = data.updated_resources;
        document.getElementById('budget-display').textContent = `${res.budget} €`;
        document.getElementById('satisfaction-display').textContent = `${res.satisfaction} %`;
        document.getElementById('autonomie-display').textContent = `${res.autonomie} pts`;
        document.getElementById('week-display').textContent = res.week;
    }
    
    if (data.error) {
        eventLog.innerHTML = `<p style="color: #e74c3c;">Erreur: ${data.error}</p>`;
    }
}

// Démarrage
resizeCanvas();
gameLoop();
eventLog.innerHTML = `<p>Bienvenue. Déplacez-vous avec les flèches. Allez dans la salle serveur et parlez au technicien (carré vert) en appuyant sur Entrée.</p>`

