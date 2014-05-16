<?php
/**
 * Plugin Name: WP Users Media
 * Plugin URI: http://www.wknet.com/wp-users-media/
 * Description: WP Users Media is a WordPress plugin that displays only the current users media files and attachments in WP Admin.
 * Version: 2.0.0
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

function um_filter_media_files($wp_query)
{
	global $current_user;

	if(!current_user_can('manage_options') && (is_admin() && $wp_query->query['post_type'] === 'attachment'))
		$wp_query->set('author', $current_user->ID);
}

function um_recount_attachments($counts_in)
{
	global $wpdb;
	global $current_user;

	$and = wp_post_mime_type_where(''); //Default mime type //AND post_author = {$current_user->ID}
	$count = $wpdb->get_results( "SELECT post_mime_type, COUNT( * ) AS num_posts FROM $wpdb->posts WHERE post_type = 'attachment' AND post_status != 'trash' AND post_author = {$current_user->ID} $and GROUP BY post_mime_type", ARRAY_A );

	$counts = array();
	foreach((array)$count as $row)
		$counts[ $row['post_mime_type'] ] = $row['num_posts'];

	$counts['trash'] = $wpdb->get_var( "SELECT COUNT( * ) FROM $wpdb->posts WHERE post_type = 'attachment' AND post_author = {$current_user->ID} AND post_status = 'trash' $and");
	return $counts;
};

add_filter('wp_count_attachments', 'um_recount_attachments');
add_action('pre_get_posts', 'um_filter_media_files');