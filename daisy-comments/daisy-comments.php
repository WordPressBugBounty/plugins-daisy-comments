<?php
/**
 * Plugin Name:			Daisy Comments
 * Plugin URI:			https://wordpress.org/plugins/daisy-comments/
 * Description:			Disables comment functionality and hides all existing comments from your website.
 * Version:				1.0.12
 * Requires at least:	5.2
 * Requires PHP:		7.2
 * Tested up to:		6.9
 * Author:				DaisyPlugins
 * Author URI:          https://profiles.wordpress.org/daisyplugins/
 * License:				GPL v2 or later
 * License URI:			https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:			daisy-comments
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DaisyComments {
	const DAISY_COMMENTS_SETTING_NAME = 'daisy_comments_disable_all';

	/**
	 * Initialize the plugin
	 */
	public function __construct() {
		// Load text domain

		// Initialize settings
		add_action( 'admin_init', array( $this, 'daisy_comments_init_settings' ) );

		// Add admin menu
		add_action( 'admin_menu', array( $this, 'daisy_comments_add_admin_menu' ) );

		// Disable comments
		$this->daisy_comments_disable_comments();

		// Register uninstall hook
		register_uninstall_hook( __FILE__, array( 'DaisyComments', 'daisy_comments_uninstall' ) );

		// Enqueue admin styles
		add_action( 'admin_enqueue_scripts', array( $this, 'daisy_comments_enqueue_admin_styles' ) );

		// Add plugin action links
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'daisy_comments_add_plugin_action_links' ) );
	}


	/**
	 * Add settings link to plugin actions
	 */
	public function daisy_comments_add_plugin_action_links( $links ) {
		$settings_link = '<a href="' . admin_url( 'admin.php?page=daisy-comments' ) . '">' . __( 'Settings', 'daisy-comments' ) . '</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}

	/**
	 * Disable all comment functionality
	 */
	private function daisy_comments_disable_comments() {
		// If disabled in settings, proceed
		if ( get_option( 'daisy_comments_disable_all', true ) ) {
			// Close comments on the front-end
			add_filter( 'comments_open', '__return_false', 20, 2 );
			add_filter( 'pings_open', '__return_false', 20, 2 );

			// Hide existing comments
			add_filter( 'comments_array', '__return_empty_array', 10, 2 );

add_filter( 'comments_array', array( $this, 'daisy_comments_hide_existing_comments' ), 10, 2 );
add_action( 'wp_head', array( $this, 'daisy_comments_theme_support' ) );
add_action( 'pre_comment_on_post', array( $this, 'daisy_comments_no_wp_comments' ) );

			// Remove comments page in menu
			add_action( 'admin_menu', array( $this, 'daisy_comments_remove_comments_menu' ) );

			// Remove comments links from admin bar
			add_action( 'init', array( $this, 'daisy_comments_remove_admin_bar_comments' ) );

			// Disable support for comments and trackbacks in post types
			add_action( 'admin_init', array( $this, 'daisy_comments_disable_comments_post_types_support' ) );

			// Redirect any user trying to access comments page
			add_action( 'admin_init', array( $this, 'daisy_comments_redirect_comments_admin_page' ) );

			// Remove comments metabox from dashboard
			add_action( 'admin_init', array( $this, 'daisy_comments_remove_comments_dashboard' ) );

			// Remove comments column from admin lists
			add_filter( 'manage_posts_columns', array( $this, 'daisy_comments_remove_comments_column' ) );
			add_filter( 'manage_pages_columns', array( $this, 'daisy_comments_remove_comments_column' ) );

			// Disable comments feed
			add_action( 'wp_loaded', array( $this, 'daisy_comments_disable_comments_feed' ) );
		}
	}

public function daisy_comments_no_wp_comments() {
    wp_die( 'Comments are closed.' );
}

public function daisy_comments_theme_support() {
    ?>
        <style>
            #comments {
                display: none;
            }
            .nocomments,
            .no-comments,
            .has-comments,
            .post-comments,
            .comments-link,
            .comments-area,
            .comment-respond,
            .comments-closed,
            .comments-wrapper,
            .wp-block-comments,
            .comments-area__wrapper,
            .wp-block-post-comments,
            .wp-block-comments-title,
            .wp-block-comment-template,
            .wp-block-comments-query-loop {
                display: none;
            }
            /** Blocksy **/
            li.meta-comments {
                display: none;
            }
        </style>
    <?php
}

