<?php
if (!defined('ABSPATH')) exit; // just in case

// this does the work of the 404 processing. It is not loaded unless there is a 404.

// Guts of the plugin. This is where we do the redirect. We are already in a 404 before we get here.
function kpg_permalink_fixer() {
	$options=kpg_pf_get_options();
	extract($options);
	// fix request_uri on IIS
	if (!array_key_exists('REQUEST_URI',$_SERVER)) {
		$_SERVER['REQUEST_URI'] = substr($_SERVER['PHP_SELF'],1 );
		if (isset($_SERVER['QUERY_STRING'])) { 
			$_SERVER['REQUEST_URI'].='?'.$_SERVER['QUERY_STRING']; 
		}
	}	
	
	$plink= $_SERVER['REQUEST_URI'];
	$pulink=$plink;
	// keeping the query - there is a chance that there is a query variable that needs to be preserved.
	// possibly a search or an update has been bookmarked.
	if (strpos($plink,'/feed/')!==false) return;
	$query='';
	if (strpos($plink,'?')!==false) {
		$query=substr($plink,strpos($plink,'?'));
		$plink=substr($plink,0,strpos($plink,'?'));
	}
	// do not redirect search queries
	if (strpos('?'.$query,'?s=')!==false) return;
	if (strpos($query,'&s=')!==false) return;
	if (strpos($plink,'#')!==false)  $plink=substr($plink,0,strpos($plink,'#'));
	$plink=trim($plink,'/');
	$flink= $plink; // flink has the page that was 404'd - not the basename
	
	//$plink=basename($plink); // plink now is the permalink part of the request.
	// often I found this is wrong, I want to use the wholw taxonomy in the search
	$plink=kpg_pf_more_clean($plink);
	$plink=str_replace('index.html','',$plink);
	$plink=str_replace('index.shtml','',$plink);
	$plink=str_replace('index.htm','',$plink);
	$plink=str_replace('index.asp','',$plink);
	$plink=str_replace('.html','',$plink);
	$plink=str_replace('.shtml','',$plink);
	$plink=str_replace('.htm','',$plink);
	$plink=str_replace('.asp','',$plink);
	$plink=str_replace('.aspx','',$plink);
	// set up stats	

	// now get rid of the slashes
	$reason=$plink;
	$plink=trim($plink);
	$plink=trim($plink,'/');
	$plink=str_replace('--','-',$plink); // had a problem with double dashes

	$plink=str_replace('/','-',$plink); // this way the taxonomy becomes part of the search
	$plink=str_replace('%20','-',$plink); // spaces are wrong
	
	$ref='';
	if (array_key_exists('HTTP_REFERER',$_SERVER)) $ref=$_SERVER['HTTP_REFERER'];	
	$ref=esc_url_raw($ref);
	$ref=strip_tags($ref);
	$ref=remove_accents($ref);
	$ref=kpg_pf_really_clean($ref);
	
	$agent='';
	if (array_key_exists('HTTP_USER_AGENT',$_SERVER)) $agent=$_SERVER["HTTP_USER_AGENT"];
	$agent=strip_tags($agent);
	$agent=remove_accents($agent);
	$agent=kpg_pf_really_clean($agent);
	$agent=htmlentities($agent);
	$request=$flink;
	$request=esc_url_raw($request);
	$request=strip_tags($request);
	$request=remove_accents($request);
	$request=kpg_pf_really_clean($request);
	$request=str_replace('http://','',$request);
	
	// set up stats
	$r404=array();
	$r404[0]=date('m/d/Y H:i:s',time() + ( get_option( 'gmt_offset' ) * 3600 ));
	$r404[1]=$pulink;
	$r404[2]=$ref;
	$r404[3]=$agent;
	$r404[4]=$_SERVER['REMOTE_ADDR'];
	$r404[6]='';
	// testing an ignore for the category
	if (strpos($plink,"/category/")!==false) {
		$cnt404++;
		$options['cnt404']=$cnt404;
		$r404[6]='/category/ is not redirected.';
		kpg_find_permalink_error_log($options,$e404,$r404,$stats);
		return;
	}

	// do not mess with robots trying to find wp-login.php and wp-signup.php
	if (strpos($plink."\t","/wp-login.php\t")!==false||strpos($plink."\t","/wp-signup.php\t")!==false||strpos($plink."\t","/feed\t")!==false){
		$cnt404++;
		$options['cnt404']=$cnt404;
		$r404[6]='$plink is probably a robot looking for exploits.';
		kpg_find_permalink_error_log($options,$e404,$r404,$stats);
		return;
	}

	// check for bypassed or generated files
	if ($chkrobots=='Y'&&strpos(strtolower($plink)."\t","robots.txt\t")!==false) {
		// looking for a robots.txt
		// header out the .txt file
		$cnt404++;
		$options['cnt404']=$cnt404;
		$r404[6]='display tobots.txt';
		kpg_find_permalink_error_log($options,$e404,$r404,$stats);
		header('HTTP/1.1 200 OK');
		header('Content-Type: text/plain');
		echo $robots;
		exit();
	}
	
	if ($chkcrossdomain=='Y'&&strpos(strtolower($plink)."\t","crossdomain.xml\t")!==false) {
		// looking for a robots.txt
		// header out the .txt file
		$cnt404++;
		$options['cnt404']=$cnt404;
		$r404[6]='display crossdomain.xml';
		kpg_find_permalink_error_log($options,$e404,$r404,$stats);
		header('HTTP/1.1 200 OK');
		header('Content-Type: application/xml');  
		echo '<'.'?xml version="1.0"?'.">\r\n"; // because of ? and stuff need to echo this separate
		?>
<!DOCTYPE cross-domain-policy SYSTEM "http://www.macromedia.com/xml/dtds/cross-domain-policy.dtd">
<cross-domain-policy>
<allow-access-from domain="<?php echo $_SERVER["HTTP_HOST"]; ?>" />
</cross-domain-policy>
		<?php
		exit();
	}

	if ($chkicon=='Y'&&strpos(strtolower($plink)."\t","favicon.ico\t")!==false) {
		// this only works if the favicon.ico is being redirected to wordpress on a 404
		$f=dirname(__FILE__)."/includes/favicon.ico";
		if (!file_exists($f)) {
			// can't find the icon file - what's up with this???
			$r404[6]='did not find favicon.ico';
			kpg_find_permalink_error_log($options,$e404,$r404,$stats);
		    exit();
		}
		if (file_exists($f)) {
			//if (function_exists('header_remove')) header_remove();
			ini_set('zlib.output_compression','Off');
			header('HTTP/1.1 200 OK');
			$r404[6]='display favicon.ico';
			$cnt404++;
			$options['cnt404']=$cnt404;
			kpg_find_permalink_error_log($options,$e404,$r404,$stats);
			header('Content-Type: image/vnd.microsoft.icon');
			header('Content-Disposition: attachment; filename="favicon.ico"');
			header('Content-Length: '.filesize($f));
			readfile($f);
			exit();
		}
	}
/*
	apple-touch-icon-57x57-precomposed.png
	apple-touch-icon-57x57.png
	apple-touch-icon-precomposed.png
	apple-touch-icon.png
*/
	if ($chkicon=='Y'&&(strpos(strtolower($plink)."\t","apple-touch-icon.png\t")!==false
			||strpos(strtolower($plink)."\t","apple-touch-icon-57x57.png\t")!==false
			||strpos(strtolower($plink)."\t","apple-touch-icon-precomposed.png\t")!==false
			||strpos(strtolower($plink)."\t","apple-touch-icon.png\t")!==false
		)
	) {
		// this only works if the favicon.ico is being redirected to wordpress on a 404
		$f=dirname(__FILE__)."/includes/apple-touch-icon.png";
		if (file_exists($f)) {
			if (function_exists('header_remove'))header_remove();
			ini_set('zlib.output_compression','Off');
			$r404[6]='display apple-touch-icon.png';
			$cnt404++;
			$options['cnt404']=$cnt404;
			kpg_find_permalink_error_log($options,$e404,$r404,$stats);
			header('HTTP/1.1 200 OK');
			header('Content-Type: image/png');           
			readfile($f);
			exit();
		}
	}
//	if anyone is asking for a feed that does not exist, send them the sitemap
	if (strpos(strtolower($plink)."\t","feed\t")!==false) {
		// if there is no sitemap, return the last 20 entries made
		$r404[6]='feed send sitemap.xml';
		$cnt404++;
		$options['cnt404']=$cnt404;
		kpg_find_permalink_error_log($options,$e404,$r404,$stats);
		header('HTTP/1.1 200 OK');
		header('Content-Type: application/xml');  
		$sitemap=kpg_pf_sitemap();
		exit();
	}

	if ($chksitemap=='Y'&&strpos(strtolower($plink)."\t","sitemap.xml\t")!==false) {
		// if there is no sitemap, return the last 20 entries made
		$r404[6]='display sitemap.xml';
		$cnt404++;
		$options['cnt404']=$cnt404;
		kpg_find_permalink_error_log($options,$e404,$r404,$stats);
		header('HTTP/1.1 200 OK');
		header('Content-Type: application/xml');  
		$sitemap=kpg_pf_sitemap();
		exit();
	}
	if ($chkdublin=='Y'&&strpos(strtolower($plink)."\t","dublin.rdf\t")!==false) {
		// dublin.rdf is a little used method for robots to get more info about your site
		$r404[6]='display dublin.rdf';
		$cnt404++;
		$options['cnt404']=$cnt404;
		kpg_find_permalink_error_log($options,$e404,$r404,$stats);
		header('HTTP/1.1 200 OK');
		header('Content-Type: application/xml');  
		echo '<'.'?xml version="1.0"?'.'>'; // because of ? and stuff need to echo this separate
	?>
 <rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:dc= "http://purl.org/dc/elements/1.1/">
 <rdf:Description rdf:about="<?php echo get_home_url(); ?>">
 <dc:contributor><?php echo get_bloginfo('name'); ?></dc:contributor>
 <dc:date><?php echo date('Y-m-d',time() + ( get_option( 'gmt_offset' ) * 3600 )); ?></dc:date>
 <dc:description><?php echo get_bloginfo('description'); ?></dc:description>
 <dc:language><?php echo get_bloginfo('language'); ?></dc:language>
 <dc:publisher></dc:publisher>
 <dc:source><?php echo get_home_url(); ?></dc:source>
 </rdf:Description>
 </rdf:RDF>

	<?php
		exit();
	}
	if ($chkopensearch=='Y'&&(strpos(strtolower($plink)."\t","opensearch.xml\t")!==false||strpos(strtolower($plink)."\t","search.xml\t")!==false)) {
		// search.xml may hel people search your site.
		$r404[6]='display opensearch.xml';
		$cnt404++;
		$options['cnt404']=$cnt404;
		kpg_find_permalink_error_log($options,$e404,$r404,$stats);
		header('HTTP/1.1 200 OK');
		header('Content-Type: application/xml');  
		echo '<'.'?xml version="1.0"?'.">\r\n"; // because of ? and stuff need to echo this separate
	?>
 <OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/">
 <ShortName><?php echo get_bloginfo('name'); ?></ShortName>
 <Description>Search this site</Description>
 <Image>favicon.ico</Image>
 <Url type="text/html" template="<?php echo get_home_url(); ?>/seach"/>
 </OpenSearchDescription>
 

	<?php
		exit();
	}
	// some file types should not be included. these files are true 404s and Wordpress can't fix that.
	$ignoreTypes=array(
	'jpg',
	'gif',
	'png',
	'pdf',
	'txt',
	'asp',
	'php',
	'cfm',
	'js',
	'xml',
	'php',
	'mp3',
	'wmv',
	'css'
	);
    foreach ($ignoreTypes as $it) {
		if(strpos(strtolower($plink)."\t",'.'.$it."\t")!==false) {
			$r404[6]="request for non WP file:.$it";
			$cnt404++;
			$options['cnt404']=$cnt404;
			kpg_find_permalink_error_log($options,$e404,$r404,$stats);
			return;
		}
	}

	// santize to get rid of all odd characters, including cross browser scripts.
	$plink=strtolower($plink); // make it case insensitive
	// do some more cleanup
	$plink=urldecode($plink);
	$plink=strip_tags($plink);
	$plink=remove_accents($plink);
	$plink=kpg_pf_really_clean($plink);
	$plink=str_replace('_','-',$plink);
	$plink=str_replace(' ','-',$plink); 
	$plink=str_replace('%20','-',$plink); 
	$plink=str_replace('%22','-',$plink); 
	$plink=str_replace('/archive/','-',$plink); 
	$plink=sanitize_title_with_dashes($plink); // gets rid of some words that wordpress things are unimportant
	// check if the incoming line needs a blogger fix
	// for looking for recursive redirects
	$old_link=$_SERVER['REQUEST_URI'];
	if (empty($plink)) {
		// redirect back to siteurl
		$flink=home_url();
		// recursion check
		if ($flink==$old_link||$flink==$old_link.$query) {
			$r404[5]=$flink;
			$cntredir++;
			$options['cntredir']=$cntredir;
			$totredir++;
			$options['totredir']=$totredir;
			$r404[6]="Recursive redirect on home url, returning to wordpress ";
			kpg_find_permalink_fixed_log($options,$f404,$r404,$stats);
			return;
		}
		$r404[5]=$flink;
		$cntredir++;
		$options['cntredir']=$cntredir;
		$totredir++;
		$options['totredir']=$totredir;
		$r404[6]="empty search, send to home";
		kpg_find_permalink_fixed_log($options,$f404,$r404,$stats);
		wp_redirect($flink.$query,(int)$kpg_pf_301); // let wp do it - more compatable.
		exit();
	}

	if ($labels=='Y') { 
		if (strpos($flink,'/labels/')>0) {
			if ($flink==$old_link||$flink==$old_link.$query) {
				$r404[5]=$flink;
				$cntredir++;
				$options['cntredir']=$cntredir;
				$totredir++;
				$options['totredir']=$totredir;
				$r404[6]="Recursive redirect on label url, returning to wordpress ";
				kpg_find_permalink_fixed_log($options,$f404,$r404,$stats);
				return;
			}

			$flink=str_replace('/labels/','/category/',$flink);
			$flink=str_replace('.html','',$flink); // get dir of html and shtml at the end - don't need to search for these
			$flink=str_replace('.shtml','',$flink); 
			$flink=str_replace('.htm','',$flink); 
			$flink=str_replace('_','-',$flink); // underscores should be dashes
			$flink=str_replace('.','-',$flink); // periods should be dashes 
			$flink=str_replace(' ','-',$flink); // spaces are wrong
			$flink=str_replace('%20','-',$flink); // spaces are wrong
			$flink=str_replace('%22','-',$flink); // spaces are wrong
			$flink=str_replace('"','-',$flink); // spaces are wrong
			$r404[5]=$flink;
			$r404[6]="Redirect /label/ to /category/";
			$cntredir++;
			$options['cntredir']=$cntredir;
			$totredir++;
			$options['totredir']=$totredir;
			kpg_find_permalink_fixed_log($options,$f404,$r404,$stats);
			wp_redirect($flink.$query,(int)$kpg_pf_301); // let wp do it - more compatable.
			exit();
		}
	}
	
	// check to see if the user is coming in on a base default

	// now figure if we need to fix a permalink
	//echo "\r\n\r\n<!-- step 2 $find -->\r\n\r\n";

	if ($find>0) {
		$plink=str_replace('.html','',$plink); // get dir of html and shtml at the end - don't need to search for these
		$plink=str_replace('.shtml','',$plink); 
		$plink=str_replace('.htm','',$plink); 
		$plink=str_replace('.asp','',$plink); 
		// first check for the original slug - use the wordpress slug fixer on it.
		if (strpos(strtolower($flink)."\t","/index.html\t")!==false) $flink=substr($flink."\t",0,strpos(strtolower($flink)."\t","/index.html\t"));
		if (strpos(strtolower($flink)."\t","/index.htm\t")!==false) $flink=substr($flink."\t",0,strpos(strtolower($flink)."\t","/index.htm\t"));
		if (strpos(strtolower($flink)."\t","/index.shtml\t")!==false) $flink=substr($flink."\t",0,strpos(strtolower($flink)."\t","/index.shtml\t"));
		if (strpos(strtolower($flink)."\t","/default.asp\t")!==false) $flink=substr($flink."\t",0,strpos(strtolower($flink)."\t","/default.asp\t"));
		$flink=basename($flink);
		$flink=str_replace('.html','',$flink); // get dir of html and shtml at the end - don't need to search for these
		$flink=str_replace('.shtml','',$flink); 
		$flink=str_replace('.htm','',$flink); 
		$flink=str_replace('_','-',$flink); // underscores should be dashes
		$flink=str_replace('.','-',$flink); // periods should be dashes 
		$flink=str_replace(' ','-',$flink); // spaces are wrong
		$flink=str_replace('%20','-',$flink); // spaces are wrong
		$flink=str_replace('http://','',$flink);
		$flink=str_replace('https://','',$flink);
		$flink=sanitize_url($flink);
		$flink=str_replace('http://','',$flink);
		$flink=str_replace('https://','',$flink);
		$flink=str_replace('%22','-',$flink); // spaces are wrong
		$flink=str_replace('"','-',$flink); // spaces are wrong

		// check for matches to slugs
		
		// start with a check to category
		
		$ID=false;
		$cnt=0;
		$reason="working...";
		$cat='';
		if ($chkcat=='Y') {
			$cat=get_category_by_slug($flink);
			// if exact match on the category slug we can do a redirect right now.
			if (!empty($cat)) {
				// need to redirect to the category
				//echo "\r\n\r\n\r\n";
				//print_r($cat);
				$ID=$cat->cat_ID;
				//echo "\r\n\r\n\r\n";
				//exit();
			} else {
				$ID=false;
			}
		}

		
		
		if (empty($ID)) $ID=false;
		
		if ($ID===false) $ID=kpg_find_permalink_post_direct($flink);
		if (empty($ID)) $ID=false;
		if ($ID!==false) {
			// redirect directly to the link now
			$cnt=1;
			$reason="(1) exact match to slug $plink $flink";
			if (!empty($cat)) $reason="exact match to Category slug $flink";
		}
		// check - exact matches on flink
		if ($ID===false) {
			$ansa=kpg_find_permalink_post_exact($flink,$find ,$kpg_pf_numbs ,$kpg_pf_common ,$kpg_pf_short);
			$ID=$ansa[0];
			$cnt=$ansa[1];
			$reason="Found $cnt exact word matches to slug $plink $flink";
			if (empty($ID)) $ID=false;
		}
		if ($ID===false&&$chkloose=='Y') {
			$ansa=kpg_find_permalink_post_loose($flink,$find ,$kpg_pf_numbs ,$kpg_pf_common ,$kpg_pf_short);
			$ID=$ansa[0];
			$cnt=$ansa[1];
			$reason="Found $cnt loose word matches to $flink";
			if (empty($ID)) $ID=false;
		}
		if ($ID===false&&$chkfullurl=='Y') {
			$ansa=kpg_find_permalink_post_exact($plink,$find ,$kpg_pf_numbs ,$kpg_pf_common ,$kpg_pf_short);
			$ID=$ansa[0];
			$cnt=$ansa[1];
			$reason="Found $cnt exact word matches to $plink";
			if (empty($ID)) $ID=false;
		}
		if ($ID===false&&$chkloose=='Y'&&$chkfullurl=='Y') {
			$ansa=kpg_find_permalink_post_loose($plink,$find ,$kpg_pf_numbs ,$kpg_pf_common ,$kpg_pf_short);
			$ID=$ansa[0];
			$cnt=$ansa[1];
			$reason="Found $cnt loose word matches to $plink";
			if (empty($ID)) $ID=false;
		}

		if( $ID===false && $chkmetaphone=='Y')  { 
			// missed on regular words - try a metaphone search?? Only do it on original slug
			$ansa=kpg_find_permalink_post_metaphone( $flink,$find ,$kpg_pf_numbs ,$kpg_pf_common ,$kpg_pf_short);
			$ID=$ansa[0];
			$cnt=$ansa[1];
			if ($ID!==false) 
				$reason="Found $cnt metaphone 'sounds-like' word matches to $flink";
			else
				$reason="failed all searches";
			if (empty($ID)) $ID=false;
		}
		if( $ID===false && $chkmetaphone=='Y' && $chkfullurl=='Y')  { 
			// missed on regular words - try a metaphone search?? Only do it on original slug
			$ansa=kpg_find_permalink_post_metaphone( $plink,$find ,$kpg_pf_numbs ,$kpg_pf_common ,$kpg_pf_short);
			$ID=$ansa[0];
			$cnt=$ansa[1];
			if ($ID!==false) 
				$reason="Found $cnt metaphone 'sounds-like' word matches to $plink";
			else
				$reason="failed all searches";
			if (empty($ID)) $ID=false;
		}
		
		if( $ID!==false)  { 
// got the page
			if (!empty($cat)) {
				$link=get_category_link($ID);
			} else {
				$link=get_permalink( $ID );
			}
		    if ($do200=='Y') {
				// here we display the page
				$r404[5]=$link;
				$r404[6]=$reason." -page loaded direct '$ID'";
				$cntredir++;
				$options['cntredir']=$cntredir;
				$totredir++;
				$options['totredir']=$totredir;
				kpg_find_permalink_fixed_log($options,$f404,$r404,$stats);
				header("HTTP/1.1 200 Ok");
				if (kpg_pf_load_page($ID)) exit();
				$r404[5]=$link;
				$r404[6]=$reason." page not found '$ID'";
				$cntredir++;
				$options['cntredir']=$cntredir;
				$totredir++;
				$options['totredir']=$totredir;
				kpg_find_permalink_fixed_log($options,$f404,$r404,$stats);
				
			}
			if (!empty($link)) {
				if ($link==$old_link||$link==$old_link.$query) {
					$r404[5]=$flink;
					$cntredir++;
					$options['cntredir']=$cntredir;
					$totredir++;
					$options['totredir']=$totredir;
					$r404[6]="Recursive redirect on url, returning to wordpress ";
					kpg_find_permalink_fixed_log($options,$f404,$r404,$stats);
					return;
				}
				$r404[5]=$_SERVER['REQUEST_URI'].'/'.$link;
				$r404[6]=$reason;
				$cntredir++;
				$options['cntredir']=$cntredir;
				$totredir++;
				$options['totredir']=$totredir;
				kpg_find_permalink_fixed_log($options,$f404,$r404,$stats);
				
				wp_redirect($link.$query,(int)$kpg_pf_301); // let wp do it - more compatable.
				exit();
			}
		}
	}
	// still here, it must be a real 404, we should log it
	$reason="Not found - slug:$flink, loose url:$plink";
	//echo "\r\n\r\n<!-- step 5 -->\r\n\r\n";
	$cnt404++;
	$options['cnt404']=$cnt404;
	$r404[6]=$reason;
	kpg_find_permalink_error_log($options,$e404,$r404,$stats);

	return; // end of permalink fixer
}


