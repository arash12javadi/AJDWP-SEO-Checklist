<?php

if (!defined('ABSPATH')) exit;

//__________________________________________________________________________//
//                  LOAD JAVASCRIPT & CSS IN ADMIN PAGES                   
//__________________________________________________________________________//

add_action('admin_enqueue_scripts', function ($hook_suffix) {
    global $post;

    // Load only on post, page edit, and Elementor editor
    if (in_array($hook_suffix, ['post.php', 'post-new.php']) || did_action('elementor/editor/after_enqueue_scripts')) {
        
        // ✅ Load CSS
        wp_enqueue_style(
            'keyword-checklist-style',
            plugin_dir_url(__FILE__) . 'assets/keyword-checklist.css',
            [],
            '1.0'
        );

        // ✅ Register & Enqueue JS
        wp_register_script(
            'keyword-checklist-script',
            plugin_dir_url(__FILE__) . 'assets/keyword-checklist.js',
            ['jquery'],
            '1.0',
            true // Load in footer
        );

        // ✅ Localize Script (Pass Post ID and AJAX URL)
        wp_localize_script('keyword-checklist-script', 'keywordChecklistData', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'post_id' => isset($post->ID) ? $post->ID : 0,
            'nonce' => wp_create_nonce('delete_keyword_nonce'),
        ]);

        wp_enqueue_script('keyword-checklist-script');
    }
});

//__________________________________________________________________________//
//                    ADD META BOX TO POST & PAGE SIDEBAR
//__________________________________________________________________________//

add_action('add_meta_boxes', function () {
    add_meta_box(
        'keyword_checklist_meta_box',     // Unique ID
        '📌 Global Keyword List',           // Box Title
        'keyword_checklist_meta_box_content', // Callback function
        ['post', 'page'],                 // Post Types
        'side',                            // Sidebar Position
        'high'                             // Priority
    );
});

//__________________________________________________________________________//
//                     META BOX CONTENT                                    
//__________________________________________________________________________//

function keyword_checklist_meta_box_content() {
    $keywords = get_option('global_keyword_checklist', []);
    if (!is_array($keywords)) {
        $keywords = [];
    }
    ?>
    <div id="keyword-checklist-meta-box">
        <input type="text" id="keyword-search" placeholder="🔍 Search keywords..." />

        <table id="keyword-table">
            <thead>
                <tr>
                    <th class="AJDWP-kw-table-th-tooltip">🗑<span class="AJDWP-kw-table-th-tooltiptext">Select the keywords which you want to remove from this list.</span></th>
                    <th class="AJDWP-kw-table-th-tooltip">Used⁉<span class="AJDWP-kw-table-th-tooltiptext">Click the icon ✍🏼 to mark the keyword as used. Press again to make it available again.</span></th>
                    <th class="AJDWP-kw-table-th-tooltip" data-sort="keyword">Keyword 🔑🔠<span class="AJDWP-kw-table-th-tooltiptext">Click on the table header to sort the keywords from A-Z and vice versa.</span></th>
                    <th class="AJDWP-kw-table-th-tooltip" data-sort="search_volume">SV 👀🔉<span class="AJDWP-kw-table-th-tooltiptext">Click on this header to sort the keywords <b>Search Volume</b> from Min to Max and vice versa.</span></th>
                    <th class="AJDWP-kw-table-th-tooltip" data-sort="difficulty">KD 🐢🐇<span class="AJDWP-kw-table-th-tooltiptext">Click on this header to sort the <b>Keywords Difficulty</b> from Min to Max and vice versa.</span></th>
                    <th class="AJDWP-kw-table-th-tooltip" data-sort="page_info">Page 🔗<span class="AJDWP-kw-table-th-tooltiptext">The page that this keyword has been used in. it is a clickable link.</span> </th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($keywords as $keyword) : ?>
                    <tr data-keyword="<?php echo esc_attr($keyword['keyword']); ?>" class="<?php echo !empty($keyword['used']) ? 'used-keyword' : ''; ?>">
                        <td>
                            <input type="checkbox" class="delete-checkbox" data-keyword="<?php echo esc_attr($keyword['keyword']); ?>"
                                <?php echo !empty($keyword['used']) ? 'disabled' : ''; ?> />
                        </td>
                        <td class="toggle-used"></td>
                        <td><?php echo esc_html($keyword['keyword']); ?></td>
                        <td><?php echo esc_html($keyword['search_volume']); ?></td>
                        <td><?php echo esc_html($keyword['difficulty']); ?></td>
                        <td class="page-info">
                            <?php echo !empty($keyword['page_link']) ? '<a href="' . esc_url($keyword['page_link']) . '" target="_blank">' . esc_html($keyword['page_title']) . '</a>' : '<em>Not Assigned</em>'; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <textarea id="bulk-keyword-input" placeholder="Paste keywords (slash/, double space or tab-separated. exp |𝄞| aaa/222/333 |𝄞| aaa  222  333 |𝄞| aaa   222   333)"></textarea>
        <div id="keyword-list-save-del-btns">
            <button id="save-keywords">💾 Save Keywords</button>
            <button id="delete-keywords">🗑️ Delete Selected</button>
        </div>

    </div>
    <?php
}




