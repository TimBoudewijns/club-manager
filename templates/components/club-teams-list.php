<?php
// File: templates/components/club-teams-list.php
?>
<div x-show="clubTeamsData.length > 0" class="space-y-8">
    <template x-for="coachData in clubTeamsData" :key="coachData.coach_id">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <!-- Coach Header -->
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-bold" x-text="coachData.coach_name"></h3>
                        <p class="text-blue-100 mt-1" x-text="coachData.coach_email"></p>
                    </div>
                    <div class="bg-blue-400 bg-opacity-50 rounded-full px-4 py-2">
                        <span class="font-semibold" x-text="coachData.teams.length + ' team' + (coachData.teams.length !== 1 ? 's' : '')"></span>
                    </div>
                </div>
            </div>
            
            <!-- Teams Grid -->
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <template x-for="team in coachData.teams" :key="team.id">
                        <div @click="selectClubTeam(team)" 
                             class="bg-gray-50 rounded-xl shadow-md hover:shadow-xl transform transition-all duration-300 hover:scale-105 cursor-pointer overflow-hidden group">
                            <div class="bg-gradient-to-r from-blue-400 to-blue-500 h-2 group-hover:h-3 transition-all duration-300"></div>
                            <div class="p-6">
                                <h4 class="text-xl font-bold text-gray-900 group-hover:text-blue-600 transition-colors mb-2" x-text="team.name"></h4>
                                <div class="space-y-2 text-sm">
                                    <div class="flex items-center text-gray-600">
                                        <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                        <span>Coach: <span x-text="team.coach"></span></span>
                                    </div>
                                    <div class="flex items-center text-gray-600">
                                        <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <span x-text="team.season"></span>
                                    </div>
                                </div>
                                <div class="mt-4 pt-4 border-t border-gray-200">
                                    <span class="text-sm text-gray-500">Click to view players →</span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </template>
</div>

<!-- Empty State -->
<div x-show="clubTeamsData.length === 0" class="text-center py-16">
    <div class="bg-gray-50 rounded-full w-24 h-24 mx-auto mb-6 flex items-center justify-center">
        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
        </svg>
    </div>
    <h3 class="text-2xl font-bold text-gray-900 mb-2">No club teams found</h3>
    <p class="text-gray-600">No teams have been created by members of your club yet.</p>
</div>