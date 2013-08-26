<?php
/**
 * @copyright	© 2009-2011 JiaThis Inc.
 * @author		plhwin <plhwin@plhwin.com>
 * @since		version - 2011-8-18
 */

/**
 * 搜狐OAuth认证php类
 */
if(!class_exists('SohuOAuth')){
class SohuOAuth {
	/* Contains the last HTTP status code returned. */
	public $http_code;
	/* Contains the last API call. */
	public $url;
	/* Set up the API root URL. */
	public $host = "http://api.t.sohu.com/";
	/* Set timeout default. */
	public $timeout = 30;
	/* Set connect timeout. */
	public $connecttimeout = 30;
	/* Verify SSL Cert. */
	public $ssl_verifypeer = FALSE;
	/* Respons format. */
	public $format = 'json';
	/* Decode returned json data. */
	public $decode_json = TRUE;
	/* Contains the last HTTP headers returned. */
	public $http_info;
	/* Set the useragnet. */
	public $useragent = 'SohuOAuth v0.0.1';

	/**
	 * 设置OAuth认证需要的Urls
	 */
	function accessTokenURL()  { return 'http://api.t.sohu.com/oauth/access_token'; }
	function authenticateURL() { return 'http://api.t.sohu.com/oauth/authorize'; }
	function authorizeURL()    { return 'http://api.t.sohu.com/oauth/authorize'; }
	function requestTokenURL() { return 'http://api.t.sohu.com/oauth/request_token'; }

	function lastStatusCode() { return $this->http_status; }
	function lastAPICall() { return $this->last_api_call; }

	/**
	 *
	 * 创建SohuOAuth对象实例
	 * @param String $consumer_key
	 * @param String $consumer_secret
	 * @param String $oauth_token 这是access key，没有申请到的时候可以省略
	 * @param String $oauth_token_secret  这是access key对应的密钥，没有申请到的时候可以省略
	 */
	function __construct($consumer_key, $consumer_secret, $oauth_token = NULL, $oauth_token_secret = NULL) {
		$this->sha1_method = new TsohuOAuthSignatureMethod_HMAC_SHA1();
		$this->consumer = new TsohuOAuthConsumer($consumer_key, $consumer_secret);
		if (!empty($oauth_token) && !empty($oauth_token_secret)) {
			$this->token = new TsohuOAuthConsumer($oauth_token, $oauth_token_secret);
		} else {
			$this->token = NULL;
		}
	}


	/**
	 * 获取request_token
	 * @param $oauth_callback
	 * @return a key/value array containing oauth_token and oauth_token_secret
	 */
	function getRequestToken($oauth_callback = NULL) {
		$parameters = array();
		if (!empty($oauth_callback)) {
			$parameters['oauth_callback'] = $oauth_callback;
		}
		$request = $this->TsohuOAuthRequest($this->requestTokenURL(), 'GET', $parameters);
		$token = TsohuOAuthUtil::parse_parameters($request);
		$this->token = new TsohuOAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
		return $token;
	}

	/**
	 * 获取用户认证地址（authorize url），此过程用户将对App的访问进行授权。
	 * @param string $token 这是之前获取的request_token
	 * @param $sign_in_with_sohu
	 */
	function getAuthorizeURL($token, $sign_in_with_sohu = TRUE) {
		if (is_array($token)) {
			$token = $token['oauth_token'];
		}
		if (empty($sign_in_with_sohu)) {
			return $this->authorizeURL() . "?oauth_token={$token}";
		} else {
			return $this->authenticateURL() . "?oauth_token={$token}";
		}
	}
	/**
     * get authorize url for oauth version 1.0
     * @param $token request token
     * @param $oauth_callback oauth callback url
     */
    function getAuthorizeUrl1($token, $oauth_callback) {
        if (is_array($token)) {
            $token = $token['oauth_token'];
        }
        return $this->authorizeURL() . "?oauth_token={$token}"."&oauth_callback={$oauth_callback}";
    }

	/**
	 *
	 * 用户认证完毕后获取access token
	 * @param string $oauth_verifier 用户授权后产生的认证码
	 * @returns array("oauth_token" => "the-access-token",
	 *                "oauth_token_secret" => "the-access-secret",
	 *                "user_id" => "9436992",
	 *                "screen_name" => "abraham")
	 */
	function getAccessToken($oauth_verifier = FALSE) {
		$parameters = array();
		if (!empty($oauth_verifier)) {
			$parameters['oauth_verifier'] = $oauth_verifier;
		}
		$request = $this->TsohuOAuthRequest($this->accessTokenURL(), 'GET', $parameters);
		$token = TsohuOAuthUtil::parse_parameters($request);
		$this->token = new TsohuOAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
		return $token;
	}

	/**
	 * XAuth的获取access token方法，需要对应用授权的用户的用户名和密码。
	 *
	 * @param string $username 用户名
	 * @param string $password 用户的秘密
	 * @returns array("oauth_token" => "the-access-token",
	 *                "oauth_token_secret" => "the-access-secret",
	 *                "user_id" => "9436992",
	 *                "screen_name" => "abraham",
	 *                "x_auth_expires" => "0")
	 */
	function getXAuthToken($username, $password) {
		$parameters = array();
		$parameters['x_auth_username'] = $username;
		$parameters['x_auth_password'] = $password;
		$parameters['x_auth_mode'] = 'client_auth';
		$request = $this->TsohuOAuthRequest($this->accessTokenURL(), 'POST', $parameters);
		$token = TsohuOAuthUtil::parse_parameters($request);
		$this->token = new TsohuOAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
		return $token;
	}

	/**
	 * TsohuOAuthRequest GET请求的包装类
	 */
	function get($url, $parameters = array()) {
		$response = $this->TsohuOAuthRequest($url, 'GET', $parameters);
		if ($this->format === 'json' && $this->decode_json) {
			return json_decode($response);
		}
		return $response;
	}

	/**
	 * TsohuOAuthRequest POST请求的包装类
	 */
	function post($url, $parameters = array()) {
		$response = $this->TsohuOAuthRequest($url, 'POST', $parameters);
		if ($this->format === 'json' && $this->decode_json) {
			return json_decode($response);
		}
		return $response;
	}

	/**
	 * TsohuOAuthRequest DELETE请求的包装类
	 */
	function delete($url, $parameters = array()) {
		$response = $this->TsohuOAuthRequest($url, 'DELETE', $parameters);
		if ($this->format === 'json' && $this->decode_json) {
			return json_decode($response);
		}
		return $response;
	}

	/**
	 * 签名方法并发送http请求
	 * @param string $url api 地址
	 * @param string $method http请求方法，包括 GET,POST,DELETE,TRACE,HEAD,OPTIONS,PUT
	 * @param $parameters 请求参数
	 */
	function TsohuOAuthRequest($url, $method, $parameters) {
		if (strrpos($url, 'https://') !== 0 && strrpos($url, 'http://') !== 0) {
			$url = "{$this->host}{$url}.{$this->format}";
		}
		$request = TsohuOAuthRequest::from_consumer_and_token($this->consumer, $this->token, $method, $url, $parameters);
		$request->sign_request($this->sha1_method, $this->consumer, $this->token);
		switch ($method) {
			case 'GET':
				return $this->http($request->to_url(), 'GET');
			default:
				return $this->http($request->get_normalized_http_url(), $method, $request->to_postdata());
		}
	}

	/**
	 * 发起HTTP请求
	 *
	 * @return API返回结果
	 */
	function http($url, $method, $postfields = NULL) {
		if(extension_loaded('curl')){
			
			$this->http_info = array();
			$ci = curl_init();
			/* Curl settings */
			curl_setopt($ci, CURLOPT_USERAGENT, $this->useragent);
			curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout);
			curl_setopt($ci, CURLOPT_TIMEOUT, $this->timeout);
			curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ci, CURLOPT_HTTPHEADER, array('Expect:'));
			curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifypeer);
			curl_setopt($ci, CURLOPT_HEADERFUNCTION, array($this, 'getHeader'));
			curl_setopt($ci, CURLOPT_HEADER, FALSE);
	
			switch ($method) {
				case 'POST':
					curl_setopt($ci, CURLOPT_POST, TRUE);
					if (!empty($postfields)) {
						curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
					}
					break;
				case 'DELETE':
					curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
					if (!empty($postfields)) {
						$url = "{$url}?{$postfields}";
					}
			}
	
			curl_setopt($ci, CURLOPT_URL, $url);
			$response = curl_exec($ci);
			$this->http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
			$this->http_info = array_merge($this->http_info, curl_getinfo($ci));
			$this->url = $url;
			curl_close ($ci);
	
		}else{
			// 没有开启CRUL，用fsockopen
			$response = '';
			//获取主机地址
			$array = explode("/", $url);
			if($array[0] != "http:")
			{
				return false;
			}
			$host = $array[2];
			
			$post = "$method $url HTTP/1.1\r\n";
			$post.= "Host: $host\r\n";
			$post .= "Accept: */*\r\n";
			$post.= "Content-type: application/x-www-form-urlencoded\r\n";
			$post.= "Content-length: ".strlen($postfields)."\r\n";
			$post.= "Connection: close\r\n\r\n";
			$post.= $postfields ;
			$fp = fsockopen($host,80);
			$result = fwrite($fp, $post);
			//循环读取页面内容并返回
			while(!feof($fp)){
				// $content .= fgets($fp,4096); // 所有写到里面的值都泛返回
				$response = fgets($fp,4096); // 只写入执行页面返回的结果
			}
			//关闭服务器连接并返回页面的全部数据
			fclose($fp);
		}
			return $response;
	}

	/**
	 * Get the header info to store.
	 */
	function getHeader($ch, $header) {
		$i = strpos($header, ':');
		if (!empty($i)) {
			$key = str_replace('-', '_', strtolower(substr($header, 0, $i)));
			$value = trim(substr($header, $i + 2));
			$this->http_header[$key] = $value;
		}
		return strlen($header);
	}
}
}

