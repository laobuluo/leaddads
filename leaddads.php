<?php
/**
 * Plugin Name: LeAddAds
 * Plugin URI: https://www.laojiang.me/7246.html
 * Description: 一个WordPress插件，允许您在文章的特定段落后插入广告。
* Version: 1.0.0
 * Author: 老蒋和他的小伙伴
 * Author URI: https://www.laojiang.me
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: leaddads
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Plugin activation
function leaddads_activate() {
    // Add default options
    $default_options = array(
        'enabled' => 0,
        'ad_code' => '',
        'paragraph_number' => 1
    );
    add_option('leaddads_options', $default_options);
}
register_activation_hook(__FILE__, 'leaddads_activate');

// Plugin deactivation
function leaddads_deactivate() {
    // Cleanup if needed
}
register_deactivation_hook(__FILE__, 'leaddads_deactivate');

// Add settings menu
function leaddads_add_admin_menu() {
    add_options_page(
        'LeAddAds 设置',
        'LeAddAds',
        'manage_options',
        'leaddads',
        'leaddads_options_page'
    );
}
add_action('admin_menu', 'leaddads_add_admin_menu');

// Register settings
function leaddads_settings_init() {
    register_setting('leaddads', 'leaddads_options');
    
    add_settings_section(
        'leaddads_section',
        __('广告设置', 'leaddads'),
        'leaddads_section_callback',
        'leaddads'
    );

    add_settings_field(
        'leaddads_enabled',
        __('启用插件', 'leaddads'),
        'leaddads_enabled_render',
        'leaddads',
        'leaddads_section'
    );

    add_settings_field(
        'leaddads_ad_code',
        __('广告代码', 'leaddads'),
        'leaddads_ad_code_render',
        'leaddads',
        'leaddads_section'
    );

    add_settings_field(
        'leaddads_paragraph_number',
        __('插入段落后', 'leaddads'),
        'leaddads_paragraph_number_render',
        'leaddads',
        'leaddads_section'
    );
}
add_action('admin_init', 'leaddads_settings_init');

// Settings section callback
function leaddads_section_callback() {
    echo __('配置您的广告设置如下:', 'leaddads');
}

// Render settings fields
function leaddads_enabled_render() {
    $options = get_option('leaddads_options');
    ?>
    <input type='checkbox' name='leaddads_options[enabled]' <?php checked($options['enabled'], 1); ?> value='1'>
    <?php
}

function leaddads_ad_code_render() {
    $options = get_option('leaddads_options');
    ?>
    <textarea name='leaddads_options[ad_code]' rows='10' cols='50'><?php echo esc_textarea($options['ad_code']); ?></textarea>
    <p class="description"><?php _e('输入您的广告HTML/JavaScript代码在这里。', 'leaddads'); ?></p>
    <?php
}

function leaddads_paragraph_number_render() {
    $options = get_option('leaddads_options');
    ?>
    <select name='leaddads_options[paragraph_number]'>
        <option value='1' <?php selected($options['paragraph_number'], 1); ?>><?php _e('第一段后', 'leaddads'); ?></option>
        <option value='2' <?php selected($options['paragraph_number'], 2); ?>><?php _e('第二段后', 'leaddads'); ?></option>
        <option value='3' <?php selected($options['paragraph_number'], 3); ?>><?php _e('第三段后', 'leaddads'); ?></option>
        <option value='4' <?php selected($options['paragraph_number'], 4); ?>><?php _e('第四段后', 'leaddads'); ?></option>
        <option value='5' <?php selected($options['paragraph_number'], 5); ?>><?php _e('第五段后', 'leaddads'); ?></option>
    </select>
    <?php
}

// Settings page
function leaddads_options_page() {
    ?>
    <form action='options.php' method='post'>
        <h2>LeAddAds 设置</h2>
        <p>这款插件适合在文章的特定段落后插入广告。<a href="https://www.laojiang.me/7246.html" target="_blank">插件介绍</a>（关注公众号：<span style="color: red;">老蒋朋友圈</span>）</p>
        <?php
        settings_fields('leaddads');
        do_settings_sections('leaddads');
        submit_button();
        ?>
    </form>
    <p><img width="150" height="150" src="<?php echo plugins_url('/images/wechat.png', __FILE__); ?>" alt="扫码关注公众号" /></p>
    <?php
}

// Main functionality to insert ads
function leaddads_insert_ads($content) {
    $options = get_option('leaddads_options');
    
    // Check if plugin is enabled and we're in a single post
    if (!$options['enabled'] || empty($options['ad_code']) || !is_single()) {
        return $content;
    }

    // Split content into paragraphs
    $paragraphs = explode('</p>', $content);
    
    // Get the target paragraph number (1-based index)
    $target = isset($options['paragraph_number']) ? (int)$options['paragraph_number'] : 1;
    
    // If target is greater than available paragraphs, add to the end
    $target = min($target, count($paragraphs));
    
    // Reconstruct content with ad
    $content = '';
    for ($i = 0; $i < count($paragraphs); $i++) {
        if ($paragraphs[$i]) {
            $content .= $paragraphs[$i] . '</p>';
            if ($i + 1 === $target) {
                $content .= '<div class="leaddads-container">' . $options['ad_code'] . '</div>';
            }
        }
    }
    
    return $content;
}
add_filter('the_content', 'leaddads_insert_ads'); 