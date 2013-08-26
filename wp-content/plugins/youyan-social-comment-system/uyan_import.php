<?php
/*
 *
 * uyan_import.php
 * 从友言导入评论数据
 */
header("content-type:text/html;charset=utf-8");
require '../../../wp-load.php';
require '../../../wp-admin/includes/admin.php';
do_action('admin_init');

$myFile = "data/data.json";
$fh = @fopen($myFile, 'r');
//没有json评论文件 退出
if(!$fh){
	echo json_encode(array('noFile'=>1));
	exit;
}
$cont = fread($fh, filesize($myFile));
fclose($fh);
$contArr=json_decode($cont,true);
if(empty($contArr)){
	echo json_encode(array('errArr'=>1));
	exit;
}
$sucNum=$errNum1=$errNum2=$errNum3=0;
$wpSdm = get_option('siteurl');
$wpSdmArr=parse_url($wpSdm);
foreach ($contArr as $val){
	
	$su=$val['su'];
	//如果用户没有加http:// 程序加上协议
	if (!preg_match('/^http\:\/\//i', $su)) {
		$su='http://'.$su;
	}	
	$suArr=parse_url($su);
	//如果域名不相同，跳出循环
	/*echo $suArr['host'];
	echo '--';
	echo $wpSdmArr['host'];
	echo "\n";*/
	if($suArr['host'] != $wpSdmArr['host']){
		$errNum1++;
		continue;		
	}
	//获取文章id,没有id跳出循环
	$docId=convertUrlQuery($suArr['query']);
	if(empty($docId['p'])){
		$errNum2++;
		continue;
	}else{
		//判断文章是否存在，存在则不导入评论  跳过本条记录   wordpress 的处理方式 
		$post_exists = $wpdb->get_var($wpdb->prepare("SELECT * FROM $wpdb->posts WHERE ID = %d LIMIT 1", $docId['p']));
		/*if($post_exists != $post_exists) {
			var_dump($post_exists);
			var_dump($docId['p']);
			echo "\n";
		}*/
		if($post_exists === null) {
			$errNum2++;
			continue;
		}		
	}

	$cmt=$val;
	unset($cmt['time']);
	foreach($cmt as $k => $v){
		$cmt[$k] = newhtmlspecialchars($v);			
	}
	
	//判断评论是否存在，存在则导入评论   跳过本条记录  wordpress 的处理方式 
	$cid = $wpdb->get_var($wpdb->prepare("SELECT comment_Id FROM $wpdb->comments WHERE comment_post_ID = %d AND comment_date = %s AND comment_content = %s", $docId['p'], $val['time'], $cmt['content']));
	if($cid) {
		//如果有子评论
		if($cmt['child']){
			foreach ($cmt['child'] as $child){		
				$child_cmt=$child;
				unset($child_cmt['time']);
				foreach($child_cmt as $k => $v){
					$child_cmt[$k] = newhtmlspecialchars($v);			
				}
				//判断评论是否存在，存在则导入评论   跳过本条记录  wordpress 的处理方式 
				$child_cmt_exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(comment_ID) AS cnt FROM $wpdb->comments WHERE comment_post_ID = %d AND comment_date = %s AND comment_content = %s", $docId['p'], $child['time'], $child_cmt['content']));
				if ($child_cmt_exists) {
					$errNum3++;
					continue;
				}
				$childcmt_fields = array(
					'comment_post_ID' => $docId['p'],
					'comment_date' => $child['time'],
					'comment_date_gmt' => $child['time'],
					'comment_content' => $child_cmt['content'],
					'comment_agent' => 'YouYan Social Comment System',
					'comment_approved' => getwpStatus($child_cmt['status']),
					'comment_author' => $child_cmt['uname'],
					'comment_author_email' => $child_cmt['email'],
					'comment_author_url' => $child_cmt['ulink'],
					'comment_author_IP' => '',
					'comment_parent' => $cid
				);
				$wpdb->insert($wpdb->prefix . "comments", $childcmt_fields);
				$sucNum++;
			}
		}
		$errNum3++;
		continue;
	} else {
		$cmt_fields = array(
			'comment_post_ID' => $docId['p'],
			'comment_date' => $val['time'],
			'comment_date_gmt' => $val['time'],
			'comment_content' => $cmt['content'],
			'comment_agent' => 'YouYan Social Comment System',
			'comment_approved' => getwpStatus($cmt['status']),
			'comment_author' => $cmt['uname'],
			'comment_author_email' => $cmt['email'],
			'comment_author_url' => $cmt['ulink'],
			'comment_author_IP' => '',
			'comment_parent' => 0
		);
		$wpdb->insert($wpdb->prefix . "comments", $cmt_fields);
		$cid = $wpdb->insert_id;
		$sucNum++;
		//如果有子评论
		if($cmt['child']){
			foreach ($cmt['child'] as $child){		
				$child_cmt=$child;
				unset($child_cmt['time']);
				foreach($child_cmt as $k => $v){
					$child_cmt[$k] = newhtmlspecialchars($v);			
				}
				//判断评论是否存在，存在则导入评论   跳过本条记录  wordpress 的处理方式 
				$child_cmt_exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(comment_ID) AS cnt FROM $wpdb->comments WHERE comment_post_ID = %d AND comment_date = %s AND comment_content = %s", $docId['p'], $child['time'], $child_cmt['content']));
				if ($child_cmt_exists) {
					$errNum3++;
					continue;
				}
				$childcmt_fields = array(
					'comment_post_ID' => $docId['p'],
					'comment_date' => $child['time'],
					'comment_date_gmt' => $child['time'],
					'comment_content' => $child_cmt['content'],
					'comment_agent' => 'YouYan Social Comment System',
					'comment_approved' => getwpStatus($child_cmt['status']),
					'comment_author' => $child_cmt['uname'],
					'comment_author_email' => $child_cmt['email'],
					'comment_author_url' => $child_cmt['ulink'],
					'comment_author_IP' => '',
					'comment_parent' => $cid
				);
				$wpdb->insert($wpdb->prefix . "comments", $childcmt_fields);
				$sucNum++;
			}
		}
	}
}
echo json_encode(array('sucNum'=>$sucNum,'errNum1'=>$errNum1,'errNum2'=>$errNum2,'errNum3'=>$errNum3));

