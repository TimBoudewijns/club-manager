<?php
// File: templates/tabs/club-teams.php
?>

<!-- Club Teams Info -->
<div class="mb-8 bg-blue-50 border border-blue-200 rounded-xl p-6">
    <div class="flex items-start space-x-4">
        <div class="flex-shrink-0">
            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <div>
            <h3 class="text-lg font-semibold text-blue-900 mb-1">Club Teams Overview</h3>
            <p class="text-blue-700 text-sm">As a club owner/manager, you can view all teams from members in your club. You have read-only access to player cards and history.</p>
        </div>
    </div>
</div>

<!-- Club Teams by Coach -->
<?php include CLUB_MANAGER_PLUGIN_DIR . 'templates/components/club-teams-list.php'; ?>

<!-- Club Team Details Section -->
<div x-show="selectedClubTeam" x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform translate-y-4"
     x-transition:enter-end="opacity-100 transform translate-y-0"
     class="mt-8">
    <?php include CLUB_MANAGER_PLUGIN_DIR . 'templates/components/club-team-details.php'; ?>
</div>

<!-- Club Player Card -->
<?php include CLUB_MANAGER_PLUGIN_DIR . 'includes/club-player-card.php'; ?>