<?php
/*
	Permalink Finder Plugin 
	Options Setup Page
*/
if (!defined('ABSPATH')) exit; // just in case

	if(!current_user_can('manage_options')) {
		die('Access Denied');
	}
	$options=kpg_pf_get_options();
	extract($options);
    // delete options and reset as not being autoloaded, I am retrofitting this into the plugin
	if ($autoload=='N') {
		$options['autoload']='Y';
		$autoload='Y';
		delete_option('kpg_permalinfinder_options');
		add_option('kpg_permalinfinder_options',$options,'','no'); // now it loads only at 404
	}

	$nonce='';	
	
	if (array_key_exists('kpg_pf_control',$_POST)) $nonce=$_POST['kpg_pf_control'];
		if (array_key_exists('kpg_pf_log',$_POST)) {
			// clear the cache
			$f=dirname(__FILE__)."/../.pf_debug_output.txt";
			if (file_exists($f)) {
			    @unlink($f);
				echo "<h2>Deleted Error Log File</h2>";
			}
		}
	if (array_key_exists('action1',$_POST)&&wp_verify_nonce($nonce,'kpg_pf_update')) {
		// clear the fixed
		$cntredir=0;
		$options['cntredir']=$cntredir;
		$f404=array();
		$options['f404']=$f404;
		update_option('kpg_permalinfinder_options',$options);
		echo "<h2>Fixed Permalinks Cleared</h2>";
	} 
	if (array_key_exists('action2',$_POST)&&wp_verify_nonce($nonce,'kpg_pf_update')) {
		// clear the errors
		$cnt404=0;
		$options['cnt404']=$cnt404;
		$e404=array();
		$options['e404']=$e404;
		update_option('kpg_permalinfinder_options',$options);
		echo "<h2>404 Errors Cleared</h2>";
	} 
	if (array_key_exists('action3',$_POST)&&wp_verify_nonce($nonce,'kpg_pf_update')) {
		// update overrides
		$redir301='';
		$redirtexact='';
		$redirsearch='';
		$redirfind='';		

		if (array_key_exists('redir301',$_POST)) $redir301=$_POST['redir301'];
		if (array_key_exists('redirtexact',$_POST)) $redirtexact=$_POST['redirtexact'];
		if (array_key_exists('redirsearch',$_POST)) $redirsearch=$_POST['redirsearch'];
		if (array_key_exists('redirfind',$_POST)) $redirfind=$_POST['redirfind'];
        $redirs[count($redirs)]=array($redir301,$redirtexact,$redirsearch,$redirfind);
		$options['redirs']=$redirs;
		update_option('kpg_permalinfinder_options',$options);
		echo "<h2>Added Override</h2>";
	} 
	if (array_key_exists('action4',$_POST)&&wp_verify_nonce($nonce,'kpg_pf_update')) {
		// delete redirs
		$redirlink=array();
		if (array_key_exists('redirlink',$_POST)) $redirlink=$_POST['redirlink'];
		if (empty($redirlink)) $redirlink=array();
		$newredirs=array();
		$x=0;
		for ($j=0;$j<count($redirs);$j++) {
			if (!in_array($j,$redirlink)) {
				$newredirs[count($newredirs)]=$redirs[$j];
			} else {
				$x++;
			}
		}
		$options['redirs']=$newredirs;
		update_option('kpg_permalinfinder_options',$options);
		$redirs=$newredirs;
		echo "<h2>Deleted $x Override</h2>";
	} 
	if (array_key_exists('action',$_POST)&&wp_verify_nonce($nonce,'kpg_pf_update')) { 
		if (array_key_exists('find',$_POST)) {
			$find=stripslashes($_POST['find']);
		} else {
			$find='2';
		}
		$options['find']=$find;
					
					
		if (array_key_exists('labels',$_POST)) {
			$labels=stripslashes($_POST['labels']);
		} else {
			$labels='N';
		}
		$options['labels']=$labels;
		
		if (array_key_exists('chkcat',$_POST)) {
			$chkcat=stripslashes($_POST['chkcat']);
		} else {
			$chkcat='N';
		}
		$options['chkcat']=$chkcat;
		
		
		if (array_key_exists('stats',$_POST)) {
			$stats=stripslashes($_POST['stats']);
		} else {
			$stats=30;
		}
		$options['stats']=$stats;
		
		if (array_key_exists('kpg_pf_short',$_POST)) {
			$kpg_pf_short=stripslashes($_POST['kpg_pf_short']);
		} else {
			$kpg_pf_short='N';
		}
		$options['kpg_pf_short']=$kpg_pf_short;
		
		if (array_key_exists('kpg_pf_numbs',$_POST)) {
			$kpg_pf_numbs=stripslashes($_POST['kpg_pf_numbs']);
		} else {
			$kpg_pf_numbs='N';
		}
		$options['kpg_pf_numbs']=$kpg_pf_numbs;
		
		if (array_key_exists('kpg_pf_common',$_POST)) {
			$kpg_pf_common=stripslashes($_POST['kpg_pf_common']);
		} else {
			$kpg_pf_common='N';
		}
		$options['kpg_pf_common']=$kpg_pf_common;
		
		if (array_key_exists('kpg_pf_301',$_POST)) {
			$kpg_pf_301=stripslashes($_POST['kpg_pf_301']);
		} else {
			$kpg_pf_301='301';
		}
		$options['kpg_pf_301']=$kpg_pf_301;
		
		if (array_key_exists('kpg_pf_mu',$_POST)) {
			$kpg_pf_mu=stripslashes($_POST['kpg_pf_mu']);
		} else {
			$kpg_pf_mu='N';
		}
		$options['kpg_pf_mu']=$kpg_pf_mu;
		
		if (array_key_exists('chkdublin',$_POST)) {
			$chkdublin=stripslashes($_POST['chkdublin']);
		} else {
			$chkdublin='N';
		}
		$options['chkdublin']=$chkdublin;
		
		
		
		if (array_key_exists('chkopensearch',$_POST)) {
			$chkopensearch=stripslashes($_POST['chkopensearch']);
		} else {
			$chkopensearch='N';
		}
		$options['chkopensearch']=$chkopensearch;
		
		if (array_key_exists('chkcrossdomain',$_POST)) {
			$chkcrossdomain=stripslashes($_POST['chkcrossdomain']);
		} else {
			$chkcrossdomain='N';
		}
		$options['chkcrossdomain']=$chkcrossdomain;
		
		if (array_key_exists('chkrobots',$_POST)) {
			$chkrobots=stripslashes($_POST['chkrobots']);
		} else {
			$chkrobots='N';
		}
		$options['chkrobots']=$chkrobots;
		
		if (array_key_exists('chkicon',$_POST)) {
			$chkicon=stripslashes($_POST['chkicon']);
		} else {
			$chkicon='N';
		}
		$options['chkicon']=$chkicon;
		
		if (array_key_exists('chksitemap',$_POST)) {
			$chksitemap=stripslashes($_POST['chksitemap']);
		} else {
			$chksitemap='N';
		}
		$options['chksitemap']=$chksitemap;
		
		if (array_key_exists('robots',$_POST)) {
			$robots=stripslashes($_POST['robots']);
		} else {
			$robots='';
		}
		$options['robots']=trim($robots);
		
		if (array_key_exists('nobuy',$_POST)) {
			$nobuy=stripslashes($_POST['nobuy']);
		} else {
			$nobuy='N';
		}
		if ($nobuy!='Y') $nobuy='N';
		$options['nobuy']=$nobuy;
		
		if (array_key_exists('chkloose',$_POST)) {
			$chkloose=stripslashes($_POST['chkloose']);
		} else {
			$chkloose='N';
		}
		if ($chkloose!='Y') $chkloose='N';
		$options['chkloose']=$chkloose;
		
		if (array_key_exists('chkfullurl',$_POST)) {
			$chkfullurl=stripslashes($_POST['chkfullurl']);
		} else {
			$chkfullurl='N';
		}
		if ($chkfullurl!='Y') $chkfullurl='N';
		$options['chkfullurl']=$chkfullurl;
		
		if (array_key_exists('chkmetaphone',$_POST)) {
			$chkmetaphone=stripslashes($_POST['chkmetaphone']);
		} else {
			$chkmetaphone='N';
		}
		if ($chkmetaphone!='Y') $chkmetaphone='N';
		$options['chkmetaphone']=$chkmetaphone;
		
		if (array_key_exists('fixhtml',$_POST)) {
			$fixhtml=stripslashes($_POST['fixhtml']);
		} else {
			$fixhtml='N';
		}
		if ($fixhtml!='Y') $fixhtml='N';
		$options['fixhtml']=$fixhtml;
		
		if (array_key_exists('do200',$_POST)) {
			$do200=stripslashes($_POST['do200']);
		} else {
			$do200='N';
		}
		if ($do200!='Y') $do200='N';
		$options['do200']=$do200;
		
		
		if (function_exists('is_multisite') && is_multisite() 
				&& function_exists('kpg_pf_global_unsetup') && function_exists('kpg_pf_global_setup')) {
			if ($kpg_pf_mu=='N') {
				kpg_pf_global_unsetup();
				switch_to_blog(1);
				update_option('kpg_permalinfinder_options',$options);
				restore_current_blog();
			} else {
				kpg_pf_global_setup();
			}
		}			
		update_option('kpg_permalinfinder_options',$options);
		echo "<h2>Options Updated</h2>";

		$options=kpg_pf_get_options();
		extract($options);
	}
