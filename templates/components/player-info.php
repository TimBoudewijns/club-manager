<?php
// File: templates/components/player-info.php
?>
<div class="flex-1">
    <div class="flex items-center mb-6">
        <div class="flex-shrink-0 h-20 w-20 bg-gradient-to-br from-orange-400 to-orange-600 rounded-full flex items-center justify-center text-white font-bold text-2xl shadow-lg">
            <span x-text="(viewingPlayer?.first_name ? viewingPlayer.first_name.charAt(0) : '') + (viewingPlayer?.last_name ? viewingPlayer.last_name.charAt(0) : '')"></span>
        </div>
        <div class="ml-6">
            <h3 class="text-2xl font-bold text-gray-900" x-text="viewingPlayer?.first_name + ' ' + viewingPlayer?.last_name"></h3>
            <p class="text-gray-600" x-text="selectedTeam?.name"></p>
            <div class="flex items-center mt-2 space-x-4 text-sm">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                    <span x-text="viewingPlayer?.position || 'Not assigned'"></span>
                </span>
                <span class="inline-flex items-center">
                    <span class="font-medium text-gray-500">Jersey #</span>
                    <span class="ml-1 font-bold text-gray-900" x-text="viewingPlayer?.jersey_number || '-'"></span>
                </span>
            </div>
        </div>
    </div>
    
    <!-- Player Details -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-lg p-4 shadow-sm">
            <p class="text-sm text-gray-500">Email</p>
            <p class="font-medium text-gray-900" x-text="viewingPlayer?.email"></p>
        </div>
        <div class="bg-white rounded-lg p-4 shadow-sm">
            <p class="text-sm text-gray-500">Birth Date</p>
            <p class="font-medium text-gray-900" x-text="viewingPlayer?.birth_date"></p>
        </div>
    </div>
    
    <!-- Notes -->
    <div x-show="viewingPlayer?.notes" class="bg-white rounded-lg p-4 shadow-sm">
        <p class="text-sm text-gray-500 mb-2">Notes</p>
        <p class="text-gray-900" x-text="viewingPlayer?.notes"></p>
    </div>
    
    <!-- Evaluation History -->
    <div class="mt-6">
        <div class="flex items-center justify-between mb-3">
            <h4 class="text-lg font-semibold text-gray-900">Evaluation History</h4>
            <select x-model="selectedEvaluationDate" 
                    @change="onEvaluationDateChange"
                    x-show="availableEvaluationDates.length > 0"
                    class="select select-sm select-bordered bg-white border-gray-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 rounded-lg">
                <option value="all">All Evaluations</option>
                <template x-for="date in availableEvaluationDates" :key="date">
                    <option :value="date" x-text="new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })"></option>
                </template>
            </select>
        </div>
        <div class="bg-white rounded-lg p-4 shadow-sm">
            <div x-show="getFilteredEvaluationHistory().length > 0" class="max-h-96 overflow-y-auto space-y-4">
                <template x-for="(eval, index) in getFilteredEvaluationHistory()" :key="index">
                    <div class="border-l-4 border-orange-300 pl-4 pb-4">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium text-gray-700" x-text="eval.category.replace(/_/g, ' ').split(' ').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ')"></span>
                            <span class="text-sm text-gray-500" x-text="new Date(eval.evaluated_at).toLocaleDateString()"></span>
                        </div>
                        <div class="flex items-center mb-3">
                            <span class="text-lg font-bold text-orange-600" x-text="parseFloat(eval.score).toFixed(1)"></span>
                            <span class="text-sm text-gray-500 ml-2">/10</span>
                        </div>
                        
                        <!-- Show subcategory scores for this evaluation -->
                        <div class="space-y-2" x-data="{ subcategories: getSubcategoryEvaluations(eval.category, eval.evaluated_at) }">
                            <template x-for="subEval in subcategories" :key="subEval.subcategory">
                                <div class="flex justify-between items-center text-xs bg-gray-50 rounded px-2 py-1">
                                    <span class="text-gray-600" x-text="formatSubcategoryName(subEval.subcategory)"></span>
                                    <span class="font-medium text-gray-700" x-text="parseFloat(subEval.score).toFixed(1) + '/10'"></span>
                                </div>
                            </template>
                        </div>
                        
                        <!-- Notes if available -->
                        <div x-show="eval.notes && eval.notes.trim()" class="mt-2 text-xs text-gray-600 italic" x-text="eval.notes"></div>
                    </div>
                </template>
            </div>
            <div x-show="getFilteredEvaluationHistory().length === 0" class="text-gray-500 text-center py-4">
                <span x-show="selectedEvaluationDate === 'all'">No evaluations yet</span>
                <span x-show="selectedEvaluationDate !== 'all'">No evaluations for this date</span>
            </div>
        </div>
    </div>
</div>