<?php
/*

 Copyright (c) Ampache.org
 All Rights Reserved

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

/**
 *
 * Browse By Page
 * This page shows the browse menu, which allows you to browse by many different
 * fields including genre, artist, album, catalog, ??? 
 * this page also handles the actuall browse action
 *
 */

/* Base Require */
require_once 'lib/init.php';

// This page is a little wonky we don't want the sidebar until we know what type we're dealing with
// so we've got a little switch here that creates the type.. this feels hackish...

switch ($_REQUEST['action']) { 
	case 'file': 
	case 'album': 
	case 'artist': 
	case 'genre': 
	case 'playlist': 
	case 'live_stream': 
	case 'song': 
		Browse::reset_filters(); 
		Browse::set_type($_REQUEST['action']); 
		Browse::set_simple_browse(1); 
	break; 
} // end switch 

show_header(); 

switch($_REQUEST['action']) {
	case 'file':
	break; 
	case 'album':
		Browse::set_sort('name','ASC');
		$album_ids = Browse::get_objects(); 
		Browse::show_objects($album_ids); 
	break;
	case 'artist':
		Browse::set_sort('name','ASC');
		$artist_ids = Browse::get_objects(); 
		Browse::show_objects($artist_ids); 
	break;
	case 'genre':
		Browse::set_sort('name','ASC');
		$genre_ids = Browse::get_objects(); 
		Browse::show_objects($genre_ids); 
	break;
	case 'song':
		Browse::set_sort('title','ASC');
		$song_ids = Browse::get_objects(); 
		Browse::show_objects($song_ids); 
	break;
	case 'live_stream':
		Browse::set_sort('name','ASC');
		$live_stream_ids = Browse::get_objects(); 
		Browse::show_objects($live_stream_ids); 
	break;
	case 'catalog':
	
	break;
	case 'playlist': 
		Browse::set_sort('type','ASC');
		Browse::set_filter('playlist_type','1');
		$playlist_ids = Browse::get_objects(); 
		Browse::show_objects($playlist_ids); 
	break;
	default: 

	break; 
} // end Switch $action

/* Show the Footer */
show_footer();
?>