// load a page based on my Static-Pages Plugin.
function kpg_pf_load_page($ID) {
	global $wp_query;
	//global $post;
	// create a new $wp_query?
	global $wp_query;
	if (empty($wp_query)) {
		// create a new $wp_query?
		$wp_query=new WP_Query();
	}
	$post=get_post($ID);
	if (empty($post)) {
		$post = new stdClass();
		$post->ID=$ID;
		$post->post_category=array($category); //Add some categories. an array()???
		$post->post_status='publish'; //Set the status of the new post.
		$post->post_type='page'; //Sometimes you might want to post a page.
		$post->comment_status='open';
	}
	$wp_query->queried_object=$post;
	$wp_query->post=$post;
	$wp_query->found_posts = 1;
	$wp_query->post_count = 1;
	$wp_query->current_post = 0;
	$wp_query->max_num_pages = 1;
	$wp_query->is_single = 1;
	$wp_query->is_404 = false;
	$wp_query->is_posts_page = false;
	$wp_query->posts = array($post);
	$wp_query->page=$post;
	$wp_query->is_post=true;
	$wp_query->is_page=true;
	$wp_query->in_the_loop=true;
	$wp_query->post=$post;
	if (!have_posts()) {
	  //echo "<!-- have posts fails -->";
	}
	// find the correct template for this post
	// stolen from template-redirect 
	$td=get_template_directory();
	$template= $td.'/index.php';
	//$template = get_index_template();
	//if ( $template = apply_filters( 'template_include', $template ) )
		include( $template );
	return true;

}


