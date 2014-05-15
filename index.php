<?php
/**
* Plugin Name: WP Users Media
* Plugin URI: http://www.wknet.com/wp-users-media/
* Description: WP Users Media is a WordPress plugin that displays only the current users media files and attachments in WP Admin.
* Version: 1.0.0
* Author: Damir Calusic
* Author URI: http://www.damircalusic.com/
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

function filter_media_files($wp_query){
    global $current_user;
    
	if(!current_user_can('manage_options')){
        $wp_query->set('author', $current_user->ID);
        add_filter('views_upload', 'fix_media_counts');
    }
}

function fix_media_counts($views) {
   	global $wpdb, $current_user, $post_mime_types, $avail_post_mime_types;
    
	$_total_posts = array();
    $_num_posts = array();
    $views = array();
    
	$count = $wpdb->get_results("
        SELECT post_mime_type, COUNT(*) AS num_posts 
        FROM $wpdb->posts 
        WHERE post_type = 'attachment' 
        AND post_author = $current_user->ID 
        AND post_status != 'trash' 
        GROUP BY post_mime_type
    ", ARRAY_A);
	
    foreach($count as $row){
        $_num_posts[$row['post_mime_type']] = $row['num_posts'];
    	$_total_posts = array_sum($_num_posts);
    	$detached = isset($_REQUEST['detached']) || isset($_REQUEST['find_detached']);
    
		if(!isset($total_orphans)){
			$total_orphans = $wpdb->get_var("
				SELECT COUNT (*) 
				FROM $wpdb->posts 
				WHERE post_type = 'attachment' 
				AND post_author = $current_user->ID 
				AND post_status != 'trash' 
				AND post_parent < 1
			");
    	
			$matches = wp_match_mime_types(array_keys($post_mime_types), array_keys($_num_posts));
    		
			foreach($matches as $type => $reals){
        		foreach ($reals as $real){
					$num_posts[$type] = (isset($num_posts[$type])) ? $num_posts[$type] + $_num_posts[$real] : $_num_posts[$real];
    				$class = (empty($_GET['post_mime_type']) && !$detached && !isset($_GET['status'])) ? ' class="current"' : '';
					$views['all'] = "<a href='upload.php'$class>".sprintf(__('All <span class="count">(%s)</span>', 'uploaded files' ), number_format_i18n($_total_posts)).'</a>';
					
					foreach($post_mime_types as $mime_type => $label){
						$class = '';
						
						if(!wp_match_mime_types($mime_type, $avail_post_mime_types)){
							continue;
						}
						if(!empty($_GET['post_mime_type']) && wp_match_mime_types($mime_type, $_GET['post_mime_type'])){
							$class = ' class="current"';
						}
						if(!empty( $num_posts[$mime_type])){
							$views[$mime_type] = "<a href='upload.php?post_mime_type=$mime_type'$class>".sprintf(translate_nooped_plural($label[2], $num_posts[$mime_type]), $num_posts[$mime_type]).'</a>';
						}
					}
					
					$views['detached'] = '<a href="upload.php?detached=1"'.($detached ? ' class="current"' : '').'>'.sprintf(__( 'Unattached <span class="count">(%s)</span>', 'detached files'), $total_orphans).'</a>';
				}
			}
		}
	}
	
	return $views;
}

add_action('pre_get_posts', 'filter_media_files');