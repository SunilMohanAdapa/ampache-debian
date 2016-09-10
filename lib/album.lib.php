<?php
/*
Copyright (c) 2001 - 2007 Ampache.org
All Rights Reserved

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License v2
as published by the Free Software Foundation.

This program is distributed int he hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANT ABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See, the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307,USA.

 This library handles album related functions.... wooo!
*/

/*!
	@function get_albums
	@discussion pass a sql statement, and it gets full album info and returns
		an array of the goods.. can be set to format them as well
*/
function get_albums($sql, $action=0) {

	$db_results = mysql_query($sql, dbh());
	while ($r = mysql_fetch_array($db_results)) {
		$album = new Album($r[0]);
		$album->format_album();
		$albums[] = $album;
	}

	return $albums;


} // get_albums

/**
 * get_image_from_source
 * This gets an image for the album art from a source as 
 * defined in the passed array. Because we don't know where
 * its comming from we are a passed an array that can look like
 * ['url']	= URL *** OPTIONAL ***
 * ['file']	= FILENAME *** OPTIONAL ***
 * ['raw']	= Actual Image data, already captured
 */
function get_image_from_source($data) { 

	// Already have the data, this often comes from id3tags
	if (isset($data['raw'])) { 
		return $data['raw'];
	}

	// If it came from the database
	if (isset($data['db'])) { 
		// Repull it 
		$album_id = Dba::escape($data['db']); 
		$sql = "SELECT * FROM `album_data` WHERE `album_id`='$album_id'"; 
		$db_results = Dba::query($sql); 
		$row = Dba::fetch_assoc($db_results); 
		return $row['art']; 
	} // came from the db

	// Check to see if it's a URL
	if (isset($data['url'])) { 
		$snoopy = new Snoopy(); 
		$snoopy->fetch($data['url']); 
		return $snoopy->results; 
	} 

	// Check to see if it's a FILE
	if (isset($data['file'])) { 
		$handle = fopen($data['file'],'rb'); 
		$image_data = fread($handle,filesize($data['file'])); 		
		fclose($handle); 
		return $image_data; 
	} 
	
	// Check to see if it is embedded in id3 of a song
	if (isset($data['song'])) { 
        	// If we find a good one, stop looking
		$getID3 = new getID3();
		$id3 = $getID3->analyze($data['song']);

		if ($id3['format_name'] == "WMA") { 
			return $id3['asf']['extended_content_description_object']['content_descriptors']['13']['data'];
		}
		elseif (isset($id3['id3v2']['APIC'])) { 
			// Foreach incase they have more then one 
			foreach ($id3['id3v2']['APIC'] as $image) { 
				return $image['data'];
			} 
		}
	} // if data song

	return false; 

} // get_image_from_source

/**
 * get_random_albums
 * This returns a random number of albums from the catalogs
 * this is used by the index to return some 'potential' albums to play
 */
function get_random_albums($count=6) {

        $sql = 'SELECT `id` FROM `album` ORDER BY RAND() LIMIT ' . ($count*2);
        $db_results = Dba::query($sql);

	$in_sql = '`album_id` IN ('; 

        while ($row = Dba::fetch_assoc($db_results)) { 
		$in_sql .= "'" . $row['id'] . "',"; 
		$total++; 
        }
       
	if ($total < $count) { return false; } 

	$in_sql = rtrim($in_sql,',') . ')'; 

	$sql = "SELECT `album_id`,ISNULL(`art`) AS `no_art` FROM `album_data` WHERE $in_sql"; 
	$db_results = Dba::query($sql);
        $results = array();

	while ($row = Dba::fetch_assoc($db_results)) { 
	        $results[$row['album_id']] = $row['no_art'];
        } // end for

	asort($results); 
	$albums = array_keys($results); 
	$results = array_slice($albums,0,$count); 

        return $results;
} // get_random_albums

?>