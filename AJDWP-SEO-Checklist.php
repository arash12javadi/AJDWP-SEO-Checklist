<?php

if ( ! defined( 'ABSPATH' ) ) exit; 

/**
 * Plugin Name:       AJDWP-SEO-Checklist
 * Plugin URI:        https://github.com/arash12javadi/
 * Description:       Simple light weight plugin for SEO Purposes.
 * Version:           250127
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Arash Javadi
 * Author URI:        https://arashjavadi.com/  
 */


include_once plugin_dir_path(__FILE__) . 'keyword_checklist.php';
include_once plugin_dir_path(__FILE__) . 'elementor_keyword_checklist.php';

//__________________________________________________________________________//
//                          ADD JAVASCRIPTS AND CSS
//__________________________________________________________________________//

// Enqueue JavaScript and CSS on Elementor page edit
add_action('elementor/editor/after_enqueue_scripts', function() {
    if (!is_admin()) {
        return; // Ensure it only runs in the admin area
    }

    // Enqueue CSS
    wp_enqueue_style(
        'seo-checklist-styles',
        plugin_dir_url(__FILE__) . 'assets/SEO-Checklist.css'
    );

    // Register JavaScript
    wp_register_script(
        'seo-checklist-scripts',
        plugin_dir_url(__FILE__) . 'assets/SEO-Checklist.js',
        ['jquery'],
        '1.0',
        true
    );

    // Get the current post ID safely
    $seo_checklist_post_id = get_the_ID() ?: 0;

    // Localize script for Elementor context
    wp_localize_script('seo-checklist-scripts', 'seoChecklistData', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'post_id' => $seo_checklist_post_id,
    ]);

    // Enqueue JavaScript
    wp_enqueue_script('seo-checklist-scripts');
});

// Enqueue JavaScript and CSS for the post edit screen
add_action('admin_enqueue_scripts', function ($hook_suffix) {
    // Only load scripts for post edit screens
    if ($hook_suffix === 'post.php' || $hook_suffix === 'post-new.php') {
        // Enqueue CSS
        wp_enqueue_style(
            'seo-checklist-edit-style',
            plugin_dir_url(__FILE__) . 'assets/SEO-Checklist.css'
        );

        // Register JavaScript
        wp_register_script(
            'seo-checklist-edit-script',
            plugin_dir_url(__FILE__) . 'assets/SEO-Checklist.js',
            ['jquery'],
            '1.0',
            true
        );

        // Get the current post ID safely
        $seo_checklist_post_id = get_the_ID() ?: 0;

        // Localize script for post editor pages
        wp_localize_script('seo-checklist-edit-script', 'seoChecklistAdminData', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'post_id' => $seo_checklist_post_id,
        ]);

        // Enqueue JavaScript
        wp_enqueue_script('seo-checklist-edit-script');
    }
});


//__________________________________________________________________________//
//                   AJAX HANDLERS ON ELEMENTOR PAGE EDIT                  
//__________________________________________________________________________//

