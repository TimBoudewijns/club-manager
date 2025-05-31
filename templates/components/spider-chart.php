<?php
// File: templates/components/spider-chart.php
?>
<div class="flex-1 bg-white rounded-xl p-6 shadow-lg">
    <div class="flex items-center justify-between mb-4">
        <h4 class="text-lg font-semibold text-gray-900">Performance Overview</h4>
        <span x-show="selectedEvaluationDate !== 'all'" 
              class="text-sm text-orange-600 font-medium">
            <span x-text="new Date(selectedEvaluationDate).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })"></span>
        </span>
    </div>
    <div class="relative" style="height: 400px; min-height: 400px;">
        <canvas id="playerCardSpiderChart" style="display: block; width: 100%; height: 100%;"></canvas>
    </div>
    
    <!-- Category Scores -->
    <div class="mt-6 grid grid-cols-2 gap-2 text-sm max-h-48 overflow-y-auto">
        <template x-for="category in evaluationCategories" :key="category.key">
            <div class="flex justify-between items-center py-1 px-2 rounded hover:bg-gray-50">
                <span class="text-gray-600 text-xs" x-text="category.name"></span>
                <span class="font-bold text-orange-600" x-text="getPlayerCardCategoryAverage(category.key)"></span>
            </div>
        </template>
    </div>
</div>