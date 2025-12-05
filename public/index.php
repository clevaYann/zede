<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZEDE - Le Simulateur de Résistance Numérique</title>
    <!-- Importation de Tailwind CSS pour le style rapide de l'UI -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Importation d'une police "Pixel" ou "Tech" -->
    <link href="https://fonts.googleapis.com/css2?family=VT323&family=Inter:wght@400;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            background-color: #1a202c; /* Dark mode base */
            font-family: 'Inter', sans-serif;
            overflow: hidden; /* Empêche le scroll pendant le jeu */
        }

        /* Style rétro pour le titre et les chiffres */
        .retro-font {
            font-family: 'VT323', monospace;
        }

        /* Effet de scanline (vieux moniteur) sur le canvas */
        #canvas-container {
            position: relative;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
            border: 4px solid #4a5568;
            border-radius: 8px;
            overflow: hidden;
        }

        #canvas-container::after {
            content: " ";
            display: block;
            position: absolute;
            top: 0;
            left: 0;
            bottom: 0;
            right: 0;
            background: linear-gradient(rgba(18, 16, 16, 0) 50%, rgba(0, 0, 0, 0.1) 50%);
            background-size: 100% 4px;
            z-index: 2;
            pointer-events: none;
        }

        /* Animation des barres de progression */
        .progress-bar {
            transition: width 0.5s ease-in-out;
        }

        /* Modale d'événement */
        .modal {
            display: none;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 10;
            background: rgba(255, 255, 255, 0.95);
            border: 2px solid #2d3748;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
            max-width: 400px;
            width: 90%;
            animation: popIn 0.3s ease-out;
        }

        @keyframes popIn {
            from { transform: translate(-50%, -60%); opacity: 0; }
            to { transform: translate(-50%, -50%); opacity: 1; }
        }
    </style>
