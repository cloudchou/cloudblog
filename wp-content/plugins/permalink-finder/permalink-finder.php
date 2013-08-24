<?php
/*
Plugin Name: Permalink Finder
Plugin URI: http://www.BlogsEye.com/
Description: Never get a 404 page not found again. If you have restructured or moved your blog, this plugin will find the right post or page every time.
Version: 2.3
Author: Keith P. Graham
Author URI: http://www.BlogsEye.com/

This software is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/
/***************************************************************

My apologies to anyone trying to read this code.
This was the first plugin that I wrote and at the time
I did not understand how things work in PHP and Wordpress.
I have added to it with cut and paste from my other plugins
and I have moved and changed many parts within.


***************************************************************/
if (!defined('ABSPATH')) exit; // just in case
// set up the main action firs
// this hooks template redirect where it detects the 404 error and does a redirect
add_action( 'template_redirect', 'kpg_permalink_finder' ); 
// kpg_permalink_finder is the main action. 
// it checks to see if the includes file exists and then includes it.

function kpg_permalink_finder() {
	// if we made it here, remove the redundant actions
	if (!is_404()) return;
	remove_action('template_redirect', 'kpg_permalink_finder');
	kpg_pf_errorsonoff();
	if (!function_exists('kpg_permalink_fixer')) load_404_process();
	kpg_permalink_fixer(); // in the include file. Only loaded on a 404
	kpg_pf_errorsonoff('off');
	return; // if we are redirecting we will be back. if not return for legit 404
}

// there are two versions of the admin menus. One is for regular and one is for networks
add_action('admin_menu', 'kpg_pf_admin_menus');
if (function_exists('is_multisite') && is_multisite()) {
	add_action('network_admin_menu', 'kpg_pf_net_admin_menus');
}
// set up the admin menu stuff. only happens when the user is an admin
function kpg_pf_admin_menus() {
	if(!current_user_can('manage_options')) return;
	if (function_exists('is_multisite') && is_multisite()) {
		// this is an MU blog and needs further checking.	
		$options=kpg_pf_get_options();
		$kpg_pf_mu=$options['kpg_pf_mu'];
		// now install the admin stuff
		// if the kpg_pf_mu is "Y" then we are in a network environment and do not install
		if ($kpg_pf_mu=='Y') {
			// we are in the normal admin menu
			return; // a network - only the admin can do it.
		}
	} 
	// this means we can install the options page on the network options page.
	add_options_page('Permalink Finder', 'Permalink Finder', 'manage_options', 'permalink_finder','kpg_permalink_finder_control');
	add_action('rightnow_end', 'kpg_pf_rightnow');
}
// install the network admin stuff
function kpg_pf_net_admin_menus() {
	if(!current_user_can('manage_network_options')) return;
	add_submenu_page('settings.php','Permalink Finder', 'Permalink Finder', 'manage_network_options', 'adminpermalink_finder','kpg_permalink_finder_control');
	//add_options_page('Stop Spammers', 'Stop Spammers', 'manage_options','adminstopspammersoptions','kpg_pf_control');
	//add_options_page('Stop Spammers History', 'Spammer History', 'manage_options','adminstopspammerstats','kpg_pf_stats_control');
	add_action('mu_rightnow_end','kpg_pf_rightnow');
}

// these load the settings pages
function kpg_permalink_finder_control()  {
// this is the display of information about the page.
	kpg_pf_errorsonoff();
	require_once("includes/pf-options.php");
	kpg_pf_errorsonoff('off');
}
function kpg_pf_rightnow() {
	$options=kpg_pf_get_options();
	extract($options);
	$kpg_pf_mu=$options['kpg_pf_mu'];
 	$me=admin_url('options-general.php?page=permalink_finder');
    if (function_exists('is_multisite') && is_multisite() && $kpg_pf_mu=='Y') {
		switch_to_blog(1);
		$me=get_admin_url( 1,'network/settings.php?page=adminpermalink_finder');
		restore_current_blog();
	}
	if ($totredir>0) {
		// steal the akismet stats css format 
		// get the path to the plugin
		echo "<p><a style=\"font-style:italic;\" href=\"$me\">Permalink Finder</a> has redirected $totredir pages.";
		if ($nobuy=='N' && $totredir>10000) echo "  <a style=\"font-style:italic;\" href=\"http://www.blogseye.com/buy-the-book/\">Buy Keith Graham&apos;s Science Fiction Book</a>";
		echo"</p>";
	} else {
		echo "<p><a style=\"font-style:italic\" href=\"$me\">Permalink Finder</a> has not redirected any 404 errors, yet.";
		echo"</p>";
	}
}