// Hide existing comments
public function daisy_comments_hide_existing_comments( $comments ) {
	$comments = array();
	return $comments;
}

	/**
	 * Remove comments menu from admin
	 */
	public function daisy_comments_remove_comments_menu() {
		remove_menu_page( 'edit-comments.php' );
	}

	/**
	 * Remove comments links from admin bar
	 */
	public function daisy_comments_remove_admin_bar_comments() {
		if ( is_admin_bar_showing() ) {
			remove_action( 'admin_bar_menu', 'wp_admin_bar_comments_menu', 60 );
		}
	}

	/**
	 * Disable support for comments and trackbacks in post types
	 */
	public function daisy_comments_disable_comments_post_types_support() {
		$post_types = get_post_types();
		foreach ( $post_types as $post_type ) {
			if ( post_type_supports( $post_type, 'comments' ) ) {
				remove_post_type_support( $post_type, 'comments' );
				remove_post_type_support( $post_type, 'trackbacks' );
			}
		}
	}

	/**
	 * Redirect any user trying to access comments page
	 */
	public function daisy_comments_redirect_comments_admin_page() {
		global $pagenow;

		if ( $pagenow === 'edit-comments.php' || $pagenow === 'options-discussion.php' ) {
			wp_redirect( admin_url() );
			exit;
		}
	}

	/**
	 * Remove comments metabox from dashboard
	 */
	public function daisy_comments_remove_comments_dashboard() {
		remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
	}

	/**
	 * Remove comments column from admin lists
	 */
	public function daisy_comments_remove_comments_column( $columns ) {
		if ( isset( $columns['comments'] ) ) {
			unset( $columns['comments'] );
		}
		return $columns;
	}

	/**
	 * Disable comments feed
	 */
	public function daisy_comments_disable_comments_feed() {
		add_action( 'do_feed', array( $this, 'daisy_comments_disable_feed' ), 1 );
		add_action( 'do_feed_rdf', array( $this, 'daisy_comments_disable_feed' ), 1 );
		add_action( 'do_feed_rss', array( $this, 'daisy_comments_disable_feed' ), 1 );
		add_action( 'do_feed_rss2', array( $this, 'daisy_comments_disable_feed' ), 1 );
		add_action( 'do_feed_atom', array( $this, 'daisy_comments_disable_feed' ), 1 );
		add_action( 'do_feed_rss2_comments', array( $this, 'daisy_comments_disable_feed' ), 1 );
		add_action( 'do_feed_atom_comments', array( $this, 'daisy_comments_disable_feed' ), 1 );
	}

	public function daisy_comments_disable_feed() {
		if ( is_comment_feed() ) {
			wp_die(
				esc_html__( 'Comments are disabled.', 'daisy-comments' ),
				'',
				array( 'response' => 403 )
			);
		}
	}

	/**
	 * Add admin menu
	 */
	public function daisy_comments_add_admin_menu() {
	    add_menu_page(
	        __('Daisy Comments', 'daisy-comments'),
	        __('Daisy Comments', 'daisy-comments'),
	        'manage_options',
	        'daisy-comments',
	        array($this, 'daisy_comments_render_settings_page'),
	        'dashicons-testimonial',
	        80
	    );
	}

	/**
	 * Initialize settings
	 */
	public function daisy_comments_init_settings() {
		register_setting(
			'daisy_comments_settings',
			self::DAISY_COMMENTS_SETTING_NAME,
			array(
				'type'              => 'boolean',
				'sanitize_callback' => 'rest_sanitize_boolean',
				'default'           => true,
			)
		);

		add_settings_section(
			'daisy_comments_main_section',
			__( 'Comment Settings', 'daisy-comments' ),
			array( $this, 'daisy_comments_render_section_info' ),
			'daisy-comments'
		);

		add_settings_field(
			'daisy_comments_disable_all',
			__( 'Disable All Comments', 'daisy-comments' ),
			array( $this, 'daisy_comments_render_toggle_field' ),
			'daisy-comments',
			'daisy_comments_main_section',
			array(
				'label_for'   => 'daisy_comments_disable_all',
				'description' => __( 'Toggle to disable all comment functionality across your website.', 'daisy-comments' ),
			)
		);
	}

	/**
	 * Render section info
	 */
	public function daisy_comments_render_section_info() {
		echo '<p>' . esc_html__( 'Configure how Daisy Comments handles comments on your website.', 'daisy-comments' ) . '</p>';
	}

	/**
	 * Render toggle field
	 */
	public function daisy_comments_render_toggle_field( $args ) {
		$option = get_option( $args['label_for'], true );
		?>
		<label class="switch">
			<input type="checkbox" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="<?php echo esc_attr( $args['label_for'] ); ?>" <?php checked( $option, true ); ?> value="1">
			<span class="slider round"></span>
		</label>
		<?php if ( ! empty( $args['description'] ) ) : ?>
			<p class="description"><?php echo esc_html( $args['description'] ); ?></p>
		<?php endif; ?>
		<?php
	}

	/**
	 * Render settings page
	 */
	public function daisy_comments_render_settings_page() {
	    // Check user capabilities
	    if (!current_user_can('manage_options')) {
	        return;
	    }

	    // Handle form submission
	    if (isset($_POST['submit']) && check_admin_referer('daisy_comments_save_settings', '_wpnonce')) {
	        // Save the setting manually
	        $disable_comments = isset($_POST['daisy_comments_disable_all']) ? true : false;
	        update_option('daisy_comments_disable_all', $disable_comments);
	        
	        // Add success message
	        add_settings_error(
	            'daisy_comments_messages',
	            'daisy_comments_message',
	            esc_html__('Settings Saved', 'daisy-comments'),
	            'updated'
	        );
	    }

	    // Show error/update messages
	    settings_errors('daisy_comments_messages');
	    ?>
	    <div class="wrap daisy-comments-settings">
	        <div class="daisy-comments-header">
	            <div class="daisy-comments-header-content">
	                <h1><?php esc_html_e('Daisy Comments Control Panel', 'daisy-comments'); ?></h1>
	                <p><?php esc_html_e('Easily manage comment functionality across your entire WordPress website.', 'daisy-comments'); ?></p>
	            </div>
	            <div class="daisy-comments-header-icon">
	                <span class="dashicons dashicons-testimonial"></span>
	            </div>
	        </div>
	        
	        <div class="daisy-comments-card">
	            <form method="post" action="<?php echo esc_url(admin_url('admin.php?page=daisy-comments')); ?>">
	                <?php 
	                settings_fields('daisy_comments_settings'); 
	                wp_nonce_field('daisy_comments_save_settings', '_wpnonce');
	                do_settings_sections('daisy-comments'); 
	                submit_button(__('Save Settings', 'daisy-comments')); 
	                ?>
	            </form>
	        </div>
		        
	        <div class="daisy-comments-features">
	            <div class="daisy-comments-feature-box">
	                <div class="feature-icon" style="background-color: #e49b0f;">
	                    <span class="dashicons dashicons-hidden" style="color: #ffffff;"></span>
	                </div>
	                <h3><?php esc_html_e('Hide Comments', 'daisy-comments'); ?></h3>
	                <p><?php esc_html_e('Completely hides all existing comments from your website.', 'daisy-comments'); ?></p>
	            </div>
	            
	            <div class="daisy-comments-feature-box">
	                <div class="feature-icon" style="background-color: #e49b0f;">
	                    <span class="dashicons dashicons-admin-generic" style="color: #ffffff;"></span>
	                </div>
	                <h3><?php esc_html_e('Disable Functionality', 'daisy-comments'); ?></h3>
	                <p><?php esc_html_e('Removes all comment-related features from WordPress.', 'daisy-comments'); ?></p>
	            </div>
	            
	            <div class="daisy-comments-feature-box">
	                <div class="feature-icon" style="background-color: #e49b0f;">
	                    <span class="dashicons dashicons-admin-tools" style="color: #ffffff;"></span>
	                </div>
	                <h3><?php esc_html_e('Clean Dashboard', 'daisy-comments'); ?></h3>
	                <p><?php esc_html_e('Removes comment-related admin menus and widgets.', 'daisy-comments'); ?></p>
	            </div>
	        </div>
	    </div>
	    <?php
	}

	/**
	 * Enqueue admin styles
	 */
	public function daisy_comments_enqueue_admin_styles($hook) {
	    // Load only on our plugin's page
	    if ($hook === 'toplevel_page_daisy-comments') {
	        wp_enqueue_style(
	            'daisy-comments-admin',
	            plugins_url('admin.css', __FILE__),
	            array(),
	            filemtime(plugin_dir_path(__FILE__) . 'admin.css')
	        );
	    }
	}

	/**
	 * Clean up on uninstall
	 */
	public static function daisy_comments_uninstall() {
		delete_option( 'daisy_comments_disable_all' );
	}
}

