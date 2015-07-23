<?php
/**
 * Plugin Name: WP Users Media
 * Plugin URI: -
 * Description: WP Users Media is a WordPress plugin that displays only the current users media files and attachments in WP Admin.
 * Version: 3.0.0
 * Author: Damir Calusic
 * Author URI: https://www.damircalusic.com/
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

/*  Copyright (C) 2014  Damir Calusic (email : damir@damircalusic.com)
	
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as 
	published by the Free Software Foundation.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	
	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/* Define the version of the plugin */
define('WPUSERSMEDIA_VERSION', '3.0.0');

/* Load plugin languages */
load_plugin_textdomain('wpusme', false, basename( dirname( __FILE__ ) ) . '/languages');

/* Add menu items to the WP Admin menues */
function wpusersmedia_menu() {
	add_action('admin_init', 'register_wpusme_settings');
	add_submenu_page('options-general.php', 'WP Users Media', 'WP Users Media', 'manage_options', 'wpusme_settings_page', 'wpusme_settings_page');
}

/* Register option settings for the plugin */
function register_wpusme_settings() {
	register_setting('wpusme-settings-group', 'wpusmesidemenu');
	register_setting('wpusme-settings-group', 'wpusmeadminself');
}

/* Display the options/settings page for the site user */
function wpusme_settings_page() {
?>
    <form method="post" action="options.php" style="width:98%;color:rgba(128,128,128,1) !important;">
        <?php settings_fields('wpusme-settings-group'); ?>
        <div id="welcome-panel" class="welcome-panel">
            <label style="position:absolute;top:5px;right:10px;padding:20px 15px 0 3px;font-size:13px;text-decoration:none;line-height:1;">
            	<?php _e('Version', 'wpusme'); ?> <?php echo WPUSERSMEDIA_VERSION; ?>
            </label>
            <div class="welcome-panel-content">
                <h1><?php _e('WP Users Media','wpbe'); ?></h1>
                <p class="about-description"><?php _e('When you change something do not forget to click on this blue Save Changes button below this text.','wpusme'); ?></p>
                <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save Changes','wpusme'); ?>"></p>
                <div class="welcome-panel-column-container">
                    <div class="welcome-panel-column"></div>
                    <div class="welcome-panel-column"></div>
                    <div class="welcome-panel-column welcome-panel-last"></div>
                </div>
            </div>
        </div>
        
        <div id="dashboard-widgets-wrap">
        	<div id="dashboard-widgets" class="metabox-holder">
            	
                <div id="postbox-container-1" class="postbox-container">
                    <div id="normal-sortables" class="meta-box-sortables ui-sortable">
                    
                    	<div id="wpcore" class="postbox">
                        	<div class="handlediv" data-src="wpcore" title="<?php _e('Toggle content','wpusme'); ?>"><br></div>
                            <h3 class="hndle"><span><?php _e('Options','wpusme'); ?></span></h3>
							<div class="inside">
								<div class="main">
                                    <ul>
                                        <li>
                                            <label>
                                                <input type="checkbox" name="wpusmesidemenu" value="1" <?php echo checked(1, get_option('wpusmesidemenu'), false); ?> />
                                                <?php _e('Add shortcut for WP Users Media in the sidebar menu.','wpusme'); ?>
                                            </label>
                                        </li>
                                        <li>
                                            <label>
                                                <input type="checkbox" name="wpusmeadminself" value="1" <?php echo checked(1, get_option('wpusmeadminself'), false); ?> />
                                                <?php _e('Enable so Admins can only view their own attachments.','wpusme'); ?>
                                            </label>
                                        </li>
                                    </ul>
                                 </div>
                          	</div>
						</div>
                
                    </div>
            	</div>
            
            </div>
        </div>
        
    </form>
    <script>
		jQuery(document).ready(function( $ ) {
			$('.handlediv').click(function() {
				var div = $(this).attr("data-src");
				if($("#" + div).hasClass("closed")){
					$("#" + div).removeClass("closed");
				}
				else{
					$("#" + div).addClass("closed");
				}
			});
		});
	</script>
<?php 
}  

/* Add shortcut to the main menu */
function wpusme_shortcut(){ 
	add_menu_page('WP Users Media', 'WP Users Media', 'manage_options', __FILE__, 'wpusme_settings_page', 'dashicons-images-alt2', 75);
}

/* Filter attachments for the specific user */
function um_filter_media_files($wp_query){
	global $current_user;
		
	if(get_option('wpusmeadminself') == '1'){
		if(current_user_can('manage_options') && (is_admin() && $wp_query->query['post_type'] === 'attachment')){
			$wp_query->set('author', $current_user->ID);
		}
	}

	if(!current_user_can('manage_options') && (is_admin() && $wp_query->query['post_type'] === 'attachment')){
		$wp_query->set('author', $current_user->ID);
	}
}

/* Recount attachments for the specific user */
function um_recount_attachments($counts_in){
	global $wpdb;
	global $current_user;

	$and = wp_post_mime_type_where(''); // Default mime type // AND post_author = {$current_user->ID}
	$count = $wpdb->get_results("SELECT post_mime_type, COUNT(*) AS num_posts FROM $wpdb->posts WHERE post_type = 'attachment' AND post_status != 'trash' AND post_author = {$current_user->ID} $and GROUP BY post_mime_type", ARRAY_A);

	$counts = array();
	
	foreach((array)$count as $row){
		$counts[$row['post_mime_type']] = $row['num_posts'];
	}

	$counts['trash'] = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = 'attachment' AND post_author = {$current_user->ID} AND post_status = 'trash' $and");
	
	return $counts;
};

/* Add actions */
add_action('admin_menu', 'wpusersmedia_menu');
add_action('pre_get_posts', 'um_filter_media_files');
if(get_option('wpusmesidemenu') == '1'){ add_action('admin_menu', 'wpusme_shortcut'); }

/* Add Filters*/
add_filter('wp_count_attachments', 'um_recount_attachments');