?>
<div class="wrap">
  <h2>Permalink-Finder Options</h2>
  <h3>Version 2.3</h3>
  <h4><?php echo $totredir; ?> Permalinks redirected</h4>
  <?php
	if ($nobuy!='Y') {
?>
  <div style="width:60%;background-color:ivory;border:#333333 medium groove;padding:4px;margin-left:4px;margin-left:auto;margin-right:auto;">
    <p>This plugin is free and I expect nothing in return. You can support me by donating a dollar or so.
      <a target="_blank" href="http://www.blogseye.com/donate" target="_blank">Donate!</a></p>
  </div>
  <?php
	}
	
	   $nonce=wp_create_nonce('kpg_pf_update');
 
?>
  <p style="font-weight:bold;">The Permalink-Finder Plugin is installed and working correctly.</p>
  <p style="font-weight:bold;"><a href="" onclick="window.location.href=window.location.href;return false;">Refresh</a></p>
  <hr/>
  <h4>For questions and support please check my website <a href="http://www.blogseye.com/i-make-plugins/permalink-finder-plugin/">BlogsEye.com</a>.</h4>
  <form method="post" action="">
    <input type="hidden" name="action" value="update" />
    <input type="hidden" name="kpg_pf_control" value="<?php echo $nonce;?>" />
    <?php
		if (function_exists('is_multisite') && is_multisite()) {
			global $blog_id;
			if (!empty($blog_id)&&$blog_id<=1) {
	?>
    <h3>Network Blog Option:</h3>
    <table align="center" cellspacing="2" style="background-color:#CCCCCC;font-size:.9em;">
      <tr bgcolor="white">
        <td width="20%" valign="top"><strong>Select how you want to control options in a networked blog environment:&nbsp;</strong></td>
        <td valign="top"> Networked ON:
          <input name="kpg_pf_mu" type="radio" value='Y'  <?php if ($kpg_pf_mu=='Y') echo "checked=\"true\""; ?> />
          <br/>
          Networked OFF:
          <input name="kpg_pf_mu" type="radio" value='N' <?php if ($kpg_pf_mu!='Y') echo "checked=\"true\""; ?>  />
        </td>
        <td valign="top"> If you are running WPMU and want to control all options and logs through the main log admin panel, select on. If you select OFF, each blog will have to configure the plugin separately. </td>
      </tr>
    </table>
    <br/>
    <?php
			} else {
				//echo "<br/>Blog id is '$blog_id' <br/>";
			}
		}
	?>
    <h3>Permalink Finder Options:</h3>
    <p>You can control how the Permalink Finder finds the correct match when a 404 occurs.</p>
    <table align="center" cellspacing="2" style="background-color:#CCCCCC;font-size:.9em;">
      <tr bgcolor="white">
        <td width="20%" valign="top"><strong>Finding Permalinks:&nbsp;</strong></td>
        <td valign="top"><select name="find">
            <option value="9999" <?php if ($find=='9999') {?> selected="selected" <?php } ?>>Disabled</option>
            <option value="1" <?php if ($find=='1') {?> selected="selected" <?php } ?>>any single word match</option>
            <option value="2" <?php if ($find=='2') {?> selected="selected" <?php } ?>>at least 2 words match (recommended)</option>
            <option value="3" <?php if ($find=='3') {?> selected="selected" <?php } ?>>at least 3 words match</option>
            <option value="4" <?php if ($find=='4') {?> selected="selected" <?php } ?>>at least 4 words match</option>
          </select>
        </td>
        <td valign="top"> Indicate how many words in the bad url must match a real permalink. 
          For instance: if the mistaken link is "a-list-of-games" this will find a post called "list-of-games" or "games-list". <br/>
          Matching any single word might redirect to a totally unrelated post, but if you ask for 4 matches you will never be able to fix links with only three words. </td>
      </tr>
      <tr bgcolor="white">
        <td width="20%" valign="top"><strong>Redirect Status Code:&nbsp;</strong></td>
        <td valign="top"><select name="kpg_pf_301">
            <option value="301" <?php if ($kpg_pf_301=='301') {?> selected="selected" <?php } ?>>301 moved permanently</option>
            <option value="302" <?php if ($kpg_pf_301=='302') {?> selected="selected" <?php } ?>>302 found (originally temporary redirect)</option>
            <option value="303" <?php if ($kpg_pf_301=='303') {?> selected="selected" <?php } ?>>303 see other</option>
            <option value="307" <?php if ($kpg_pf_301=='307') {?> selected="selected" <?php } ?>>307 temporary redirect</option>
          </select></td>
        <td valign="top"> Status code returned with the redirect URL.<br/>
          Usually this is 301. This will tell search engines to update their indexes. Use 302 or 307 if you don't want the new page in the search engines just now, but still want to send this user to a new page. Use 303 to indicate that the page is redirecting to another script to finish processing, but keep using the original url.</td>
      </tr>
	  
      <tr bgcolor="white">
        <td width="20%" valign="top"><strong>Search Categories:&nbsp;</strong></td>
        <td valign="top"><input name="chkcat" type="checkbox" value="Y" <?php if ($chkcat=='Y') {?> checked="checked" <?php } ?>/>
        </td>
        <td valign="top">Checks for exact match on category. If someone leaves of the /category/ or mangles the url, this checks the slug against the category list. This is done before the slug is checked for a published page or post. If you have a page with the same slug as a category this might cause an issue.</td>
      </tr>
	  
     <tr bgcolor="white">
        <td width="20%" valign="top"><strong>Fix Blogger Labels:&nbsp;</strong></td>
        <td valign="top"><input name="labels" type="checkbox" value="Y" <?php if ($labels=='Y') {?> checked="checked" <?php } ?>/>
        </td>
        <td valign="top"> Blogger.com uses the url &quot;/labels/&quot; folder instead of categories. If you have imported your site from Blogger.com, you can check off this option to automatically redirect links from /labels/string to /category/string. </td>
      </tr>
      <tr bgcolor="white">
        <td width="20%" valign="top"><strong>Don&apos;t use Common words:&nbsp;</strong></td>
        <td valign="top"><input name="kpg_pf_common" type="checkbox" value="Y" <?php if ($kpg_pf_common=='Y') {?> checked="checked" <?php } ?>/>
        </td>
        <td valign="top"> common words such as &quot;the&quot;, &quot;fix&quot;, &quot;why&quot;, &quot;could&quot;, &quot;not&quot;, can screw up the accuracy of the search for the right slug. Try checking this box to get more accuracy. If you get too many 404s uncheck it </td>
      </tr>
      <tr bgcolor="white">
        <td width="20%" valign="top"><strong>Don&apos;t use short words:&nbsp;</strong></td>
        <td valign="top"><input name="kpg_pf_short" type="checkbox" value="Y" <?php if ($kpg_pf_short=='Y') {?> checked="checked" <?php } ?>/>
        </td>
        <td valign="top"> Words that are one or two letters long can interfere with accuracy. By checking this, the search for a permalink will not
          use words like &quot;a&quot;, &quot;an&quot;,&quot;to&quot;,&quot;I&quot;,&quot;it&quot;, increasing accuracy. </td>
      </tr>
      <tr bgcolor="white">
        <td width="20%" valign="top"><strong>Don&apos;t use numbers:&nbsp;</strong></td>
        <td valign="top"><input name="kpg_pf_numbs" type="checkbox" value="Y" <?php if ($kpg_pf_numbs=='Y') {?> checked="checked" <?php } ?>/>
        </td>
        <td valign="top"> Numbers can confuse the search for a permalink. the number 11 will find 911 and 2011, not just 11. Check this if you accuracy is being hurt by numbers and you don't want to search for them. </td>
      </tr>
      <tr bgcolor="white">
        <td width="20%" valign="top"><strong>Check Using Loose Search: </strong> </td>
        <td valign="top"><input name="chkloose" type="checkbox" value="Y" <?php if ($chkloose=='Y') {?> checked="checked" <?php } ?>/>
        </td>
        <td valign="top"> This will check for partial words so it will find politic in politician or member in remember. The plugin will try for exact words first and only does a loose search if it can't find exact matches. </td>
      </tr>
      <tr bgcolor="white">
        <td width="20%" valign="top"><strong>Use all words in the URL: </strong> </td>
        <td valign="top"><input name="chkfullurl" type="checkbox" value="Y" <?php if ($chkfullurl=='Y') {?> checked="checked" <?php } ?>/>
        </td>
        <td valign="top"> Normally the plugin just searches the slug/post-name permalink. Checking this allows the plugin to search the whole url include taxonomy, categories and tags in the url. If there is a date in the URL it will search for this, too. The plugin tries this only after it fails to find a match using the simple slug using exact and loose searches. This obviously is a last resort. Sometimes it gets a good hit, and almost always keeps your user from seeing a 404 page. Even if it is wrong it will be a near miss. </td>
      </tr>
      <tr bgcolor="white">
        <td width="20%" valign="top"><strong>Metaphone search (sounds like): </strong> </td>
        <td valign="top"><input name="chkmetaphone" type="checkbox" value="Y" <?php if ($chkmetaphone=='Y') {?> checked="checked" <?php } ?>/>
        </td>
        <td valign="top"> If a permalink can't be found, then check this to use a second metaphone search. This does a "Sounds-Like" search. Metaphone can solve problems where there is a spelling error in the permalink. </td>
      </tr>
      <tr bgcolor="white">
        <td width="20%" valign="top"><strong>Track 404 and redirects:&nbsp;</strong></td>
        <td valign="top"><select name="stats">
            <option value="0" <?php if ($stats=='0') {?> selected="selected" <?php } ?>>Disabled</option>
            <option value="10" <?php if ($stats=='10') {?> selected="selected" <?php } ?>>Last 10</option>
            <option value="20" <?php if ($stats=='20') {?> selected="selected" <?php } ?>>Last 20</option>
            <option value="30" <?php if ($stats=='30') {?> selected="selected" <?php } ?>>Last 30</option>
          </select></td>
        <td valign="top"> As long as we are looking at 404&apos;s and trying to redirect them we might as well keep track of the last few hits and what happened to them. You can keep up to 30 of the last hits in memory. (If you set this to zero you will lose any statistics that have been recorded.)</td>
      </tr>
      </tr>
