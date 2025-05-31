<?php

class Club_Manager_AI {
    
    public static function init() {
        // Hook for scheduled advice generation
        add_action('cm_generate_player_advice', array(__CLASS__, 'generate_advice'), 10, 3);
    }
    
    public static function generate_advice($player_id, $team_id, $season) {
        global $wpdb;
        
        // Check if OpenAI API key is defined
        if (!defined('OPENAI_API_KEY')) {
            error_log('Club Manager: OpenAI API key not defined');
            return;
        }
        
        // Get player information
        $player = $wpdb->get_row($wpdb->prepare(
            "SELECT p.*, tp.position 
            FROM {$wpdb->prefix}cm_players p
            JOIN {$wpdb->prefix}cm_team_players tp ON p.id = tp.player_id
            WHERE p.id = %d AND tp.team_id = %d AND tp.season = %s",
            $player_id, $team_id, $season
        ));
        
        if (!$player) {
            error_log('Club Manager: Player not found for advice generation');
            return;
        }
        
        // Get all evaluations for the player
        $evaluations = $wpdb->get_results($wpdb->prepare(
            "SELECT category, subcategory, AVG(score) as avg_score
            FROM {$wpdb->prefix}cm_player_evaluations
            WHERE player_id = %d AND team_id = %d AND season = %s
            GROUP BY category, subcategory
            ORDER BY category, subcategory",
            $player_id, $team_id, $season
        ));
        
        if (empty($evaluations)) {
            error_log('Club Manager: No evaluations found for player');
            return;
        }
        
        // Prepare evaluation data for the prompt
        $evaluation_text = self::prepare_evaluation_text($evaluations);
        
        // Generate the prompt
        $prompt = self::create_advice_prompt($player, $evaluation_text);
        
        // Call OpenAI API
        $advice = self::call_openai_api($prompt);
        
        if ($advice) {
            // Save the advice to database
            $advice_table = $wpdb->prefix . 'cm_player_advice';
            
            // Mark any existing advice as old
            $wpdb->update(
                $advice_table,
                array('status' => 'old'),
                array(
                    'player_id' => $player_id,
                    'team_id' => $team_id,
                    'season' => $season
                ),
                array('%s'),
                array('%d', '%d', '%s')
            );
            
            // Insert new advice
            $wpdb->insert(
                $advice_table,
                array(
                    'player_id' => $player_id,
                    'team_id' => $team_id,
                    'season' => $season,
                    'advice' => $advice,
                    'status' => 'current',
                    'generated_at' => current_time('mysql')
                ),
                array('%d', '%d', '%s', '%s', '%s', '%s')
            );
        }
    }
    
    private static function prepare_evaluation_text($evaluations) {
        $categories = array();
        
        foreach ($evaluations as $eval) {
            $category_name = str_replace('_', ' ', $eval->category);
            $category_name = ucwords($category_name);
            
            if (!isset($categories[$category_name])) {
                $categories[$category_name] = array();
            }
            
            if ($eval->subcategory) {
                $subcategory_name = str_replace('_', ' ', $eval->subcategory);
                $subcategory_name = ucwords($subcategory_name);
                $categories[$category_name][$subcategory_name] = round($eval->avg_score, 1);
            } else {
                $categories[$category_name]['Overall'] = round($eval->avg_score, 1);
            }
        }
        
        $text = "";
        foreach ($categories as $category => $subcategories) {
            $text .= "\n$category:\n";
            foreach ($subcategories as $sub => $score) {
                if ($sub !== 'Overall') {
                    $text .= "  - $sub: $score/10\n";
                }
            }
            if (isset($subcategories['Overall'])) {
                $text .= "  Overall: {$subcategories['Overall']}/10\n";
            }
        }
        
        return $text;
    }
    
    private static function create_advice_prompt($player, $evaluation_text) {
        $position = $player->position ?: 'Unknown';
        $age = self::calculate_age($player->birth_date);
        
        $prompt = "You are an expert hockey coach analyzing a player's performance evaluation. Generate personalized training advice for the coach to help improve this player.

Player Information:
- Name: {$player->first_name} {$player->last_name}
- Position: {$position}
- Age: {$age} years old

Performance Evaluation Scores (out of 10):
{$evaluation_text}

Instructions:
1. Start with 1-2 sentences explaining how the evaluation scores relate to what's important for a {$position} position.
2. Then provide 4-5 specific, actionable training tips based on the lowest scores and position requirements.
3. Write in English language.
4. Maximum 1000 characters total.
5. Be concise, practical, and focused on immediate improvements.";
        
        return $prompt;
    }
    
    private static function calculate_age($birth_date) {
        $birth = new DateTime($birth_date);
        $today = new DateTime();
        $age = $today->diff($birth);
        return $age->y;
    }
    
    private static function call_openai_api($prompt) {
        $api_key = OPENAI_API_KEY;
        $url = 'https://api.openai.com/v1/chat/completions';
        
        $data = array(
            'model' => 'gpt-4o',
            'messages' => array(
                array(
                    'role' => 'system',
                    'content' => 'You are an expert hockey coach providing training advice based on player evaluations.'
                ),
                array(
                    'role' => 'user',
                    'content' => $prompt
                )
            ),
            'temperature' => 0.7,
            'max_tokens' => 300
        );
        
        $options = array(
            'http' => array(
                'header' => array(
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $api_key
                ),
                'method' => 'POST',
                'content' => json_encode($data),
                'timeout' => 30
            )
        );
        
        $context = stream_context_create($options);
        $result = @file_get_contents($url, false, $context);
        
        if ($result === FALSE) {
            error_log('Club Manager: Failed to call OpenAI API');
            return false;
        }
        
        $response = json_decode($result, true);
        
        if (isset($response['choices'][0]['message']['content'])) {
            return $response['choices'][0]['message']['content'];
        } else {
            error_log('Club Manager: Invalid response from OpenAI API');
            return false;
        }
    }
}