//统一评论状态
function getwpStatus($status){
	switch($status){
		case 0:
			$status = 1;
		break;
		case 1:
			$status = 0;
		break;
		case 2:
			$status = 'spam';
		break;
		case 3:
			$status = 'trash';
		break;
		
	}
	return $status;
}
// 过滤字符串
function newhtmlspecialchars($string, $type = 'all') {
	if(is_array($string)){
		return array_map('newhtmlspecialchars', $string);
	} else {
		if($type == 'easy') {
			// $string = htmlspecialchars($string, ENT_QUOTES);
			$string = urldecode($string);
			$string = htmlspecialchars($string);
		} else {
			$string = urldecode($string);
			$string = htmlspecialchars($string);
			$string = sstripslashes($string);
			$string = saddslashes($string);
		}
		return trim($string);
	}
}

//去掉slassh
function sstripslashes($string) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = sstripslashes($val);
		}
	} else {
		$string = stripslashes($string);
	}
	return $string;
}

//SQL ADDSLASHES
function saddslashes($string) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = saddslashes($val);
		}
	} else {
		$string = addslashes($string);
	}
	return $string;
}
//url params
function convertUrlQuery($query)
{ 
    $queryParts = explode('&', $query); 
    
    $params = array(); 
    foreach ($queryParts as $param) 
	{ 
        $item = explode('=', $param); 
        $params[$item[0]] = $item[1]; 
    } 
    
    return $params; 
}

?>