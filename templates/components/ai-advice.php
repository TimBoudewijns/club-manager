<?php
// File: templates/components/ai-advice.php
?>
<div class="mt-8">
    <h4 class="text-lg font-semibold text-gray-900 mb-3">AI Coaching Advice</h4>
    <div class="bg-white rounded-lg p-6 shadow-sm">
        <!-- No evaluations state -->
        <div x-show="adviceStatus === 'no_evaluations' && !playerAdvice" class="text-center py-8">
            <div class="bg-gray-50 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                </svg>
            </div>
            <p class="text-gray-600">Complete an evaluation first to receive personalized coaching advice.</p>
        </div>
        
        <!-- No advice yet but has evaluations -->
        <div x-show="adviceStatus === 'no_advice_yet' && !playerAdvice" class="text-center py-8">
            <div class="bg-blue-50 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <p class="text-gray-600">AI advice will be generated after your next evaluation.</p>
        </div>
        
        <!-- Loading/Generating state -->
        <div x-show="adviceLoading || adviceStatus === 'generating'" class="text-center py-8">
            <div class="bg-orange-50 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center animate-pulse">
                <svg class="w-8 h-8 text-orange-500 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
            <p class="text-gray-600">Generating personalized advice based on recent evaluations...</p>
            <p class="text-sm text-gray-500 mt-2">This may take up to a minute.</p>
        </div>
        
        <!-- Generation failed -->
        <div x-show="adviceStatus === 'generation_failed'" class="text-center py-8">
            <div class="bg-red-50 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                <svg class="w-8 h-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <p class="text-gray-600">AI advice generation timed out. Please try evaluating again.</p>
        </div>
        
        <!-- Error state -->
        <div x-show="adviceStatus === 'error'" class="text-center py-8">
            <div class="bg-red-50 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                <svg class="w-8 h-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </div>
            <p class="text-gray-600">An error occurred while loading advice. Please try again later.</p>
        </div>
        
        <!-- Advice content -->
        <div x-show="playerAdvice && !adviceLoading && adviceStatus === 'current'" class="prose prose-sm max-w-none">
            <div class="whitespace-pre-wrap text-gray-800" x-text="playerAdvice"></div>
            <div class="mt-4 pt-4 border-t border-gray-200">
                <p class="text-xs text-gray-500">
                    <span x-show="lastAdviceTimestamp">Last updated: <span x-text="new Date(lastAdviceTimestamp).toLocaleString()"></span></span>
                </p>
            </div>
        </div>
    </div>
</div>