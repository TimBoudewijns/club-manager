<?php
// File: templates/tabs/my-teams.php
?>

<!-- Add Team Button -->
<div class="mb-8">
    <button @click="showCreateTeamModal = true" 
            class="bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white font-bold py-3 px-8 rounded-xl shadow-lg transform transition-all duration-200 hover:scale-105 flex items-center space-x-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        <span>Create New Team</span>
    </button>
</div>

<!-- Teams Grid -->
<?php include CLUB_MANAGER_PLUGIN_DIR . 'templates/components/teams-grid.php'; ?>

<!-- Team Details Section -->
<div x-show="selectedTeam" x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform translate-y-4"
     x-transition:enter-end="opacity-100 transform translate-y-0"
     class="mt-8">
    <?php include CLUB_MANAGER_PLUGIN_DIR . 'templates/components/team-details.php'; ?>
</div>

<!-- Player Card Display -->
<div x-show="selectedPlayerCard && selectedPlayerCard.id === selectedTeam.id && viewingPlayer" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform translate-y-4"
     x-transition:enter-end="opacity-100 transform translate-y-0"
     class="mt-8 mb-8">
    <?php include CLUB_MANAGER_PLUGIN_DIR . 'templates/components/player-card.php'; ?>
</div>