/**
 * OAuth操作类
 *
 * $Id: class_oauth.php 580 2011-07-18 02:47:05Z pp_zhangheng $
 */
if(!class_exists('TsohuOAuthConsumer')){
class TsohuOAuthConsumer
{
    public $key;
    public $secret;

    function __construct($key, $secret)
    {
        $this->key = $key;
        $this->secret = $secret;
    }

    function __toString()
    {
        return "TsohuOAuthConsumer[key=$this->key,secret=$this->secret]";
    }
}
}

if(!class_exists('TsohuOAuthToken')){
class TsohuOAuthToken
{
    public $key;
    public $secret;

    function __construct($key, $secret)
    {
        $this->key = $key;
        $this->secret = $secret;
    }

    /**
	 *
	 * generates the basic string serialization of a token that a server
     * would respond to request_token and access_token calls with
     */
    function to_string()
    {
        return "oauth_token=" .
            TsohuOAuthUtil::urlencode_rfc3986($this->key) .
            "&oauth_token_secret=" .
            TsohuOAuthUtil::urlencode_rfc3986($this->secret);
    }

    function __toString()
    {
        return $this->to_string();
    }
}
}
// OAuth签名方法
if(!class_exists('TsohuOAuthSignatureMethod')){
	class TsohuOAuthSignatureMethod
	{
	    public function check_signature(&$request, $consumer, $token, $signature)
	    {
	        $built = $this->build_signature($request, $consumer, $token);
	        return $built == $signature;
	    }
	}
}

