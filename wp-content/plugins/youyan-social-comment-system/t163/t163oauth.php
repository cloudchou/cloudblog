<?php
/**
 * @copyright	© 2009-2011 JiaThis Inc.
 * @author		plhwin <plhwin@plhwin.com>
 * @since		version - 2011-8-18
 */

if(!class_exists('T163OAuthException')){
	class T163OAuthException extends Exception
	{
		// pass
	}
	
}

if(!class_exists('T163OAuthConsumer')){
class T163OAuthConsumer
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
        return "T163OAuthConsumer[key=$this->key,secret=$this->secret]";
    }
}
}

if(!class_exists('T163OAuthToken')){
class T163OAuthToken
{
    public $key;
    public $secret;
    
    /** 
     * key = the token 
     * secret = the token secret 
     */ 
    function __construct($key, $secret)
    {
        $this->key = $key; 
        $this->secret = $secret; 
    } 
    
    function __toString() { 
        return $this->to_string(); 
    }
    
    /** 
     * generates the basic string serialization of a token that a server 
     * would respond to request_token and access_token calls with 
     */ 
    function to_string()
    {
        return "oauth_token="
            . T163OAuthUtil::urlencode_rfc3986($this->key)
            . "&oauth_token_secret="
            . T163OAuthUtil::urlencode_rfc3986($this->secret);
    }
}
}

