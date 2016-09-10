<?php
/*

 Copyright (c) Ampache.org
 All rights reserved.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License v2
 as published by the Free Software Foundation

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

*/

/***
 * DO NOT EDIT THIS FILE 
 ***/

// Use output buffering, this gains us a few things and 
// fixes some CSS issues
ob_start();

// Do a check for PHP5 because nothing will work without it
if (strcmp('5.0.0',phpversion()) > 0) {
	echo "ERROR: Ampache requires PHP5";
	exit; 
}

// Set the Error level manualy... I'm to lazy to fix notices
error_reporting(E_ALL ^ E_NOTICE);

// This makes this file nolonger need customization
// the config file is in the same dir as this (init.php) file.
$ampache_path = dirname(__FILE__);
$prefix = realpath($ampache_path . "/../");
$configfile = "$prefix/config/ampache.cfg.php";
require_once $prefix . '/lib/general.lib.php';
require_once $prefix . '/lib/class/config.class.php';

// Define some base level config options
Config::set('prefix',$prefix); 


/*
 Check to see if this is Http or https
*/
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') { 
	$http_type = "https://";
}
else { 
	$http_type = "http://";
}

/*
 See if the Config File Exists if it doesn't
 then go ahead and move them over to the install
 script
*/
if (!file_exists($configfile)) { 
        $path = preg_replace("/(.*)\/(\w+\.php)$/","\${1}", $_SERVER['PHP_SELF']);
	$link = $http_type . $_SERVER['HTTP_HOST'] . $path . "/install.php";
	header ("Location: $link");
	exit();
}

// Use the built in PHP function, supress errors here so we can handle it properly 
$results = @parse_ini_file($configfile); 

if (!count($results)) { 
	$path = preg_replace("/(.*)\/(\w+\.php)$/","\${1}", $_SERVER['PHP_SELF']);
	$link = $http_type . $_SERVER['HTTP_HOST'] . $path . "/test.php?action=config";
	header ("Location: $link");
	exit();
} 

/** This is the version.... fluf nothing more... **/
$results['version']		= '3.4.1-RC1';
$results['int_config_version']	= '7'; 

$results['raw_web_path']	= $results['web_path'];
$results['web_path']		= $http_type . $_SERVER['HTTP_HOST'] . $results['web_path'];
$results['http_port']		= $_SERVER['SERVER_PORT'];
if (!$results['http_port']) { 
	$results['http_port']	= '80';
} 
if (!$results['site_charset']) { 
	$results['site_charset'] = "UTF-8";
}
if (!$results['raw_web_path']) { 
	$results['raw_web_path'] = '/';
}
if (!$_SERVER['SERVER_NAME']) { 
	$_SERVER['SERVER_NAME'] = '';
}
if (!$results['user_ip_cardinality']) { 
	$results['user_ip_cardinality'] = 42;
}

/* Variables needed for vauth class */
$results['cookie_path'] 	= $results['raw_web_path'];
$results['cookie_domain']	= $_SERVER['SERVER_NAME'];
$results['cookie_life']		= $results['session_cookielife'];
$results['cookie_secure']	= $results['session_cookiesecure'];
$results['mysql_password']	= $results['database_password'];
$results['mysql_username']	= $results['database_username'];
$results['mysql_hostname']	= $results['database_hostname'];
$results['mysql_db']		= $results['database_name'];

// Define that we've loaded the INIT file
define('INIT_LOADED','1');

// Library and module includes we can't do with the autoloader
require_once $prefix . '/lib/album.lib.php';
require_once $prefix . '/lib/artist.lib.php';
require_once $prefix . '/lib/search.php';
require_once $prefix . '/lib/preferences.php';
require_once $prefix . '/lib/rss.php';
require_once $prefix . '/lib/log.lib.php';
require_once $prefix . '/lib/ui.lib.php';
require_once $prefix . '/lib/gettext.php';
require_once $prefix . '/lib/batch.lib.php';
require_once $prefix . '/lib/themes.php';
require_once $prefix . '/lib/stream.lib.php';
require_once $prefix . '/lib/xmlrpc.php';
require_once $prefix . '/lib/class/localplay.abstract.php';
require_once $prefix . '/modules/xmlrpc/xmlrpc.inc';
require_once $prefix . '/modules/getid3/getid3.php';
require_once $prefix . '/modules/infotools/Snoopy.class.php';
require_once $prefix . '/modules/infotools/AmazonSearchEngine.class.php';
require_once $prefix . '/modules/infotools/lastfm.class.php';
require_once $prefix . '/modules/infotools/openstrands.class.php';
//require_once $prefix . '/modules/infotools/jamendoSearch.class.php';