if(!class_exists('TsohuOAuthSignatureMethod_HMAC_SHA1')){
	class TsohuOAuthSignatureMethod_HMAC_SHA1 extends TsohuOAuthSignatureMethod
	{
	    function get_name()
	    {
	        return "HMAC-SHA1";
	    }

	    public function build_signature($request, $consumer, $token)
	    {
			$base_string = $request->get_signature_base_string();
	        $request->base_string = $base_string;
	        $key_parts = array(
	            $consumer->secret,
	            ($token) ? $token->secret : ""
	        );

			$key_parts = TsohuOAuthUtil::urlencode_rfc3986($key_parts);

			$key = implode('&', $key_parts);
	        return base64_encode(hash_hmac('sha1', $base_string, $key, true));
	    }
	}
}
if(!class_exists('TsohuOAuthSignatureMethod_PLAINTEXT')){
	class TsohuOAuthSignatureMethod_PLAINTEXT extends TsohuOAuthSignatureMethod
	{
	    public function get_name()
	    {
	        return "PLAINTEXT";
	    }

	    public function build_signature($request, $consumer, $token)
	    {
	        $sig = array(
	            TsohuOAuthUtil::urlencode_rfc3986($consumer->secret)
	        );

	        if ($token) {
	            array_push($sig, TsohuOAuthUtil::urlencode_rfc3986($token->secret));
	        } else {
	            array_push($sig, '');
	        }

	        $raw = implode("&", $sig);
	        // for debug purposes
	        $request->base_string = $raw;

	        return TsohuOAuthUtil::urlencode_rfc3986($raw);
	    }
	}
}
if(!class_exists('TsohuOAuthSignatureMethod_RSA_SHA1')){
class TsohuOAuthSignatureMethod_RSA_SHA1 extends TsohuOAuthSignatureMethod
{
    public function get_name()
    {
        return "RSA-SHA1";
    }

    protected function fetch_public_cert(&$request)
    {
        // not implemented yet, ideas are:
        // (1) do a lookup in a table of trusted certs keyed off of consumer
        // (2) fetch via http using a url provided by the requester
        // (3) some sort of specific discovery code based on request
        //
        // either way should return a string representation of the certificate
        throw Exception("fetch_public_cert not implemented");
    }

    protected function fetch_private_cert(&$request)
    {
        // not implemented yet, ideas are:
        // (1) do a lookup in a table of trusted certs keyed off of consumer
        //
        // either way should return a string representation of the certificate
        throw Exception("fetch_private_cert not implemented");
    }

    public function build_signature(&$request, $consumer, $token)
    {
        $base_string = $request->get_signature_base_string();
        $request->base_string = $base_string;

        // Fetch the private key cert based on the request
        $cert = $this->fetch_private_cert($request);

        // Pull the private key ID from the certificate
        $privatekeyid = openssl_get_privatekey($cert);

        // Sign using the key
        $ok = openssl_sign($base_string, $signature, $privatekeyid);

        // Release the key resource
        openssl_free_key($privatekeyid);

        return base64_encode($signature);
    }

    public function check_signature(&$request, $consumer, $token, $signature)
    {
        $decoded_sig = base64_decode($signature);

        $base_string = $request->get_signature_base_string();

        // Fetch the public key cert based on the request
        $cert = $this->fetch_public_cert($request);

        // Pull the public key ID from the certificate
        $publickeyid = openssl_get_publickey($cert);

        // Check the computed signature against the one passed in the query
        $ok = openssl_verify($base_string, $decoded_sig, $publickeyid);

        // Release the key resource
        openssl_free_key($publickeyid);

        return $ok == 1;
    }
}
}
if(!class_exists('TsohuOAuthRequest')){
	class TsohuOAuthRequest
	{
		public $parameters;
		private $http_method;
		private $http_url;
		// for debug purposes
		public $base_string;
		public static $version = '1.0';
		public static $POST_INPUT = 'php://input';
	
		function __construct($http_method, $http_url, $parameters=NULL)
		{
			@$parameters or $parameters = array();
			$this->parameters = $parameters;
			$this->http_method = $http_method;
			$this->http_url = $http_url;
		}
	
	
		/**
		 * attempt to build up a request from what was passed to the server
		 */
		public static function from_request($http_method=NULL, $http_url=NULL, $parameters=NULL)
		{
			$scheme = (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != "on")
				? 'http'
				: 'https';
			@$http_url or $http_url = $scheme .
				'://' . $_SERVER['HTTP_HOST'] .
				':' .
				$_SERVER['SERVER_PORT'] .
				$_SERVER['REQUEST_URI'];
			@$http_method or $http_method = $_SERVER['REQUEST_METHOD'];
	
			// We weren't handed any parameters, so let's find the ones relevant to
			// this request.
			// If you run XML-RPC or similar you should use this to provide your own
			// parsed parameter-list
			if (!$parameters) {
				// Find request headers
				$request_headers = TsohuOAuthUtil::get_headers();
	
				// Parse the query-string to find GET parameters
				$parameters = TsohuOAuthUtil::parse_parameters($_SERVER['QUERY_STRING']);
	
				// It's a POST request of the proper content-type, so parse POST
				// parameters and add those overriding any duplicates from GET
				if ($http_method == "POST"
					&& @strstr($request_headers["Content-Type"],
						"application/x-www-form-urlencoded")
				) {
					$post_data = TsohuOAuthUtil::parse_parameters(
						file_get_contents(self::$POST_INPUT)
					);
					$parameters = array_merge($parameters, $post_data);
				}
	
				// We have a Authorization-header with OAuth data. Parse the header
				// and add those overriding any duplicates from GET or POST
				if (@substr($request_headers['Authorization'], 0, 6) == "OAuth ") {
					$header_parameters = TsohuOAuthUtil::split_header(
						$request_headers['Authorization']
					);
					$parameters = array_merge($parameters, $header_parameters);
				}
	
			}
	
			return new TsohuOAuthRequest($http_method, $http_url, $parameters);
		}
	
		/**
		 * pretty much a helper function to set up the request
		 */
		public static function from_consumer_and_token($consumer, $token, $http_method, $http_url, $parameters=NULL)
		{
			@$parameters or $parameters = array();
			$defaults = array("oauth_version" => TsohuOAuthRequest::$version,
				"oauth_nonce" => TsohuOAuthRequest::generate_nonce(),
				"oauth_timestamp" => TsohuOAuthRequest::generate_timestamp(),
				"oauth_consumer_key" => $consumer->key);
			if ($token)
				$defaults['oauth_token'] = $token->key;
	
			$parameters = array_merge($defaults, $parameters);
			//unset($parameters['pic']);
			return new TsohuOAuthRequest($http_method, $http_url, $parameters);
		}
	
		public function set_parameter($name, $value, $allow_duplicates = true)
		{
			if ($allow_duplicates && isset($this->parameters[$name])) {
				// We have already added parameter(s) with this name, so add to the list
				if (is_scalar($this->parameters[$name])) {
					// This is the first duplicate, so transform scalar (string)
					// into an array so we can add the duplicates
					$this->parameters[$name] = array($this->parameters[$name]);
				}
	
				$this->parameters[$name][] = $value;
			} else {
				$this->parameters[$name] = $value;
			}
		}
	
		public function get_parameter($name) {
			return isset($this->parameters[$name]) ? $this->parameters[$name] : null;
		}
	
		public function get_parameters() {
			return $this->parameters;
		}
	
		public function unset_parameter($name) {
			unset($this->parameters[$name]);
		}
	
		/**
		 * The request parameters, sorted and concatenated into a normalized string.
		 * @return string
		 */
		public function get_signable_parameters() {
			// Grab all parameters
			$params = $this->parameters;
	
			// remove pic
			if (isset($params['pic'])) {
				unset($params['pic']);
			}
	
			  if (isset($params['image']))
			 {
				unset($params['image']);
			}
	
			// Remove oauth_signature if present
			// Ref: Spec: 9.1.1 ("The oauth_signature parameter MUST be excluded.")
			if (isset($params['oauth_signature'])) {
				unset($params['oauth_signature']);
			}
	
			return TsohuOAuthUtil::build_http_query($params);
		}
	
		/**
		 * Returns the base string of this request
		 *
		 * The base string defined as the method, the url
		 * and the parameters (normalized), each urlencoded
		 * and the concated with &.
		 */
		public function get_signature_base_string() {
			$parts = array(
				$this->get_normalized_http_method(),
				$this->get_normalized_http_url(),
				$this->get_signable_parameters()
			);
	
			//print_r( $parts );
	
			$parts = TsohuOAuthUtil::urlencode_rfc3986($parts);
			return implode('&', $parts);
		}
	
		/**
		 * just uppercases the http method
		 */
		public function get_normalized_http_method() {
			return strtoupper($this->http_method);
		}
	
		/**
		 * parses the url and rebuilds it to be
		 * scheme://host/path
		 */
		public function get_normalized_http_url() {
			$parts = parse_url($this->http_url);
	
			$port = @$parts['port'];
			$scheme = $parts['scheme'];
			$host = $parts['host'];
			$path = @$parts['path'];
	
			$port or $port = ($scheme == 'https') ? '443' : '80';
	
			if (($scheme == 'https' && $port != '443')
				|| ($scheme == 'http' && $port != '80')) {
					$host = "$host:$port";
				}
			return "$scheme://$host$path";
		}
	
		/**
		 * builds a url usable for a GET request
		 */
		public function to_url() {
			$post_data = $this->to_postdata();
			$out = $this->get_normalized_http_url();
			if ($post_data) {
				$out .= '?'.$post_data;
			}
			return $out;
		}
	
		/**
		 * builds the data one would send in a POST request
		 */
		public function to_postdata( $multi = false ) {
		if( $multi ){
			return TsohuOAuthUtil::build_http_query_multi($this->parameters);}
		else
			return TsohuOAuthUtil::build_http_query($this->parameters);
		}
	
		/**
		 * builds the Authorization: header
		 */
		public function to_header() {
			$out ='Authorization: OAuth realm=""';
			$total = array();
			foreach ($this->parameters as $k => $v) {
				if (substr($k, 0, 5) != "oauth") continue;
				if (is_array($v)) {
					throw new MBOAuthExcep('Arrays not supported in headers');
				}
				$out .= ',' .
					TsohuOAuthUtil::urlencode_rfc3986($k) .
					'="' .
					TsohuOAuthUtil::urlencode_rfc3986($v) .
					'"';
			}
			return $out;
		}
	
		public function __toString() {
			return $this->to_url();
		}
	
	
		public function sign_request($signature_method, $consumer, $token) {
			$this->set_parameter(
				"oauth_signature_method",
				$signature_method->get_name(),
				false
			);
			$signature = $this->build_signature($signature_method, $consumer, $token);
			$this->set_parameter("oauth_signature", $signature, false);
		}
	
		public function build_signature($signature_method, $consumer, $token) {
			$signature = $signature_method->build_signature($this, $consumer, $token);
			return $signature;
		}
	
		/**
		 * util function: current timestamp
		 */
		private static function generate_timestamp() {
			return time();
		}
	
		/**
		 * util function: current nonce
		 */
		private static function generate_nonce() {
			$mt = microtime();
			$rand = mt_rand();
	
			return md5($mt . $rand); // md5s look nicer than numbers
		}
	}
}
if(!class_exists('TsohuOAuthServer')){
	class TsohuOAuthServer
	{
		protected $timestamp_threshold = 300; // in seconds, five minutes
		protected $version = 1.0;             // hi blaine
		protected $signature_methods = array();
	
		protected $data_store;
	
		function __construct($data_store)
		{
			$this->data_store = $data_store;
		}
	
		public function add_signature_method($signature_method) {
			$this->signature_methods[$signature_method->get_name()] =
				$signature_method;
		}
	
		// high level functions
	
		/**
		 * process a request_token request
		 * returns the request token on success
		 */
		public function fetch_request_token(&$request) {
			$this->get_version($request);
	
			$consumer = $this->get_consumer($request);
	
			// no token required for the initial token request
			$token = NULL;
	
			$this->check_signature($request, $consumer, $token);
	
			$new_token = $this->data_store->new_request_token($consumer);
	
			return $new_token;
		}
	
		/**
		 * process an access_token request
		 * returns the access token on success
		 */
		public function fetch_access_token(&$request) {
			$this->get_version($request);
	
			$consumer = $this->get_consumer($request);
	
			// requires authorized request token
			$token = $this->get_token($request, $consumer, "request");
	
	
			$this->check_signature($request, $consumer, $token);
	
			$new_token = $this->data_store->new_access_token($token, $consumer);
	
			return $new_token;
		}
	
		/**
		 * verify an api call, checks all the parameters
		 */
		public function verify_request(&$request) {
			$this->get_version($request);
			$consumer = $this->get_consumer($request);
			$token = $this->get_token($request, $consumer, "access");
			$this->check_signature($request, $consumer, $token);
			return array($consumer, $token);
		}
	
		// Internals from here
		/**
		 * version 1
		 */
		private function get_version(&$request) {
			$version = $request->get_parameter("oauth_version");
			if (!$version) {
				$version = 1.0;
			}
			if ($version && $version != $this->version) {
				throw new MBOAuthExcep("OAuth version '$version' not supported");
			}
			return $version;
		}
	
		/**
		 * figure out the signature with some defaults
		 */
		private function get_signature_method(&$request) {
			$signature_method =
				@$request->get_parameter("oauth_signature_method");
			if (!$signature_method) {
				$signature_method = "PLAINTEXT";
			}
	
			if (!in_array($signature_method,
				array_keys($this->signature_methods))) {
					throw new MBOAuthExcep(
						"Signature method '$signature_method' not supported " .
						"try one of the following: " .
						implode(", ", array_keys($this->signature_methods))
					);
				}
			return $this->signature_methods[$signature_method];
		}
	
		/**
		 * try to find the consumer for the provided request's consumer key
		 */
		private function get_consumer(&$request) {
			$consumer_key = @$request->get_parameter("oauth_consumer_key");
			if (!$consumer_key) {
				throw new MBOAuthExcep("Invalid consumer key");
			}
	
			$consumer = $this->data_store->lookup_consumer($consumer_key);
			if (!$consumer) {
				throw new MBOAuthExcep("Invalid consumer");
			}
	
			return $consumer;
		}
	
		/**
		 * try to find the token for the provided request's token key
		 */
		private function get_token(&$request, $consumer, $token_type="access") {
			$token_field = @$request->get_parameter('oauth_token');
			$token = $this->data_store->lookup_token(
				$consumer, $token_type, $token_field
			);
			if (!$token) {
				throw new MBOAuthExcep("Invalid $token_type token: $token_field");
			}
			return $token;
		}
	
		/**
		 * all-in-one function to check the signature on a request
		 * should guess the signature method appropriately
		 */
		private function check_signature(&$request, $consumer, $token) {
			// this should probably be in a different method
			$timestamp = @$request->get_parameter('oauth_timestamp');
			$nonce = @$request->get_parameter('oauth_nonce');
	
			$this->check_timestamp($timestamp);
			$this->check_nonce($consumer, $token, $nonce, $timestamp);
	
			$signature_method = $this->get_signature_method($request);
	
			$signature = $request->get_parameter('oauth_signature');
			$valid_sig = $signature_method->check_signature(
				$request,
				$consumer,
				$token,
				$signature
			);
	
			if (!$valid_sig) {
				throw new MBOAuthExcep("Invalid signature");
			}
		}
	
		/**
		 * check that the timestamp is new enough
		 */
		private function check_timestamp($timestamp) {
			// verify that timestamp is recentish
			$now = time();
			if ($now - $timestamp > $this->timestamp_threshold) {
				throw new MBOAuthExcep(
					"Expired timestamp, yours $timestamp, ours $now"
				);
			}
		}
	
		/**
		 * check that the nonce is not repeated
		 */
		private function check_nonce($consumer, $token, $nonce, $timestamp) {
			// verify that the nonce is uniqueish
			$found = $this->data_store->lookup_nonce(
				$consumer,
				$token,
				$nonce,
				$timestamp
			);
			if ($found) {
				throw new MBOAuthExcep("Nonce already used: $nonce");
			}
		}
	}
}
if(!class_exists('TsohuOAuthDataStore')){
	class TsohuOAuthDataStore
	{
		function lookup_consumer($consumer_key)
		{
			// implement me
		}
	
		function lookup_token($consumer, $token_type, $token) {
			// implement me
		}
	
		function lookup_nonce($consumer, $token, $nonce, $timestamp) {
			// implement me
		}
	
		function new_request_token($consumer) {
			// return a new token attached to this consumer
		}
	
		function new_access_token($token, $consumer) {
			// return a new access token attached to this consumer
			// for the user associated with this token if the request token
			// is authorized
			// should also invalidate the request token
		}
	}
}