// check exact match to the post 
function kpg_find_permalink_post_exact( $plink,$find,$kpg_pf_numbs ,$kpg_pf_common ,$kpg_pf_short) {
	global $wpdb; // useful db functions
	// common word list - these tend to skew results so don't use them
	$common="  am an and at be but by did does had has her him his its may she than that the them then there these they ";
	$ss1=explode("-",$plink); // place into an arrary
	$ss=array();
	// look for each word in the array. If found add in 1; if not add in 0. Order by sum and the best bet bubbles to top.
	// remove the numbers and small words from $ss1
	foreach($ss1 as $se) {
		if (!empty($se)) {
			if($kpg_pf_numbs=='Y' && is_numeric($se)) {
				// ignore this guy - he's numeric
			} else if ($kpg_pf_common=='Y'&& strpos(' '.$common.' ',$se)!==false) {
				// ignore because of a common word
			} else if ($kpg_pf_short=='Y' && strlen($se)<3) {
				// ignore the word it is too short
			} else {
				// use this word
				$ss[count($ss)]=$se;
			}
		}
	}
	$findcnt=$find;
	if ($find>count($ss)) $findcnt=count($ss);
	if (empty($ss)) return array(false,0);
	$sql="SELECT ID, ";

	for ($j=0;$j<count($ss);$j++) {
		// CONCAT(name, ' - ', description)
		$sql=$sql." if(INSTR(CONCAT('-',LCASE(post_name),'-'),'-".mysql_real_escape_string($ss[$j])."-'),1,0)+" ;
	}
	$sql=$sql."0 as CNT FROM ".$wpdb->posts." WHERE post_status = 'publish'
		and POST_TYPE <> 'attachment' and POST_TYPE <> 'nav_menu_item' 
	ORDER BY CNT DESC, post_modified DESC LIMIT 1";
	$row=$wpdb->get_row($sql);
	if ($row) {	
	   $ID=$row->ID; 
	   $CNT=$row->CNT;
		//echo "\r\n\r\n<!-- step 3c '$CNT' '$findcnt' -->\r\n\r\n";
	   if ($CNT>=$findcnt) return array($ID,$CNT);
	} 
	
	return array(false,0);
}
// use the loose search
function kpg_find_permalink_post_loose( $plink,$find,$kpg_pf_numbs ,$kpg_pf_common ,$kpg_pf_short) {
	global $wpdb; // useful db functions
	// common word list - these tend to skew results so don't use them
	$common="  am an and at be but by did does had has her him his its may she than that the them then there these they ";
	global $wpdb; // useful db functions
	// common word list - these tend to skew results so don't use them
	$common="  am an and at be but by did does had has her him his its may she than that the them then there these they ";
	$ss1=explode("-",$plink); // place into an arrary
	$ss=array();
	// look for each word in the array. If found add in 1; if not add in 0. Order by sum and the best bet bubbles to top.
	// remove the numbers and small words from $ss1
	foreach($ss1 as $se) {
		if (!empty($se)) {
			if($kpg_pf_numbs=='Y' && is_numeric($se)) {
				// ignore this guy - he's numeric
			} else if ($kpg_pf_common=='Y'&& strpos(' '.$common.' ',$se)!==false) {
				// ignore because of a common word
			} else if ($kpg_pf_short=='Y' && strlen($se)<3) {
				// ignore the word it is too short
			} else {
				// use this word
				$ss[count($ss)]=$se;
			}
		}
	}
	$findcnt=$find;
	if ($find>count($ss)) $findcnt=count($ss);
	if (empty($ss)) return array(false,0);
	// try it the old way without explicit searching for the dashes, hits anywhere on any part of a word.
	$sql="SELECT ID, ";
	for ($j=0;$j<count($ss);$j++) {
		$sql=$sql." if(INSTR(LCASE(post_name),'".mysql_real_escape_string($ss[$j])."'),1,0)+" ;
	}
	$sql=$sql."0 as CNT FROM ".$wpdb->posts." WHERE post_status = 'publish' 
		and POST_TYPE <> 'attachment' and POST_TYPE <> 'nav_menu_item' 
		ORDER BY CNT DESC, post_modified DESC LIMIT 1";
	//echo "\r\n\r\n<!-- step 3b  - $sql - -->\r\n\r\n";
	$row=$wpdb->get_row($sql);
	if ($row) {	
	   $ID=$row->ID; 
	   $CNT=$row->CNT;
	   if ($CNT>=$findcnt) return array($ID,$CNT);
	} 
	return array(false,0);
}
//kpg_find_permalink_post using metaphone
function kpg_find_permalink_post_metaphone( $plink,$find,$kpg_pf_numbs ,$kpg_pf_common ,$kpg_pf_short ) {
	global $wpdb; // useful db functions
	// common word list - these tend to skew results so don't use them
	$common="  am an and at be but by did does had has her him his its may she than that the them then there these they ";
	global $wpdb; // useful db functions
	// common word list - these tend to skew results so don't use them
	$common="  am an and at be but by did does had has her him his its may she than that the them then there these they ";
	$ss1=explode("-",$plink); // place into an arrary
	$ss=array();
	// look for each word in the array. If found add in 1; if not add in 0. Order by sum and the best bet bubbles to top.
	// remove the numbers and small words from $ss1
	foreach($ss1 as $se) {
		if (!empty($se)) {
			if($kpg_pf_numbs=='Y' && is_numeric($se)) {
				// ignore this guy - he's numeric
			} else if ($kpg_pf_common=='Y'&& strpos(' '.$common.' ',$se)!==false) {
				// ignore because of a common word
			} else if ($kpg_pf_short=='Y' && strlen($se)<3) {
				// ignore the word it is too short
			} else {
				// use this word
				$ss[count($ss)]=$se;
			}
		}
	}
	$findcnt=$find;
	if ($find>count($ss)) $findcnt=count($ss);
	if (empty($ss)) return array(false,0);
	// we need to do the search but do a metaphone  search on each word
	$ss1=$ss;
	$ss=array();
	foreach($ss1 as $se) {
		if (strlen(metaphone($se))>1) {
			$ss[]=metaphone($se);
		}
	}
	$findcnt=$find;
	
	if ($find > count($ss)) $findcnt=count($ss);
	if (empty($ss)) return array(false,0);
	$sql="SELECT ID,post_name as PN FROM ".$wpdb->posts." WHERE post_status = 'publish' 
	and POST_TYPE <> 'attachment' and POST_TYPE <> 'nav_menu_item' 
	ORDER BY post_modified DESC";
	$rows=$wpdb->get_results($sql,ARRAY_A);
	$ansa=array();
	foreach ($rows as $row) {
		extract($row);
		$PN=str_replace(' ','-',$PN); // just for the hell of it
		$PN=str_replace('_','-',$PN);
		$st=explode('-',$PN);
		$CNT=0;
		if (!empty($st) && count($st)>=$findcnt) {
			foreach ($st as $sst) {
				$se=metaphone($sst);
				if (strlen($se)>1) {
					if (in_array($se,$ss)) $CNT++;
				}
			}
			if ($CNT>=$findcnt) $ansa[$ID]=$CNT;
		}
	}
	if (empty($ansa)) return array(false,0);
	// sort array by CNT keeping keys
	arsort($ansa);
	foreach ($ansa as $ID=>$CNT) {
		if ($CNT>=$findcnt)
			return array($ID,$CNT); // we were getting zero counts somehow
	}

	return array(false,0);
}