function load_pf_mu() {
// check to see if this is an MU installation
// called from the get option screen so it does not load unless there is a 404 or this is needed.
	if (!function_exists('is_multisite') || !is_multisite()) return;
    if (function_exists('kpg_pf_global_setup')) return; // been there done that
		// install the global hooks to globalize the options
		$kpg_pf_mu='N';
		global $blog_id;
		// check blog 1 for the main copy of options
		switch_to_blog(1);
		$ansa=get_option('kpg_permalinfinder_options');
		restore_current_blog();
		if (empty($ansa)) $ansa=array();
		if (!is_array($ansa)) $ansa=array();
		if (array_key_exists('kpg_pf_mu',$ansa)) $kpg_pf_mu=$ansa['kpg_pf_mu'];
		if ($kpg_pf_mu!='N') $kpg_pf_mu='Y';
		if ($kpg_pf_mu=='Y') { // if it is true then the global options need to be installed.\
			load_pf_mu_options_file();
			kpg_pf_global_setup();
		}
}
function load_pf_mu_options_file() {
	kpg_pf_errorsonoff();
	require_once('includes/pf-mu-options.php');
	kpg_pf_errorsonoff('off');
}

// uninstall routine
function kpg_permalink_finder_uninstall() {
	if(!current_user_can('manage_options')) {
		die('Access Denied');
	}
	delete_option('kpg_permalinfinder_options'); 
	return;
}
if ( function_exists('register_uninstall_hook') ) {
	register_uninstall_hook(__FILE__, 'kpg_permalink_finder_uninstall');
}
// actions to handle 404


// generic logging routines - I do it too many times
function kpg_find_permalink_error_log($options,$e404,$r404,$stats) {
		if ($stats<=0) return;
		array_unshift($e404,$r404);
		for ($j=0;$j<10;$j++) {
			$n=count($e404);
			if ($n>$stats) {
				unset($e404[$n-1]);
			}
		}
		//echo "\r\n\r\n<!-- step 6 -->\r\n\r\n";
		$options['e404']=$e404;
		update_option('kpg_permalinfinder_options', $options);
		return;
}
function kpg_find_permalink_fixed_log($options,$f404,$r404,$stats) {
		if ($stats<=0) return;
		array_unshift($f404,$r404);
		for ($j=0;$j<10;$j++) {
			$n=count($f404);
			if ($n>$stats) {
				unset($f404[$n-1]);
			}
		}
		//echo "\r\n\r\n<!-- step 6 -->\r\n\r\n";
		$options['f404']=$f404;
		update_option('kpg_permalinfinder_options', $options);
		return;
}




// here are the debug functions
// change the debug=false to debug=true to start debugging.
// the plugin will drop a file kpg_pf_debug_output.txt in the current directory (root, wp-admin, or network) 
// directory must be writeable or plugin will crash.

function kpg_pf_errorsonoff($old=null) {
	$debug=true;  // change to true to debug, false to stop debugging.
	if (!$debug) return;
	if (empty($old)) return set_error_handler("kpg_pf_ErrorHandler");
	restore_error_handler();
}
function kpg_pf_ErrorHandler($errno, $errmsg, $filename, $linenum, $vars) {
	// write the answers to the file
	// we are only conserned with the errors and warnings, not the notices
	//if ($errno==E_NOTICE || $errno==E_WARNING) return false;
	$serrno="";
	if (strpos($filename,'permalink-finder')===false&&strpos($filename,'options-general.php')===false&&strpos($filename,'pf-')===false) return false;
	switch ($errno) {
		case E_ERROR: 
			$serrno="Fatal run-time errors. These indicate errors that can not be recovered from, such as a memory allocation problem. Execution of the script is halted. ";
			break;
		case E_WARNING: 
			$serrno="Run-time warnings (non-fatal errors). Execution of the script is not halted. ";
			break;
		case E_NOTICE: 
			$serrno="Run-time notices. Indicate that the script encountered something that could indicate an error, but could also happen in the normal course of running a script. ";
			break;
		default;
			$serrno="Unknown Error type $errno";
	}
	if (strpos($errmsg,'modify header information')) return false;
 
	$msg="
	Error number: $errno
	Error type: $serrno
	Error Msg: $errmsg
	File name: $filename
	Line Number: $linenum
	---------------------
	";
	// write out the error
	$f=@fopen(dirname(__FILE__)."/.pf_debug_output.txt",'a');
	$e=@fwrite($f,$msg);
	$e=@fclose($f);
	return false;
}