if(!class_exists('TsohuOAuthUtil')){
	class TsohuOAuthUtil
	{
	
		public static $boundary = '';
	
		public static function urlencode_rfc3986($input) {
			if (is_array($input)) {
				return array_map(array('TsohuOAuthUtil', 'urlencode_rfc3986'), $input);
			} else if (is_scalar($input)) {
				return str_replace(
					'+',
					' ',
					str_replace('%7E', '~', rawurlencode($input))
				);
			} else {
				return '';
			}
		}
	
	
		// This decode function isn't taking into consideration the above
		// modifications to the encoding process. However, this method doesn't
		// seem to be used anywhere so leaving it as is.
		public static function urldecode_rfc3986($string) {
			return urldecode($string);
		}
	
		// Utility function for turning the Authorization: header into
		// parameters, has to do some unescaping
		// Can filter out any non-oauth parameters if needed (default behaviour)
		public static function split_header($header, $only_allow_oauth_parameters = true) {
			$pattern = '/(([-_a-z]*)=("([^"]*)"|([^,]*)),?)/';
			$offset = 0;
			$params = array();
			while (preg_match($pattern, $header, $matches, PREG_OFFSET_CAPTURE, $offset) > 0) {
				$match = $matches[0];
				$header_name = $matches[2][0];
				$header_content = (isset($matches[5])) ? $matches[5][0] : $matches[4][0];
				if (preg_match('/^oauth_/', $header_name) || !$only_allow_oauth_parameters) {
					$params[$header_name] = TsohuOAuthUtil::urldecode_rfc3986($header_content);
				}
				$offset = $match[1] + strlen($match[0]);
			}
	
			if (isset($params['realm'])) {
				unset($params['realm']);
			}
	
			return $params;
		}
	
		// helper to try to sort out headers for people who aren't running apache
		public static function get_headers() {
			if (function_exists('apache_request_headers')) {
				// we need this to get the actual Authorization: header
				// because apache tends to tell us it doesn't exist
				return apache_request_headers();
			}
			// otherwise we don't have apache and are just going to have to hope
			// that $_SERVER actually contains what we need
			$out = array();
			foreach ($_SERVER as $key => $value) {
				if (substr($key, 0, 5) == "HTTP_") {
					// this is chaos, basically it is just there to capitalize the first
					// letter of every word that is not an initial HTTP and strip HTTP
					// code from przemek
					$key = str_replace(
						" ",
						"-",
						ucwords(strtolower(str_replace("_", " ", substr($key, 5))))
					);
					$out[$key] = $value;
				}
			}
			return $out;
		}
	
		// This function takes a input like a=b&a=c&d=e and returns the parsed
		// parameters like this
		// array('a' => array('b','c'), 'd' => 'e')
		public static function parse_parameters( $input ) {
			if (!isset($input) || !$input) return array();
	
			$pairs = explode('&', $input);
	
			$parsed_parameters = array();
			foreach ($pairs as $pair) {
				$split = explode('=', $pair, 2);
				$parameter = TsohuOAuthUtil::urldecode_rfc3986($split[0]);
				$value = isset($split[1]) ? TsohuOAuthUtil::urldecode_rfc3986($split[1]) : '';
	
				if (isset($parsed_parameters[$parameter])) {
					// We have already recieved parameter(s) with this name, so add to the list
					// of parameters with this name
					if (is_scalar($parsed_parameters[$parameter])) {
						// This is the first duplicate, so transform scalar (string) into an array
						// so we can add the duplicates
						$parsed_parameters[$parameter] = array($parsed_parameters[$parameter]);
					}
					$parsed_parameters[$parameter][] = $value;
				} else {
					$parsed_parameters[$parameter] = $value;
				}
			}
			return $parsed_parameters;
		}
	
		public static function build_http_query_multi($params) {
			if (!$params) return '';
	
			//print_r( $params );
			//return null;
	
			// Urlencode both keys and values
			$keys = array_keys($params);
			$values = array_values($params);
			//$keys = TsohuOAuthUtil::urlencode_rfc3986(array_keys($params));
			//$values = TsohuOAuthUtil::urlencode_rfc3986(array_values($params));
			$params = array_combine($keys, $values);
	
			// Parameters are sorted by name, using lexicographical byte value ordering.
			// Ref: Spec: 9.1.1 (1)
			uksort($params, 'strcmp');
	
			$pairs = array();
	
			self::$boundary = $boundary = uniqid('------------------');
			$MPboundary = '--'.$boundary;
			$endMPboundary = $MPboundary. '--';
			$multipartbody = '';
	
			foreach ($params as $parameter => $value) {
				//if( $parameter == 'pic' && $value{0} == '@' )
				if( in_array($parameter,array("pic","image")) )
				{
					
					//$tmp = 'mm.jpg';
					$tmp = $value;
					$url = ltrim($tmp,'@');
					$content = file_get_contents( $url );
					@$filename = reset( explode( '?' , basename( $url ) ));
					$mime = self::get_image_mime($url);
				
					//$url = ltrim( $value , '@' );
					/*$content = $value[2];//file_get_contents( $url );
					$filename = $value[1];//reset( explode( '?' , basename( $url ) ));
					$mime = $value[0];//self::get_image_mime($url);*/
	
					$multipartbody .= $MPboundary . "\r\n";
					$multipartbody .= 'Content-Disposition: form-data; name="' . $parameter . '"; filename="' . $filename . '"'. "\r\n";
					$multipartbody .= 'Content-Type: '. $mime . "\r\n\r\n";
					$multipartbody .= $content. "\r\n";
				}
				else
				{
					$multipartbody .= $MPboundary . "\r\n";
					$multipartbody .= 'Content-Disposition: form-data; name="'.$parameter."\"\r\n\r\n";
					$multipartbody .= $value."\r\n";
	
				}
			}
	
			$multipartbody .=  "$endMPboundary\r\n";
			// For each parameter, the name is separated from the corresponding value by an '=' character (ASCII code 61)
			// Each name-value pair is separated by an '&' character (ASCII code 38)
			return $multipartbody;
		}
	
		public static function build_http_query($params) {
			if (!$params) return '';
	
			// Urlencode both keys and values
			$keys = TsohuOAuthUtil::urlencode_rfc3986(array_keys($params));
			$values = TsohuOAuthUtil::urlencode_rfc3986(array_values($params));
			$params = array_combine($keys, $values);
	
			// Parameters are sorted by name, using lexicographical byte value ordering.
			// Ref: Spec: 9.1.1 (1)
			uksort($params, 'strcmp');
	
			$pairs = array();
			foreach ($params as $parameter => $value) {
				if (is_array($value)) {
					// If two or more parameters share the same name, they are sorted by their value
					// Ref: Spec: 9.1.1 (1)
					natsort($value);
					foreach ($value as $duplicate_value) {
						$pairs[] = $parameter . '=' . $duplicate_value;
					}
				} else {
					$pairs[] = $parameter . '=' . $value;
				}
			}
			// For each parameter, the name is separated from the corresponding value by an '=' character (ASCII code 61)
			// Each name-value pair is separated by an '&' character (ASCII code 38)
			return implode('&', $pairs);
		}
	
		public static function get_image_mime( $file )
		{
			$ext = strtolower(pathinfo( $file , PATHINFO_EXTENSION ));
			switch( $ext )
			{
				case 'jpg':
				case 'jpeg':
					$mime = 'image/jpg';
					break;
	
				case 'png';
					$mime = 'image/png';
					break;
	
				case 'gif';
				default:
					$mime = 'image/gif';
					break;
			}
			return $mime;
		}
	}
}