function kpg_pf_sitemap() {
	// get the last 20 entries in descending order and make them into a sitemap
echo '<'.'?xml version="1.0" encoding="UTF-8"?'.'>'; // because of ? and stuff need to echo this separate
echo "\r\n";
	// header goes out
	$pd=date('c',time() + ( get_option( 'gmt_offset' ) * 3600 ));
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

<url>
<loc><?php echo home_url('/'); ?></loc>
<lastmod><?php echo $pd; ?></lastmod>
<changefreq>daily</changefreq>
<priority>0.8</priority>
</url>

<?php
	global $wpdb;	
	$sql="SELECT ID FROM ".$wpdb->posts." WHERE post_status = 'publish' 
	and POST_TYPE <> 'attachment' and POST_TYPE <> 'nav_menu_item' 
	ORDER BY post_modified DESC LIMIT 20";
	$rows=$wpdb->get_results($sql,ARRAY_A);
	foreach ($rows as $row) {
		extract($row);
		// get the info from the ID
		$link=get_permalink($ID);
		
// body of xml
?>

<url>
<loc><?php echo $link ?></loc>
<changefreq>daily</changefreq>
<priority>0.8</priority>
</url>

<?php


// end xml		
		
		
	}
?>
</urlset>
<?php
}

// do a quick check to see if the sanitized slug can be found
/*
     post
    page
    attachment
    revision
    nav_menu_item 
*/
function kpg_find_permalink_post_direct($flink) {
	global $wpdb; // useful db functions
	$sql="SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_status = 'publish'
	and POST_TYPE <> 'attachment' and POST_TYPE <> 'nav_menu_item'";
	$post = $wpdb->get_var( $wpdb->prepare($sql , $flink ));
	if ( !empty($post) ) return $post;

	return false;
}

