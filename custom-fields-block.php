<?php

/**
 * Plugin Name: Custom Fields Block
 * Plugin URI: https://github.com/your-username/custom-fields-block
 * Description: Fügt native WordPress Custom Fields als Blöcke mit Typografie- und Farboptionen ein
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL v2 or later
 * Text Domain: custom-fields-block
 * Domain Path: /languages
 * Update URI: https://github.com/your-username/custom-fields-block/releases/latest/download/custom-fields-block.zip
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('CFB_VERSION', '1.0.0');
define('CFB_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CFB_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CFB_GITHUB_REPO', 'gbyat/custom-fields-block');

/**
 * Main plugin class
 */
class CustomFieldsBlock
{

    public function __init__()
    {
        add_action('init', array($this, 'init'));
        add_action('enqueue_block_editor_assets', array($this, 'enqueue_block_editor_assets'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));

        // Update system
        add_filter('pre_set_site_transient_update_plugins', array($this, 'check_for_updates'));
        add_filter('plugins_api', array($this, 'plugin_info'), 10, 3);
        add_filter('upgrader_post_install', array($this, 'upgrader_post_install'), 10, 3);

        // Admin settings
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'init_settings'));
    }

    /**
     * Check for plugin updates
     */
    public function check_for_updates($transient)
    {
        if (empty($transient->checked)) {
            return $transient;
        }

        $plugin_slug = basename(dirname(__FILE__));
        $plugin_file = basename(__FILE__);
        $plugin_path = $plugin_slug . '/' . $plugin_file;

        // Get latest release info from GitHub
        $latest_release = $this->get_latest_release();

        if ($latest_release && version_compare($latest_release['version'], CFB_VERSION, '>')) {
            $transient->response[$plugin_path] = (object) array(
                'slug' => $plugin_slug,
                'new_version' => $latest_release['version'],
                'url' => 'https://github.com/' . CFB_GITHUB_REPO,
                'package' => $latest_release['download_url'],
                'requires' => '5.0',
                'requires_php' => '7.4',
                'tested' => '6.4',
                'last_updated' => $latest_release['published_at'],
                'sections' => array(
                    'description' => $latest_release['description'],
                    'changelog' => $latest_release['changelog']
                )
            );
        }

        return $transient;
    }

    /**
     * Get plugin information for update screen
     */
    public function plugin_info($result, $action, $args)
    {
        if ($action !== 'plugin_information') {
            return $result;
        }

        $plugin_slug = basename(dirname(__FILE__));

        if ($args->slug !== $plugin_slug) {
            return $result;
        }

        $latest_release = $this->get_latest_release();

        if (!$latest_release) {
            return $result;
        }

        return (object) array(
            'name' => 'Custom Fields Block',
            'slug' => $plugin_slug,
            'version' => $latest_release['version'],
            'author' => 'Your Name',
            'author_profile' => 'https://github.com/your-username',
            'last_updated' => $latest_release['published_at'],
            'requires' => '5.0',
            'requires_php' => '7.4',
            'tested' => '6.4',
            'download_link' => $latest_release['download_url'],
            'sections' => array(
                'description' => $latest_release['description'],
                'changelog' => $latest_release['changelog'],
                'installation' => 'Upload the plugin files to the /wp-content/plugins/custom-fields-block directory, or install the plugin through the WordPress plugins screen directly.',
                'screenshots' => ''
            )
        );
    }

    /**
     * Get latest release from GitHub
     */
    private function get_latest_release()
    {
        $cache_key = 'cfb_latest_release';
        $cached = get_transient($cache_key);

        if ($cached !== false) {
            return $cached;
        }

        $api_url = 'https://api.github.com/repos/' . CFB_GITHUB_REPO . '/releases/latest';

        $headers = array(
            'User-Agent' => 'WordPress/' . get_bloginfo('version'),
            'Accept' => 'application/vnd.github.v3+json'
        );

        // Add GitHub token if available (for private repos)
        $github_token = defined('CFB_GITHUB_TOKEN') ? CFB_GITHUB_TOKEN : '';
        if (!empty($github_token)) {
            $headers['Authorization'] = 'token ' . $github_token;
        }

        $response = wp_remote_get($api_url, array(
            'headers' => $headers,
            'timeout' => 15
        ));

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $release = json_decode($body, true);

        if (!$release) {
            return false;
        }

        // Find the plugin zip file
        $download_url = '';
        foreach ($release['assets'] as $asset) {
            if ($asset['name'] === 'custom-fields-block.zip') {
                $download_url = $asset['browser_download_url'];
                break;
            }
        }

        $release_data = array(
            'version' => ltrim($release['tag_name'], 'v'),
            'download_url' => $download_url,
            'published_at' => $release['published_at'],
            'description' => $release['body'],
            'changelog' => $release['body']
        );

        // Cache for 12 hours
        set_transient($cache_key, $release_data, 12 * 3600);

        return $release_data;
    }

    /**
     * Handle post-installation
     */
    public function upgrader_post_install($response, $hook_extra, $result)
    {
        if (isset($hook_extra['plugin']) && $hook_extra['plugin'] === basename(__FILE__)) {
            // Clear update cache
            delete_transient('cfb_latest_release');
        }

        return $response;
    }

    /**
     * Initialize the plugin
     */
    public function init()
    {
        // Register block
        register_block_type(CFB_PLUGIN_DIR . 'build', array(
            'render_callback' => array($this, 'render_block'),
        ));

        // Load text domain
        load_plugin_textdomain('custom-fields-block', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    /**
     * Enqueue block editor assets
     */
    public function enqueue_block_editor_assets()
    {
        wp_enqueue_script(
            'custom-fields-block-editor',
            CFB_PLUGIN_URL . 'build/index.js',
            array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n'),
            CFB_VERSION
        );

        wp_enqueue_style(
            'custom-fields-block-editor',
            CFB_PLUGIN_URL . 'build/index.css',
            array('wp-edit-blocks'),
            CFB_VERSION
        );

        // Localize script with custom fields data
        wp_localize_script('custom-fields-block-editor', 'cfbData', array(
            'customFields' => $this->get_custom_fields(),
            'nonce' => wp_create_nonce('cfb_nonce'),
        ));
    }

    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets()
    {
        wp_enqueue_style(
            'custom-fields-block-frontend',
            CFB_PLUGIN_URL . 'build/style.css',
            array(),
            CFB_VERSION
        );
    }

    /**
     * Get all custom fields for the current post
     */
    private function get_custom_fields()
    {
        global $post;

        if (!$post) {
            return array();
        }

        $custom_fields = get_post_custom($post->ID);
        $fields = array();

        foreach ($custom_fields as $key => $values) {
            // Skip internal WordPress fields
            if (strpos($key, '_') === 0) {
                continue;
            }

            $fields[] = array(
                'key' => $key,
                'label' => $this->format_field_name($key),
                'value' => is_array($values) ? $values[0] : $values,
            );
        }

        return $fields;
    }

    /**
     * Format field name for display
     */
    private function format_field_name($key)
    {
        return ucwords(str_replace(array('_', '-'), ' ', $key));
    }

    /**
     * Render the block
     */
    public function render_block($attributes, $content)
    {
        $field_key = isset($attributes['fieldKey']) ? $attributes['fieldKey'] : '';
        $display_type = isset($attributes['displayType']) ? $attributes['displayType'] : 'paragraph';
        $typography = isset($attributes['typography']) ? $attributes['typography'] : array();
        $colors = isset($attributes['colors']) ? $attributes['colors'] : array();
        $spacing = isset($attributes['spacing']) ? $attributes['spacing'] : array();
        $alignment = isset($attributes['alignment']) ? $attributes['alignment'] : '';

        if (empty($field_key)) {
            return '';
        }

        $field_value = get_post_meta(get_the_ID(), $field_key, true);

        if (empty($field_value)) {
            return '';
        }

        // Build inline styles
        $styles = array();

        // Typography styles
        if (!empty($typography)) {
            if (!empty($typography['fontSize'])) {
                $styles[] = 'font-size: ' . $typography['fontSize'] . 'px';
            }
            if (!empty($typography['fontWeight'])) {
                $styles[] = 'font-weight: ' . $typography['fontWeight'];
            }
            if (!empty($typography['lineHeight'])) {
                $styles[] = 'line-height: ' . $typography['lineHeight'];
            }
            if (!empty($typography['letterSpacing'])) {
                $styles[] = 'letter-spacing: ' . $typography['letterSpacing'] . 'px';
            }
        }

        // Color styles
        if (!empty($colors)) {
            if (!empty($colors['textColor'])) {
                $styles[] = 'color: ' . $colors['textColor'];
            }
            if (!empty($colors['backgroundColor'])) {
                $styles[] = 'background-color: ' . $colors['backgroundColor'];
            }
        }

        // Spacing styles
        if (!empty($spacing)) {
            if (!empty($spacing['marginTop'])) {
                $styles[] = 'margin-top: ' . $spacing['marginTop'] . 'px';
            }
            if (!empty($spacing['marginBottom'])) {
                $styles[] = 'margin-bottom: ' . $spacing['marginBottom'] . 'px';
            }
            if (!empty($spacing['paddingTop'])) {
                $styles[] = 'padding-top: ' . $spacing['paddingTop'] . 'px';
            }
            if (!empty($spacing['paddingBottom'])) {
                $styles[] = 'padding-bottom: ' . $spacing['paddingBottom'] . 'px';
            }
        }

        $style_attr = !empty($styles) ? ' style="' . esc_attr(implode('; ', $styles)) . '"' : '';

        // Build classes
        $classes = array('cfb-block');
        if (!empty($alignment)) {
            $classes[] = 'has-text-align-' . $alignment;
        }
        if (!empty($colors['textColor'])) {
            $classes[] = 'has-text-color';
        }
        if (!empty($colors['backgroundColor'])) {
            $classes[] = 'has-background';
        }

        $class_attr = ' class="' . esc_attr(implode(' ', $classes)) . '"';

        // Render based on display type
        switch ($display_type) {
            case 'heading':
                $tag = 'h2';
                if (!empty($typography['fontSize'])) {
                    if ($typography['fontSize'] <= 16) $tag = 'h6';
                    elseif ($typography['fontSize'] <= 20) $tag = 'h5';
                    elseif ($typography['fontSize'] <= 24) $tag = 'h4';
                    elseif ($typography['fontSize'] <= 32) $tag = 'h3';
                    elseif ($typography['fontSize'] <= 40) $tag = 'h2';
                    else $tag = 'h1';
                }
                return '<' . $tag . $class_attr . $style_attr . '>' . esc_html($field_value) . '</' . $tag . '>';

            case 'paragraph':
            default:
                return '<p' . $class_attr . $style_attr . '>' . esc_html($field_value) . '</p>';
        }
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu()
    {
        add_options_page(
            'Custom Fields Block Settings',
            'Custom Fields Block',
            'manage_options',
            'custom-fields-block-settings',
            array($this, 'settings_page')
        );
    }

    /**
     * Initialize settings
     */
    public function init_settings()
    {
        register_setting('cfb_settings', 'cfb_github_token');

        add_settings_section(
            'cfb_github_section',
            'GitHub Update Settings',
            array($this, 'github_section_callback'),
            'custom-fields-block-settings'
        );

        add_settings_field(
            'cfb_github_token',
            'GitHub Personal Access Token',
            array($this, 'github_token_callback'),
            'custom-fields-block-settings',
            'cfb_github_section'
        );
    }

    /**
     * GitHub section callback
     */
    public function github_section_callback()
    {
        echo '<p>Configure GitHub settings for automatic plugin updates from your private repository.</p>';
        echo '<p><strong>How to get a GitHub Token:</strong></p>';
        echo '<ol>';
        echo '<li>Go to <a href="https://github.com/settings/tokens" target="_blank">GitHub Settings → Developer settings → Personal access tokens</a></li>';
        echo '<li>Click "Generate new token (classic)"</li>';
        echo '<li>Give it a name like "WordPress Plugin Updates"</li>';
        echo '<li>Select scopes: <code>repo</code> and <code>workflow</code></li>';
        echo '<li>Generate and copy the token</li>';
        echo '</ol>';
    }

    /**
     * GitHub token field callback
     */
    public function github_token_callback()
    {
        $token = get_option('cfb_github_token', '');
        $masked_token = !empty($token) ? str_repeat('*', strlen($token) - 4) . substr($token, -4) : '';

        echo '<input type="password" id="cfb_github_token" name="cfb_github_token" value="' . esc_attr($token) . '" class="regular-text" />';
        echo '<p class="description">Enter your GitHub Personal Access Token for private repository access.</p>';

        if (!empty($token)) {
            echo '<p><strong>Current token:</strong> ' . esc_html($masked_token) . '</p>';
            echo '<p><a href="#" onclick="document.getElementById(\'cfb_github_token\').value=\'\'; return false;">Clear token</a></p>';
        }
    }

    /**
     * Settings page
     */
    public function settings_page()
    {
        if (isset($_POST['submit'])) {
            update_option('cfb_github_token', sanitize_text_field($_POST['cfb_github_token']));
            echo '<div class="notice notice-success"><p>Settings saved successfully!</p></div>';

            // Clear update cache
            delete_transient('cfb_latest_release');
        }

        $token = get_option('cfb_github_token', '');
?>
        <div class="wrap">
            <h1>Custom Fields Block Settings</h1>

            <form method="post" action="">
                <table class="form-table">
                    <tr>
                        <th scope="row">GitHub Personal Access Token</th>
                        <td>
                            <input type="password" name="cfb_github_token" value="<?php echo esc_attr($token); ?>" class="regular-text" />
                            <p class="description">
                                Required for automatic updates from private GitHub repository.
                                <a href="https://github.com/settings/tokens" target="_blank">Create token here</a>
                            </p>
                            <?php if (!empty($token)): ?>
                                <p><strong>Current token:</strong> <?php echo esc_html(str_repeat('*', strlen($token) - 4) . substr($token, -4)); ?></p>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Repository</th>
                        <td>
                            <code><?php echo esc_html(CFB_GITHUB_REPO); ?></code>
                            <p class="description">Your GitHub repository for plugin updates</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Current Version</th>
                        <td>
                            <code><?php echo esc_html(CFB_VERSION); ?></code>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Settings">
                    <a href="<?php echo admin_url('plugins.php'); ?>" class="button">Back to Plugins</a>
                </p>
            </form>

            <h2>Test Update Check</h2>
            <p>Click the button below to manually check for updates:</p>
            <a href="<?php echo admin_url('update-core.php'); ?>" class="button">Check for Updates</a>

            <h2>Help</h2>
            <div class="card">
                <h3>How to get a GitHub Token:</h3>
                <ol>
                    <li>Go to <a href="https://github.com/settings/tokens" target="_blank">GitHub Settings → Developer settings → Personal access tokens</a></li>
                    <li>Click "Generate new token (classic)"</li>
                    <li>Give it a name like "WordPress Plugin Updates"</li>
                    <li>Select scopes: <code>repo</code> and <code>workflow</code></li>
                    <li>Generate and copy the token</li>
                    <li>Paste it in the field above and save</li>
                </ol>

                <h3>Creating a Release:</h3>
                <ol>
                    <li>Make your changes and commit them</li>
                    <li>Run: <code>npm run release:patch</code> (or minor/major)</li>
                    <li>GitHub will automatically create a release with the plugin ZIP</li>
                    <li>WordPress will detect the update and show it in the admin</li>
                </ol>
            </div>
        </div>
<?php
    }
}

// Initialize the plugin
$custom_fields_block = new CustomFieldsBlock();
$custom_fields_block->__init__();
