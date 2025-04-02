<?php
/**
 * Created by PhpStorm.
 * User: 585
 * Date: 2/22/2025
 * Time: 5:17 PM
 */

use App\Models\Permission;
use App\Models\Recording;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;


if (!function_exists('internal_api_get_user_role')) {
    function internal_api_get_user_role($user_id, $organization_id)
    {
        $permission = Permission::where(['user_id' => $user_id, 'organization_id' => $organization_id * 1])->first();
        return $permission ? $permission->role : null;
    }
}

if (!function_exists('internal_api_is_user_admin')) {
    function internal_api_is_user_admin($role)
    {
        // Log::debug("Is user admin: " . $role);
        return $role == ADMIN_ROLE;
    }
}

if (!function_exists('internal_api_is_user_master')) {
    function internal_api_is_user_master($role)
    {
        // Log::debug("In the is user master API call. The user role is: " . $role);
        return $role == ADMIN_ROLE or $role == MASTER_ACCOUNT_ROLE; // Admin account or master account
    }
}

if (!function_exists('internal_api_is_user_self')) {
    function internal_api_is_user_self($authenticated_user, $user_id)
    {
        // Log::info("Authenticated user is: " . $authenticated_user . " and the user to be updated is " . $user_id);
        return $authenticated_user == $user_id; // User is trying to update themselves
    }
}

if (!function_exists('internal_api_is_user_to_update_same_org')) {
    function internal_api_is_user_to_update_same_org($authenticated_user_org, $user_id_to_update_org)
    {
        // Log::info("Authenticated user org is: " . $authenticated_user_org . " and the user to be updated is " . $user_id_to_update_org);
        return $authenticated_user_org == $user_id_to_update_org; // User is trying to update themselves
    }
}

if (!function_exists('internal_api_is_user_request_own_org')) {
    function internal_api_is_user_request_own_org($authenticated_user_org, $org_encounter_request)
    {
        // Log::info("Authenticated user org is: " . $authenticated_user_org . " and the user to be updated is " . $org_encounter_request);
        return $authenticated_user_org == $org_encounter_request; // User is trying to update themselves
    }
}

if (!function_exists('internal_api_user_has_master_role')) {
    function internal_api_user_has_master_role($user_id, $organization_id)
    {
        $admin_permission = Permission::where(['user_id' => $user_id, 'organization_id' => $organization_id, 'role' => ADMIN_ROLE])->first();
        $master_permission = Permission::where(['user_id' => $user_id, 'organization_id' => $organization_id, 'role' => MASTER_ACCOUNT_ROLE])->first();

        if ($admin_permission or $master_permission) {
            return true;
        }
        return false;
    }
}



/// Checks if the user is a master role in the before and after update
if (!function_exists('internal_api_is_user_both_master')) {
    function internal_api_is_user_both_master($user_id, $before_organization_id, $after_organization_id)
    {
        $before_permission = Permission::where([
            'user_id' => $user_id,
            'organization_id' => $before_organization_id
        ])->first();
        $after_permission = Permission::where([
            'user_id' => $user_id,
            'organization_id' => $after_organization_id
        ])->first();

        if (!$before_permission or !$after_permission) {
            return false;
        }
        Log::info("before_permission is: " . $before_permission . " and after_permission is: " . $after_permission);
        return internal_api_is_user_master($before_permission->role) and internal_api_is_user_master($after_permission->role);
    }
}

if (!function_exists('internal_api_get_role_string')) {
    function internal_api_get_role_string($role)
    {
        switch ($role) {
            case 0:
                return "Master Account";
            case 1:
                return "Sub User Account";
            case 2:
                return "Clerical Account";
            case 9:
                return "Admin Account";
            default:
                return "Sub User Account";
        }
    }
}

if (!function_exists('internal_api_get_login_server_string')) {
    function internal_api_get_login_server_string($login_server)
    {
        switch ($login_server) {
            case 0:
                return "USA";
            case 1:
                return "CANADA";
            case 2:
                return "TEST";
            default:
                return "TEST";
        }
    }
}

if (!function_exists('internal_api_get_encounter_status_string')) {
    function internal_api_get_encounter_status_string($status)
    {
        switch ($status) {
            case 0:
                return 'Open';
            case 1:
                return 'In Progress';
            case 2:
                return 'Ready for Review';
            case 3:
                return 'Closed';
            default:
                return 'Open';
        }
    }
}

if (!function_exists('internal_api_get_recording_count')) {
    function internal_api_get_recording_count($id_of_encounter)
    {
        return Recording::where(['encounter_id' => $id_of_encounter])->count();
    }
}