// Add the flying button and checklist in Elementor editor
add_action('elementor/editor/footer', function() {
    ?>
    <div id="seo-checklist-container">
        <button id="seo-checklist-button">🚀 SEO Checklist</button>
        <div id="seo-checklist-popup">
            <h3>SEO Checklist</h3>
            <div id="seo-checklist-content">
                <h4>Critical SEO Essentials List</h4>
                <ul>
                    <li>
                        <input type="checkbox" id="keyword-research" />
                        <strong>Search Intent Match</strong> 
                        <span>
                            ✅ Identify the intent behind targeted keywords (e.g., informational, navigational, transactional). <br />
                            ✅ Create content that aligns with the user's needs for that keyword (e.g., how-to guides for informational intent, product pages for transactional intent). <br />
                            ✅ Analyze top-ranking competitors to understand the type of content Google favors for the search query. <br />
                            ✅ Use headings to clarify the content's relevance to the search query.
                        </span>
                    </li>
                    <li>
                        <input type="checkbox" id="content-optimization" /> 
                        <strong>Content Optimization</strong> 
                        <span>
                            ✅ Ensure content is unique, engaging, and provides value to the reader. <br />
                            ✅ Use keywords naturally and strategically in the title, meta description, headings, and first 100 words. <br />
                            ✅ Structure content with H1, H2, and H3 tags for readability and SEO. <br />
                            ✅ Add multimedia (images, videos, infographics) to enhance engagement. <br />
                            ✅ Include actionable takeaways or clear CTAs to guide users. <br />
                            ✅ Update and repurpose outdated content for freshness and relevance.
                        </span>
                    </li>
                    <li>
                        <input type="checkbox" id="heading-tags" /> 
                        <strong>Heading Tags </strong> 
                        <span>
                            ✅H1 include the Main Keyword: Use only 1 H1 tag per page, and include the primary keyword.<br>
                            (e.g., Keyword: "WordPress SEO tutorial" => H1: "The Ultimate WordPress SEO Tutorial for Beginners")<br>
                            ✅H2/H3 with related keywords: Break down content logically with headings every 2-3 paragraphs.<br>
                            ✅Avoid Keyword Stuffing: Use variations and synonyms instead of repeating the exact keyword excessively.<br>
                            ✅Maintain Consistency: Ensure heading tags follow a sequential order (e.g., no skipping from H1 to H3).
                        </span>
                    </li>
                    <li>
                        <input type="checkbox" id="mobile-optimization" /> 
                        <strong>Mobile Optimization </strong> 
                        <span>	
                            ✅ Ensure your site is 100% mobile-responsive.<br>
                            ✅ Test across devices using <a href="https://developers.google.com/search/blog/2016/05/a-new-mobile-friendly-testing-tool" target="_blank">Google’s Mobile-Friendly Test Tool</a>.<br>
                            ✅ Use a tap-friendly design with buttons that are at least 48px x 48px.<br>
                            ✅ Test across devices using Google’s Mobile-Friendly Test Tool and BrowserStack.<br>
                            ✅ Ensure forms and input fields are mobile-optimized for easy usability on smaller screens.<br>
                            ✅ Enable lazy loading for images to improve page speed on mobile devices.<br>
                            ✅ Use mobile-friendly fonts with a minimum size of 16px for readability.<br>
                        </span>
                    </li>
                </ul>

                <h4>High Priority SEO List</h4>
                <ul>
                    <li>
                        <input type="checkbox" id="internal-linking" /> 
                        <strong>Internal Linking</strong> 
                        <span>
                            ✅Strategic Placement: Include 1 internal link per 100-150 words, ensuring relevance to the content.<br>
                            ✅Anchor Text Optimization: Use descriptive, keyword-rich anchor text while avoiding over-optimization or generic phrases like "click here." Example: "Learn about <em>SEO best practices</em>."<br>
                            ✅Avoid Duplicate Anchor Text: Do not link the same anchor text to multiple pages, as it can confuse search engines about page priority.<br>
                            ✅Orphaned Pages: Regularly check for and link to orphaned pages (pages with no internal links pointing to them).<br>
                            ✅Contextual Linking: Place links in content where they provide value and context to the reader.<br>
                            ✅Navigation Links: Use internal links in navigation menus, breadcrumbs, and footers for better user experience.<br>
                            ✅Track and Update: Periodically review and update internal links to ensure they point to active, up-to-date pages.<br>
                        </span>
                    </li>

                    <li>
                        <input type="checkbox" id="external-linking" /> 
                        <strong>External Linking</strong> 
                        <span>
                            ✅ Link to High-Authority Sources: Reference reputable, relevant websites to back up your claims and add credibility. Example: Linking to Moz or Google guidelines for SEO-related content.<br>
                            ✅ Open in a New Tab: Set external links to open in a new tab to keep users on your site.<br>
                            ✅ Regularly Audit Links: Check for broken external links monthly to maintain a good user experience.<br>
                            ✅ Link Diversity: Balance links to well-known authorities with niche-specific resources for context.<br>
                        </span>
                        <span>
                            <strong>Backlink Strategy:</strong><br>
                            ✅ Aim to acquire 5-10 high-quality backlinks per month from authoritative sites.<br>
                            ✅ Diversify anchor text:<br>
                                ○ 50% branded keywords (e.g., "ArashJavadi.com").<br>
                                ○ 30% descriptive phrases (e.g., "SEO tutorials").<br>
                                ○ 20% exact match keywords (e.g., "WordPress tutorial").<br>
                            ✅ Focus on Contextual Backlinks: Build links from within relevant content rather than generic footer or sidebar links.<br>
                            ✅ Target Industry-Relevant Sites: Obtain backlinks from sites in your niche to improve topical authority.<br>
                            ✅ Avoid Spammy Links: Do not engage with low-quality, irrelevant, or paid link schemes.<br>
                            ✅ Use Outreach Campaigns: Pitch guest posts, collaborations, and PR to build relationships and earn links naturally.<br>
                        </span>
                    </li>

                    <li>
                        <input type="checkbox" id="image-optimization" /> 
                        <strong>Image Optimization </strong> 
                        <span>
                            ✅Alt Text: Include descriptive, keyword-rich alt text for 100% of your images.<br>
                            ✅File Size: Compress images to below 100 KB to improve loading times.<br>
                            ✅File Name: Rename files descriptively (e.g., wordpress-tutorial.jpg instead of IMG12345.jpg).<br>
                            ✅Include at least 1 image per 500 words.
                        </span>
                    </li>
                    <li>
                        <input type="checkbox" id="keyword-density" /> 
                        <strong>Keyword Density </strong> 
                        <span> ✅Aim for 1-2% keyword density in your content (1-2 mentions per 100 words).</span>
                        <span> ✅Avoid keyword stuffing; keep the placement natural.</span>
                    </li>
                </ul>

                <h4>Medium Priority for Enhancement</h4>
                <ul>
                    <li>
                        <input type="checkbox" id="ctr-optimization" /> 
                        <strong>CTR (Click-Through Rate)</strong> 
                        <span>
                            ✅ Use strong action verbs like "Download" or "Learn Now."<br>
                            ✅ Add buttons or banners near the top of the page.<br>
                            ✅ Write for Users First: Ensure content is helpful, readable, and meets user intent. (e.g., "Get Free Tips").<br>
                            ✅ Test and Improve: Use A/B testing for CTAs, headlines, and layouts<br>
                        </span>
                    </li>
                    <li>
                        <input type="checkbox" id="bounce-rate" /> 
                        <strong>Bounce Rate</strong> 
                        <span>
                            ✅Target a bounce rate of 50% or lower.
                            ✅Engage users with multimedia like images, videos, and interactive elements.
                            ✅Ensure clear CTAs to guide users to other pages.
                        </span>
                    </li>
                    <li>
                        <input type="checkbox" id="social-sharing" /> 
                        <strong>Social Sharing</strong> 
                        <span>
                            ✅ Add sharing buttons on key pages and blog posts. <br />
                            ✅ Optimize Open Graph tags (title, description, image). <br />
                            ✅ Set Twitter Card metadata for better sharing on Twitter. <br />
                            ✅ Use clear CTAs like "Share this post" to encourage sharing.
                        </span>
                    </li>
                    <li>
                        <input type="checkbox" id="breadcrumbs" /> 
                        <strong>Breadcrumbs Navigation</strong> 
                        <span>
                            ✅ Implement Breadcrumb Schema: Add breadcrumb schema markup for e-commerce, blog categories, and other hierarchical pages to improve visibility in search results.<br>
                            ✅ Ensure Clickable Links: Make breadcrumb links clickable to improve navigation and user experience.<br>
                            ✅ Maintain Logical Hierarchy: Ensure the breadcrumb path reflects the structure of your site (e.g., Home > Category > Subcategory > Page).<br>
                            ✅ Mobile-Friendly Design: Ensure breadcrumbs are responsive and easy to navigate on all devices.<br>
                            ✅ SEO Optimization: Use concise, keyword-rich breadcrumb labels that match the page content while avoiding keyword stuffing.
                        </span>
                    </li>
                    <li>
                        <input type="checkbox" id="pagination" /> 
                        <strong>Pagination</strong> 
                        <span>
                            ✅ Correct rel="prev" and rel="next" Implementation: Add rel="prev" and rel="next" attributes in the page's `<head>` section for paginated content to help search engines understand the sequence.<br>
                            ✅ Canonical Tag Usage: Use self-referential canonical tags on each paginated page to prevent duplicate content issues.<br>
                            ✅ User-Friendly Navigation: Ensure paginated links are intuitive, clickable, and prominently displayed at the top and bottom of the content.<br>
                            ✅ Unique Meta Titles and Descriptions: Avoid duplicate meta information on paginated pages by including the page number (e.g., "SEO Best Practices - Page 2").<br>
                            ✅ Load Testing: Ensure paginated pages load quickly and perform well across devices.<br>
                            ✅ Optimize Content on Pagination Pages: Provide summary or unique introductory content on each page to retain value and engagement.<br>
                            ✅ Mobile-Friendly Pagination: Use designs like "Load More" buttons or infinite scroll for better user experience on mobile devices (but retain traditional pagination in the HTML for search engines).
                        </span>
                    </li>

                    <li>
                        <input type="checkbox" id="video-seo" /> 
                        <strong>Video SEO</strong> 
                        <span>✅Video Hosting: Use YouTube or Vimeo for broad visibility, but also consider self-hosting for on-site SEO benefits.</span>
                        <span>✅Video Metadata: Include a descriptive title, meta description, and relevant tags for videos.<br>
                        Use keywords naturally in the video title and description.
                        </span>
                        <span>✅Video Transcript: Add a transcript of the video on the same page to help search engines and improve accessibility.</span>
                        <span>✅Thumbnails: Use custom, high-quality thumbnails with descriptive alt text and file names.</span>
                        <span>✅Lazy Loading: Implement lazy loading for videos using attributes like loading="lazy" or third-party plugins</span>
                        <span>✅Placement: Position videos prominently above the fold for better user engagement.</span>
                    </li>

                    <li>
                        <input type="checkbox" id="content-length" />
                        <strong>Optimal Content Length</strong>
                        <span> 	
                            ✅ Blog Posts: 1,500–2,000 words for in-depth content.<br/>
                            ✅ Product Pages: 300–500 words of unique, engaging content.<br/>
                            ✅ Engagement Tip: Break long content into smaller sections with subheadings (H2, H3) every 150-300 words.
                        </span>
                    </li>
                    <li>
                        <input type="checkbox" id="user-engagement" />
                        <strong>User Engagement</strong>
                        <span> 	
                            ✅ Storytelling and Multimedia: Add storytelling elements, images, videos, and infographics to capture user interest and make content memorable.<br/>
                            ✅ FAQs and Interactive Elements: Include FAQs, polls, quizzes, or interactive tools to engage users and keep them on the page longer.<br/>
                            ✅ Encourage Interaction: Add clear and compelling CTAs (Call-to-Actions) such as "Download Now," "Sign Up," or "Explore More" to guide user behavior.<br/>
                            ✅ Optimize for Time on Page: Aim for at least 2-3 minutes per session by creating in-depth, well-structured, and visually appealing content.<br/>
                            ✅ Internal Links: Add relevant internal links to encourage users to explore other parts of your website.<br/>
                            ✅ Personalized Content: Use dynamic or personalized elements (e.g., "Recommended for You") to tailor content to user preferences and needs.
                        </span>
                    </li>

                    <li>
                        <input type="checkbox" id="cta-buttons" /> 
                        <strong>CTA Techniques</strong> 

                        <span><strong>CTA Buttons</strong> </span> 
                        <span>
                            ✅ Place clickable buttons prominently on the page.<br>
                            ✅ Use action-driven language like "Sign Up Now," "Learn More," or "Get Started."<br>
                            ✅ Ensure buttons are visually distinct with contrasting colors and proper padding.
                        </span>

                        <span><strong>CTA Banners</strong></span> 
                        <span>
                            ✅ Use banners to promote key actions or special offers.<br>
                            ✅ Place them at key points on the page (e.g., above the fold).<br>
                            ✅ Add strong CTAs like "Limited Time Offer—Get It Now!".
                        </span>

                        <span><strong>Engaging CTAs for Blog Posts</strong></span>     
                        <span>
                            ✅ Encourage readers to read related posts (e.g., "Explore More SEO Tips").<br>
                            ✅ Offer downloadable resources: "Download Free SEO Guide."<br>
                            ✅ Add share buttons to promote social engagement.
                        </span>

                        <span><strong>Effective CTAs for Landing Pages</strong> </span>
                        <span>
                            ✅ Use bold, concise headlines.<br>
                            ✅ Include customer-centric benefits (e.g., "Start Your Free Trial Today and Save 20%").<br>
                            ✅ Ensure CTAs are aligned with the page's goal (e.g., "Sign Up for More Details").<br>
                        </span>
                    </li>
                    <li>
                        <input type="checkbox" id="content-updates" />
                        <strong>Content Updates</strong>
                        <span id="content-updates-date">❌ Last change: Not updated</span>
                        <span>✅ Periodically update content and add new internal links.</span>
                        <span>✅ Check and fix broken links monthly.<br></span>
                    </li>
                </ul>

            </div>
        </div>
    </div>
    <?php
});