function kpg_pf_get_options() {
	// whenever we do a get_option we need to check if this is a multisite setup	
	load_pf_mu(); // does nothing unless this is the first time and we are in multisite.

	$opts=get_option('kpg_permalinfinder_options');
	if (empty($opts)||!is_array($opts)) $opts=array();
	$options=array(
		'redirs'=>array(),
		'e404'=>array(),
		'f404'=>array(),
		'autoload'=>'N',
		'find'=>'2',
		'stats'=>30,
		'labels'=>'N',
		'nobuy'=>'N',
		'chkloose'=>'Y',
		'chkfullurl'=>'Y',
		'chkrobots'=>'Y',
		'chkicon'=>'Y',
		'chkcat'=>'Y',
		'chksitemap'=>'Y',
		'chkdublin'=>'Y',
		'chkopensearch'=>'Y',
		'chkmetaphone'=>'Y',
		'chkcrossdomain'=>'Y',
		'kpg_pf_mu'=>'N',
		'kpg_pf_short'=>'N',
		'kpg_pf_numbs'=>'N',
		'kpg_pf_common'=>'N',
		'kpg_pf_301'=>'301',
		'cnt404'=>0,
		'cntredir'=>0,
		'totredir'=>0,
		'fixhtml'=>'N',
		'do200'=>'N',
		'robots'=>"# robots.txt generated by Permalink Finder
User-agent: *
Disallow: */cgi-bin/
Disallow: */wp-admin/
Disallow: */wp-includes/
Disallow: */wp-content/plugins/
Disallow: */wp-content/cache/
Disallow: */wp-content/themes/
Disallow: */category/*/*
Disallow: */trackback/
Disallow: */feed/
Disallow: */comments/
Disallow: /*?
		"
	);
	

	$ansa=array_merge($options,$opts);
	if (!is_array($ansa['redirs'])) $ansa['redirs']=array();
	if (!is_array($ansa['e404'])) $ansa['e404']=array();
	if (!is_array($ansa['f404'])) $ansa['f404']=array();
	if (!is_numeric($ansa['find'])||$ansa['find']<0) $ansa['find']='0';
	if (!is_numeric($ansa['stats'])||$ansa['stats']<0) $ansa['stats']=30;
	if (!is_numeric($ansa['cnt404'])||$ansa['cnt404']<0) $ansa['cnt404']=0;
	if (!is_numeric($ansa['cntredir'])||$ansa['cntredir']<0) $ansa['cntredir']=0;
	if (!is_numeric($ansa['totredir'])||$ansa['totredir']<0) $ansa['totredir']=0;
	if ($ansa['labels']!='Y') $ansa['labels']='N';
	if ($ansa['kpg_pf_mu']!='Y') $ansa['kpg_pf_mu']='N';
	if ($ansa['kpg_pf_short']!='Y') $ansa['kpg_pf_short']='N';
	if ($ansa['kpg_pf_numbs']!='Y') $ansa['kpg_pf_numbs']='N';
	if ($ansa['kpg_pf_common']!='Y') $ansa['kpg_pf_common']='N';
	if ($ansa['chkrobots']!='Y') $ansa['chkrobots']='N';
	if ($ansa['fixhtml']!='Y') $ansa['fixhtml']='N';
	if ($ansa['do200']!='Y') $ansa['do200']='N';
	if ($ansa['chkicon']!='Y') $ansa['chkicon']='N';
	if ($ansa['chkcat']!='Y') $ansa['chkcat']='N';
	if ($ansa['chkloose']!='Y') $ansa['chkloose']='N';
	if ($ansa['chkfullurl']!='Y') $ansa['chkfullurl']='N';
	if ($ansa['chkdublin']!='Y') $ansa['chkdublin']='N';
	if ($ansa['chkopensearch']!='Y') $ansa['chkopensearch']='N';
	if ($ansa['chkmetaphone']!='Y') $ansa['chkmetaphone']='N';
	if ($ansa['chkcrossdomain']!='Y') $ansa['chkcrossdomain']='N';
	if ($ansa['kpg_pf_common']!='Y') $ansa['kpg_pf_common']='N';
	if ($ansa['kpg_pf_301']!='301'&&$ansa['kpg_pf_301']!='302'&&$ansa['kpg_pf_301']!='303'&&$ansa['kpg_pf_301']!='307') 
		$ansa['kpg_pf_301']='301';

	return $ansa;
}// done

// add the html fixups to the queries
// experiment and see which of these is actually doing the deed.
// couldn't get this to work consistently. maybe in the next release
//add_filter( 'pre_get_posts', 'kpg_pf_html_filter',0 );
//add_action( 'wp', 'kpg_pf_html_filter',0 );
//add_filter( 'parse_request', 'kpg_pf_html_filter',0 );

// posts_where


function load_404_process() {
	require_once('includes/pf-404.php');
	return;
}

// experimental fix fo html pages to avoid redirects
function kpg_pf_html_filter($query) {
	if (empty($query)) return $query;
	// see if the query ends in html, htm, shtml with or without a trailing slash
	// this does a very late get_option because we don't want to load options on every page.
	$fixhtml='?';
	$fixup=array('/index.html/','/index.shtml/','/index.htm/',
		'/index.html','/index.shtml','/index.htm',
		'.html/','.shtml/','.htm/',
		'.html','.shtml','.htm');
	if (array_key_exists('pagename',$query->query_vars) && !empty($query->query_vars['pagename'])) {
		$pn=$query->query_vars['pagename'];
		foreach ($fixup as $f) {
			if (stripos($pn,$f)!==false) {
				// found something to fixup
				// first need to check if we really should be doing this.
				if ($fixhtml=='?') {
					$options=kpg_pf_get_options(); // delay this until it is actually needed.
					$fixhtml=$options['fixhtml'];
				}
				if ($fixhtml=='Y') {
					// fix it and break;
					if (function_exists('str_ireplace')) // in case someone tries to run this on php4
						$pn=str_ireplace($f,'',$pn);
					else
						$pn=str_replace($f,'',$pn);
					if (substr($pn,-1)=='/') $pn=substr($pn,0,-1); // remove trailing slash
					$query->query_vars['pagename']=$pn;
					if (function_exists('set_query_var')) set_query_var('pagename',$pn);
					break;
				} else return $query; // ain't sposd to be here
			}
		}
	}
	if (array_key_exists('name',$query->query_vars) && !empty($query->query_vars['name'])) {
		$pn=$query->query_vars['name'];
		foreach ($fixup as $f) {
			if (stripos($pn,$f)!==false) {
				if ($fixhtml=='?') {
					$options=kpg_pf_get_options(); // delay this until it is actually needed.
					$fixhtml=$options['fixhtml'];
				}
				if ($fixhtml=='Y') {
					// fix it and break;
					if (function_exists('str_ireplace')) // in case someone tries to run this on php4
						$pn=str_ireplace($f,'',$pn);
					else
						$pn=str_replace($f,'',$pn);
					if (substr($pn,-1)=='/') $pn=substr($pn,0,-1); // remove trailing slash
					$query->query_vars['name']=$pn;
					if (function_exists('set_query_var')) set_query_var('pagename',$pn);
					break;
				} else return $query;
			} 
		}
	}
	if (isset($query->query_string)) {
		$pn=$query->query_string;
		foreach ($fixup as $f) {
			if (stripos($pn,$f)!==false) {
				if ($fixhtml=='?') {
					$options=kpg_pf_get_options(); // delay this until it is actually needed.
					$fixhtml=$options['fixhtml'];
				}
				if ($fixhtml=='Y') {
					// fix it and break;
					if (function_exists('str_ireplace')) 
						$pn=str_ireplace($f,'',$pn);
					else {
						$pn=str_replace($f,'',$pn);
						$query->query_string=$pn;
						if (function_exists('set_query_var')) set_query_var('pagename',$pn);
						break;
					}
				} else return $query;
			}
		}
	}
	if (isset($query->request)) {
		$pn=$query->request;
		foreach ($fixup as $f) {
			if (stripos($pn,$f)!==false) {
				if ($fixhtml=='?') {
					$options=kpg_pf_get_options(); // delay this until it is actually needed.
					$fixhtml=$options['fixhtml'];
				}
				if ($fixhtml=='Y') {
					// fix it and break;
					if (function_exists('str_ireplace')) {
						$pn=str_ireplace($f,'',$pn);
					} else {
						$pn=str_replace($f,'',$pn);
						$query->$query->request=$pn;
						if (function_exists('set_query_var')) set_query_var('pagename',$pn);
						break;
					}
				} else return $query;
			}
		}
	}
	if (isset($query->matched_query)) {
		$pn=$query->matched_query;
		foreach ($fixup as $f) {
			if (stripos($pn,$f)!==false) {
				if ($fixhtml=='?') {
					$options=kpg_pf_get_options(); // delay this until it is actually needed.
					$fixhtml=$options['fixhtml'];
				}
				if ($fixhtml=='Y') {
					// fix it and break;
					if (function_exists('str_ireplace')) {
						$pn=str_ireplace($f,'',$pn);
					} else {
						$pn=str_replace($f,'',$pn);
						$query->$query->matched_query=$pn;
						if (function_exists('set_query_var')) set_query_var('pagename',$pn);
						break;
					}
				} else return $query;
				
			}
		}
	}
	
	return $query;
}

?>