if(!class_exists('T163OAuthSignatureMethod')){
    class T163OAuthSignatureMethod{
        public function check_signature(&$request, $consumer, $token, $signature)
        {
            $built = $this->build_signature($request, $consumer, $token);
            return $built == $signature;
        }
        }
if(!class_exists('T163OAuthSignatureMethod_HMAC_SHA1')){
    class T163OAuthSignatureMethod_HMAC_SHA1 extends T163OAuthSignatureMethod
    {
        function get_name()
        {
            return "HMAC-SHA1";
        }
        
        public function build_signature($request, $consumer, $token)
        {
            $base_string = $request->get_signature_base_string();
            $request->base_string = $base_string;
            $key_parts = array($consumer->secret, ($token) ? $token->secret : "");
            $key_parts = T163OAuthUtil::urlencode_rfc3986($key_parts);
            $key = implode('&', $key_parts);
            return base64_encode(hash_hmac('sha1', $base_string, $key, true));
        }
    }
}

if(!class_exists('T163OAuthRequest')){
    class T163OAuthRequest
    {
        private $parameters;
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
            @$http_url or $http_url = $scheme . '://'
                . $_SERVER['HTTP_HOST'] . ':'
                . $_SERVER['SERVER_PORT']
                . $_SERVER['REQUEST_URI']; 
            @$http_method or $http_method = $_SERVER['REQUEST_METHOD'];

            // We weren't handed any parameters, so let's find the ones relevant to 
            // this request. 
            // If you run XML-RPC or similar you should use this to provide your own 
            // parsed parameter-list 
            if (!$parameters)
            {
                // Find request headers
                $request_headers = T163OAuthUtil::get_headers();
                
                // Parse the query-string to find GET parameters 
                $parameters = T163OAuthUtil::parse_parameters($_SERVER['QUERY_STRING']);
                
                // It's a POST request of the proper content-type, so parse POST
                // parameters and add those overriding any duplicates from GET
                if ($http_method == "POST"
                    && @strstr($request_headers["Content-Type"],
                        "application/x-www-form-urlencoded"))
                {
                    $post_data = T163OAuthUtil::parse_parameters(
                        file_get_contents(self::$POST_INPUT)
                    );
                    $parameters = array_merge($parameters, $post_data);
                }
                
                // We have a Authorization-header with OAuth data. Parse the header 
                // and add those overriding any duplicates from GET or POST 
                if (@substr($request_headers['Authorization'], 0, 6) == "OAuth ")
                {
                    $header_parameters = T163OAuthUtil::split_header($request_headers['Authorization']);
                    $parameters = array_merge($parameters, $header_parameters);
                }
            }
            return new T163OAuthRequest($http_method, $http_url, $parameters);
        }
        
        /**
         * pretty much a helper function to set up the request 
         */
        public static function from_consumer_and_token($consumer, $token, $http_method, $http_url, $parameters=NULL)
        {
            @$parameters or $parameters = array();
            $defaults = array("oauth_version" => T163OAuthRequest::$version,
                "oauth_nonce" => T163OAuthRequest::generate_nonce(),
                "oauth_timestamp" => T163OAuthRequest::generate_timestamp(),
                "oauth_consumer_key" => $consumer->key);
            if ($token)
                $defaults['oauth_token'] = $token->key;
            
            $parameters = array_merge($defaults, $parameters);
            
            return new T163OAuthRequest($http_method, $http_url, $parameters);
        }
        
        public function set_parameter($name, $value, $allow_duplicates = true)
        {
            if ($allow_duplicates && isset($this->parameters[$name]))
            {
                // We have already added parameter(s) with this name, so add to the list
                if (is_scalar($this->parameters[$name]))
                {
                    // This is the first duplicate, so transform scalar (string)
                    // into an array so we can add the duplicates
                    $this->parameters[$name] = array($this->parameters[$name]);
                }
                $this->parameters[$name][] = $value;
            }
            else
            {
                $this->parameters[$name] = $value;
            } 
        }
        
        public function get_parameter($name)
        {
            return isset($this->parameters[$name]) ? $this->parameters[$name] : null;
        }
        
        public function get_parameters()
        {
            return $this->parameters;
        }
        
        public function unset_parameter($name)
        {
            unset($this->parameters[$name]);
        } 
        
        /**
         * The request parameters, sorted and concatenated into a normalized string. 
         * @return string 
         */
        public function get_signable_parameters()
        {
            // Grab all parameters
            $params = $this->parameters;
            
            // remove pic
            if (isset($params['pic']))
            {
                unset($params['pic']);
            }
            
              if (isset($params['image']))
             { 
                unset($params['image']);
            }
            
            // Remove oauth_signature if present
            // Ref: Spec: 9.1.1 ("The oauth_signature parameter MUST be excluded.")
            if (isset($params['oauth_signature']))
            {
                unset($params['oauth_signature']);
            }
            
            return T163OAuthUtil::build_http_query($params);
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

            $parts = T163OAuthUtil::urlencode_rfc3986($parts); 

            return implode('&', $parts); 
        }
        
        /**
         * just uppercases the http method
         */
        public function get_normalized_http_method()
        {
            return strtoupper($this->http_method);
        }
        
        /**
         * parses the url and rebuilds it to be
         * scheme://host/path
         */
        public function get_normalized_http_url()
        {
            $parts = parse_url($this->http_url);
            $port = @$parts['port'];
            $scheme = $parts['scheme'];
            $host = $parts['host'];
            $path = @$parts['path'];
            $port or $port = ($scheme == 'https') ? '443' : '80'; 
            
            if (($scheme == 'https' && $port != '443') 
                || ($scheme == 'http' && $port != '80'))
            { 
                    $host = "$host:$port"; 
            }
            return "$scheme://$host$path"; 
        }
        
        /** 
         * builds a url usable for a GET request 
         */ 
        public function to_url()
        {
            $post_data = $this->to_postdata();
            $out = $this->get_normalized_http_url();
            if ($post_data)
            {
                $out .= '?'.$post_data;
            }
            return $out; 
        }
        
        /**
         * builds the data one would send in a POST request
         */
        public function to_postdata( $multi = false )
        {
        //echo "multi=" . $multi . '`';
        if($multi)
            return T163OAuthUtil::build_http_query_multi($this->parameters);
        else
            return T163OAuthUtil::build_http_query($this->parameters);
        }
        
        /**
         * builds the Authorization: header
         */
        public function to_header()
        {
            $out ='Authorization: OAuth realm=""';
            $total = array();
            foreach ($this->parameters as $k => $v)
            {
                if (substr($k, 0, 5) != "oauth") continue;
                if (is_array($v))
                {
                    throw new T163OAuthException('Arrays not supported in headers');
                }
                $out .= ',' . T163OAuthUtil::urlencode_rfc3986($k)
                        . '="' . T163OAuthUtil::urlencode_rfc3986($v) . '"';
            }
            return $out;
        }
        
        public function __toString()
        {
            return $this->to_url();
        }
        
        public function sign_request($signature_method, $consumer, $token)
        {
            $this->set_parameter("oauth_signature_method", $signature_method->get_name(), false);
            $signature = $this->build_signature($signature_method, $consumer, $token);
            $this->set_parameter("oauth_signature", $signature, false);
        } 
        
        public function build_signature($signature_method, $consumer, $token)
        {
            $signature = $signature_method->build_signature($this, $consumer, $token);
            return $signature;
        }
        
        /** 
         * util function: current timestamp 
         */ 
        private static function generate_timestamp()
        {
            return time(); 
        } 
        
        /** 
         * util function: current nonce 
         */ 
        private static function generate_nonce()
        {
            $mt = microtime();
            $rand = mt_rand();
            return md5($mt . $rand); // md5s look nicer than numbers
        }
    }
}
if(!class_exists('T163OAuthServer')){
    class T163OAuthServer
    {
        protected $timestamp_threshold = 300;    // in seconds, five minutes
        protected $version = 1.0;
        protected $signature_methods = array();
        protected $data_store;
        
        function __construct($data_store)
        {
            $this->data_store = $data_store;
        }
        
        public function add_signature_method($signature_method)
        {
            $this->signature_methods[$signature_method->get_name()] = $signature_method;
        }
        
        /** 
         * process a request_token request 
         * returns the request token on success 
         */ 
        public function fetch_request_token(&$request)
        {
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
        public function fetch_access_token(&$request)
        {
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
        public function verify_request(&$request)
        {
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
        private function get_version(&$request)
        {
            $version = $request->get_parameter("oauth_version");
            if (!$version)
            {
                $version = 1.0;
            } 
            if ($version && $version != $this->version)
            {
                throw new T163OAuthException("OAuth version '$version' not supported");
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
                    throw new T163OAuthException( 
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
                throw new T163OAuthException("Invalid consumer key"); 
            } 

            $consumer = $this->data_store->lookup_consumer($consumer_key); 
            if (!$consumer) { 
                throw new T163OAuthException("Invalid consumer"); 
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
                throw new T163OAuthException("Invalid $token_type token: $token_field"); 
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
                throw new T163OAuthException("Invalid signature"); 
            } 
        } 

        /** 
         * check that the timestamp is new enough 
         */ 
        private function check_timestamp($timestamp) { 
            // verify that timestamp is recentish 
            $now = time(); 
            if ($now - $timestamp > $this->timestamp_threshold) { 
                throw new T163OAuthException( 
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
                throw new T163OAuthException("Nonce already used: $nonce"); 
            } 
        }
    }
}
/** 
 * @ignore
 */ 
if(!class_exists('T163OAuthUtil')){}
    class T163OAuthUtil
    {
        public static $boundary = '';
        public static function urlencode_rfc3986($input)
        {
            if (is_array($input))
            {
                return array_map(array('T163OAuthUtil', 'urlencode_rfc3986'), $input);
            }
            else if (is_scalar($input))
            {
                return str_replace('+', ' ', str_replace('%7E', '~', rawurlencode($input)));
            }
            else
            { 
                return '';
            }
        }
        
        // This decode function isn't taking into consideration the above
        // modifications to the encoding process. However, this method doesn't
        // seem to be used anywhere so leaving it as is.
        public static function urldecode_rfc3986($string)
        {
            return urldecode($string);
        } 
        
        // Utility function for turning the Authorization: header into 
        // parameters, has to do some unescaping 
        // Can filter out any non-oauth parameters if needed (default behaviour) 
        public static function split_header($header, $only_allow_oauth_parameters = true)
        {
            $pattern = '/(([-_a-z]*)=("([^"]*)"|([^,]*)),?)/';
            $offset = 0;
            $params = array();
            while (preg_match($pattern, $header, $matches, PREG_OFFSET_CAPTURE, $offset) > 0)
            {
                $match = $matches[0];
                $header_name = $matches[2][0];
                $header_content = (isset($matches[5])) ? $matches[5][0] : $matches[4][0];
                if (preg_match('/^oauth_/', $header_name) || !$only_allow_oauth_parameters)
                {
                    $params[$header_name] = T163OAuthUtil::urldecode_rfc3986($header_content);
                }
                $offset = $match[1] + strlen($match[0]);
            }
            
            if (isset($params['realm']))
            {
                unset($params['realm']);
            }
            
            return $params;
        }
        
        // helper to try to sort out headers for people who aren't running apache
        public static function get_headers()
        {
            if (function_exists('apache_request_headers'))
            {
                // we need this to get the actual Authorization: header
                // because apache tends to tell us it doesn't exist
                return apache_request_headers();
            }
            // otherwise we don't have apache and are just going to have to hope
            // that $_SERVER actually contains what we need
            $out = array();
            foreach ($_SERVER as $key => $value)
            {
                if (substr($key, 0, 5) == "HTTP_")
                {
                    // this is chaos, basically it is just there to capitalize the first
                    // letter of every word that is not an initial HTTP and strip HTTP
                    // code from przemek
                    $key = str_replace(" ", "-", ucwords(strtolower(str_replace("_", " ", substr($key, 5))))
                    );
                    $out[$key] = $value;
                }
            }
            return $out;
        }
        
        // This function takes a input like a=b&a=c&d=e and returns the parsed
        // parameters like this
        // array('a' => array('b','c'), 'd' => 'e')
        public static function parse_parameters( $input )
        {
            if (!isset($input) || !$input) return array();
            
            $pairs = explode('&', $input);
            $parsed_parameters = array();
            
            foreach ($pairs as $pair)
            {
                $split = explode('=', $pair, 2);
                $parameter = T163OAuthUtil::urldecode_rfc3986($split[0]);
                $value = isset($split[1]) ? T163OAuthUtil::urldecode_rfc3986($split[1]) : '';
                
                if (isset($parsed_parameters[$parameter]))
                {
                    // We have already recieved parameter(s) with this name, so add to the list 
                    // of parameters with this name 
                    
                    if (is_scalar($parsed_parameters[$parameter]))
                    {
                        // This is the first duplicate, so transform scalar (string) into an array 
                        // so we can add the duplicates 
                        $parsed_parameters[$parameter] = array($parsed_parameters[$parameter]);
                    }
                    $parsed_parameters[$parameter][] = $value;
                }
                else
                {
                    $parsed_parameters[$parameter] = $value;
                }
            }
            return $parsed_parameters;
        }
        
        public static function build_http_query_multi($params)
        {
            if (!$params) return '';
            
            // Urlencode both keys and values 
            $keys = array_keys($params);
            $values = array_values($params);
            $params = array_combine($keys, $values);
            
            // Parameters are sorted by name, using lexicographical byte value ordering.
            // Ref: Spec: 9.1.1 (1)
            uksort($params, 'strcmp');
            
            $pairs = array();
            
            self::$boundary = $boundary = uniqid('------------------');
            $MPboundary = '--'.$boundary;
            $endMPboundary = $MPboundary. '--';
            $multipartbody = '';
            
            foreach ($params as $parameter => $value)
            {
                if( in_array($parameter,array("pic","image")) && $value{0} == '@' )
                {
                    $url = ltrim( $value , '@' );
                    $content = file_get_contents( $url );
                    @$filename = reset( explode( '?' , basename( $url ) ));
                    $mime = self::get_image_mime($url); 
                    
                    $multipartbody .= $MPboundary . "\r\n";
                    $multipartbody .= 'Content-Disposition: form-data; name="' . $parameter . '"; filename="' . $filename . '"'. "\r\n";
                    $multipartbody .= 'Content-Type: '. $mime . "\r\n\r\n";
                    $multipartbody .= $content. "\r\n";
                }
                else
                {
                    $multipartbody .= $MPboundary . "\r\n";
                    $multipartbody .= 'content-disposition: form-data; name="'.$parameter."\"\r\n\r\n";
                    $multipartbody .= $value."\r\n";
                }
            }
            
            $multipartbody .=  $endMPboundary;
            // For each parameter, the name is separated from the corresponding value by an '=' character (ASCII code 61)
            // Each name-value pair is separated by an '&' character (ASCII code 38)
            // echo $multipartbody;
            return $multipartbody;
        }
        
        public static function build_http_query($params)
        {
            if (!$params) return '';
            
            // Urlencode both keys and values
            $keys = T163OAuthUtil::urlencode_rfc3986(array_keys($params));
            $values = T163OAuthUtil::urlencode_rfc3986(array_values($params));
            $params = array_combine($keys, $values);
            
            // Parameters are sorted by name, using lexicographical byte value ordering.
            // Ref: Spec: 9.1.1 (1)
            uksort($params, 'strcmp');
            
            $pairs = array();
            foreach ($params as $parameter => $value)
            {
                if (is_array($value))
                {
                    // If two or more parameters share the same name, they are sorted by their value
                    // Ref: Spec: 9.1.1 (1)
                    natsort($value);
                    foreach ($value as $duplicate_value)
                    {
                        $pairs[] = $parameter . '=' . $duplicate_value;
                    }
                }
                else
                {
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
                case 'png':
                    $mime = 'image/png';
                    break;
                case 'bmp':
                    $mime = 'image/bmp';
                    break;
                case 'gif':
                default:
                    $mime = 'image/gif';
                    break;
            }
            return $mime;
        }
    }
}
/**
 * 网易微博操作类
 */
if(!class_exists('TBlog')){
    class TBlog
    {
        /**
         * 构造函数
         *
         * @access public
         * @param mixed $akey 微博开放平台应用APP KEY
         * @param mixed $skey 微博开放平台应用APP SECRET
         * @param mixed $accecss_token OAuth认证返回的token
         * @param mixed $accecss_token_secret OAuth认证返回的token secret
         * @return void
         */
        function __construct($akey, $skey, $accecss_token, $accecss_token_secret)
        {
            $this->oauth = new OAuth($akey, $skey, $accecss_token, $accecss_token_secret);
        }
        
        /**
         * 最新公共微博
         *
         * @access public
         * @return array
         */
        function public_timeline()
        {
            return $this->oauth->get('http://api.t.163.com/statuses/public_timeline.json');
        }
        
        /**
         * 最新关注人微博
         *
         * @access public
         * @return array
         */
        function friends_timeline()
        {
            return $this->home_timeline();
        }
        
        /**
         * 最新关注人微博
         *
         * @access public
         * @return array
         */
        function home_timeline($count=30, $since=false, $max=false, $trim=false)
        {
            return $this->request_163('http://api.t.163.com/statuses/home_timeline.json', $count, $since, $max, $trim); 
        }
        
        /** 
         * 最新 @用户的 
         *  
         * @access public 
         * @param int $count 每次返回的最大记录数（即页面大小），不大于200，默认为30。 
         * @return array 
         */ 
        function mentions($count=30, $since=false, $max=false, $trim=false) 
        { 
            return $this->request_163('http://api.t.163.com/statuses/mentions.json', $count, $since, $max, $trim); 
        }
        
        /** 
         * 发表微博 
         *  
         * @access public 
         * @param mixed $text 要更新的微博信息。 
         * @return array 
         */ 
        function update($text)
        { 
            $param = array();
            $param['status'] = $text;
            return $this->oauth->post('http://api.t.163.com/statuses/update.json', $param);
        }
        
    	 /** 
         * 上传图片 
         *  
         * @access public 
         * @param string $text 要更新的微博信息。 
         * @param string $text 要发布的图片路径,支持url。[只支持png/jpg/gif三种格式,增加格式请修改get_image_mime方法] 
         * @return array 
         */ 
        function uploadImage($pic_path)
        {
            $param = array();
            $param['pic'] = '@'.$pic_path;
            $pic = $this->oauth->post('http://api.t.163.com/statuses/upload.json', $param, true);
    		$param['url'] =$pic;
    		return $param;
        }

        /** 
         * 发表图片微博 
         *  
         * @access public 
         * @param string $text 要更新的微博信息。 
         * @param string $text 要发布的图片路径,支持url。[只支持png/jpg/gif三种格式,增加格式请修改get_image_mime方法] 
         * @return array 
         */ 
        function upload($text, $pic_path)
        {
            $param = array();
            $param['pic'] = "@".$pic_path;
            $pic = $this->oauth->post('http://api.t.163.com/statuses/upload.json', $param, true);
            $param1 = array();
            $param1['status'] = isset($text) ? $text." ".$pic['upload_image_url'] : $pic['upload_image_url'];
            return $this->oauth->post('http://api.t.163.com/statuses/update.json' , $param1);
        }
        
        /**
         * 获取单条微博
         *
         * @access public
         * @param mixed $sid 要获取已发表的微博ID
         * @return array
         */
        function show_status($sid)
        {
            return $this->oauth->get('http://api.t.163.com/statuses/show/' . $sid . '.json');
        }
        
        /**
         * 返回当前登录用户未读的新消息数量
         *
         * @return array
         */
        function latest()
        {
            return $this->oauth->get('http://api.t.163.com/reminds/message/latest.json');
        }
        
        /** 
         * 删除微博 
         *  
         * @access public 
         * @param mixed $sid 要删除的微博ID 
         * @return array 
         */ 
        function delete($sid) 
        { 
            return $this->destroy($sid); 
        } 
        
        /** 
         * 删除微博 
         *  
         * @access public 
         * @param mixed $sid 要删除的微博ID 
         * @return array 
         */ 
        function destroy($sid)
        {
            return $this->oauth->post('http://api.t.163.com/statuses/destroy/' . $sid . '.json');
        }
        
        /**
         * 被谁转发过
         *
         * @access public
         * @param mixed $sid 要查询的微博ID
         * @return array
         */
        function retweeted_by($sid, $count=false)
        {
            $param = array();
            if($count)$param['count'] = $count;
            return $this->oauth->get('http://api.t.163.com/statuses/' . $sid . '/retweeted_by.json');
        }
        
        /**
         * 个人资料
         *
         * @access public
         * @param mixed $uid_or_name 用户UID或微博昵称
         * @return array
         */
        function show_user_id($id_or_screen_name)
        {
            $param = array();
            $param['id'] = $id_or_screen_name;
            return $this->oauth->get('http://api.t.163.com/users/show.json', $param);
        }
        
        function show_user_name($name) 
        {
            $param = array();
            $param['name'] = $name;
            return $this->oauth->get('http://api.t.163.com/users/show.json', $param);
        }
        
        /**
         * 关注人列表
         *
         * @access public
         * @param bool $cursor 单页只能包含100个关注列表，为了获取更多则cursor默认从-1开始，通过增加或减少cursor来获取更多的关注列表
         * @param bool $count 每次返回的最大记录数（即页面大小），不大于200,默认返回20
         * @param mixed $uid_or_name 要获取的 UID或微博昵称
         * @return array
         */
        function friends($uid_or_screen_name, $cursor = false)
        {
            return $this->request_with_uid('http://api.t.163.com/statuses/friends.json', $uid_or_screen_name, false, false, $cursor);
        }
        
        /**
         * 粉丝列表
         * 
         * @access public
         * @param bool $cursor 单页只能包含100个粉丝列表，为了获取更多则cursor默认从-1开始，通过增加或减少cursor来获取更多的粉丝列表
         * @param bool $count 每次返回的最大记录数（即页面大小），不大于200,默认返回20
         * @param mixed $uid_or_name  要获取的 UID或微博昵称
         * @return array
         */
        function followers($uid_or_screen_name , $cursor = false)
        {
            return $this->request_with_uid('http://api.t.163.com/statuses/followers.json', $uid_or_screen_name, false, false, $cursor);
        }
        
        /**
         * 关注一个用户
         *
         * @access public
         * @param mixed $uid_or_name 要关注的用户UID或个性网址
         * @return array
         */
        function follow($uid_or_screen_name)
        {
            return $this->request_with_uid('http://api.t.163.com/friendships/create.json', $uid_or_screen_name, false, false, false, true);
        }
        
        function create($uid_or_screen_name)
        {
            return $this->request_with_uid('http://api.t.163.com/friendships/create.json', $uid_or_screen_name, false, false, false, true);
        }
        
        /**
         * 取消关注某用户
         *
         * @access public
         * @param mixed $uid_or_name 要取消关注的用户UID或个性网址
         * @return array
         */
        function unfollow($uid_or_screen_name)
        {
            return $this->request_with_uid('http://api.t.163.com/friendships/destroy.json', $uid_or_screen_name, false, false, false, true);
        }
        
        function destroy_friend($uid_or_screen_name)
        {
            return $this->request_with_uid('http://api.t.163.com/friendships/destroy.json', $uid_or_screen_name, false, false, false, true);
        }
        
        /**
         * 返回两个用户关系的详细情况
         *
         * @access public
         * @param mixed $uid_or_name 要判断的用户UID
         * @return array
         */
        function is_followed($s_id_or_screen_name, $t_id_or_screen_name=false)
        {
            $param = array();
            
            if(is_numeric($s_id_or_screen_name))
                $param['source_id'] = $s_id_or_screen_name;
            else
                $param['source_screen_name'] = $s_id_or_screen_name;
            
            if($t_id_or_screen_name)
            {
                if(is_numeric($t_id_or_screen_name))
                    $param['target_id'] = $t_id_or_screen_name;
                else
                    $param['target_screen_name'] = $t_id_or_screen_name;
            }
            return $this->oauth->get('http://api.t.163.com/friendships/show.json', $param);
        }
        
        function top_hot($type, $size=50)
        {
            switch ($type)
            {
                case "1": 
                case "oneHour":
                    $lx="oneHour"; 
                    break; 
                case "2": 
                case "sixHours":
                    $lx="sixHours"; 
                    break;  
                case "3": 
                case "oneDay":
                    $lx="oneDay"; 
                    break;  
                case "4": 
                case "oneWeek":
                    $lx="oneWeek"; 
                    break; 
                default: 
                    $lx=="oneHour"; 
                    break;
            }
            
            return $this->oauth->get('http://api.t.163.com/statuses/topRetweets/'.$lx.'.json?size='.$size);
        }
        
        /**
        * 用户发表微博列表
        *
        * @access public
        * @param int $count 每次返回的最大记录数，最多返回200条，默认30
        * @param mixed $uid_or_name 指定用户UID或微博昵称
        * @return array
        */
        function user_timeline_uid($user_id, $count = 30, $since_id = false, $max_id = false, $trim_user = false)
        {
            $param = array();
            $param['user_id'] = $user_id;
            if($count) $param['count'] = $count;
            if($since_id) $param['$since_id'] = $since_id;
            if($max_id) $param['max_id'] = $max_id;
            if($trim_user) $param['trim_user'] = $trim_user;
            $url = 'http://api.t.163.com/statuses/user_timeline.json';
            return $this->oauth->get($url, $param );
        }
        
        function user_timeline_sname($sname, $count=30, $since_id=false, $max_id=false, $trim_user=false) 
        { 
            $param = array();
            $param['screen_name'] = $sname;
            if($count) $param['count'] = $count;
            if($since_id) $param['$since_id'] = $since_id;
            if($max_id) $param['max_id'] = $max_id;
            if($trim_user) $param['trim_user'] = $trim_user;
                $url = 'http://api.t.163.com/statuses/user_timeline.json';
            return $this->oauth->get($url , $param );
        }
        
        function user_timeline_name($name, $count=30, $since_id=false, $max_id=false, $trim_user=false) 
        {
            $param = array();
            $param['name'] = $name;
            if( $count) $param['count'] = $count; 
            if( $since_id) $param['$since_id'] = $since_id; 
            if( $max_id) $param['max_id'] = $max_id; 
            if( $trim_user) $param['trim_user'] = $trim_user; 
                $url = 'http://api.t.163.com/statuses/user_timeline.json';
            return $this->oauth->get($url , $param ); 
        }
        
        function retweets_of_me($count=30, $since_id=false)
        { 
            $param = array();
            if( $count) $param['count'] = $count;
            if( $since_id) $param['$since_id'] = $since_id;
            $url = 'http://api.t.163.com/statuses/retweets_of_me.json';
            return $this->oauth->get($url , $param );
        }
        
        /**
         * 获取私信列表
         *
         * @access public
         * @param int $count 每次返回的最大记录数，最多返回200条，默认30
         * @return array
         */
        function list_dm($count = 30, $since_id=false)
        {
            $param = array();
            if($count) $param['count'] = $count;
            if($since_id) $param['$since_id'] = $since_id;
            return $this->oauth->get('http://api.t.163.com/direct_messages.json', $param);
        }
        
        /**
         * 发送的私信列表
         *
         * @access public
         * @param int $count 每次返回的最大记录数，最多返回200条，默认30
         * @return array
         */
        function list_dm_sent($count=30, $since_id=false)
        { 
            $param = array();
            if( $count) $param['count'] = $count;
            if( $since_id) $param['$since_id'] = $since_id;
            return $this->oauth->get('http://api.t.163.com/direct_messages/sent.json', $param);
        }
        
        /**
         * 发送私信
         *
         * @access public
         * @param mixed $uid_or_name UID或微博昵称
         * @param mixed $text 要发生的消息内容，文本大小必须小于300个汉字
         * @return array
         */
        function send_dm($name , $text)
        {
            $param = array();
            $param['text'] = $text;
            $param['name'] = $name;
            
            return $this->oauth->post('http://api.t.163.com/direct_messages/new.json', $param);
        }
        
        /**
         * 删除一条私信
         *
         * @access public
         * @param mixed $did 要删除的私信主键ID
         * @return array
         */
        function delete_dm($did)
        {
            return $this->oauth->post('http://api.t.163.com/direct_messages/destroy/'.$did.'.json');
        }
        
        /** 
         * 转发一条微博信息
         *
         * @access public
         * @param mixed $sid 转发的微博ID
         * @return array
         */
        function retweet($sid)
        {
            return $this->oauth->post('http://api.t.163.com/statuses/retweet/'.$sid.'.json');
        }
        
        /**
         * 对一条微博信息进行评论
         *
         * @access public
         * @param mixed $sid 要评论的微博id
         * @param mixed $text 评论内容
         * @param bool $cid 要评论的评论id
         * @return array
         */
        function send_comment($sid, $text, $cid=false)
        {
            $param = array();
            $param['id'] = $sid;
            $param['comment'] = $text;
            if( $cid ) $param['cid '] = $cid;
            
            return $this->oauth->post('http://api.t.163.com/statuses/comment.json', $param);
        } 
        
        /**
         * 发出的评论
         *
         * @access public
         * @param int $page 页码
         * @param int $count 每次返回的最大记录数，最多返回200条，默认20
         * @return array
         */
        function comments_by_me($page=1, $count=20)
        { 
            return $this->request_with_pager('http://api.t.163.com/statuses/comments_by_me.json', $page, $count);
        }
        
        /**
         * 最新评论(按时间)
         *
         * @access public
         * @param int $page 页码
         * @param int $count 每次返回的最大记录数，最多返回200条，默认20
         * @return array
         */
        function comments_timeline($page=1, $count=20)
        {
            return $this->request_with_pager('http://api.t.163.com/statuses/comments_timeline.json', $page, $count);
        }
        
        /** 
         * 单条评论列表(按微博)
         *
         * @access public
         * @param mixed $sid 指定的微博ID
         * @param int $page 页码 
         * @param int $count 每次返回的最大记录数，最多返回200条，默认20
         * @return array
         */
        function get_comments_by_sid($sid, $count=30, $since_id=false, $max_id=false, $trim_user=false)
        {
            $param = array();
            $param['id'] = $sid;
            if( $count) $param['count'] = $count;
            if( $since_id) $param['$since_id'] = $since_id;
            if( $max_id) $param['max_id'] = $max_id;
            if( $trim_user) $param['trim_user'] = $trim_user;
            
            return $this->oauth->get('http://api.t.163.com/statuses/comments.json', $param);
        }
        
        /**
         * 批量统计微博的评论数，转发数，一次请求最多获取100个
         *
         * @access public
         * @param mixed $sids 微博ID号列表，用逗号隔开
         * @return array
         */
        function get_count_info_by_ids($sids)
        {
            $param = array();
            $param['ids'] = $sids;
            return $this->oauth->get('http://api.t.163.com/statuses/counts.json', $param);
        }
        
        /**
         * 对一条微博评论信息进行回复
         *
         * @access public
         * @param mixed $sid 微博id
         * @param mixed $text 评论内容
         * @param mixed $cid 评论id
         * @return array
         */
        function reply($sid, $text, $cid)
        {
            $param = array();
            $param['id'] = $sid;
            $param['comment'] = $text;
            $param['cid '] = $cid;
            return $this->oauth->post('http://api.t.163.com/statuses/reply.json', $param);
        }
        
        /**
         * 返回用户的发布的最近20条收藏信息，和用户收藏页面返回内容是一致的
         *
         * @access public
         * @param bool $page 返回结果的页序号
         * @return array
         */
        function get_favorites($id_or_screen_name, $count=30, $since_id=false)
        {
            $param = array();
            if($count) $param['count'] = $count;
            if($since_id) $param['since_id'] = $since_id;
            $param['id'] = $id_or_screen_name;
            return $this->oauth->get('http://api.t.163.com/favorites/'.$id_or_screen_name.'.json', $param);
        }
        
        /**
         * 收藏一条微博信息
         *
         * @access public
         * @param mixed $sid 收藏的微博id
         * @return array
         */
        function add_to_favorites($sid)
        {
            return $this->oauth->post('http://api.t.163.com/favorites/create/'.$sid.'.json');
        }
        
        /**
         * 删除微博收藏
         *
         * @access public
         * @param mixed $sid 要删除的收藏微博信息ID
         * @return array
         */
        function remove_from_favorites($sid)
        {
            return $this->oauth->post('http://api.t.163.com/favorites/destroy/'.$sid.'.json');
        }
        
        function verify_credentials()
        {
            return $this->oauth->get('http://api.t.163.com/account/verify_credentials.json');
        }
        
        function update_avatar($pic_path)
        {
            $param = array();
            $param['image'] = "@".$pic_path;
            return $this->oauth->post('http://api.t.163.com/account/update_profile_image.json', $param , true);
        }
        
        /**
         * @ignore
         */
        protected function request_with_pager($url, $page=false, $count=false)
        {
            $param = array();
            if( $page ) $param['page'] = $page;
            if( $count ) $param['count'] = $count;
            
            return $this->oauth->get($url, $param);
        }
        
        protected function request_163($url, $count=false, $since=false, $max=false, $trim=false)
        {
            $param = array();
            if($count) $param['count'] = $count;
            if($since) $param['$since_id'] = $since;
            if($max) $param['max_id'] = $max;
            if($trim) $param['trim_user'] = $trim;
            
            return $this->oauth->get($url , $param); 
        }
        
        /**
         * @ignore
         */
        protected function request_with_uid($url, $uid_or_name, $page=false, $count=false, $cursor=false, $post=false)
        {
            $param = array();
            if($page) $param['page'] = $page;
            if($count) $param['count'] = $count;
            if($cursor)$param['cursor'] = $cursor;
            
            if($post) $method = 'post';
            else $method = 'get';
            
            if(is_numeric($uid_or_name))
            {
                $param['user_id'] = $uid_or_name;
                return $this->oauth->$method($url, $param);
            }
            elseif($uid_or_name !== null)
            {
                $param['screen_name'] = $uid_or_name;
                return $this->oauth->$method($url, $param);
            }
            else
            {
                return $this->oauth->$method($url, $param);
            }
        }
    }
}

if(!class_exists('OAuth')){
    class OAuth
    {
        /**
         * Contains the last HTTP status code returned.
         *
         * @ignore
         */
        public $http_code;
        /**
         * Contains the last API call.
         *
         * @ignore
         */
        public $url;
        /**
         * Set up the API root URL.
         *
         * @ignore
         */
        public $host = "http://api.t.163.com/";
        /**
         * Set timeout default.
         *
         * @ignore
         */
        public $timeout = 30;
        /**
         * Set connect timeout.
         *
         * @ignore
         */
        public $connecttimeout = 30;
        /**
         * Verify SSL Cert.
         *
         * @ignore
         */
        public $ssl_verifypeer = false;
        /**
         * Respons format.
         *
         * @ignore
         */
        public $format = 'json';
        /**
         * Decode returned json data.
         *
         * @ignore
         */
        public $decode_json = true;
        /**
         * Contains the last HTTP headers returned.
         *
         * @ignore
         */
        public $http_info;
        /**
         * Set the useragnet.
         *
         * @ignore
         */
        public $useragent = 't.163.com OAuth';
        
        /** 
         * Set API URLS 
         */ 
        /** 
         * @ignore 
         */ 
        function accessTokenURL() { return 'http://api.t.163.com/oauth/access_token'; }
        /** 
         * @ignore 
         */ 
        function authenticateURL() { return 'http://api.t.163.com/oauth/authenticate'; }
        /** 
         * @ignore 
         */ 
        function authorizeURL() { return 'http://api.t.163.com/oauth/authorize'; }
        /** 
         * @ignore 
         */ 
        function requestTokenURL() { return 'http://api.t.163.com/oauth/request_token'; }
        
        /** 
         * Debug helpers 
         */ 
        /** 
         * @ignore 
         */ 
        function lastStatusCode() { return $this->http_status; }
        /**
         * @ignore
         */
        function lastAPICall() { return $this->last_api_call; }
        
        function __construct($consumer_key, $consumer_secret, $oauth_token = null, $oauth_token_secret = null)
        {
            $this->sha1_method = new T163OAuthSignatureMethod_HMAC_SHA1();
            $this->consumer = new T163OAuthConsumer($consumer_key, $consumer_secret);
            if (!empty($oauth_token) && !empty($oauth_token_secret))
            {
                $this->token = new T163OAuthConsumer($oauth_token, $oauth_token_secret);
            }else
            {
                $this->token = null;
            }
        }
        
        /**
         * Get a request_token
         *
         * @return array a key/value array containing oauth_token and oauth_token_secret
         */
        function getRequestToken()
        {
            $parameters = array();
            $request = $this->T163OAuthRequest($this->requestTokenURL(), 'GET', $parameters);
            $token = T163OAuthUtil::parse_parameters($request);
            $this->token = new T163OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
            return $token;
        }
        
        /**
         * Get the authorize URL
         *
         * @return string
         */
        function getAuthorizeURL($token, $url=null)
        {
            if (is_array($token))
            {
                $token = $token['oauth_token'];
            } 
            if (empty($url))
            {
                return $this->authorizeURL() . "?oauth_token={$token}";
            }
            else
            {
                return $this->authenticateURL() . "?oauth_token={$token}&oauth_callback=". urlencode($url);
            }
        }
        
        /**
         * Exchange the request token and secret for an access token and
         * secret, to sign API calls.
         *
         * @return array array("oauth_token" => the access token,
         *                "oauth_token_secret" => the access secret)
         */
        function getAccessToken($oauth_verifier=false, $oauth_token=false)
        {
            $parameters = array();
            if (!empty($oauth_verifier))
            {
                $parameters['oauth_verifier'] = $oauth_verifier;
            }
            
            $request = $this->T163OAuthRequest($this->accessTokenURL(), 'GET', $parameters);
            $token = T163OAuthUtil::parse_parameters($request);
            $this->token = new T163OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
            return $token;
        }
        
        /**
         * GET wrappwer for T163OAuthRequest.
         *
         * @return mixed
         */
        function get($url, $parameters=array())
        {
            $response = $this->T163OAuthRequest($url, 'GET', $parameters);
            if ($this->format === 'json' && $this->decode_json)
            {
                return json_decode($response, true);
            } 
            return $response;
        }
        
        /**
         * POST wreapper for T163OAuthRequest.
         *
         * @return mixed
         */
        function post($url, $parameters=array(), $multi = false)
        {
            $response = $this->T163OAuthRequest($url, 'POST', $parameters , $multi);
            if ($this->format === 'json' && $this->decode_json)
            {
                return json_decode($response, true);
            }
            return $response;
        }
        
        /**
         * DELTE wrapper for oAuthReqeust.
         *
         * @return mixed
         */
        function delete($url, $parameters = array())
        {
            $response = $this->T163OAuthRequest($url, 'DELETE', $parameters);
            if ($this->format === 'json' && $this->decode_json)
            {
                return json_decode($response, true);
            }
            return $response;
        }
        
        /**
         * Format and sign an OAuth / API request
         *
         * @return string
         */
        function T163OAuthRequest($url, $method, $parameters, $multi=false)
        {
            if (strrpos($url, 'http://') !== 0 && strrpos($url, 'http://') !== 0)
            {
                $url = "{$this->host}{$url}.{$this->format}";
            }
            
            $request = T163OAuthRequest::from_consumer_and_token($this->consumer, $this->token, $method, $url, $parameters);
            $request->sign_request($this->sha1_method, $this->consumer, $this->token);
            switch ($method)
            {
                case 'GET':
                    return $this->http($request->to_url(), 'GET');
                default:
                    return $this->http($request->get_normalized_http_url(), $method, $request->to_postdata($multi) , $multi,$request->to_header() );
            }
        }
        
        /**
         * Make an HTTP request
         *
         * @return string API results
         */
        function http($url, $method, $postfields=null, $multi=false, $headermulti = "")
        {
    		if(extension_loaded('curl')){
    				
    			$this->http_info = array();
    			$ci = curl_init();
    			/* Curl settings */
    			curl_setopt($ci, CURLOPT_USERAGENT, $this->useragent);
    			curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout);
    			curl_setopt($ci, CURLOPT_TIMEOUT, $this->timeout);
    			curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
    			curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifypeer);
    			curl_setopt($ci, CURLOPT_HEADERFUNCTION, array($this, 'getHeader'));
    			curl_setopt($ci, CURLOPT_HEADER, false);
    			
    			switch ($method)
    			{
    				case 'POST':
    					curl_setopt($ci, CURLOPT_POST, true);
    					if (!empty($postfields))
    					{
    						curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
    					}
    					break; 
    				case 'DELETE': 
    					curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE'); 
    					if (!empty($postfields))
    					{
    						$url = "{$url}?{$postfields}";
    					}
    				default:
    					break;
    			}
    			
    			$header_array=array();
    			if($multi)
    				$header_array = array("Content-Type: multipart/form-data; boundary=" . T163OAuthUtil::$boundary , "Expect: ");
    			
    			array_push($header_array,$headermulti);
    			
    			curl_setopt($ci, CURLOPT_HTTPHEADER, $header_array);
    			curl_setopt($ci, CURLINFO_HEADER_OUT, true); 
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
    			$post.= "Content-type: application/x-www-form-urlencoded\r\n";
    			$post .= "Accept: */*\r\n";
    			$post.= "Content-length: ".strlen($postfields)."\r\n";
    			$post.= "Connection: close\r\n\r\n";
    			if($multi){
    				$post .= "Content-Type: multipart/form-data; boundary=" . T163OAuthUtil::$boundary;
    			}

    			$post.= $postfields;
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
         * 
         * @return int 
         */ 
        function getHeader($ch, $header)
        {
            $i = strpos($header, ':');
            if (!empty($i))
            {
                $key = str_replace('-', '_', strtolower(substr($header, 0, $i)));
                $value = trim(substr($header, $i + 2));
                $this->http_header[$key] = $value;
            }
            return strlen($header);
        }

    	 /** 
         *  store access token 
         */ 
        function storeAccessToken($token)
        {
            #TODO
        }

    	function loadAccessToken($uid)
        {
            $i = strpos($header, ':');
            if (!empty($i))
            {
                $key = str_replace('-', '_', strtolower(substr($header, 0, $i)));
                $value = trim(substr($header, $i + 2));
                $this->http_header[$key] = $value;
            }
            return strlen($header);
        }
    }
}