// Save checklist data via AJAX
add_action('wp_ajax_save_seo_checklist', function() {
    if ( ! isset($_POST['post_id'], $_POST['checklist']) ) {
        wp_send_json_error(['message' => 'Invalid request.']);
    }

    $post_id = intval($_POST['post_id']);
    $checklist = json_decode(stripslashes($_POST['checklist']), true);

    // Handle specific logic for 'content-updates'
    if (isset($checklist['content-updates']) && $checklist['content-updates']['checked']) {
        $checklist['content-updates']['date'] = current_time('Y-m-d H:i:s');
    }

    update_post_meta($post_id, '_seo_checklist', json_encode($checklist));
    wp_send_json_success(['message' => 'Checklist saved successfully.']);
});


// Retrieve checklist data via AJAX
add_action('wp_ajax_get_seo_checklist', function() {
    if ( ! isset($_POST['post_id']) ) {
        wp_send_json_error(['message' => 'Invalid request.']);
    }

    $post_id = intval($_POST['post_id']);
    $checklist = get_post_meta($post_id, '_seo_checklist', true);

    wp_send_json_success(['checklist' => $checklist]);
});


//__________________________________________________________________________//
//                       ADD META BOX ON POST & PAGE EDIT
//__________________________________________________________________________//    

// Add a meta box to the post/page edit screen
add_action('add_meta_boxes', function () {
    add_meta_box(
        'seo_checklist_meta_box',         // Unique ID
        '🚀 This Page SEO Checklist',                 // Box title
        'seo_checklist_meta_box_content', // Callback function
        ['post', 'page'],                 // Post types
        'side',                           // Context (side, normal, advanced)
        'high'                            // Priority
    );
});


