<?php
if (!defined('ABSPATH')) exit;

//__________________________________________________________________________//
//                ADD KEYWORD LIST TO ELEMENTOR PAGE EDIT SIDE            
//__________________________________________________________________________//

// Enqueue JavaScript and CSS on Elementor page edit
add_action('elementor/editor/after_enqueue_scripts', function() {
    if (!is_admin()) return; // Ensure it only runs in the admin area

    // ✅ Enqueue CSS
    wp_enqueue_style(
        'elementor-keyword-checklist-styles',
        plugin_dir_url(__FILE__) . 'assets/keyword-checklist.css'
    );

    // ✅ Register JavaScript FIRST
    wp_register_script(
        'elementor-keyword-checklist-scripts',
        plugin_dir_url(__FILE__) . 'assets/keyword-checklist.js',
        ['jquery'],
        '1.0',
        true
    );

    // ✅ Get the current post ID safely
    global $post;
    $post_id = isset($post->ID) ? $post->ID : 0;

    // ✅ Localize script AFTER registering & BEFORE enqueueing
    wp_localize_script('elementor-keyword-checklist-scripts', 'keywordChecklistData', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'post_id'  => $post_id,
    ]);

    // ✅ Now Enqueue JavaScript (so localized data is available)
    wp_enqueue_script('elementor-keyword-checklist-scripts');
});


// Add the floating button and checklist in Elementor editor
add_action('elementor/editor/footer', function() {
    ?>
    <div id="keyword-checklist-container">
        <button id="keyword-checklist-button">🚀 <?php echo esc_html__('Keyword Checklist', 'your-textdomain'); ?></button>
        <div id="keyword-checklist-popup" style="display: none;">
            <h3><?php echo esc_html__('Keyword Checklist', 'your-textdomain'); ?></h3>
            <div id="keyword-checklist-content">
                <div id="keyword-checklist-items">
                    <?php elementor_keyword_checklist_meta_box_content(); ?>
                </div>
            </div>
        </div>
    </div>
    <?php
});

//__________________________________________________________________________//
//                   FUNCTION TO DISPLAY KEYWORD LIST                    
//__________________________________________________________________________//

function elementor_keyword_checklist_meta_box_content() {
    $keywords = get_option('global_keyword_checklist', []);
    if (!is_array($keywords)) {
        $keywords = [];
    }
    ?>
    <div id="elementor-keyword-checklist-meta-box">
        <input type="text" id="keyword-search" placeholder="🔍 Search keywords..." />

        <table id="keyword-table">
            <thead>
                <tr>
                    <th class="AJDWP-kw-table-th-tooltip">🗑
                        <span class="AJDWP-kw-table-th-tooltiptext">Select keywords to remove.</span>
                    </th>
                    <th class="AJDWP-kw-table-th-tooltip">Used⁉
                        <span class="AJDWP-kw-table-th-tooltiptext">Click ✍🏼 to mark as used. Click again to reset.</span>
                    </th>
                    <th class="AJDWP-kw-table-th-tooltip" data-sort="keyword">Keyword 🔑🔠
                        <span class="AJDWP-kw-table-th-tooltiptext">Sort A-Z/Z-A.</span>
                    </th>
                    <th class="AJDWP-kw-table-th-tooltip" data-sort="search_volume">SV 👀🔉
                        <span class="AJDWP-kw-table-th-tooltiptext">Sort by Search Volume (Min-Max).</span>
                    </th>
                    <th class="AJDWP-kw-table-th-tooltip" data-sort="difficulty">KD 🐢🐇
                        <span class="AJDWP-kw-table-th-tooltiptext">Sort by Keyword Difficulty (Min-Max).</span>
                    </th>
                    <th class="AJDWP-kw-table-th-tooltip" data-sort="page_info">Page 🔗
                        <span class="AJDWP-kw-table-th-tooltiptext">Click page link to view.</span>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($keywords as $keyword) : ?>
                    <tr data-keyword="<?php echo esc_attr($keyword['keyword']); ?>" class="<?php echo !empty($keyword['used']) ? 'used-keyword' : ''; ?>">
                        <td>
                            <input type="checkbox" class="delete-checkbox" data-keyword="<?php echo esc_attr($keyword['keyword']); ?>"
                                <?php echo !empty($keyword['used']) ? 'disabled' : ''; ?> />
                        </td>
                        <td class="toggle-used">
                            <button class="use-btn"><?php echo !empty($keyword['used']) ? '✅' : '✍🏼'; ?></button>
                        </td>
                        <td><?php echo esc_html($keyword['keyword']); ?></td>
                        <td><?php echo esc_html($keyword['search_volume']); ?></td>
                        <td><?php echo esc_html($keyword['difficulty']); ?></td>
                        <td class="page-info">
                            <?php echo !empty($keyword['page_link']) 
                                ? '<a href="' . esc_url($keyword['page_link']) . '" target="_blank">' . esc_html($keyword['page_title']) . '</a>' 
                                : '<em>Not Assigned</em>'; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <textarea id="bulk-keyword-input" placeholder="Paste keywords (slash /, double space, or tab-separated)"></textarea>
        <div id="keyword-list-save-del-btns">
            <button id="save-keywords">💾 Save Keywords</button>
            <button id="delete-keywords">🗑️ Delete Selected</button>
        </div>
    </div>
    <?php
}
?>
