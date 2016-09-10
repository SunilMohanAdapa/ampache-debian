<?php
/*

 Copyright (c) Ampache.org
 All rights reserved.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License v2
 as published by the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

*/

require_once 'lib/init.php';

/* Make sure they have access to this */
if (!Config::get('allow_democratic_playback')) { 
	access_denied(); 
	exit;
}

show_header(); 

// Switch on their action
switch ($_REQUEST['action']) { 
	case 'show_create': 
		if (!Access::check('interface','75')) { 
			access_denied(); 
			break;
		} 

		// Show the create page
		require_once Config::get('prefix') . '/templates/show_create_democratic.inc.php'; 
	break; 
	case 'delete': 
		if (!Access::check('interface','75')) { 
			access_denied(); 
			break; 
		} 
	
		Democratic::delete($_REQUEST['democratic_id']); 

		$title = ''; 
		$text = _('The Requested Playlist has been deleted.'); 
		$url = Config::get('web_path') . '/democratic.php?action=manage_playlists'; 
		show_confirmation($title,$text,$url); 
	break; 
	case 'create': 
		// Only power users here 
		if (!Access::check('interface','75')) { 
			access_denied(); 
			break;
		} 

		// Create the playlist
		Democratic::create($_POST); 
		header("Location: " . Config::get('web_path') . "/democratic.php?action=manage_playlists"); 	
	break; 
	case 'manage_playlists': 
		if (!Access::check('interface','75')) { 
			access_denied(); 
			break;
		} 
		// Get all of the non-user playlists
		$playlists = Democratic::get_playlists(); 

		require_once Config::get('prefix') . '/templates/show_manage_democratic.inc.php'; 

	break;
	case 'show_playlist': 
	default: 
		$democratic = Democratic::get_current_playlist(); 
		$democratic->set_parent(); 
		$objects = $democratic->get_items();
		require_once Config::get('prefix') . '/templates/show_democratic.inc.php';
	break;
} // end switch on action

show_footer(); 

?>