// Render the content of the meta box
function seo_checklist_meta_box_content($post) {
    // Retrieve saved checklist data
    $checklist = get_post_meta($post->ID, '_seo_checklist_meta', true);
    $checklist = $checklist ? json_decode($checklist, true) : [];

    echo '
    <div id="seo-checklist-meta-box">
        <ul>
            <li>
            <input type="checkbox" id="keywords" ' . checked(!empty($checklist['keywords']), true, false) . ' /> 
            <strong>Keywords</strong> 
            <span>
                ✅ Research: Use tools like Google Keyword Planner, Ubersuggest, SemRush, or Ahrefs to find high-volume, low-competition keywords.<br>
                ✅ Primary Keyword: Ensure the primary keyword matches the users search intent.<br>
                ✅ Long-Tail Keywords: Incorporate long-tail keywords for more specific and targeted traffic.<br>
                ✅ Keyword Placement: Naturally integrate the primary keyword in key areas:<br>
                    - Title Tag<br>
                    - Meta Description<br>
                    - URL<br>
                ✅ Synonyms and Variations: Use LSI (Latent Semantic Indexing) keywords and synonyms to increase topical relevance.<br>
                ✅ Competitor Analysis: Analyze competitor content to identify relevant keyword opportunities.<br>
                ✅ Update and Refresh: Regularly review and update keywords to reflect current trends and search behavior.
            </span>
            </li>
            <li>
                <input type="checkbox" id="title-tag" ' . checked(!empty($checklist['title-tag']), true, false) . ' /> 
                <strong>SEO Title Tag</strong> 
                <span>✅Place the primary keyword at the beginning. </span>
                <span>✅Keep title tags between 50-60 characters. </span>
                <span>
                    ✅CTR Boost: Use numbers or lists, brackets, or power words to increase click-through rates.<br> 
                    Example: "WordPress Tutorial [Step-by-Step Guide] – Updated 2025."<br>
                    Example: "10 Proven Tips to Improve Your WordPress SEO"
                </span>
                <span>
                    ✅Create Curiosity or Urgency: 
                    Phrases like "Step-by-Step Guide," "Essential Tips," or "Don’t Miss Out" can encourage clicks.<br>
                    Example: "Master SEO for WordPress: Don’t Miss These Tips"
                <span>
            </li>
            <li>
                <input type="checkbox" id="meta-description" ' . checked(!empty($checklist['meta-description']), true, false) . ' /> 
                <strong>Meta Description</strong> 
                <span>✅Write meta descriptions between 120-160 characters. </span>
                <span>✅Include the primary keyword once. </span>
                <span>
                    ✅Highlight Benefits or Solutions:<br> 
                    Example: "Struggling with low traffic? Learn SEO tips to rank your WordPress site higher and attract more visitors."
                </span>
                <span>
                    ✅Encourage users to take action with phrases like "Learn More," "Download Now," or "Start Today."<br> 
                    Example: "Ready to optimize your WordPress site? Follow our simple SEO tutorial now!"
                </span>
                <span>
                    ✅Make It Unique:<br> 
                    Avoid duplicate meta descriptions for different pages. Tailor each description to the content.
                </span>
            </li>
            <li>
                <input type="checkbox" id="url-slug" ' . checked(!empty($checklist['url-slug']), true, false) . ' /> 
                <strong>Slug</strong> 
                <span>✅Use the keyword in a short, descriptive and readable URL (e.g., example.com/wordpress-tutorial). </span>
                <span>✅Keep URLs under 75 characters. </span>
                <span>✅Avoid unnecessary parameters; use hyphens (-) instead of underscores (_). </span>
                <span>✅Include the Target Keyword: Keywords in the URL reassure users that your page matches their search.</span>
                <span>✅Match URL to Content: Ensure the URL reflects the page content accurately to avoid misleading users.</span>
                <span>✅Avoid Special Characters and Numbers: <br>
                        Good: /seo-guide<br>
                        Bad: /page12345
                </span>
                <span>✅Link Depth: Ensure important pages are no more than 3 clicks away from the homepage.<br></span>
            </li>
            <li>
                <input type="checkbox" id="structured-data" ' . checked(!empty($checklist['structured-data']), true, false) . ' /> 
                <strong>Structured Data</strong> 
                <span>✅Use schema markup: </span>
                <span>
                    * Article:  Use for blog posts or news articles to help them appear in Top Stories or with rich headlines.<br>
                    * Product:  Include for product pages to display key details like price, availability, and ratings.<br>
                    * FAQ:  Use for FAQ sections to display collapsible Q&A snippets in search results.<br>
                    * Review:  Add for review pages to highlight user ratings, reviews, and stars.
                </span>
                <span>✅Test Schema Markup: </span>
                <span>
                    * Use <a href="https://search.google.com/test/rich-results" target="_blank">Google’s Structured Data Testing Tool</a> to check for errors or warnings.<br>
                    * Ensure markup is valid and matches the content on the page.
                </span>
                <span>✅Prioritize Pages: </span>
                <span>Focus on high-traffic or conversion-focused pages (e.g., homepage, key product pages, blog posts). </span>
            </li>
            <li>
                <input type="checkbox" id="build-backlink" ' . checked(!empty($checklist['build-backlink']), true, false) . ' /> 
                <strong>Build Backlinks For this Post</strong> 
                <span>✅Share the page or post with influencers and build backlinks.</span>
                <span>✅Share each post on at least 3 social platforms.</span>
                <span>✅Guest Blogging:<br>
                Find reputable blogs in your niche with "Write for Us" pages.
                </span>
                <span>✅Broken Link Building:<br>
                Use tools like Ahrefs, Screaming Frog, or Check My Links to find broken links on related sites.<br>
                Contact the site owner and suggest your content as a replacement for the broken link.
                </span>
                <span>✅Directory and Local Listings:<br>
                Add your site to reputable directories like Yelp, Yellow Pages, or Google My Business.<br>
                Use niche-specific directories for your industry.
                </span>
                <span>✅Skyscraper Technique:<br>
                Use tools like Ahrefs or SEMrush to find high-performing content in your niche.<br>
                Create a better, more comprehensive version of it.<br>
                Outreach to sites linking to the original content and suggest your improved version.
                </span>
                <span>✅Host or Participate in Webinars:<br>
                Host webinars on trending topics in your niche.<br>
                Share the replay on your site and ask participants to link to it.
                </span>
                <span>✅Leverage Testimonials and Reviews:<br>
                Write testimonials for tools or services you use.<br>
                Include your name, company name, and a link to your website.
                </span>
                <span>✅Use HARO (Help a Reporter Out):<br>
                Sign up at <a href="https://www.connectively.us/" target="_blank">Help a Reporter Out</a>.<br>
                Respond to relevant journalist queries with valuable insights and include your website link.
                </span>
                <span>✅Repurpose and Promote Existing Content:<br>
                Turn blog posts into videos, infographics, or presentations.<br>
                Share them on platforms like YouTube, SlideShare, or Pinterest.
                </span>
            </li>
        </ul>
    </div>
';
}



