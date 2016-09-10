<?php
/* vim:set tabstop=8 softtabstop=8 shiftwidth=8 noexpandtab: */
/**
 * Image
 *
 *
 * LICENSE: GNU General Public License, version 2 (GPLv2)
 * Copyright (c) 2001 - 2011 Ampache.org All Rights Reserved
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License v2
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 * @package	Ampache
 * @copyright	2001 - 2011 Ampache.org
 * @license	http://opensource.org/licenses/gpl-2.0 GPLv2
 * @link	http://www.ampache.org/
 */

/**
 * Album Art
 * This pulls album art out of the file using the getid3 library
 * and dumps it to the browser as an image mime type.
 *
 */

// This file is a little weird it needs to allow API session
// this needs to be done a little better, but for now... eah
define('NO_SESSION','1');
require 'lib/init.php';

// Check to see if they've got an interface session or a valid API session, if not GTFO
if (!vauth::session_exists('interface',$_COOKIE[Config::get('session_name')]) AND !vauth::session_exists('api',$_REQUEST['auth']) AND !vauth::session_exists('xml-rpc',$_REQUEST['auth'])) {
	debug_event('DENIED','Image Access, Checked Cookie Session:' . $_COOKIE[Config::get('session_name')] . ' and Auth:' . $_REQUEST['auth'],'1');
	exit;
}

// If we aren't resizing just trash thumb
if (!Config::get('resize_images')) { unset($_GET['thumb']); } 

// FIXME: Legacy stuff - should be removed after a version or so
if (!isset($_GET['object_type'])) { 
	$_GET['object_type'] = 'album'; 
} 

$type = Art::validate_type($_GET['object_type']); 

/* Decide what size this image is */
switch ($_GET['thumb']) {
	case '1':
		/* This is used by the now_playing stuff */
		$size['height'] = '75';
		$size['width']	= '75';
	break;
	case '2':
		$size['height']	= '128';
		$size['width']	= '128';
	break;
	case '3':
		/* This is used by the flash player */
		$size['height']	= '80';
		$size['width']	= '80';
	break;
	default:
		$size['height'] = '275';
		$size['width']	= '275';
		if (!isset($_GET['thumb'])) { $return_raw = true; }
	break;
} // define size based on thumbnail

switch ($_GET['type']) {
	case 'popup':
		require_once Config::get('prefix') . '/templates/show_big_art.inc.php';
	break;
	// If we need to pull the data out of the session
	case 'session':
		vauth::check_session();
		$key = scrub_in($_REQUEST['image_index']);
		$image = Art::get_from_source($_SESSION['form']['images'][$key]);
		$mime = $_SESSION['form']['images'][$key]['mime'];
		$data = explode("/",$mime);
		$extension = $data['1'];

		// Send the headers and output the image
		header("Expires: Sun, 19 Nov 1978 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Pragma: no-cache");
		header("Content-type: $mime");
		header("Content-Disposition: filename=" . $key . "." . $extension);
		echo $image;
	break;
	default:
		$media = new $type($_GET['id']); 
		$art = new Art($media->id,$type); 
		$art->get_db();  
		
		if (!$art->raw_mime) { 
			header('Content-type: image/jpeg');
			readfile(Config::get('prefix') . Config::get('theme_path') . '/images/blankalbum.jpg');
			break;
		} // else no image
		
		if ($_GET['thumb']) {
			$thumb_data = $art->get_thumb($size);
		}
				
		$mime = $thumb_data ? $thumb_data['thumb_mime'] : $art->raw_mime; 	
		$source = $thumb_data ? $thumb_data['thumb'] : $art->raw; 
		$extension = Art::extension($mime); 
		
		// Send the headers and output the image
		header("Expires: Tue, 27 Mar 1984 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Pragma: no-cache");
		header("Content-type: $mime");
		header("Content-Disposition: filename=" . scrub_out($media->name) . "." . $extension);
		echo $source;

	break;
} // end switch type

?>