/* Temp Fixes */
$results = Preference::fix_preferences($results);

Config::set_by_array($results,1);

// Modules (These are conditionaly included depending upon config values)
if (Config::get('ratings')) { 
	require_once $prefix . '/lib/class/rating.class.php';
	require_once $prefix . '/lib/rating.lib.php';
}

/* Set a new Error Handler */
$old_error_handler = set_error_handler('ampache_error_handler');

/* Check their PHP Vars to make sure we're cool here */
$post_size = @ini_get('post_max_size');
if (substr($post_size,strlen($post_size)-1,strlen($post_size)) != 'M') { 
	/* Sane value time */
	ini_set('post_max_size','8M');
}

if ($results['memory_limit'] < 24) { 
	$results['memory_limit'] = 24;
}
set_memory_limit($results['memory_limit']);

/**** END Set PHP Vars ****/

/* We have to check for HTTP Auth */
if (in_array("http",$results['auth_methods'])) { 

	$username = scrub_in($_SERVER['PHP_AUTH_USER']);
	$results = vauth::http_auth($username);

	if ($results['success']) { 
		vauth::create_cookie();
		vauth::session_create($results);
		$session_name = Config::get('session_name');
		$_SESSION['userdata'] = $results;
		$_COOKIE[$session_name] = session_id();
	} 

} // end if http auth

// If we want a session
if (NO_SESSION != '1' AND Config::get('use_auth')) { 
	/* Verify Their session */
	if (!vauth::check_session()) { vauth::logout(session_id()); exit; }

	/* Create the new user */
	$GLOBALS['user'] = User::get_from_username($_SESSION['userdata']['username']);
	
	/* If they user ID doesn't exist deny them */
	if (!$GLOBALS['user']->id AND !Config::get('demo_mode')) { vauth::logout(session_id()); exit; } 

	/* Load preferences and theme */
	$GLOBALS['user']->update_last_seen();
}
elseif (!Config::get('use_auth')) { 
	$auth['success'] = 1;
	$auth['username'] = '-1';
	$auth['fullname'] = "Ampache User";
	$auth['id'] = -1;
	$auth['access'] = '100';
	$auth['offset_limit'] = 50;
	if (!vauth::check_session()) { 
		vauth::create_cookie(); 
		vauth::session_create($auth); 
	}
	$GLOBALS['user']	 	= new User(-1);
	$GLOBALS['user']->fullname 	= 'Ampache User';
	$GLOBALS['user']->offset_limit 	= $auth['offset_limit'];
	$GLOBALS['user']->username 	= '-1';
	$GLOBALS['user']->access	= $auth['access'];
	$_SESSION['userdata']['username'] 	= $auth['username'];
}
// If Auth, but no session is set
else { 
	if (isset($_REQUEST['sessid'])) { 
		session_name(Config::get('session_name')); 
		session_id(scrub_in($_REQUEST['sessid']));
		session_start();
		$GLOBALS['user'] = User::get_from_username($_SESSION['userdata']['username']);
	}
	else { 
		$GLOBALS['user'] = new User(); 
	} 
} // If NO_SESSION passed

// Load the Preferences from the database
Preference::init();

// We need to create the tmp playlist for our user only if we have a session
if (session_id()) { 
	$GLOBALS['user']->load_playlist(); 
} 

/* Add in some variables for ajax done here because we need the user */
Config::set('ajax_url',Config::get('web_path') . '/server/ajax.server.php',1);

// Load gettext mojo
load_gettext();

/* Set CHARSET */
header ("Content-Type: text/html; charset=" . Config::get('site_charset'));

/* Clean up a bit */
unset($array);
unset($results);

/* Setup the flip class */
flip_class(array('odd','even')); 

/* Check to see if we need to perform an update */
if (! preg_match('/update\.php/', $_SERVER['PHP_SELF'])) {
	if (Update::need_update()) {
		header("Location: " . Config::get('web_path') . "/update.php");
		exit();
	}
}

// For the XMLRPC stuff
$GLOBALS['xmlrpc_internalencoding'] = Config::get('site_charset'); 
?>