<?php

function sns($type) {
    $SNSTypeToPrefix = array(
        'SINA' => 'http://weibo.com/',
        'RENREN' => 'http://www.renren.com/profile.do?id=',
        'TENCENT' => 'http://t.qq.com/',
        'QQ' => 'http://qzone.qq.com/',
        'SOHU' => 'http://t.sohu.com/people?uid=',
        'NETEASY' => 'http://t.163.com/',
        'KAIXIN' => 'http://www.kaixin001.com/home/?uid=',
        'DOUBAN' => 'http://www.douban.com/people/'
    );
    return $SNSTypeToPrefix[$type];
}

function get_postid($page_url) {
    $pos = strrpos($page_url, 'p=');
    $post_id = substr($page_url, $pos + 2);
    return $post_id;
}

function VisitPage($method, $url, $data) {
    if (extension_loaded('curl')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $output = curl_exec($ch);
    } else {
        $query = '';
        $concat = '';
        foreach ($data as $key => $val) {
            $query .= $concat . $key . '=' . $val;
            $concat = '&';
        }
        $content = '';
        $array = explode("/", $path);
        if ($array[0] != "http:") {
            return false;
        }
        $host = $array[2];
        $post = "$method $path HTTP/1.1\r\n";
        $post.= "Host: $host\r\n";
        $post.= "Content-type: application/x-www-form-urlencoded\r\n";
        $post.= "Content-length: " . strlen($query) . "\r\n";
        $post.= "Referer: $host\r\n";
        $post.= "Connection: close\r\n\r\n";
        $post.= $query;
        $fp = fsockopen($host, 80);
        $result = fwrite($fp, $post);
        while (!feof($fp)) {
            $content .= fgets($fp, 4096);
        }
        fclose($fp);

        $arr = array_filter(explode("\r\n", $content));
        $output = end($arr);
    }
    return $output;
}