//__________________________________________________________________________//
//                HANDLE AJAX REQUESTS TO SAVE/RETRIEVE KEYWORDS            
//__________________________________________________________________________//

add_action('wp_ajax_save_keyword_checklist', function () {
    if (!isset($_POST['keywords'])) {
        wp_send_json_error(['message' => 'Invalid request.']);
    }

    $new_keywords = json_decode(stripslashes($_POST['keywords']), true);
    if (!is_array($new_keywords)) {
        wp_send_json_error(['message' => 'Invalid keyword format.']);
    }

    // Retrieve existing keywords
    $existing_keywords = get_option('global_keyword_checklist', []);
    if (!is_array($existing_keywords)) {
        $existing_keywords = [];
    }

    // Convert existing keywords into an associative array for quick lookup
    $keyword_map = [];
    foreach ($existing_keywords as $keyword) {
        $keyword_map[$keyword['keyword']] = $keyword;
    }

    // Process new keywords
    foreach ($new_keywords as $keyword) {
        if (!isset($keyword['keyword'], $keyword['search_volume'], $keyword['difficulty'])) {
            continue; // Skip invalid entries
        }

        $key = sanitize_text_field($keyword['keyword']);
        $search_volume = intval($keyword['search_volume']);
        $difficulty = intval($keyword['difficulty']);

        if (isset($keyword_map[$key])) {
            // ✅ Update values if keyword exists
            $keyword_map[$key]['search_volume'] = $search_volume;
            $keyword_map[$key]['difficulty'] = $difficulty;
        } else {
            // ✅ Insert new keyword if not found
            $keyword_map[$key] = [
                'keyword'       => $key,
                'search_volume' => $search_volume,
                'difficulty'    => $difficulty,
                'used'          => false,
                'page_title'    => '',
                'page_link'     => '',
            ];
        }
    }

    // Save updated keywords
    update_option('global_keyword_checklist', array_values($keyword_map));

    wp_send_json_success(['message' => '✅ Keywords updated successfully.']);
});



add_action('wp_ajax_get_keyword_checklist', function () {
    $keywords = get_option('global_keyword_checklist', []);
    if (!is_array($keywords)) {
        $keywords = [];
    }

    wp_send_json_success(['keywords' => $keywords]);
});

//__________________________________________________________________________//
//                HANDLE AJAX REQUESTS TO TOGGLE "USED" STATUS             
//__________________________________________________________________________//

// Toggle "Used" status globally
add_action('wp_ajax_toggle_keyword_used', function () {
    // Debug: Print received data
    error_log(print_r($_POST, true)); // 🔥 Log all received POST data for debugging

    if (!isset($_POST['post_id'], $_POST['keyword'], $_POST['page_title'], $_POST['page_link'])) {
        wp_send_json_error(['message' => 'Invalid request. Missing parameters.']);
    }

    $post_id = intval($_POST['post_id']);
    $keyword_to_toggle = sanitize_text_field($_POST['keyword']);
    $page_title = sanitize_text_field($_POST['page_title']);
    $page_link = esc_url($_POST['page_link']);

    // Retrieve existing keywords
    $keywords = get_option('global_keyword_checklist', []);

    // Ensure it's an array
    if (!is_array($keywords)) {
        $keywords = [];
    }

    // Toggle "Used" status & Assign page link
    foreach ($keywords as &$keyword) {
        if ($keyword['keyword'] === $keyword_to_toggle) {
            $keyword['used'] = !empty($keyword['used']) ? false : true;

            // ✅ If marking as used, save page info
            if ($keyword['used']) {
                $keyword['page_title'] = $page_title;
                $keyword['page_link'] = $page_link;
            } else {
                // ✅ If unmarking, remove page info
                unset($keyword['page_title'], $keyword['page_link']);
            }
        }
    }

    // Save updated keywords
    update_option('global_keyword_checklist', $keywords);

    wp_send_json_success(['message' => '✅ Used status updated successfully.']);
});


//__________________________________________________________________________//
//                HANDLE AJAX REQUEST TO DELETE SELECTED KEYWORDS            
//__________________________________________________________________________//

add_action('wp_ajax_delete_keywords', function () {
    if (!isset($_POST['keywords_to_delete'])) {
        wp_send_json_error(['message' => 'Invalid request.']);
    }

    $keywords_to_delete = json_decode(stripslashes($_POST['keywords_to_delete']), true);
    if (!is_array($keywords_to_delete)) {
        wp_send_json_error(['message' => 'Invalid keyword format.']);
    }

    // Get the existing keyword list
    $keywords = get_option('global_keyword_checklist', []);
    if (!is_array($keywords)) {
        $keywords = [];
    }

    // Remove selected keywords
    $keywords = array_filter($keywords, function ($keyword) use ($keywords_to_delete) {
        return !in_array($keyword['keyword'], $keywords_to_delete);
    });

    // Save the updated list
    update_option('global_keyword_checklist', array_values($keywords));

    wp_send_json_success(['message' => '✅ Selected keywords deleted successfully.']);
});
