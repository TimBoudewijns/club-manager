<?php

class Club_Manager_Shortcode {
    
    public static function init() {
        add_shortcode('club_manager', array(__CLASS__, 'render_shortcode'));
        add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_scripts'));
    }
    
    public static function enqueue_scripts() {
        global $post;
        
        // Only enqueue if shortcode is present
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'club_manager')) {
            // FIRST: Add inline CSS that loads before everything else
            wp_add_inline_style('wp-block-library', '
                /* DaisyUI Theme Override - Must load before DaisyUI */
                :root {
                    --p: 25 95% 53%;  /* Primary color: #f97316 */
                    --pf: 27 96% 48%; /* Primary focus: #ea580c */
                    --pc: 0 0% 100%;   /* Primary content: white */
                }
                
                [data-theme="light"] {
                    --p: 25 95% 53%;
                    --pf: 27 96% 48%;
                    --pc: 0 0% 100%;
                }
            ');
            
            // Enqueue Tailwind CSS via CDN
            wp_enqueue_script(
                'tailwind-css',
                'https://cdn.tailwindcss.com',
                array(),
                '3.4.0',
                false
            );
            
            // Enqueue DaisyUI
            wp_enqueue_style(
                'daisyui',
                'https://cdn.jsdelivr.net/npm/daisyui@4.6.0/dist/full.min.css',
                array(),
                '4.6.0'
            );
            
            // Enqueue Chart.js
            wp_enqueue_script(
                'chartjs',
                'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js',
                array(),
                '4.4.0',
                true
            );
            
            // Enqueue jsPDF
            wp_enqueue_script(
                'jspdf',
                'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js',
                array(),
                '2.5.1',
                true
            );
            
            // Enqueue html2canvas
            wp_enqueue_script(
                'html2canvas',
                'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js',
                array(),
                '1.4.1',
                true
            );
            
            // Enqueue custom CSS
            wp_enqueue_style(
                'club-manager-styles',
                CLUB_MANAGER_PLUGIN_URL . 'assets/css/club-manager-styles.css',
                array('daisyui'),
                CLUB_MANAGER_VERSION
            );
            
            // Enqueue custom JS file with jQuery dependency  
            wp_enqueue_script(
                'club-manager-frontend',
                CLUB_MANAGER_PLUGIN_URL . 'assets/js/club-manager-frontend.js',
                array('jquery'),
                CLUB_MANAGER_VERSION,
                true
            );
            
            // Localize the AJAX data
            wp_localize_script('club-manager-frontend', 'clubManagerAjax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('club_manager_nonce'),
                'user_id' => get_current_user_id(),
                'is_logged_in' => is_user_logged_in(),
                'preferred_season' => get_user_meta(get_current_user_id(), 'cm_preferred_season', true) ?: '2024-2025'
            ));
            
            // Enqueue custom JS file
            wp_enqueue_script(
                'club-manager-frontend',
                CLUB_MANAGER_PLUGIN_URL . 'assets/js/club-manager-frontend.js',
                array('jquery'),
                CLUB_MANAGER_VERSION,
                true
            );
            
            // Enqueue Alpine.js
            wp_enqueue_script(
                'alpinejs',
                'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js',
                array(),
                '3.x.x',
                true
            );
        }
    }
    
    public static function render_shortcode($atts) {
        // Check if user is logged in
        if (!is_user_logged_in()) {
            return '<div class="bg-gradient-to-r from-orange-50 to-amber-50 rounded-xl p-8 shadow-lg border border-orange-200">
                <div class="flex items-center space-x-4">
                    <div class="bg-orange-100 rounded-full p-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">Login Required</h3>
                        <p class="text-gray-600 mt-1">Please log in to access the Club Manager dashboard.</p>
                    </div>
                </div>
            </div>';
        }
        
        ob_start();
        ?>
        <style>
            /* Critical CSS - Load correct orange colors IMMEDIATELY */
            .club-manager-app .bg-orange-50 { background-color: #fff7ed !important; }
            .club-manager-app .bg-orange-100 { background-color: #ffedd5 !important; }
            .club-manager-app .bg-orange-200 { background-color: #fed7aa !important; }
            .club-manager-app .bg-orange-300 { background-color: #fdba74 !important; }
            .club-manager-app .bg-orange-400 { background-color: #fb923c !important; }
            .club-manager-app .bg-orange-500 { background-color: #f97316 !important; }
            .club-manager-app .bg-orange-600 { background-color: #ea580c !important; }
            .club-manager-app .bg-orange-700 { background-color: #c2410c !important; }
            .club-manager-app .bg-orange-800 { background-color: #9a3412 !important; }
            
            .club-manager-app .text-orange-400 { color: #fb923c !important; }
            .club-manager-app .text-orange-500 { color: #f97316 !important; }
            .club-manager-app .text-orange-600 { color: #ea580c !important; }
            .club-manager-app .text-orange-700 { color: #c2410c !important; }
            .club-manager-app .text-orange-800 { color: #9a3412 !important; }
            
            .club-manager-app .border-orange-200 { border-color: #fed7aa !important; }
            .club-manager-app .border-orange-300 { border-color: #fdba74 !important; }
            .club-manager-app .border-orange-500 { border-color: #f97316 !important; }
            
            /* Gradients with correct colors */
            .club-manager-app .from-orange-50 { --tw-gradient-from: #fff7ed !important; }
            .club-manager-app .from-orange-400 { --tw-gradient-from: #fb923c !important; }
            .club-manager-app .from-orange-500 { --tw-gradient-from: #f97316 !important; }
            .club-manager-app .to-orange-500 { --tw-gradient-to: #f97316 !important; }
            .club-manager-app .to-orange-600 { --tw-gradient-to: #ea580c !important; }
            
            /* Button gradients */
            .club-manager-app .bg-gradient-to-r.from-orange-500.to-orange-600 {
                background: linear-gradient(to right, #f97316, #ea580c) !important;
            }
            
            /* DaisyUI button overrides */
            .club-manager-app .btn-primary,
            .club-manager-app .btn {
                --btn-color-primary: #f97316 !important;
                background-color: #f97316 !important;
                border-color: #f97316 !important;
            }
            
            .club-manager-app .btn-primary:hover,
            .club-manager-app .btn:hover {
                background-color: #ea580c !important;
                border-color: #ea580c !important;
            }
            
            /* Hover states */
            .club-manager-app .hover\:bg-orange-50:hover { background-color: #fff7ed !important; }
            .club-manager-app .hover\:bg-orange-100:hover { background-color: #ffedd5 !important; }
            .club-manager-app .hover\:bg-orange-200:hover { background-color: #fed7aa !important; }
            .club-manager-app .hover\:bg-orange-600:hover { background-color: #ea580c !important; }
            .club-manager-app .hover\:bg-orange-700:hover { background-color: #c2410c !important; }
            .club-manager-app .hover\:text-orange-600:hover { color: #ea580c !important; }
            .club-manager-app .hover\:text-orange-900:hover { color: #7c2d12 !important; }
            
            /* Focus states */
            .club-manager-app .focus\:border-orange-500:focus { border-color: #f97316 !important; }
            .club-manager-app .focus\:ring-orange-200:focus { --tw-ring-color: #fed7aa !important; }
            
            /* Checkbox and radio buttons */
            .club-manager-app [type="checkbox"]:checked,
            .club-manager-app [type="radio"]:checked {
                background-color: #f97316 !important;
                border-color: #f97316 !important;
            }
            
            /* Range inputs */
            .club-manager-app .range-orange::-webkit-slider-thumb,
            .club-manager-app .range::-webkit-slider-thumb {
                background-color: #f97316 !important;
            }
            
            .club-manager-app .range-orange::-moz-range-thumb,
            .club-manager-app .range::-moz-range-thumb {
                background-color: #f97316 !important;
            }
        </style>
        <script>
            // Wait for Tailwind to be available, then configure it
            function configureTailwind() {
                if (typeof tailwind !== 'undefined') {
                    tailwind.config = {
                        darkMode: 'class',
                        theme: {
                            extend: {
                                colors: {
                                    orange: {
                                        50: '#fff7ed',
                                        100: '#ffedd5',
                                        200: '#fed7aa',
                                        300: '#fdba74',
                                        400: '#fb923c',
                                        500: '#f97316',
                                        600: '#ea580c',
                                        700: '#c2410c',
                                        800: '#9a3412',
                                        900: '#7c2d12',
                                        950: '#431407'
                                    }
                                }
                            }
                        }
                    };
                } else {
                    // If Tailwind is not ready, try again in 100ms
                    setTimeout(configureTailwind, 100);
                }
            }
            
            // Start configuration attempt
            configureTailwind();
        </script>
        <div class="club-manager-app min-h-screen bg-white" x-data="clubManager()" data-theme="light">
            <div class="w-full px-4 md:px-6 lg:px-8 py-8">
                <!-- Header Section -->
                <div class="bg-white rounded-2xl shadow-xl p-4 md:p-8 mb-8 border-t-4 border-orange-500">
                    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                        <div>
                            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">Club Manager</h1>
                            <p class="text-gray-600 text-sm md:text-base">Manage your hockey teams and players efficiently</p>
                        </div>
                        <div class="flex items-center space-x-4">
                            <div class="relative">
                                <label class="text-sm font-medium text-gray-700 mb-1 block">Season</label>
                                <select x-model="currentSeason" @change="changeSeason" 
                                    class="select select-bordered bg-white border-gray-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 rounded-lg px-4 py-2 pr-10 appearance-none cursor-pointer">
                                    <option value="2024-2025">2024-2025</option>
                                    <option value="2025-2026">2025-2026</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none mt-6">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Tabs Section -->
                <div class="bg-white rounded-xl shadow-md p-1 md:p-2 mb-8 overflow-x-auto">
                    <div class="flex items-center">
                        <div class="flex space-x-1 md:space-x-2 min-w-fit">
                            <button class="flex-1 md:flex-none py-2 md:py-3 px-3 md:px-6 rounded-lg font-semibold transition-all duration-200 whitespace-nowrap text-sm md:text-base"
                                    :class="activeTab === 'my-teams' ? 'bg-gradient-to-r from-orange-500 to-orange-600 text-white shadow-lg' : 'text-gray-600 hover:text-orange-600 hover:bg-orange-50'"
                                    @click="activeTab = 'my-teams'">
                                <span class="flex items-center justify-center space-x-1 md:space-x-2">
                                    <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                    <span>My Teams</span>
                                </span>
                            </button>
                            <button class="flex-1 md:flex-none py-2 md:py-3 px-3 md:px-6 rounded-lg font-semibold transition-all duration-200 whitespace-nowrap text-sm md:text-base"
                                    :class="activeTab === 'club-teams' ? 'bg-gradient-to-r from-orange-500 to-orange-600 text-white shadow-lg' : 'text-gray-600 hover:text-orange-600 hover:bg-orange-50'"
                                    @click="activeTab = 'club-teams'">
                                <span class="flex items-center justify-center space-x-1 md:space-x-2">
                                    <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                    <span>Club Teams</span>
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- My Teams Tab -->
                <div x-show="activeTab === 'my-teams'" x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform scale-95"
                     x-transition:enter-end="opacity-100 transform scale-100">
                    
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
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                        <template x-for="team in teams" :key="team.id">
                            <div @click="selectTeam(team)" 
                                 class="bg-white rounded-xl shadow-lg hover:shadow-2xl transform transition-all duration-300 hover:scale-105 cursor-pointer overflow-hidden group">
                                <div class="bg-gradient-to-r from-orange-400 to-orange-500 h-2 group-hover:h-3 transition-all duration-300"></div>
                                <div class="p-6">
                                    <div class="flex items-start justify-between mb-4">
                                        <h3 class="text-2xl font-bold text-gray-900 group-hover:text-orange-600 transition-colors" x-text="team.name"></h3>
                                        <div class="bg-orange-100 text-orange-600 px-3 py-1 rounded-full text-sm font-semibold">
                                            <span x-text="team.season"></span>
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <div class="flex items-center text-gray-600">
                                            <svg class="w-5 h-5 mr-2 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                            <span class="font-medium">Coach:</span>
                                            <span class="ml-2" x-text="team.coach"></span>
                                        </div>
                                    </div>
                                    <div class="mt-4 pt-4 border-t border-gray-100">
                                        <span class="text-sm text-gray-500">Click to manage players →</span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                    
                    <!-- Empty State -->
                    <div x-show="teams.length === 0" class="text-center py-16">
                        <div class="bg-orange-50 rounded-full w-24 h-24 mx-auto mb-6 flex items-center justify-center">
                            <svg class="w-12 h-12 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">No teams yet</h3>
                        <p class="text-gray-600 mb-6">Create your first team to get started managing your players.</p>
                        <button @click="showCreateTeamModal = true" 
                                class="bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white font-bold py-3 px-8 rounded-xl shadow-lg transform transition-all duration-200 hover:scale-105">
                            Create Your First Team
                        </button>
                    </div>
                    
                    <!-- Team Details Section -->
                    <div x-show="selectedTeam" x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 transform translate-y-4"
                         x-transition:enter-end="opacity-100 transform translate-y-0"
                         class="mt-8">
                        
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
                                <div x-show="teamPlayers.length > 0" class="overflow-x-auto shadow ring-1 ring-black ring-opacity-5 rounded-lg">
                                    <table class="min-w-full divide-y divide-gray-300">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 md:px-6 py-3 md:py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Player</th>
                                                <th class="hidden md:table-cell px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                                                <th class="hidden sm:table-cell px-4 md:px-6 py-3 md:py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Birth Date</th>
                                                <th class="px-4 md:px-6 py-3 md:py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                                                <th class="px-4 md:px-6 py-3 md:py-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Jersey #</th>
                                                <th class="hidden lg:table-cell px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                                                <th class="px-4 md:px-6 py-3 md:py-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <template x-for="player in teamPlayers" :key="player.id">
                                                <tr class="hover:bg-orange-50 transition-colors">
                                                    <td class="px-4 md:px-6 py-3 md:py-4 whitespace-nowrap">
                                                        <div class="flex items-center">
                                                            <div class="flex-shrink-0 h-8 w-8 md:h-10 md:w-10 bg-orange-100 rounded-full flex items-center justify-center">
                                                                <span class="text-orange-600 font-bold text-xs md:text-sm" x-text="(player.first_name ? player.first_name.charAt(0) : '') + (player.last_name ? player.last_name.charAt(0) : '')"></span>
                                                            </div>
                                                            <div class="ml-3 md:ml-4">
                                                                <div class="text-sm font-medium text-gray-900" x-text="player.first_name + ' ' + player.last_name"></div>
                                                                <div class="text-xs text-gray-500 md:hidden" x-text="player.email"></div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="hidden md:table-cell px-6 py-4 whitespace-nowrap">
                                                        <div class="text-sm text-gray-900" x-text="player.email"></div>
                                                    </td>
                                                    <td class="hidden sm:table-cell px-4 md:px-6 py-3 md:py-4 whitespace-nowrap text-sm text-gray-900" x-text="player.birth_date"></td>
                                                    <td class="px-4 md:px-6 py-3 md:py-4 whitespace-nowrap">
                                                        <span class="px-2 md:px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800" x-text="player.position || 'Not assigned'"></span>
                                                    </td>
                                                    <td class="px-4 md:px-6 py-3 md:py-4 whitespace-nowrap text-center">
                                                        <span class="inline-flex items-center justify-center h-6 w-6 md:h-8 md:w-8 rounded-full bg-gray-100 text-gray-800 font-bold text-xs md:text-sm" x-text="player.jersey_number || '-'"></span>
                                                    </td>
                                                    <td class="hidden lg:table-cell px-6 py-4 text-sm text-gray-900" x-text="player.notes || '-'"></td>
                                                    <td class="px-4 md:px-6 py-3 md:py-4 whitespace-nowrap text-center">
                                                        <div class="flex items-center justify-center space-x-1 md:space-x-2">
                                                            <!-- Evaluate Button -->
                                                            <button @click="handleEvaluateClick(player.id)" 
                                                                    class="text-orange-600 hover:text-orange-900 transition-colors p-2 rounded-lg hover:bg-orange-50 active:bg-orange-100 min-w-[44px] min-h-[44px] flex items-center justify-center"
                                                                    title="Evaluate player"
                                                                    type="button">
                                                                <svg class="w-4 h-4 md:w-5 md:h-5 pointer-events-none" fill="currentColor" viewBox="0 0 24 24">
                                                                    <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                                                                </svg>
                                                            </button>
                                                            <!-- View Player Card Button -->
                                                            <button @click="handlePlayerCardClick(player.id)" 
                                                                    class="text-blue-600 hover:text-blue-900 transition-colors p-2 rounded-lg hover:bg-blue-50 active:bg-blue-100 min-w-[44px] min-h-[44px] flex items-center justify-center"
                                                                    title="View player card"
                                                                    type="button">
                                                                <svg class="w-4 h-4 md:w-5 md:h-5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                                                                </svg>
                                                            </button>
                                                            <!-- History Button -->
                                                            <button @click="handleHistoryClick(player.id)" 
                                                                    class="text-purple-600 hover:text-purple-900 transition-colors p-2 rounded-lg hover:bg-purple-50 active:bg-purple-100 min-w-[44px] min-h-[44px] flex items-center justify-center"
                                                                    title="View player history"
                                                                    type="button">
                                                                <svg class="w-4 h-4 md:w-5 md:h-5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                                </svg>
                                                            </button>
                                                            <!-- Remove Button -->
                                                            <button @click="handleRemoveClick(player.id)" 
                                                                    class="text-red-600 hover:text-red-900 transition-colors p-2 rounded-lg hover:bg-red-50 active:bg-red-100 min-w-[44px] min-h-[44px] flex items-center justify-center"
                                                                    title="Remove from team"
                                                                    type="button">
                                                                <svg class="w-4 h-4 md:w-5 md:h-5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- Empty Players State -->
                                <div x-show="teamPlayers.length === 0" class="text-center py-12">
                                    <div class="bg-gray-50 rounded-full w-20 h-20 mx-auto mb-4 flex items-center justify-center">
                                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                        </svg>
                                    </div>
                                    <h4 class="text-lg font-semibold text-gray-900 mb-2">No players yet</h4>
                                    <p class="text-gray-600">Add players to build your team roster.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Player Card Display - Outside of team details -->
                    <div x-show="selectedPlayerCard && selectedPlayerCard.id === selectedTeam.id && viewingPlayer" 
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 transform translate-y-4"
                         x-transition:enter-end="opacity-100 transform translate-y-0"
                         class="mt-8 mb-8">
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
                                
                                <!-- Spider Chart Section -->
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
                            </div>
                            
                            <!-- AI Coaching Advice - Full Width -->
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
                        </div>
                    </div>
                </div>
                
                <!-- Club Teams Tab -->
                <div x-show="activeTab === 'club-teams'" x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform scale-95"
                     x-transition:enter-end="opacity-100 transform scale-100">
                    <div class="bg-white rounded-2xl shadow-xl p-16 text-center">
                        <div class="bg-orange-50 rounded-full w-24 h-24 mx-auto mb-6 flex items-center justify-center">
                            <svg class="w-12 h-12 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Coming Soon</h3>
                        <p class="text-gray-600">Club teams functionality will be available in a future update.</p>
                    </div>
                </div>
                
                <!-- Include Modals - Must be inside Alpine.js x-data scope -->
                <?php 
                include CLUB_MANAGER_PLUGIN_DIR . 'modals/create-team-modal.php';
                include CLUB_MANAGER_PLUGIN_DIR . 'modals/add-player-modal.php';
                include CLUB_MANAGER_PLUGIN_DIR . 'modals/add-existing-player-modal.php';
                include CLUB_MANAGER_PLUGIN_DIR . 'modals/evaluation-modal.php';
                include CLUB_MANAGER_PLUGIN_DIR . 'modals/player-history-modal.php';
                ?>
            </div>
        </div>
        
        <?php
        return ob_get_clean();
    }
}