<!--
	<tr bgcolor="white">
        <td width="20%" valign="top"><strong>Fix HTML, SHTML and HTML on requests </strong> </td>
        <td valign="top"><input name="fixhtml" type="checkbox" id="fixhtml" value="Y" <?php if ($fixhtml=='Y') {?> checked="checked" <?php } ?>/>
        </td>
        <td valign="top"> This works a little differently than the other options. This does not do a redirect. It checks the url and looks for an ending of .html, .shtml or .htm. It then trims these off and lets WordPress continue. It does this before there is a 404 so this often prevents a &quot;page not found&quot; error. If you switched your blog over from blogger and your pages ended in .html, then this will silently repair this without generating a redirect. </td>
      </tr>
-->

<tr bgcolor="white">
        <td width="20%" valign="top"><strong>Do not redirect</strong> </td>
        <td valign="top"><input name="do200" type="checkbox" id="do200" value="Y" <?php if ($do200=='Y') {?> checked="checked" <?php } ?>/>
        </td>
        <td valign="top"> Rather than redirect, the plugin will found pages immediately and issue a status code of 200. This does not redirect to the found page, but loads the page immediately. Bad for search engines who dislike redundant pages, but possibly good for some sites in that you can link to any set of keywords and wind up at a good page.</td>
      </tr>


    </table>
    <br/>
    <h3>Special File Handling:</h3>
    <p>If any of these files result in a 404 file not found, you can return a default version instead.</p>
    <table align="center" cellspacing="2" style="background-color:#CCCCCC;font-size:.9em;">
      <tr bgcolor="white">
        <td width="20%" valign="top"><strong>Robots.txt missing: </strong>
          <input name="chkrobots" type="checkbox" value="Y" <?php if ($chkrobots=='Y') {?> checked="checked" <?php } ?>/>
        </td>
        <td valign="top"><textarea name="robots" cols="48" rows="9"><?php echo $robots ?></textarea>
        </td>
        <td valign="top"> When a spider can't find the robots.txt file return this robots.txt file </td>
      </tr>
      <tr bgcolor="white">
        <td width="20%" valign="top"><strong>favicon.ico or apple-touch-icon.png missing: </strong> </td>
        <td valign="top"><input name="chkicon" type="checkbox" value="Y" <?php if ($chkicon=='Y') {?> checked="checked" <?php } ?>/></td>
        <td valign="top"> When your site does not have a favicon.ico or apple-touch-icon.png file return the default wordpress icon. (Only works if wordpress is set to handle the 404 for the these files.) </td>
      </tr>
      <tr bgcolor="white">
        <td width="20%" valign="top"><strong>sitemap.xml missing: </strong> </td>
        <td valign="top"><input name="chksitemap" type="checkbox" value="Y" <?php if ($chksitemap=='Y') {?> checked="checked" <?php } ?>/></td>
        <td valign="top"> When a robot looks for your site map and can't find it, this will return your last 20 pages modified, ensuring that the search engines will find your most recent posts and pages. Spiders will spider your whole site eventually, but this will cue them that you have new or changed stuff. </td>
      </tr>
      <tr bgcolor="white">
        <td width="20%" valign="top"><strong>crossdomain.xml missing: </strong> </td>
        <td valign="top"><input name="chkcrossdomain" type="checkbox" value="Y" <?php if ($chkcrossdomain=='Y') {?> checked="checked" <?php } ?>/></td>
        <td valign="top">When the adobe crossdomain.xml file is not found, the plugin provides a restrictive version that will protect your site from cross domain flash running and corrupting your site. Malicious spiders look for this file to see if you are vulnerable to exploits. </td>
      </tr>
      <tr bgcolor="white">
        <td width="20%" valign="top"><strong>Dublin.rdf missing: </strong> </td>
        <td valign="top"><input name="chkdublin" type="checkbox" value="Y" <?php if ($chkdublin=='Y') {?> checked="checked" <?php } ?>/></td>
        <td valign="top"> Dublin.rdf is a way some search engines can discover a description of your site. When missing use a default one. This does not set the required meta information in the blog head, but is only here if search engines robots look for it. </td>
      </tr>
      <tr bgcolor="white">
        <td width="20%" valign="top"><strong>OpenSearch.txt missing: </strong> </td>
        <td valign="top"><input name="chkopensearch" type="checkbox" value="Y" <?php if ($chkopensearch=='Y') {?> checked="checked" <?php } ?>/></td>
        <td valign="top"> OpenSearch is a method for displaying a search box for your site. When missing use a default one. This does not set the required meta information in the blog head, but is only here if a program looks for it. </td>
      </tr>
    </table>
    <br/>
    <h3>Remove &quot;Donate&quot; nag message:</h3>
    <input type="checkbox" name ="nobuy" value="Y" <?php if ($nobuy=='Y') echo 'checked="true"'; ?> >
    <?php 
		if ($nobuy=='Y')  {
			echo "Thanks";		
		} else {
		?>
    Check if you are tired of seeing the <a target="_blank" href="http://www.blogseye.com/donate/">donate</a> box at the top of the page.
    <?php 
		}
	?>
    <br/>
    <p class="submit">
      <input class="button-primary" value="Save Changes" type="submit">
    </p>
  </form>
  <?php
	$overidetesting=false;
	if ($overidetesting) {
  ?>
  <br/>
  <h3>Redirection Overrides:</h3>
  <p>You can manually specify a redirection link. If permalink finder is consistently sending a link to the wrong page you can manuall specify the link that you want redirected. The link does not have to exist.<br/>
    You can specify the type redirect code, 301, 307, etc. <br/>
    You can specify whether the match has to be exact or not. If you choose inexact, you can have any link that has the partial link to anew location. For instance, you might redirect /tv/startrek to http://yoursite.com/tv/star-trek.<br/>
    You must enter the target link. This does not have to be on your site. You can sepcify that anyone who tries to access a page will be redirected to an affiliate link or wikipedia, for instance.<br/>
    You can delete a link if it is not workin.<br/>
    Be careful how you enter the target link. If it is not found, the permalink finder might start looping, continually searching for a link that does not exist. Test yur links! </p>
  <fieldset style="border thin black solid;" >
  <legend>Add Override:</legend>
  <form method="POST" action="">
    <input type="hidden" name="action3" value="override" />
    <input type="hidden" name="kpg_pf_control" value="<?php echo $nonce;?>" />
    <table>
      <tr>
        <td>Redirect code:</td>
        <td><select name="redir301">
            <option value="301" selected="selected">301 moved permanently</option>
            <option value="302">302 found (originally temporary redirect)</option>
            <option value="303">303 see other</option>
            <option value="307">307 temporary redirect</option>
          </select>
      </tr>
      <tr>
        <td>Exact or loose match:</td>
        <td><select name="redirtexact">
            <option value="Y"  selected="selected">Exact Match</option>
            <option value="N">Loose Match</option>
          </select>
        </td>
      </tr>
      <tr>
        <td>Match this link:</td>
        <td><input	name="redirsearch"	type="text" size="72" />
        </td>
      </tr>
      <tr>
        <td>Redirect to this link:</td>
        <td><input	name="redirfind"	type="text" size="72" />
        </td>
      </tr>
    </table>
    </tr>
    </table>
    <input class="button-primary" value="Add Link Override" type="submit">
  </form>
  </fieldset>
  <?php
		if (!empty($redirs)) {
	?>
  <fieldset style="border thin black solid;" >
  <legend>Overrides:</legend>
  <form method="POST" action="">
    <input type="hidden" name="action4" value="override_maint" />
    <input type="hidden" name="kpg_pf_control" value="<?php echo $nonce;?>" />
    <table align="center" cellspacing="2" style="background-color:#CCCCCC;font-size:.9em;">
      <tr bgcolor="white">
        <td style="background-color:#FFFFEE">Redirect code</td>
        <td style="background-color:#FFFFEE">Match Type</td>
        <td style="background-color:#FFFFEE">Look-for link</td>
        <td style="background-color:#FFFFEE">Send-to link</td>
        <td style="background-color:#FFFFEE">Delete</td>
      </tr>
      <?php
		
		for ($j=0;$j<count($redirs);$j++) {
			$row=$redirs[$j];
			echo "\r\n<tr bgcolor=\"white\">";
				echo "<td>".$row[0]."</td>";
				echo "<td>".$row[1]."</td>";
				echo "<td>".$row[2]."</td>";
				echo "<td>".$row[3]."</td>";
				echo "\r\n\r\n<td><input type=\"checkbox\" value=\"$j\" name=\"redirlink[$j]\" ></td>\r\n";
			echo "<tr>\r\n";
		}
	?>
    </table>
    <input class="button-primary" value="Update Overrides" type="submit">
  </form>
  </fieldset>
  <?php
		}
	}
	?>
  <br/>
  <br/>
  <a href="" onclick="window.location.href=window.location.href;return false;">Refresh</a>
  <?php
