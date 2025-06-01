// Club Manager Frontend JavaScript
// File: assets/js/club-manager-frontend.js

document.addEventListener('alpine:init', () => {
    Alpine.data('clubManager', () => ({
        // Data properties
        activeTab: 'my-teams',
        currentSeason: clubManagerAjax.preferred_season || '2024-2025',
        teams: [],
        selectedTeam: null,
        teamPlayers: [],
        showCreateTeamModal: false,
        showAddPlayerModal: false,
        showAddExistingPlayerModal: false,
        showEvaluationModal: false,
        playerSearch: '',
        searchResults: [],
        showPlayerHistoryModal: false,
        playerHistory: [],
        historyPlayer: null,
        historyLoading: false,
        selectedExistingPlayer: null,
        evaluatingPlayer: null,
        evaluationNotes: '',
        playerCardChart: null,
        viewingPlayer: null,
        selectedPlayerCard: null,
        playerEvaluationHistory: [],
        availableEvaluationDates: [],
        selectedEvaluationDate: 'all',
        playerAdvice: null,
        adviceLoading: false,
        adviceStatus: 'no_evaluations',
        lastAdviceTimestamp: null,
        
        // Current evaluation session scores (separate from historical data)
        currentEvaluationScores: {},
        
        evaluationCategories: [
            {
                name: 'Ball Control',
                key: 'ball_control',
                subcategories: [
                    { key: 'first_touch', name: 'First Touch', description: 'Clean reception' },
                    { key: 'ball_carry', name: 'Ball Carry', description: 'Control under pressure' }
                ]
            },
            {
                name: 'Passing & Receiving',
                key: 'passing_receiving',
                subcategories: [
                    { key: 'push_slap_hit', name: 'Push, Slap, Hit', description: 'Accuracy & power' },
                    { key: 'timing_communication', name: 'Timing & Communication', description: 'Timing and communication' }
                ]
            },
            {
                name: 'Dribbling Skills',
                key: 'dribbling_skills',
                subcategories: [
                    { key: '1v1_situations', name: '1v1 Situations', description: '1v1 situations' },
                    { key: 'lr_control', name: 'L/R Control', description: 'Left/right control at speed' }
                ]
            },
            {
                name: 'Defensive Skills',
                key: 'defensive_skills',
                subcategories: [
                    { key: 'jab_block', name: 'Jab & Block', description: 'Jab & block tackle' },
                    { key: 'marking_positioning', name: 'Marking & Positioning', description: 'Marking & positioning' }
                ]
            },
            {
                name: 'Finishing & Scoring',
                key: 'finishing_scoring',
                subcategories: [
                    { key: 'shot_variety', name: 'Shot Variety', description: 'Hit, deflection, rebound' },
                    { key: 'scoring_instinct', name: 'Scoring Instinct', description: 'Scoring instinct' }
                ]
            },
            {
                name: 'Tactical Understanding',
                key: 'tactical_understanding',
                subcategories: [
                    { key: 'spatial_awareness', name: 'Spatial Awareness', description: 'Spatial awareness' },
                    { key: 'game_intelligence', name: 'Game Intelligence', description: 'Making the right choices' }
                ]
            },
            {
                name: 'Physical Fitness',
                key: 'physical_fitness',
                subcategories: [
                    { key: 'speed_endurance', name: 'Speed & Endurance', description: 'Speed & endurance' },
                    { key: 'strength_agility', name: 'Strength & Agility', description: 'Strength, agility, balance' }
                ]
            },
            {
                name: 'Mental Toughness',
                key: 'mental_toughness',
                subcategories: [
                    { key: 'focus_resilience', name: 'Focus & Resilience', description: 'Focus and resilience' },
                    { key: 'confidence_pressure', name: 'Confidence Under Pressure', description: 'Performance under pressure' }
                ]
            },
            {
                name: 'Team Play & Communication',
                key: 'team_play',
                subcategories: [
                    { key: 'verbal_communication', name: 'Verbal Communication', description: 'Verbal communication' },
                    { key: 'supporting_teammates', name: 'Supporting Teammates', description: 'On and off the ball' }
                ]
            },
            {
                name: 'Coachability & Attitude',
                key: 'coachability',
                subcategories: [
                    { key: 'takes_feedback', name: 'Takes Feedback', description: 'Takes feedback seriously' },
                    { key: 'work_ethic', name: 'Work Ethic', description: 'Work ethic, drive, respect' }
                ]
            }
        ],
        
        evaluations: {},
        
        newTeam: {
            name: '',
            coach: ''
        },
        
        newPlayer: {
            first_name: '',
            last_name: '',
            birth_date: '',
            email: '',
            position: '',
            jersey_number: '',
            notes: ''
        },
        
        existingPlayerTeamData: {
            position: '',
            jersey_number: '',
            notes: ''
        },
        
        // Initialize
        init() {
            this.loadTeams();
            
            // Fix for modals on mobile
            this.$watch('showCreateTeamModal', value => {
                document.body.style.overflow = value ? 'hidden' : '';
            });
            this.$watch('showAddPlayerModal', value => {
                document.body.style.overflow = value ? 'hidden' : '';
            });
            this.$watch('showAddExistingPlayerModal', value => {
                document.body.style.overflow = value ? 'hidden' : '';
            });
            this.$watch('showEvaluationModal', value => {
                document.body.style.overflow = value ? 'hidden' : '';
            });
        },
        
        // Season Management
        async changeSeason() {
            const formData = new FormData();
            formData.append('action', 'cm_save_season_preference');
            formData.append('season', this.currentSeason);
            formData.append('nonce', clubManagerAjax.nonce);
            
            await fetch(clubManagerAjax.ajax_url, {
                method: 'POST',
                body: formData
            });
            
            this.loadTeams();
            this.selectedTeam = null;
            this.teamPlayers = [];
            this.viewingPlayer = null;
            this.selectedPlayerCard = null;
        },
        
        // Team Management
        async loadTeams() {
            const formData = new FormData();
            formData.append('action', 'cm_get_teams');
            formData.append('season', this.currentSeason);
            formData.append('nonce', clubManagerAjax.nonce);
            
            const response = await fetch(clubManagerAjax.ajax_url, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            if (data.success) {
                this.teams = data.data;
            }
        },
        
        async createTeam() {
            const formData = new FormData();
            formData.append('action', 'cm_create_team');
            formData.append('name', this.newTeam.name);
            formData.append('coach', this.newTeam.coach);
            formData.append('season', this.currentSeason);
            formData.append('nonce', clubManagerAjax.nonce);
            
            const response = await fetch(clubManagerAjax.ajax_url, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            if (data.success) {
                this.showCreateTeamModal = false;
                this.newTeam = { name: '', coach: '' };
                this.loadTeams();
            }
        },
        
        async selectTeam(team) {
            this.selectedTeam = team;
            this.viewingPlayer = null;
            this.selectedPlayerCard = null;
            await this.loadTeamPlayers();
        },
        
        // Player Management
        async loadTeamPlayers() {
            const formData = new FormData();
            formData.append('action', 'cm_get_team_players');
            formData.append('team_id', this.selectedTeam.id);
            formData.append('season', this.currentSeason);
            formData.append('nonce', clubManagerAjax.nonce);
            
            const response = await fetch(clubManagerAjax.ajax_url, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            if (data.success) {
                this.teamPlayers = data.data;
            }
        },
        
        async createPlayer() {
            const formData = new FormData();
            formData.append('action', 'cm_create_player');
            Object.keys(this.newPlayer).forEach(key => {
                formData.append(key, this.newPlayer[key]);
            });
            formData.append('team_id', this.selectedTeam.id);
            formData.append('season', this.currentSeason);
            formData.append('nonce', clubManagerAjax.nonce);
            
            const response = await fetch(clubManagerAjax.ajax_url, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            if (data.success) {
                this.showAddPlayerModal = false;
                this.newPlayer = {
                    first_name: '',
                    last_name: '',
                    birth_date: '',
                    email: '',
                    position: '',
                    jersey_number: '',
                    notes: ''
                };
                this.loadTeamPlayers();
            }
        },
        
        async searchPlayers() {
            if (this.playerSearch.length < 2) {
                this.searchResults = [];
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'cm_search_players');
            formData.append('search', this.playerSearch);
            formData.append('team_id', this.selectedTeam.id);
            formData.append('season', this.currentSeason);
            formData.append('nonce', clubManagerAjax.nonce);
            
            const response = await fetch(clubManagerAjax.ajax_url, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            if (data.success) {
                this.searchResults = data.data;
            }
        },
        
        selectExistingPlayer(player) {
            this.selectedExistingPlayer = player;
            this.searchResults = [];
            this.playerSearch = '';
        },
        
        async addExistingPlayerToTeam() {
            const formData = new FormData();
            formData.append('action', 'cm_add_player_to_team');
            formData.append('team_id', this.selectedTeam.id);
            formData.append('player_id', this.selectedExistingPlayer.id);
            Object.keys(this.existingPlayerTeamData).forEach(key => {
                formData.append(key, this.existingPlayerTeamData[key]);
            });
            formData.append('season', this.currentSeason);
            formData.append('nonce', clubManagerAjax.nonce);
            
            const response = await fetch(clubManagerAjax.ajax_url, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            if (data.success) {
                this.closeAddExistingPlayerModal();
                this.loadTeamPlayers();
            }
        },
        
        closeAddExistingPlayerModal() {
            this.showAddExistingPlayerModal = false;
            this.selectedExistingPlayer = null;
            this.playerSearch = '';
            this.searchResults = [];
            this.existingPlayerTeamData = {
                position: '',
                jersey_number: '',
                notes: ''
            };
        },
        
        async removePlayerFromTeam(player) {
            if (!confirm(`Are you sure you want to remove ${player.first_name} ${player.last_name} from this team?`)) {
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'cm_remove_player_from_team');
            formData.append('team_id', this.selectedTeam.id);
            formData.append('player_id', player.id);
            formData.append('season', this.currentSeason);
            formData.append('nonce', clubManagerAjax.nonce);
            
            const response = await fetch(clubManagerAjax.ajax_url, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            if (data.success) {
                // Hide player card if this player was being viewed
                if (this.viewingPlayer && this.viewingPlayer.id === player.id) {
                    this.viewingPlayer = null;
                    this.selectedPlayerCard = null;
                }
                this.loadTeamPlayers();
            } else {
                alert('Failed to remove player from team');
            }
        },
        
        // Evaluation Methods - Fixed for mobile with direct action
        handleEvaluateClick(playerId) {
            const player = this.teamPlayers.find(p => p.id == playerId);
            if (player) {
                this.evaluatePlayer(player);
            }
        },
        
        evaluatePlayer(player) {
            this.evaluatingPlayer = player;
            this.currentEvaluationScores = {};
            this.showEvaluationModal = true;
            this.loadEvaluations(player);
            this.initializeCurrentEvaluationScores();
            
            // Force DOM update for mobile
            this.$nextTick(() => {
                // Evaluation modal opened
            });
        },
        
        // Player Card Methods - Fixed for mobile with direct action
        handlePlayerCardClick(playerId) {
            const player = this.teamPlayers.find(p => p.id == playerId);
            if (player) {
                this.viewPlayerCard(player);
            }
        },
        
        async viewPlayerCard(player) {
            // If clicking same player, toggle card
            if (this.viewingPlayer && this.viewingPlayer.id === player.id) {
                this.viewingPlayer = null;
                this.selectedPlayerCard = null;
                if (this.playerCardChart) {
                    this.playerCardChart.destroy();
                    this.playerCardChart = null;
                }
                return;
            }
            
            // Destroy existing chart if any
            if (this.playerCardChart) {
                this.playerCardChart.destroy();
                this.playerCardChart = null;
            }
            
            this.viewingPlayer = player;
            this.selectedPlayerCard = this.selectedTeam;
            
            // Load evaluations first
            await this.loadEvaluations(player);
            await this.loadEvaluationHistory(player);
            
            // Load AI advice
            await this.loadPlayerAdvice(player);
            
            // Wait for Alpine to update the DOM
            await this.$nextTick();
            
            // Wait a bit more for the DOM to be ready and try to create chart
            setTimeout(() => {
                this.createSpiderChart();
            }, 500);
        },
        
        // Remove player with direct action
        handleRemoveClick(playerId) {
            const player = this.teamPlayers.find(p => p.id == playerId);
            if (player) {
                this.removePlayerFromTeam(player);
            }
        },
        
        // Initialize current evaluation scores with either previous scores or default values
        initializeCurrentEvaluationScores() {
            this.evaluationCategories.forEach(category => {
                category.subcategories.forEach(sub => {
                    const key = `${category.key}_${sub.key}`;
                    // Start with previous score or default to 5
                    this.currentEvaluationScores[key] = this.getLastSubcategoryScore(category.key, sub.key);
                });
            });
        },
        
        // Get the last score for a subcategory (for initialization only)
        getLastSubcategoryScore(categoryKey, subcategoryKey) {
            if (!this.evaluatingPlayer || !this.evaluations[this.evaluatingPlayer.id]) return 5;
            
            const evaluations = this.evaluations[this.evaluatingPlayer.id]
                .filter(e => e.category === categoryKey && e.subcategory === subcategoryKey)
                .sort((a, b) => new Date(b.evaluated_at) - new Date(a.evaluated_at));
            
            return evaluations.length > 0 ? parseFloat(evaluations[0].score) : 5;
        },
        
        // Create spider chart (always creates new chart)
        createSpiderChart() {
            const canvas = document.getElementById('playerCardSpiderChart');
            
            if (!canvas) {
                setTimeout(() => this.createSpiderChart(), 200);
                return;
            }
            
            if (!this.viewingPlayer) {
                return;
            }
            
            if (canvas.offsetParent === null) {
                setTimeout(() => this.createSpiderChart(), 200);
                return;
            }
            
            if (typeof Chart === 'undefined') {
                setTimeout(() => this.createSpiderChart(), 200);
                return;
            }
            
            const ctx = canvas.getContext('2d');
            
            // Always destroy existing chart first
            if (this.playerCardChart && typeof this.playerCardChart.destroy === 'function') {
                try {
                    this.playerCardChart.destroy();
                    this.playerCardChart = null;
                } catch (e) {
                    this.playerCardChart = null;
                }
            }
            
            const labels = this.evaluationCategories.map(c => c.name);
            const data = this.evaluationCategories.map(c => {
                const avg = this.getPlayerCardCategoryAverage(c.key);
                return parseFloat(avg);
            });
            
            // Determine chart label based on selected date
            let chartLabel = 'Season Average Performance';
            if (this.selectedEvaluationDate !== 'all') {
                const dateObj = new Date(this.selectedEvaluationDate);
                chartLabel = `Performance on ${dateObj.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })}`;
            }
            
            try {
                this.playerCardChart = new Chart(ctx, {
                    type: 'radar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: chartLabel,
                            data: data,
                            fill: true,
                            backgroundColor: 'rgba(249, 115, 22, 0.2)',
                            borderColor: 'rgb(249, 115, 22)',
                            pointBackgroundColor: 'rgb(249, 115, 22)',
                            pointBorderColor: '#fff',
                            pointHoverBackgroundColor: '#fff',
                            pointHoverBorderColor: 'rgb(249, 115, 22)',
                            pointRadius: 4,
                            pointHoverRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: {
                            duration: 300
                        },
                        elements: {
                            line: {
                                borderWidth: 3
                            }
                        },
                        scales: {
                            r: {
                                angleLines: {
                                    display: true
                                },
                                suggestedMin: 0,
                                suggestedMax: 10,
                                ticks: {
                                    stepSize: 2,
                                    font: {
                                        size: 10
                                    }
                                },
                                pointLabels: {
                                    font: {
                                        size: 11
                                    }
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                                labels: {
                                    font: {
                                        size: 12
                                    }
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.parsed.r.toFixed(1) + '/10';
                                    }
                                }
                            }
                        }
                    }
                });
                
            } catch (error) {
                // Error creating chart
            }
        },
        
        // Force update spider chart (destroys and recreates)
        forceUpdateSpiderChart() {
            if (this.playerCardChart) {
                this.playerCardChart.destroy();
                this.playerCardChart = null;
            }
            
            // Small delay to ensure chart is destroyed
            setTimeout(() => {
                this.createSpiderChart();
            }, 100);
        },
        
        async loadEvaluationHistory(player) {
            const formData = new FormData();
            formData.append('action', 'cm_get_evaluations');
            formData.append('player_id', player.id);
            formData.append('team_id', this.selectedTeam.id);
            formData.append('season', this.currentSeason);
            formData.append('nonce', clubManagerAjax.nonce);
            
            const response = await fetch(clubManagerAjax.ajax_url, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Store all evaluations
                const allEvaluations = data.data.evaluations || [];
                this.evaluations[player.id] = allEvaluations;
                
                // Filter for main category scores only (no subcategory)
                this.playerEvaluationHistory = allEvaluations.filter(e => !e.subcategory);
                
                // Get unique evaluation dates
                const dates = [...new Set(allEvaluations.map(e => e.evaluated_at.split(' ')[0]))];
                this.availableEvaluationDates = dates.sort((a, b) => new Date(b) - new Date(a));
                
                // Sort by date descending
                this.playerEvaluationHistory.sort((a, b) => new Date(b.evaluated_at) - new Date(a.evaluated_at));
                
                // Reset selected date to 'all'
                this.selectedEvaluationDate = 'all';
            } else {
                this.playerEvaluationHistory = [];
                this.availableEvaluationDates = [];
            }
        },
        
        getFilteredEvaluationHistory() {
            if (this.selectedEvaluationDate === 'all') {
                return this.playerEvaluationHistory;
            }
            
            return this.playerEvaluationHistory.filter(e => 
                e.evaluated_at.startsWith(this.selectedEvaluationDate)
            );
        },
        
        // Get subcategory evaluations for a specific category and date
        getSubcategoryEvaluations(category, evaluatedAt) {
            if (!this.viewingPlayer || !this.evaluations[this.viewingPlayer.id]) {
                return [];
            }
            
            const evaluationDate = evaluatedAt.split(' ')[0]; // Get just the date part
            
            return this.evaluations[this.viewingPlayer.id].filter(e => 
                e.category === category && 
                e.subcategory && 
                e.evaluated_at.startsWith(evaluationDate)
            );
        },
        
        // Format subcategory name for display
        formatSubcategoryName(subcategoryKey) {
            return subcategoryKey.replace(/_/g, ' ').split(' ').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
        },
        
        onEvaluationDateChange() {
            // Force recreate the chart with new data
            this.forceUpdateSpiderChart();
        },
        
        async loadEvaluations(player) {
            const formData = new FormData();
            formData.append('action', 'cm_get_evaluations');
            formData.append('player_id', player.id);
            formData.append('team_id', this.selectedTeam.id);
            formData.append('season', this.currentSeason);
            formData.append('nonce', clubManagerAjax.nonce);
            
            const response = await fetch(clubManagerAjax.ajax_url, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            if (data.success) {
                this.evaluations[player.id] = data.data.evaluations || [];
            }
        },
        
        async loadPlayerAdvice(player) {
            this.adviceLoading = true;
            
            const formData = new FormData();
            formData.append('action', 'cm_get_player_advice');
            formData.append('player_id', player.id);
            formData.append('team_id', this.selectedTeam.id);
            formData.append('season', this.currentSeason);
            formData.append('nonce', clubManagerAjax.nonce);
            
            const response = await fetch(clubManagerAjax.ajax_url, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            if (data.success) {
                this.playerAdvice = data.data.advice;
                this.adviceStatus = data.data.status || 'no_evaluations';
                this.lastAdviceTimestamp = data.data.generated_at || null;
                
                // Check if we have evaluations but no advice yet
                if (!this.playerAdvice && this.evaluations[player.id] && this.evaluations[player.id].length > 0) {
                    // Toon gewoon de status, NIET automatisch genereren
                    this.adviceStatus = 'no_advice_yet';
                }
            }
            
            this.adviceLoading = false;
        },
 
        async generatePlayerAdvice(player) {
            const formData = new FormData();
            formData.append('action', 'cm_generate_player_advice');
            formData.append('player_id', player.id);
            formData.append('team_id', this.selectedTeam.id);
            formData.append('season', this.currentSeason);
            formData.append('nonce', clubManagerAjax.nonce);
            
            try {
                await fetch(clubManagerAjax.ajax_url, {
                    method: 'POST',
                    body: formData
                });
                
                // Verwijder de oude setTimeout, we gebruiken nu pollForAdvice
                
            } catch (error) {
                // Error generating advice
            }
        },
        
        // EVALUATION MODAL METHODS - these use currentEvaluationScores for the current session
        getCategoryScore(categoryKey) {
            // Not used anymore, but keeping for compatibility
            return 5;
        },
        
        getSubcategoryScore(categoryKey, subcategoryKey) {
            const key = `${categoryKey}_${subcategoryKey}`;
            return this.currentEvaluationScores[key] || 5;
        },
        
        getCategoryAverage(categoryKey) {
            const category = this.evaluationCategories.find(c => c.key === categoryKey);
            if (!category) return '5.0';
            
            let scores = [];
            category.subcategories.forEach(sub => {
                const score = this.getSubcategoryScore(categoryKey, sub.key);
                scores.push(score);
            });
            
            if (scores.length === 0) return '5.0';
            
            const average = scores.reduce((a, b) => a + b, 0) / scores.length;
            return average.toFixed(1);
        },
        
        // PLAYER CARD METHODS - these use historical evaluations data
        getPlayerCardCategoryAverage(categoryKey) {
            const category = this.evaluationCategories.find(c => c.key === categoryKey);
            if (!category || !this.viewingPlayer || !this.evaluations[this.viewingPlayer.id]) {
                return '5.0';
            }
            
            // Filter evaluations based on selected date
            let evaluationsToUse = this.evaluations[this.viewingPlayer.id];
            if (this.selectedEvaluationDate !== 'all') {
                evaluationsToUse = evaluationsToUse.filter(e => 
                    e.evaluated_at.startsWith(this.selectedEvaluationDate)
                );
            }
            
            // Try to get main category evaluations first
            const categoryEvaluations = evaluationsToUse.filter(e => 
                e.category === categoryKey && !e.subcategory
            );
            
            if (categoryEvaluations.length > 0) {
                // Calculate average of main category evaluations
                const sum = categoryEvaluations.reduce((acc, eval) => acc + parseFloat(eval.score), 0);
                const average = sum / categoryEvaluations.length;
                return average.toFixed(1);
            }
            
            // If no main category evaluations, calculate from subcategories
            let scores = [];
            category.subcategories.forEach(sub => {
                // Get evaluations for this subcategory
                const subEvaluations = evaluationsToUse.filter(e => 
                    e.category === categoryKey && e.subcategory === sub.key
                );
                
                if (subEvaluations.length > 0) {
                    if (this.selectedEvaluationDate !== 'all') {
                        // For specific date: use all evaluations from that date
                        const sum = subEvaluations.reduce((acc, eval) => acc + parseFloat(eval.score), 0);
                        scores.push(sum / subEvaluations.length);
                    } else {
                        // For all dates: use the most recent
                        subEvaluations.sort((a, b) => new Date(b.evaluated_at) - new Date(a.evaluated_at));
                        scores.push(parseFloat(subEvaluations[0].score));
                    }
                }
            });
            
            if (scores.length === 0) {
                return '5.0';
            }
            
            const average = scores.reduce((a, b) => a + b, 0) / scores.length;
            return average.toFixed(1);
        },
        
        updateSubcategoryScore(categoryKey, subcategoryKey, score) {
            const key = `${categoryKey}_${subcategoryKey}`;
            this.currentEvaluationScores[key] = parseFloat(score);
        },

        async saveEvaluation() {
            const savePromises = [];
            
            for (const category of this.evaluationCategories) {
                // Calculate and save main category average
                const categoryScore = this.getCategoryAverage(category.key);
                savePromises.push(this.saveEvaluationScore(category.key, null, categoryScore));
                
                // Save subcategory scores
                for (const sub of category.subcategories) {
                    const subScore = this.getSubcategoryScore(category.key, sub.key);
                    savePromises.push(this.saveEvaluationScore(category.key, sub.key, subScore));
                }
            }
            
            // Wait for all saves to complete
            try {
                await Promise.all(savePromises);
                
                // Reset current evaluation scores
                this.currentEvaluationScores = {};
                
                // Reload evaluations to get fresh data from database
                await this.loadEvaluations(this.evaluatingPlayer);
                
                // If player card is open for the same player, refresh that too
                if (this.viewingPlayer && this.viewingPlayer.id === this.evaluatingPlayer.id) {
                    await this.loadEvaluationHistory(this.viewingPlayer);
                    
                    // Force update spider chart with new data
                    this.forceUpdateSpiderChart();
                    
                    // BELANGRIJK: Wis de oude advice en stel status in op generating
                    // We laden NIET de oude advice opnieuw!
                    this.playerAdvice = null;
                    this.adviceStatus = 'generating';
                    this.adviceLoading = false; // Niet true, want we tonen al "generating" status
                    
                    // Force UI update
                    await this.$nextTick();
                    
                    // Start polling voor nieuwe advice NA een delay
                    // Dit geeft de backend tijd om te starten met genereren
                    setTimeout(() => {
                        this.pollForAdvice(this.viewingPlayer);
                    }, 2000);
                }
                
                this.closeEvaluationModal();
                alert('Evaluation saved successfully! AI advice is being generated...');
                
            } catch (error) {
                alert('Error saving evaluation. Please try again.');
            }
        },

        // Voeg deze nieuwe functie toe na saveEvaluation:

        async pollForAdvice(player, attempts = 0) {
            if (!player || attempts > 15) { // Verhoogd naar 15 pogingen (75 seconden)
                this.adviceLoading = false;
                this.adviceStatus = 'generation_failed';
                return;
            }
            
            // Check of we nog steeds dezelfde speler bekijken
            if (!this.viewingPlayer || this.viewingPlayer.id !== player.id) {
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'cm_get_player_advice');
            formData.append('player_id', player.id);
            formData.append('team_id', this.selectedTeam.id);
            formData.append('season', this.currentSeason);
            formData.append('nonce', clubManagerAjax.nonce);
            
            try {
                const response = await fetch(clubManagerAjax.ajax_url, {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success && data.data.advice) {
                    // Controleer of dit nieuwe advice is (check generated_at timestamp)
                    const isNewAdvice = !this.lastAdviceTimestamp || 
                                       data.data.generated_at !== this.lastAdviceTimestamp;
                    
                    if (isNewAdvice) {
                        // Nieuwe advice gevonden!
                        this.playerAdvice = data.data.advice;
                        this.adviceStatus = 'current';
                        this.adviceLoading = false;
                        this.lastAdviceTimestamp = data.data.generated_at;
                    } else {
                        // Dit is nog de oude advice, blijf pollen
                        setTimeout(() => {
                            this.pollForAdvice(player, attempts + 1);
                        }, 5000);
                    }
                } else {
                    // Geen advice gevonden, blijf pollen
                    setTimeout(() => {
                        this.pollForAdvice(player, attempts + 1);
                    }, 5000);
                }
            } catch (error) {
                this.adviceLoading = false;
                this.adviceStatus = 'error';
            }
        },
      
        async saveEvaluationScore(category, subcategory, score) {
            const formData = new FormData();
            formData.append('action', 'cm_save_evaluation');
            formData.append('player_id', this.evaluatingPlayer.id);
            formData.append('team_id', this.selectedTeam.id);
            formData.append('season', this.currentSeason);
            formData.append('category', category);
            if (subcategory) formData.append('subcategory', subcategory);
            formData.append('score', score);
            formData.append('notes', this.evaluationNotes);
            formData.append('nonce', clubManagerAjax.nonce);
            
            const response = await fetch(clubManagerAjax.ajax_url, {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            if (!result.success) {
                throw new Error('Failed to save evaluation');
            }
            
            return result;
        },
        
        closeEvaluationModal() {
            this.showEvaluationModal = false;
            this.evaluatingPlayer = null;
            this.evaluationNotes = '';
            this.currentEvaluationScores = {};
        },

        // Player History Methods
        async viewPlayerHistory(playerId) {
            const player = this.teamPlayers.find(p => p.id == playerId);
            if (!player) return;
            
            this.showPlayerHistoryModal = true;
            this.historyLoading = true;
            this.playerHistory = [];
            this.historyPlayer = player;
            
            const formData = new FormData();
            formData.append('action', 'cm_get_player_history');
            formData.append('player_id', playerId);
            formData.append('nonce', clubManagerAjax.nonce);
            
            try {
                const response = await fetch(clubManagerAjax.ajax_url, {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                if (data.success) {
                    this.playerHistory = data.data.history;
                    this.historyPlayer = data.data.player;
                }
            } catch (error) {
                // Error loading player history
            } finally {
                this.historyLoading = false;
            }
        },

        handleHistoryClick(playerId) {
            this.viewPlayerHistory(playerId);
        },
        
        // Download Player Card as PDF
        async downloadPlayerCardPDF(event) {
            if (!this.viewingPlayer) return;
            
            let button = null;
            let originalContent = '';
            
            try {
                // Check if jsPDF is loaded
                if (typeof window.jspdf === 'undefined') {
                    alert('PDF library not loaded. Please refresh the page and try again.');
                    return;
                }
                
                // Get button reference
                if (event && event.target) {
                    button = event.target.closest('button');
                }
                
                // Show loading state if button exists
                if (button) {
                    originalContent = button.innerHTML;
                    button.innerHTML = '<svg class="animate-spin h-5 w-5 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
                    button.disabled = true;
                }
                
                // Create PDF directly without html2canvas
                const { jsPDF } = window.jspdf;
                const pdf = new jsPDF('p', 'mm', 'a4');
                
                // Colors
                const orangeColor = [255, 152, 0];
                const darkGray = [31, 41, 55];
                const mediumGray = [107, 114, 128];
                const lightGray = [229, 231, 235];
                
                // Dimensions
                const pageWidth = pdf.internal.pageSize.getWidth();
                const pageHeight = pdf.internal.pageSize.getHeight();
                const margin = 20;
                const contentWidth = pageWidth - (margin * 2);
                let yPosition = 20;
                
                // Title
                const playerName = `${this.viewingPlayer.first_name} ${this.viewingPlayer.last_name}`;
                pdf.setFontSize(24);
                pdf.setTextColor(...orangeColor);
                pdf.text(playerName, pageWidth / 2, yPosition, { align: 'center' });
                yPosition += 10;
                
                // Subtitle
                pdf.setFontSize(14);
                pdf.setTextColor(...mediumGray);
                pdf.text(`${this.selectedTeam.name} - ${this.currentSeason}`, pageWidth / 2, yPosition, { align: 'center' });
                yPosition += 15;
                
                // Player Info Box
                pdf.setDrawColor(...lightGray);
                pdf.setFillColor(255, 243, 224); // Orange-50
                pdf.roundedRect(margin, yPosition, contentWidth, 25, 3, 3, 'FD');
                
                pdf.setFontSize(12);
                pdf.setTextColor(...darkGray);
                pdf.text(`Position: ${this.viewingPlayer.position || 'Not assigned'}`, margin + 5, yPosition + 8);
                pdf.text(`Jersey #: ${this.viewingPlayer.jersey_number || '-'}`, margin + 60, yPosition + 8);
                pdf.text(`Email: ${this.viewingPlayer.email}`, margin + 5, yPosition + 18);
                pdf.text(`Birth Date: ${this.viewingPlayer.birth_date}`, margin + 100, yPosition + 18);
                yPosition += 35;
                
                // Notes if available
                if (this.viewingPlayer.notes) {
                    pdf.setFontSize(12);
                    pdf.setTextColor(...darkGray);
                    pdf.setFont(undefined, 'bold');
                    pdf.text('Notes:', margin, yPosition);
                    pdf.setFont(undefined, 'normal');
                    yPosition += 7;
                    
                    const splitNotes = pdf.splitTextToSize(this.viewingPlayer.notes, contentWidth);
                    pdf.text(splitNotes, margin, yPosition);
                    yPosition += splitNotes.length * 5 + 10;
                }
                
                // Performance Scores
                pdf.setFontSize(16);
                pdf.setTextColor(...orangeColor);
                pdf.setFont(undefined, 'bold');
                pdf.text('Performance Evaluation', margin, yPosition);
                pdf.setFont(undefined, 'normal');
                yPosition += 10;
                
                // Draw evaluation scores
                const categories = this.evaluationCategories;
                pdf.setFontSize(11);
                
                categories.forEach((category, index) => {
                    if (yPosition > pageHeight - 40) {
                        pdf.addPage();
                        yPosition = 20;
                    }
                    
                    const score = this.getPlayerCardCategoryAverage(category.key);
                    const scoreFloat = parseFloat(score);
                    
                    // Category name
                    pdf.setTextColor(...darkGray);
                    pdf.text(category.name, margin, yPosition);
                    
                    // Score
                    pdf.setTextColor(...orangeColor);
                    pdf.text(`${score}/10`, margin + 80, yPosition);
                    
                    // Progress bar
                    pdf.setDrawColor(...lightGray);
                    pdf.setFillColor(...lightGray);
                    pdf.rect(margin + 110, yPosition - 4, 50, 5, 'F');
                    
                    // Fill based on score
                    if (scoreFloat >= 7) {
                        pdf.setFillColor(34, 197, 94); // Green
                    } else if (scoreFloat >= 5) {
                        pdf.setFillColor(...orangeColor);
                    } else {
                        pdf.setFillColor(239, 68, 68); // Red
                    }
                    pdf.rect(margin + 110, yPosition - 4, (scoreFloat / 10) * 50, 5, 'F');
                    
                    yPosition += 8;
                });
                
                yPosition += 10;
                
                // AI Advice
                if (this.playerAdvice && this.adviceStatus !== 'no_evaluations') {
                    if (yPosition > pageHeight - 60) {
                        pdf.addPage();
                        yPosition = 20;
                    }
                    
                    pdf.setFontSize(16);
                    pdf.setTextColor(...orangeColor);
                    pdf.setFont(undefined, 'bold');
                    pdf.text('AI Coaching Advice', margin, yPosition);
                    pdf.setFont(undefined, 'normal');
                    yPosition += 10;
                    
                    pdf.setFontSize(10);
                    pdf.setTextColor(...darkGray);
                    const splitAdvice = pdf.splitTextToSize(this.playerAdvice, contentWidth);
                    pdf.text(splitAdvice, margin, yPosition);
                    yPosition += splitAdvice.length * 4 + 10;
                }
                
                // Footer
                pdf.setFontSize(8);
                pdf.setTextColor(...mediumGray);
                pdf.text(`Generated: ${new Date().toLocaleDateString()} at ${new Date().toLocaleTimeString()}`, pageWidth / 2, pageHeight - 10, { align: 'center' });
                
                // Save the PDF
                const fileName = `${playerName.replace(/\s+/g, '_')}_${this.currentSeason}_PlayerCard.pdf`;
                pdf.save(fileName);
                
                // Restore button if it exists
                if (button) {
                    button.innerHTML = originalContent;
                    button.disabled = false;
                }
                
            } catch (error) {
                alert('Error generating PDF: ' + error.message);
                
                // Restore button if it exists
                if (button && originalContent) {
                    button.innerHTML = originalContent;
                    button.disabled = false;
                }
            }
        }
    }));
});