if (!function_exists('transform_snake_to_camel')) {
    function transform_snake_to_camel($snake_array)
    {
        $camel_array = [];

        foreach ($snake_array as $key => $value) {
            // Convert the key to camelCase
            $camel_key = Str::camel($key);

            // Recursively handle nested arrays
            if (is_array($value)) {
                $camel_array[$camel_key] = transform_snake_to_camel($value);
            } else {
                $camel_array[$camel_key] = $value;
            }
        }

        return $camel_array;
    }
}

if (!function_exists('convert_summary_to_html')) {
    function convert_summary_to_html($text)
    {
        $text = preg_replace('/\*\*(.+?)\*\*/', '<strong class="block mt-2">$1</strong>', $text);

//        $text = str_replace('\n', '\n ', $text);
        $text = str_replace('\r\n', '\n', $text);
        $text = str_replace('\r', '\n', $text);

        $paragraphs = explode('\n\n', $text);
        $text = '';

        foreach ($paragraphs as $paragraph) {
            $lines = explode('\n', $paragraph);
            foreach ($lines as &$line) {
                $line = trim($line);
                if (strpos($line, '-') === 0) {
                    $line = '<li class="ml-6">' . substr($line, 1) . '</li>';
                } elseif (is_numeric(substr($line, 0, 1))) {
                    $line = '<li class="ml-3 mb-2">' . substr($line, 2) . '</li>';
                    $line = '<ol>' . $line . '</ol>';
                }
            }
            $paragraph = '<p>' . implode('', $lines) . '</p>';
            $text .= $paragraph;
        }

        return $text;
    }
}

if (!function_exists('extract_sections')) {
    function extract_sections($text)
    {
        $lines = explode("\n", $text);
        $sections = [];
        $current_section = [];

        foreach ($lines as $line) {
            $trimmed_line = trim($line);

            if (preg_match('/^\*\*(.+)\*\*:?$/', $trimmed_line, $matches)) {
                if (!empty($current_section)) {
                    $sections[] = $current_section;
                }

                $current_section = [
                    'heading' => trim($matches[1], ":"),
                    'content' => '',
                    'original_text' => ''
                ];
            } elseif (!empty($current_section)) {
                $current_section['content'] .= (empty($current_section['content']) ? '' : '<br>') . $trimmed_line;
                $current_section['original_text'] .= (empty($current_section['original_text']) ? '' : "\n") . $trimmed_line;
            }
        }

        if (!empty($current_section)) {
            $current_section['content'] = convert_summary_to_html($current_section['content']);
            $sections[] = $current_section;
        }
        return $sections;
    }
}

if(!function_exists('process_transcripts')){
    function process_transcripts($transcripts){
        $transcript = json_decode($transcripts)[0]->transcript;
        $lines = explode("\n", $transcript);

        $conversation = [];
        for ($i = 0; $i < count($lines) - 1; $i += 2) {
            $speaker = $lines[$i];
            $dialogue = $lines[$i + 1];
            if ($speaker == 'Speaker A') {
                $conversation[] = [
                    'position' => 'left',
                    'dialogue' => $speaker . ': ' . $dialogue
                ];
            } else {
                $conversation[] = [
                    'position' => 'right',
                    'dialogue' => $speaker . ': ' . $dialogue
                ];
            }
        }
        return $conversation;
    }
}

if (!function_exists('process_history_summary')) {
    function process_history_summary($history_summary)
    {
        // Define a pattern to detect headers marked with **something**
        $pattern = '/\*\*(.*?)\*\*/';

        preg_match_all($pattern, $history_summary, $matches);

        $headers = $matches[0];  // This captures the full **header** format
        $header_texts = [];
        $contents = [];

        // Remove the ** and clean up the header text
        foreach ($headers as $index => $header) {
            $header_text = trim(str_replace(['**', '**'], '', $header)); // Remove the '**' around the header
            $header_texts[] = $header_text;
        }

        // Split the text by the header markers and process content
        $content_parts = preg_split($pattern, $history_summary); // This splits based on **something**

        // We now associate each header with its content.
        foreach ($header_texts as $index => $header_text) {
            // The content comes after the header, so associate them together
            $content = isset($content_parts[$index + 1]) ? trim($content_parts[$index + 1]) : '';
            $contents[] = [
                'header' => $header_text,
                'content' => $content
            ];
        }

        // Return the structured array
        return [
            'headers' => $header_texts,
            'contents' => $contents
        ];
    }
}



if(!function_exists('make_history_summary_html')){
    function make_history_summary_html($text){
        $text = preg_replace('/\R/', '<br/>',$text);
        $lines = explode('<br/>', $text);
        $formatted_lines = array_map(function($line) {
            $line = trim($line);
            if (!empty($line)) {
                return "<p>{$line}</p>";
            }
            return '';
        }, $lines);
        $result = implode("\n", array_filter($formatted_lines));
        return $result;
    }
}