// Save checklist state via AJAX
add_action('wp_ajax_save_seo_checklist_meta', function () {
    if (!isset($_POST['post_id'], $_POST['checklist'])) {
        wp_send_json_error(['message' => 'Invalid request.']);
    }

    $post_id = intval($_POST['post_id']);
    $checklist = json_decode(stripslashes($_POST['checklist']), true);

    // Save the checklist to the database
    update_post_meta($post_id, '_seo_checklist_meta', json_encode($checklist));

    wp_send_json_success(['message' => 'Checklist saved successfully.']);
});


add_action('wp_ajax_save_seo_checklist', function() {
    if (!isset($_POST['post_id'], $_POST['checklist'])) {
        wp_send_json_error(['message' => 'Invalid request.']);
    }

    $post_id = intval($_POST['post_id']);
    $checklist = json_decode(stripslashes($_POST['checklist']), true);

    update_post_meta($post_id, '_seo_checklist', json_encode($checklist));
    wp_send_json_success(['message' => 'Checklist saved successfully.']);
});

add_action('wp_ajax_nopriv_save_seo_checklist', function() {
    if (!isset($_POST['post_id'], $_POST['checklist'])) {
        wp_send_json_error(['message' => 'Invalid request.']);
    }

    $post_id = intval($_POST['post_id']);
    $checklist = json_decode(stripslashes($_POST['checklist']), true);

    update_post_meta($post_id, '_seo_checklist', json_encode($checklist));
    wp_send_json_success(['message' => 'Checklist saved successfully.']);
});