if(!class_exists('weibo_api')){
	abstract class weibo_api
	{
		/**
		 * 服务提供方
		 */
		public $host = null;
	
		/**
		 * 用户令牌
		 */
		public $token = null;
	
		/**
		 * 第三方应用客户端
		 */
		public $consumer = null;
	
		/**
		 * 数据格式
		 */
		public $format = 'json';
	
		/**
		 * 签名方法
		 *
		 * @var string
		 */
		public $TsohuOAuthSignatureMethod = null;
	
	
		public function weibo_api()
		{
		}
	
		public function init($consumerKey = null, $consumerSecret = null, $accessToken = null, $accessSecret = null)
		{
			$this->TsohuOAuthSignatureMethod = new TsohuOAuthSignatureMethod_HMAC_SHA1();
			$this->consumer = new TsohuOAuthConsumer($consumerKey, $consumerSecret);
	
			if (!empty($accessToken) && !empty($accessSecret)) {
				$this->token = new TsohuOAuthConsumer($accessToken, $accessSecret);
			}
		}
	
		public abstract function getRequestTokenURL(); // 获得临时令牌的URL
		public abstract function getAuthorizeURL(); // 获得授权的URL
		public abstract function getAccessTokenURL(); // 获得访问令牌的URL
	
		/**
		 * 获得临时令牌
		 *
		 * @param string $callback 回调URL
		 * @return string
		 */
		public function getRequestToken($callback)
		{
			$request = $this->TsohuOAuthRequest($this->getRequestTokenURL(), 'GET', array('oauth_callback' => $callback));
			$token = TsohuOAuthUtil::parse_parameters($request);
			$this->token = new TsohuOAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
	
			return $token;
		}
	
		/**
		 * 设置访问令牌
		 *
		 * @param string $TsohuOAuthToken
		 * @param string $TsohuOAuthTokenSecret
		 * @return void
		 */
		public function setAccessToken($TsohuOAuthToken, $TsohuOAuthTokenSecret)
		{
	
		}
	
		/**
		 * 获得访问令牌
		 *
		 * @param string
		 */
		public function getAccessToken($oAuthVerifier = false, $TsohuOAuthToken = false)
		{
			$parameters = array();
			if (!empty($oAuthVerifier)) {
				$parameters['oauth_verifier'] = $oAuthVerifier;
			}
	
			$this->token = new TsohuOAuthConsumer($TsohuOAuthToken['oauth_token'], $TsohuOAuthToken['oauth_token_secret']);
			$request = $this->TsohuOAuthRequest($this->getAccessTokenURL(), 'GET', $parameters);
			$token = TsohuOAuthUtil::parse_parameters($request);
			if (!isset($token['oauth_token']) || !isset($token['oauth_token_secret'])) {
				return false;
			}
	
			$this->token = new TsohuOAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
	
			return $token;
		}
	
		public function jsonDecode($response, $assoc = true)
		{
			$response  = preg_replace('/[^\x20-\xff]*/', "", $response);
			$jsonArray = json_decode($response, $assoc);
			if (!is_array($jsonArray)) {
				return array();
				// throw new Exception('格式错误!');
			}
	
			return $jsonArray;
		}
	
		/**
		 * 重新封装的get请求.
		 * @return mixed
		 */
		public function get($url, $parameters = array())
		{
			$response = $this->TsohuOAuthRequest($url, 'GET', $parameters);
			if ($this->format === 'json') {
				return $this->jsonDecode($response, true);
			}
			return $response;
		}
	
		 /**
		 * 重新封装的post请求.
		 * @return mixed
		 */
		public function post($url, $parameters = array() , $multi = false)
		{
			$response = $this->TsohuOAuthRequest($url, 'POST', $parameters , $multi);
			if ($this->format === 'json') {
				return $this->jsonDecode($response, true);
			}
			return $response;
		}
	
		 /**
		 * DELTE wrapper for oAuthReqeust.
		 * @return mixed
		 */
		public function delete($url, $parameters = array())
		{
			$response = $this->TsohuOAuthRequest($url, 'DELETE', $parameters);
			if ($this->format === 'json') {
				return $this->jsonDecode($response, true);
			}
			return $response;
		}
	
		/**
		 * 发送请求的具体类
		 * @return string
		 */
		public function TsohuOAuthRequest($url, $method, $parameters, $multi = false)
		{
			if (strrpos($url, 'http://') !== false && strrpos($url, 'https://') !== false) {
				$url = "{$this->host}{$url}.{$this->format}";
			}
			$request = TsohuOAuthRequest::from_consumer_and_token($this->consumer, $this->token, $method, $url, $parameters);
			$request->sign_request($this->TsohuOAuthSignatureMethod, $this->consumer, $this->token);
			switch ($method) {
				case 'GET':
					return $this->http($request->to_url(), 'GET');
				default:
					return $this->http($request->get_normalized_http_url(), $method, $request->to_postdata($multi) , $multi );
			}
		}
	
		public function http($url, $method, $postfields = NULL , $multi = false)
		{
			//$https = 0;
			//判断是否是https请求
			if (strrpos($url, 'https://') !== false) {
				$port = 443;
				$version = '1.1';
				$host = 'ssl://' . $this->host;
			} else {
				$port = 80;
				$version = '1.0';
				$host = $this->host;
			}
	
			$header  = "$method $url HTTP/$version\r\n";
			$header .= "Host: {$this->host}\r\n";
			if ($multi) {
				$header .= "Content-Type: multipart/form-data; boundary=" . TsohuOAuthUtil::$boundary . "\r\n";
			} else {
				$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
			}
	
			if (strtolower($method) == 'post' ) {
				$header .= "Content-Length: ".strlen($postfields)."\r\n";
				$header .= "Connection: Close\r\n\r\n";
				$header .= $postfields;
			} else {
				$header .= "Connection: Close\r\n\r\n";
			}
	
			$ret = '';
	
			$fp = fsockopen($host, $port, $errno, $errstr, 10);
	
			if (!$fp) {
				$error = '建立sock连接失败';
				return $error;
				// throw new Exception($error);
			} else {
	
				fwrite ($fp, $header);
				while (!feof($fp)) {
					$ret .= fgets($fp, 4096);
				}
				fclose($fp);
	
				if (strrpos($ret, 'Transfer-Encoding: chunked')) {
					$info = explode("\r\n\r\n", $ret);
					$response = explode("\r\n", $info[1]);
					$t = array_slice($response, 1, -1);
					$returnInfo = implode('',$t);
				} else {
					$response   = explode("\r\n\r\n", $ret);
					$returnInfo = $response[1];
				}
	
				// 转成utf-8编码
				return iconv("utf-8", "utf-8//IGNORE", $returnInfo);
			}
		}
	}
}