// now show the stats.

	if ($stats>0) {
		if (count($f404)>0) {
?>
  <h3 align="center">Fixed Permalinks</h3>
  <h4 align="center"><?php echo $cntredir; ?> Permalinks redirected since cleared</h4>
  <form method="POST" action="">
    <input class="button-primary" value="Clear Fixed Permalinks" type="submit">
    <input type="hidden" name="action1" value="clear_fixed" />
    <input type="hidden" name="kpg_pf_control" value="<?php echo $nonce;?>" />
  </form>
  <table  align="center" cellspacing="1" style="background-color:#CCCCCC;font-size:.9em;">
    <tr bgcolor="white">
      <td style="background-color:#FFFFEE">Date/Time</td>
      <td style="background-color:#FFFFEE">Requested Page</td>
      <td style="background-color:#FFFFEE">Fixed Permalink</td>
      <td style="background-color:#FFFFEE">Referring Page</td>
      <td style="background-color:#FFFFEE">Browser User Agent</td>
      <td style="background-color:#FFFFEE">Remote IP</td>
      <td style="background-color:#FFFFEE">Reason</td>
    </tr>
    <?php
for ($j=0;$j<count($f404)&&$j<$stats;$j++ ) {
    $f404[$j][1]=urldecode($f404[$j][1]);
    $f404[$j][5]=urldecode($f404[$j][5]);
    $f404[$j][2]=urldecode($f404[$j][2]);
    $f1=$f404[$j][1];
    $f5=$f404[$j][5];
    $f2=$f404[$j][2];
	if (strlen($f1)>32) $f1=substr($f1,0,32).'...';
	if (strlen($f5)>32) $f5=substr($f5,0,32).'...';
	if (strlen($f2)>32) $f2=substr($f2,0,32).'...';
?>
    <tr bgcolor="white">
      <td><?php echo $f404[$j][0]; ?></td>
      <td><a href="<?php echo $f404[$j][1]; ?>" title="<?php echo $f404[$j][1]; ?>" target="_blank"><?php echo $f1; ?></a></td>
      <td><a href="<?php echo $f404[$j][5]; ?>" title="<?php echo $f404[$j][5]; ?>" target="_blank"><?php echo $f5; ?></a></td>
      <td><a href="<?php echo $f404[$j][2]; ?>" title="<?php echo $f404[$j][2]; ?>" target="_blank"><?php echo $f2; ?></a></td>
      <td><?php echo $f404[$j][3]; ?></td>
      <td><?php echo $f404[$j][4]; ?>
      <td><?php echo $f404[$j][6]; ?>
        <?php } ?>
  </table>
  <?php } ?>
  <?php 
	if (count($e404)>0) {
?>
  <h3 align="center">404 errors</h3>
  <h4 align="center">Detected <?php echo $cnt404; ?> unfixed 404s</h4>
  <form method="POST" action="">
    <input class="button-primary" value="Clear 404 Errors" type="submit">
    <input type="hidden" name="action2" value="clear_404" />
    <input type="hidden" name="kpg_pf_control" value="<?php echo $nonce;?>" />
  </form>
  <table align="center" cellspacing="2" style="background-color:#CCCCCC;font-size:.9em;">
    <tr bgcolor="white">
      <td style="background-color:#FFFFEE">Date/Time</td>
      <td style="background-color:#FFFFEE">Requested Page</td>
      <td style="background-color:#FFFFEE">Referring Page</td>
      <td style="background-color:#FFFFEE">Browser User Agent</td>
      <td style="background-color:#FFFFEE">Remote IP
      <td style="background-color:#FFFFEE">Reason</td>
      <?php
for ($j=0;$j<count($e404)&&$j<$stats;$j++ ) {
    $e404[$j][1]=urldecode($e404[$j][1]);
    $e404[$j][2]=urldecode($e404[$j][2]);
    $f1=$e404[$j][1];
    $f2=$e404[$j][2];
	if (strlen($f1)>32) $f1=substr($f1,0, 32).'...';
	if (strlen($f2)>32) $f2=substr($f2,0,32).'...';
?>
    <tr bgcolor="white">
      <td><?php echo $e404[$j][0]; ?></td>
      <td><a href="<?php echo $e404[$j][1]; ?>" title="<?php echo $e404[$j][1]; ?>" target="_blank"><?php echo $f1; ?></td>
      <td><a href="<?php echo $e404[$j][2]; ?>" title="<?php echo $e404[$j][2]; ?>" target="_blank"><?php echo $f2; ?></a></td>
      <td><?php echo $e404[$j][3]; ?></td>
      <td><?php echo $e404[$j][4]; ?>
      <td><?php echo $e404[$j][6]; ?>
        <?php } ?>
  </table>
  <?php
	}
	}
?>
  <?php
     $f=dirname(__FILE__)."/../.pf_debug_output.txt";
	 if (file_exists($f)) {
	    ?>
  <h3>Error Log</h3>
  <p>If debugging is turned on, the plugin will drop a record each time it encounters a PHP error. 
    Most of these errors are not fatal and do not effect the operation of the plugin. Almost all come from the unexpected data that
    spammers include in their effort to fool us. The author's goal is to eliminate any and
    all errors. These errors should be corrected. Fatal errors should be reported to the author at www.blogseye.com.</p>
  <form method="post" action="">
    <input type="hidden" name="kpg_stop_spammers_control" value="<?php echo $nonce;?>" />
    <input type="hidden" name="kpg_pf_log" value="true" />
    <input value="Delete Error Log File" type="submit">
  </form>
  <pre>
<?php readfile($f); ?>
</pre>
<?php
	 }
?>
</div>