function kpg_pf_really_clean($s) {
	// try to get all non 7-bit things out of the string
	// otherwise the serialize fails - this fixes failed serialize in get and set_options
	if (empty($s)) return $s;
	$ss=array_slice(unpack("c*", "\0".$s), 1);
	if (empty($ss)) return $s;
	$s='';
	for ($j=0;$j<count($ss);$j++) {
		if ($ss[$j]<127&&$ss[$j]>31) $s.=pack('C',$ss[$j]);
	}
	return $s;
}
function kpg_pf_more_clean($ff) {
	// I've had much more experience with bad slugs lately
	$ff=trim($ff);
	$ff=trim($ff,'/');
	$ff=trim($ff,']');
	// cleanup
	$ff=str_replace('__','_',$ff);
	$ff=str_replace('__','_',$ff);
	$ff=str_replace(',_','_',$ff);
	$ff=str_replace('"','',$ff);
	$ff=str_replace('.html','',$ff);
	$ff=str_replace('.shtml','',$ff);
	$ff=str_replace('function.file-get-contents', '', $ff);
	$ff=str_replace('&amp;', '&', $ff);
	$ff=str_replace('&&', '&', $ff);
	$ff=str_replace('&','&amp;', $ff);
	$ff=str_replace(':','_', $ff);
	
	
	
	return $ff;

}

?>