/**
 * 搜狐微博API操作类
 */
if(!class_exists('weibo_api_sohu')){
class weibo_api_sohu extends weibo_api
{
    /**
     * 搜狐微博API host
     *
     * @var string
     */
    public $host = 'api.t.sohu.com';


    /**
     * 单例模式
     *
     * @return tencent
     */
    public function &instance()
    {
		static $object;

		if (empty($object)) {
			$object = new self();
		}

		return $object;
	}

    /**
     * 获取用户信息
     *
     * @return array
     */
    public function getUserInfo()
    {
        $url = 'http://' . $this->host . '/users/show.' . $this->format;

	 	return $this->get($url, array('format' => $this->format));
    }

	/**
	 * 发布一条微博信息
	 *
	 * @param array $params
	 * @return array
	 */
    public function add($params = array())
    {
        $url = "http://{$this->host}/statuses/update.{$this->format}";

        $data = array('status' => urlencode($params['content']));

		return $this->post($url, $data);
    }

    /**
     * 发布一条图片微博
     */
    public function addPic($params = array())
    {
        $url = "http://{$this->host}/statuses/upload.{$this->format}";

        $data = array('status' => urlencode($params['content']),
                      'pic' => $params['pic'],
                );

		return $this->post($url, $data, true);
    }


    /**
     * 返回错误信息说明
     *
     * 官方说明地址
     * http://open.t.sina.com.cn/wiki/index.php/Help/error
     *
     * @param array $result
     * @return array
     */
    public function getErrorMessage(&$result)
    {
        // 状态码返回值说明
        $stateArray = array(200 => '执行成功', 304 => '没有数据返回', 400 => '请求数据不合法或者超过请求频率限制', 401 => '没有进行身份验证', 402 => '没有开通微博',
                            403 => '没有权限访问对应的资源', 404 => '请求的资源不存在', 500 => '服务器内部错误', 502 => '微博接口API关闭或正在升级', 503 => '服务端资源不可用');
        // 发表接口错误字段errcode说明
        $errorCodeArray = array(40028 => '内部接口错误(如果有详细的错误信息，会给出更为详细的错误提示)', 40033 => 'source_user或者target_user用户不存在',
                                40031 => '调用的微博不存在', 40036 => '调用的微博不是当前用户发布的微博', 40034 => '不能转发自己的微博',40038 => '不合法的微博',
                                40037 => '不合法的评论', 40015 => '该条评论不是当前登录用户发布的评论', 40017 => '不能给不是你粉丝的人发私信', 40019 => '不合法的私信',
                                40021 => '不是属于你的私信', 40022 => 'source参数(appkey)缺失', 40007 => '格式不支持，仅仅支持XML或JSON格式', 40009 => '图片错误，请确保使用multipart上传了图片',
                                40011 => '私信发布超过上限', 40012 => '内容为空', 40016 => '微博id为空', 40018 => 'ids参数为空', 40020 => '评论ID为空', 40023 => '用户不存在',
                                40024 => 'ids过多，请参考API文档', 40025 => '不能发布相同的微博', 40026 => '请传递正确的目标用户uid或者screen name', 40045 => '不支持的图片类型',
                                40008 => '图片大小错误，上传的图片大小上限为5M', 40001 => '参数错误，请参考API文档', 40002 => '不是对象所属者，没有操作权限', 40010 => '私信不存在',
                                40013 => '微博太长，请确认不超过140个字符', 40039 => '地理信息输入错误', 40040 => 'IP限制，不能请求该资源', 40041 => 'uid参数为空', 40042 => 'token参数为空',
                                40043 => 'domain参数错误', 40044 => 'appkey参数缺失', 40029 => 'verifier错误', 40027 => '标签参数为空', 40032 => '列表名太长，请确保输入的文本不超过10个字符',
                                40030 => '列表描述太长，请确保输入的文本不超过70个字符',40035 => '列表不存在', 40053 => '权限不足，只有创建者有相关权限',40054 => '参数错误，请参考API文档',
                                40059 => '插入失败，记录已存在', 40060 => '数据库错误，请联系系统管理员', 40061 => '列表名冲突', 40062 => 'id列表太长了', 40063 => 'urls是空的', 40064 => 'urls太多了',
                                40065 => 'ip是空值', 40066 => 'url是空值', 40067 => 'trend_name是空值', 40068 => 'trend_id是空值', 40069 => 'userid是空值', 40070 => '第三方应用访问api接口权限受限制',
                                40071 => '关系错误，user_id必须是你关注的用户', 40072 => '授权关系已经被删除', 40073 => '目前不支持私有分组', 40074 => '创建list失败', 40075 => '需要系统管理员的权限',
                                40076 => '含有非法词', 40084 => '提醒失败，需要权限', 40082 => '无效分类!', 40083 => '无效状态码', 40084 => '目前只支持私有分组',
                                40101 => 'Oauth版本号错误', 40102 => 'Oauth缺少必要的参数', 40103 => 'Oauth参数被拒绝', 40104 => 'Oauth时间戳不正确', 40105 => 'Oauth nonce参数已经被使用',
                                40106 => 'Oauth签名算法不支持', 40107 => 'Oauth签名值不合法', 40108 => 'Oauth consumer_key不存在', 40109 => 'Oauth consumer_key不合法', 40110 => 'Oauth Token已经被使用',
                                40111 => 'Oauth Token已经过期', 40112 => 'Oauth Token不合法', 40113 => 'Oauth Token不合法', 40114 => 'Oauth Pin码认证失败',
                                40301 => '已拥有列表上限', 40302 => '认证失败', 40303 => '已经关注此用户', 40304 => '发布微博超过上限', 40305 => '发布评论超过上限',
                                40306 => '用户名密码认证超过请求限制', 40307 => '请求的HTTP METHOD不支持', 40308 => '发布微博超过上限', 40309 => '密码不正确', 40314 => '该资源需要appkey拥有更高级的授权',
                    );

        $result['error_status']    = empty($result) ? 2 : 0;
        $result['error_message']   = '';
        $result['errcode_message'] = '';

        if (isset($result['code']) && isset($result['error']) && $result['error']) {
            $result['error_status']  = 1;
            $result['error_message'] = isset($stateArray[$result['code']]) ? $stateArray[$result['code']] : $result['code'];
            $result['errcode_message'] = $result['error'];
            $errorCode = explode(':', $result['error']);
            if (isset($errorCodeArray[$errorCode[0]])) {
                $result['errcode_message'] = $errorCodeArray[$errorCode[0]];
            }
        }

        return $result;
    }

    /**
     * 应用请求临时令牌URL
     *
     * @return string
     */
    public function getRequestTokenURL()
    {
        return 'http://' . $this->host . '/oauth/request_token';
    }

    /**
     * 用户请求授权令牌URL
     *
     * @return string
     */
    public function getAuthorizeURL($TsohuOAuthToken = null, $TsohuOAuthTokenSecret = null, $oAuthCallback = null)
    {
        $url = 'http://' . $this->host . '/oauth/authorize?oauth_token=' . $TsohuOAuthToken;

        if (!empty($oAuthCallback)) {
            $url .= '&oauth_callback=' . urlencode($oAuthCallback);
        }

        return $url;
    }

    /**
     * 服务提供方授权访问令牌URL
     *
     * @return string
     */
    public function getAccessTokenURL()
    {
        return 'http://' . $this->host . '/oauth/access_token';
    }
}
}