</head>
<body class="h-screen w-screen flex items-center justify-center bg-gray-900 text-gray-100 p-4">

    <!-- Conteneur Principal -->
    <div class="flex flex-col lg:flex-row gap-6 w-full max-w-6xl h-[90vh]">
        
        <!-- Zone de Jeu (Canvas) -->
        <div id="canvas-container" class="flex-grow bg-gray-800 relative">
            <canvas id="gameCanvas" class="block w-full h-full bg-[#2d3748]"></canvas>
            
            <!-- Overlay d'interaction (affiché quand proche d'un objet) -->
            <div id="interaction-prompt" class="hidden absolute bottom-4 left-1/2 transform -translate-x-1/2 bg-black bg-opacity-75 text-white px-4 py-2 rounded font-bold border border-white retro-font text-xl">
                Appuyez sur [ESPACE] pour interagir
            </div>

            <!-- Modale d'événement (Ex: Choix budgétaire) -->
            <div id="event-modal" class="modal p-6 text-gray-900">
                <h3 class="text-xl font-bold mb-2 text-indigo-700" id="modal-title">Événement</h3>
                <p class="mb-4 text-sm" id="modal-desc">Description de l'événement...</p>
                <div id="modal-choices" class="flex gap-2 justify-end">
                    <!-- Les boutons de choix seront injectés ici -->
                </div>
            </div>
        </div>

        <!-- Interface Utilisateur (HUD) -->
        <div class="w-full lg:w-80 flex flex-col gap-4 bg-gray-800 p-6 rounded-lg border border-gray-700 shadow-xl overflow-y-auto">
            
            <!-- En-tête -->
            <div class="border-b border-gray-600 pb-4 mb-2">
                <h1 class="text-4xl text-green-400 retro-font font-bold tracking-wider">ZEDE_OS</h1>
                <p class="text-xs text-gray-400 uppercase tracking-widest mt-1">Simulateur de Résistance</p>
            </div>

            <!-- Stats Principales -->
            <div class="space-y-6">
                
                <!-- Semaine -->
                <div class="bg-gray-700 p-3 rounded border-l-4 border-blue-500">
                    <span class="text-gray-400 text-xs uppercase">Calendrier</span>
                    <div class="flex items-end justify-between">
                        <span class="text-2xl font-bold retro-font">SEMAINE</span>
                        <span class="text-3xl font-bold text-white retro-font"><span id="week-display">1</span>/36</span>
                    </div>
                </div>

                <!-- Budget -->
                <div>
                    <div class="flex justify-between mb-1">
                        <span class="text-sm font-bold text-gray-300">Budget Lycée</span>
                        <span class="text-sm font-bold text-green-400 retro-font" id="budget-display">50 000 €</span>
                    </div>
                    <div class="w-full bg-gray-900 rounded-full h-2.5">
                        <div id="budget-bar" class="bg-green-500 h-2.5 rounded-full progress-bar" style="width: 100%"></div>
                    </div>
                </div>

                <!-- Satisfaction -->
                <div>
                    <div class="flex justify-between mb-1">
                        <span class="text-sm font-bold text-gray-300">Satisfaction</span>
                        <span class="text-sm font-bold text-yellow-400 retro-font" id="satisfaction-display">75%</span>
                    </div>
                    <div class="w-full bg-gray-900 rounded-full h-2.5">
                        <div id="satisfaction-bar" class="bg-yellow-500 h-2.5 rounded-full progress-bar" style="width: 75%"></div>
                    </div>
                </div>

                <!-- Autonomie -->
                <div>
                    <div class="flex justify-between mb-1">
                        <span class="text-sm font-bold text-gray-300">Autonomie Tech</span>
                        <span class="text-sm font-bold text-blue-400 retro-font" id="autonomie-display">10 pts</span>
                    </div>
                    <div class="w-full bg-gray-900 rounded-full h-2.5">
                        <div id="autonomie-bar" class="bg-blue-500 h-2.5 rounded-full progress-bar" style="width: 10%"></div>
                    </div>
                </div>

            </div>

            <!-- Journal de bord (Log) -->
            <div class="mt-auto pt-4 border-t border-gray-600">
                <span class="text-xs text-gray-400 uppercase mb-2 block">Journal Système</span>
                <div id="event-log" class="h-32 overflow-y-auto text-xs text-green-300 font-mono bg-black p-2 rounded border border-gray-700 space-y-1">
                    <p>> Initialisation du système...</p>
                </div>
            </div>

            <!-- Contrôles Mobiles (Visibles uniquement si besoin) -->
            <div class="lg:hidden grid grid-cols-3 gap-2 mt-4">
                <div></div>
                <button ontouchstart="handleTouch('ArrowUp')" ontouchend="clearTouch()" class="bg-gray-600 p-4 rounded text-white">▲</button>
                <div></div>
                <button ontouchstart="handleTouch('ArrowLeft')" ontouchend="clearTouch()" class="bg-gray-600 p-4 rounded text-white">◀</button>
                <button ontouchstart="handleTouch('ArrowDown')" ontouchend="clearTouch()" class="bg-gray-600 p-4 rounded text-white">▼</button>
                <button ontouchstart="handleTouch('ArrowRight')" ontouchend="clearTouch()" class="bg-gray-600 p-4 rounded text-white">▶</button>
            </div>
        </div>
    </div>

    <script type="module">
        import gameData from './gameData.js';

        // --- 1. Gestion de l'État ---
        const initialState = {
            budget: 50000,
            satisfaction: 75,
            autonomie: 10,
            week: 1,
            maxBudget: 60000,
            completedEvents: []
        };
        let resources = JSON.parse(localStorage.getItem('zede_resources')) || JSON.parse(JSON.stringify(initialState));
        window.resetGame = () => {
             localStorage.removeItem('zede_resources');
             window.location.reload();
        }

        function saveState() {
            localStorage.setItem('zede_resources', JSON.stringify(resources));
            updateUI();
        }

        function updateUI() {
            document.getElementById('week-display').textContent = resources.week;
            document.getElementById('budget-display').textContent = resources.budget.toLocaleString() + ' €';
            document.getElementById('satisfaction-display').textContent = resources.satisfaction + ' %';
            document.getElementById('autonomie-display').textContent = resources.autonomie + ' pts';

            document.getElementById('budget-bar').style.width = Math.min(100, (resources.budget / resources.maxBudget) * 100) + '%';
            document.getElementById('satisfaction-bar').style.width = Math.min(100, resources.satisfaction) + '%';
            document.getElementById('autonomie-bar').style.width = Math.min(100, resources.autonomie) + '%';
        }

        function logEvent(message) {
            const log = document.getElementById('event-log');
            const entry = document.createElement('p');
            entry.textContent = `> ${message}`;
            log.appendChild(entry);
            log.scrollTop = log.scrollHeight;
        }

        // --- 2. Moteur Graphique (Canvas) ---
        const canvas = document.getElementById('gameCanvas');
        const ctx = canvas.getContext('2d');
        const TILE_SIZE = 48; 
        
        const map = gameData.maps.school;
        const player = { x: 2, y: 3, color: '#3b82f6' };
        
        const assets = {};
        function preloadAssets() {
            const playerSprite = new Image();
            playerSprite.src = 'data:image/svg+xml;base64,' + btoa(`
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48">
                    <circle cx="24" cy="34" r="14" fill="rgba(0,0,0,0.2)"/>
                    <rect x="14" y="14" width="20" height="20" rx="10" fill="#1e40af"/>
                    <circle cx="24" cy="24" r="14" fill="${player.color}"/>
                    <circle cx="24" cy="20" r="8" fill="#bfdbfe"/>
                </svg>`);
            assets.player = playerSprite;

            gameData.npcs.forEach(npc => {
                 const npcSprite = new Image();
                 npcSprite.src = 'data:image/svg+xml;base64,' + btoa(`
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48">
                         <circle cx="24" cy="34" r="14" fill="rgba(0,0,0,0.2)"/>
                         <circle cx="24" cy="24" r="15" fill="${npc.color}"/>
                         <rect x="16" y="19" width="16" height="4" fill="black"/>
                    </svg>`);
                 assets[npc.id] = npcSprite;
            });
        }
        
        function drawTile(x, y, type) {
            const posX = x * TILE_SIZE;
            const posY = y * TILE_SIZE;
            const tileInfo = gameData.tiles[type];
            if (!tileInfo) return;

            ctx.fillStyle = tileInfo.color;
            ctx.fillRect(posX, posY, TILE_SIZE, TILE_SIZE);

            if(tileInfo.decorator) {
                tileInfo.decorator(ctx, posX, posY, TILE_SIZE);
            }
        }

        function drawEntities() {
            gameData.npcs.forEach(npc => {
                const npcTile = findTileForNpc(npc.id);
                if(npcTile && assets[npc.id]){
                    ctx.drawImage(assets[npc.id], npcTile.x * TILE_SIZE, npcTile.y * TILE_SIZE, TILE_SIZE, TILE_SIZE);
                }
            });

            if(assets.player){
                ctx.drawImage(assets.player, player.x * TILE_SIZE, player.y * TILE_SIZE, TILE_SIZE, TILE_SIZE);
            }
        }

        function draw() {
            ctx.fillStyle = '#1a202c';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            
            const mapWidth = map[0].length * TILE_SIZE;
            const mapHeight = map.length * TILE_SIZE;
            ctx.save();
            ctx.translate((canvas.width - mapWidth) / 2, (canvas.height - mapHeight) / 2);
            
            for(let y=0; y<map.length; y++) {
                for(let x=0; x<map[0].length; x++) {
                    drawTile(x, y, map[y][x]);
                }
            }
            drawEntities();
            ctx.restore();
            
            checkProximity();
        }

        // --- 3. Logique de Jeu ---
        function initGame() {
            const container = document.getElementById('canvas-container');
            canvas.width = container.offsetWidth;
            canvas.height = container.offsetHeight;
            
            preloadAssets();
            updateUI();
            logEvent("Jeu chargé. Utilisez les flèches.");
            logEvent("Pour réinitialiser, tapez resetGame() dans la console.");
            requestAnimationFrame(gameLoop);
        }

        function canMove(x, y) {
            if (y < 0 || y >= map.length || x < 0 || x >= map[0].length) return false;
            const tileType = map[y][x];
            const tileInfo = gameData.tiles[tileType];
            return tileInfo && tileInfo.walkable;
        }
        
        function findTileForNpc(npcId) {
            const npc = gameData.npcs.find(n => n.id === npcId);
            if (!npc) return null;
            for(let y=0; y<map.length; y++) {
                for(let x=0; x<map[0].length; x++) {
                    if(map[y][x] === npc.tileId) return {x, y};
                }
            }
            return null;
        }

        let currentNpcInteraction = null;
        function checkProximity() {
            const prompt = document.getElementById('interaction-prompt');
            currentNpcInteraction = null;
            
            const adjacent = [{x:0, y:-1}, {x:0, y:1}, {x:-1, y:0}, {x:1, y:0}];
            for(const offset of adjacent) {
                let checkY = player.y + offset.y;
                let checkX = player.x + offset.x;
                if(checkY >=0 && checkY < map.length && checkX >= 0 && checkX < map[0].length) {
                    const tileType = map[checkY][checkX];
                    const npc = gameData.npcs.find(n => n.tileId === tileType);
                    if(npc) {
                        currentNpcInteraction = npc;
                        break;
                    }
                }
            };

            if (currentNpcInteraction) {
                prompt.classList.remove('hidden');
            } else {
                prompt.classList.add('hidden');
            }
        }

        function interact() {
            if (!currentNpcInteraction) return;

            const eventId = currentNpcInteraction.eventId;
            const event = gameData.events[eventId];

            if (!event || (resources.completedEvents.includes(eventId))) {
                logEvent("Rien de nouveau à signaler.");
                return;
            }
            
            if (event.condition && !resources.completedEvents.includes(event.condition)) {
                logEvent("Pas le bon moment pour discuter de ça.");
                return;
            }
            
            showModal(event.message, event.choices);
        }

        function showModal(message, choices) {
            document.getElementById('modal-desc').textContent = message;
            const choicesContainer = document.getElementById('modal-choices');
            choicesContainer.innerHTML = ''; // Vider les anciens choix

            choices.forEach(choice => {
                const btn = document.createElement('button');
                btn.textContent = choice.text;
                btn.className = "px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 text-sm font-bold transition";
                btn.onclick = () => applyChoice(choice.id, currentNpcInteraction.eventId);
                choicesContainer.appendChild(btn);
            });
            
            const closeBtn = document.createElement('button');
            closeBtn.textContent = 'Plus tard';
            closeBtn.className = "px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 text-sm font-bold transition";
            closeBtn.onclick = closeModal;
            choicesContainer.appendChild(closeBtn);

            document.getElementById('event-modal').style.display = 'block';
        }
        
        function applyChoice(choiceId, eventId) {
             const choice = gameData.events[eventId].choices.find(c => c.id === choiceId);
             if(!choice) return;

             if (resources.budget < (choice.consequences.budget || 0) * -1) {
                 logEvent("Budget insuffisant !");
                 closeModal();
                 return;
             }
            
             resources.budget += (choice.consequences.budget || 0);
             resources.satisfaction += (choice.consequences.satisfaction || 0);
             resources.autonomie += (choice.consequences.autonomie || 0);
             resources.week++;
             resources.completedEvents.push(eventId);
             
             logEvent(choice.outcome_message);
             saveState();
             closeModal();
        }

        function closeModal() {
            document.getElementById('event-modal').style.display = 'none';
        }

        const keys = {};
        function handleInput(e) {
            if(document.getElementById('event-modal').style.display === 'block') return;

            keys[e.code] = e.type === 'keydown';
        }
        
        let touchTimeout;
        window.handleTouch = (keyCode) => {
            keys[keyCode] = true;
            if(touchTimeout) clearTimeout(touchTimeout);
        }
        window.clearTouch = () => {
             Object.keys(keys).forEach(k => keys[k] = false);
             touchTimeout = setTimeout(() => {
                  Object.keys(keys).forEach(k => keys[k] = false);
             }, 100);
        }

        function processMovement() {
            let dx = 0;
            let dy = 0;
            if (keys['ArrowUp']) dy = -1;
            if (keys['ArrowDown']) dy = 1;
            if (keys['ArrowLeft']) dx = -1;
            if (keys['ArrowRight']) dx = 1;

            if ((dx !== 0 || dy !== 0) && canMove(player.x + dx, player.y + dy)) {
                player.x += dx;
                player.y += dy;
            }
            
            if(keys['Space']) {
                interact();
                keys['Space'] = false; // Pour éviter les interactions multiples
            }
        }
        
        let lastMoveTime = 0;
        function gameLoop(timestamp) {
            if(timestamp - lastMoveTime > 150) { // Limite le mouvement à ~6fps
                processMovement();
                lastMoveTime = timestamp;
            }
            draw();
            requestAnimationFrame(gameLoop);
        }

        // Démarrage
        document.addEventListener('keydown', handleInput);
        document.addEventListener('keyup', handleInput);
        window.onload = initGame;
        window.onresize = initGame;

    </script>
</body>
</html>
