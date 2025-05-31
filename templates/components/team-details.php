<?php
// File: templates/components/team-details.php
?>
<div class="bg-white rounded-2xl shadow-xl overflow-hidden border-t-4 border-orange-500">
    <!-- Team Header -->
    <div class="bg-white p-4 md:p-8">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2" x-text="selectedTeam?.name"></h2>
                <p class="text-gray-600 text-sm md:text-base">Team Roster Management</p>
            </div>
            <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3 w-full md:w-auto">
                <button type="button"
                        @click="showAddPlayerModal = true" 
                        class="bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white font-bold py-2 px-4 md:px-6 rounded-lg shadow-md transform transition-all duration-200 hover:scale-105 flex items-center justify-center space-x-2">
                    <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                    </svg>
                    <span>Add New Player</span>
                </button>
                <button @click="showAddExistingPlayerModal = true" 
                        class="bg-orange-100 hover:bg-orange-200 text-orange-700 font-bold py-2 px-4 md:px-6 rounded-lg shadow-md transform transition-all duration-200 hover:scale-105 flex items-center justify-center space-x-2">
                    <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Add Existing Player</span>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Players Table -->
    <div class="p-4 md:p-8">
        <?php include CLUB_MANAGER_PLUGIN_DIR . 'templates/components/players-table.php'; ?>
    </div>
</div>