// Initialize the plugin
new DaisyComments();





// Migration notice for old plugin
add_action('admin_notices', 'daisy_comments_show_cleanup_notice');

function daisy_comments_show_cleanup_notice() {
    if (!is_plugin_active('turn-off-comments/turn-off-comments.php')) {
        return;
    }
    
    $deactivate_url = wp_nonce_url(
        add_query_arg([
            'action' => 'deactivate',
            'plugin' => 'turn-off-comments/turn-off-comments.php'
        ], admin_url('plugins.php')),
        'deactivate-plugin_turn-off-comments/turn-off-comments.php'
    );
    
    $delete_url = wp_nonce_url(
        add_query_arg([
            'action' => 'delete-selected',
            'checked[]' => 'turn-off-comments/turn-off-comments.php',
            'plugin_status' => 'all'
        ], admin_url('plugins.php')),
        'bulk-plugins'
    );
    ?>
    <div class="notice notice-warning">
        <h3><?php esc_html_e('Plugin Cleanup Recommended', 'daisy-comments'); ?></h3>
        <p>
            <?php esc_html_e('You now have "Daisy Comments" active which replaces the old "Turn Off Comments" plugin. The old plugin is no longer needed and should be removed to prevent potential conflicts.', 'daisy-comments'); ?>
        </p>
        <p>
            <a href="<?php echo esc_url($deactivate_url); ?>" class="button">
                <?php esc_html_e('Deactivate Turn Off Comments', 'daisy-comments'); ?>
            </a>
            <a href="<?php echo esc_url($delete_url); ?>" class="button button-danger" onclick="return confirm('<?php esc_attr_e('Are you sure you want to delete the old plugin?', 'daisy-comments'); ?>')">
                <?php esc_html_e('Delete Turn Off Comments', 'daisy-comments'); ?>
            </a>
        </p>
    </div>
    <?php
}