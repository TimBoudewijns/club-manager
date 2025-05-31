<?php
// File: templates/components/player-card.php
?>
<div id="playerCardContent" class="bg-gradient-to-br from-orange-50 to-amber-50 rounded-2xl shadow-xl p-6 md:p-8 border-2 border-orange-200">
    <!-- Download PDF Button -->
    <div class="flex justify-end mb-4">
        <button @click="downloadPlayerCardPDF()" 
                class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 px-4 rounded-lg shadow-md transform transition-all duration-200 hover:scale-105 flex items-center space-x-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <span>Download PDF</span>
        </button>
    </div>
    
    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Player Info Section -->
        <?php include CLUB_MANAGER_PLUGIN_DIR . 'templates/components/player-info.php'; ?>
        
        <!-- Spider Chart Section -->
        <?php include CLUB_MANAGER_PLUGIN_DIR . 'templates/components/spider-chart.php'; ?>
    </div>
    
    <!-- AI Coaching Advice -->
    <?php include CLUB_MANAGER_PLUGIN_DIR . 'templates/components/ai-advice.php'; ?>
</div>