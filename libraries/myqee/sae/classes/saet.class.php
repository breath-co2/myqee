<?php

/*
 * Code based on:
 * Abraham Williams (abraham@abrah.am) http://abrah.am
 */

/* Load OAuth lib. You can find it at http://oauth.net */
/**
 * @ignore
 */
class OAuthException extends Exception {
    // pass
}

/**
 * @ignore
 */
class OAuthConsumer {
    public $key;
    public $secret;

    function __construct($key, $secret) {
        $this->key = $key;
        $this->secret = $secret;
    }

    function __toString() {
        return "OAuthConsumer[key=$this->key,secret=$this->secret]";
    }
}

/**
 * @ignore
 */
class OAuthToken {
    // access tokens and request tokens
    public $key;
    public $secret;

    /**
     * key = the token
     * secret = the token secret
     */
    function __construct($key, $secret) {
        $this->key = $key;
        $this->secret = $secret;
    }

    /**
     * generates the basic string serialization of a token that a server
     * would respond to request_token and access_token calls with
     */
    function to_string() {
        return "oauth_token=" .
            OAuthUtil::urlencode_rfc3986($this->key) .
            "&oauth_token_secret=" .
            OAuthUtil::urlencode_rfc3986($this->secret);
    }

    function __toString() {
        return $this->to_string();
    }
}

/**
 * @ignore
 */
class OAuthSignatureMethod {
    public function check_signature(&$request, $consumer, $token, $signature) {
        $built = $this->build_signature($request, $consumer, $token);
        return $built == $signature;
    }
}

/**
 * @ignore
 */
class OAuthSignatureMethod_HMAC_SHA1 extends OAuthSignatureMethod {
    function get_name() {
        return "HMAC-SHA1";
    }

    public function build_signature($request, $consumer, $token) {
        $base_string = $request->get_signature_base_string();
        $request->base_string = $base_string;

        $key_parts = array(
            $consumer->secret,
            ($token) ? $token->secret : ""
        );

        $key_parts = OAuthUtil::urlencode_rfc3986($key_parts);
        $key = implode('&', $key_parts);

        return base64_encode(hash_hmac('sha1', $base_string, $key, true));
    }
}

/**
 * @ignore
 */
class OAuthSignatureMethod_PLAINTEXT extends OAuthSignatureMethod {
    public function get_name() {
        return "PLAINTEXT";
    }

    public function build_signature($request, $consumer, $token) {
        $sig = array(
            OAuthUtil::urlencode_rfc3986($consumer->secret)
        );

        if ($token) {
            array_push($sig, OAuthUtil::urlencode_rfc3986($token->secret));
        } else {
            array_push($sig, '');
        }

        $raw = implode("&", $sig);
        // for debug purposes
        $request->base_string = $raw;

        return OAuthUtil::urlencode_rfc3986($raw);
    }
}

/**
 * @ignore
 */
class OAuthSignatureMethod_RSA_SHA1 extends OAuthSignatureMethod {
    public function get_name() {
        return "RSA-SHA1";
    }

    protected function fetch_public_cert(&$request) {
        // not implemented yet, ideas are:
        // (1) do a lookup in a table of trusted certs keyed off of consumer
        // (2) fetch via http using a url provided by the requester
        // (3) some sort of specific discovery code based on request
        //
        // either way should return a string representation of the certificate
        throw Exception("fetch_public_cert not implemented");
    }

    protected function fetch_private_cert(&$request) {
        // not implemented yet, ideas are:
        // (1) do a lookup in a table of trusted certs keyed off of consumer
        //
        // either way should return a string representation of the certificate
        throw Exception("fetch_private_cert not implemented");
    }

    public function build_signature(&$request, $consumer, $token) {
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

    public function check_signature(&$request, $consumer, $token, $signature) {
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

/**
 * @ignore
 */
class OAuthRequest {
    private $parameters;
    private $http_method;
    private $http_url;
    // for debug purposes
    public $base_string;
    public static $version = '1.0a';
    public static $POST_INPUT = 'php://input';

    function __construct($http_method, $http_url, $parameters=NULL) {
        @$parameters or $parameters = array();
        $this->parameters = $parameters;
        $this->http_method = $http_method;
        $this->http_url = $http_url;
    }


    /**
     * attempt to build up a request from what was passed to the server
     */
    public static function from_request($http_method=NULL, $http_url=NULL, $parameters=NULL) {
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
            $request_headers = OAuthUtil::get_headers();

            // Parse the query-string to find GET parameters
            $parameters = OAuthUtil::parse_parameters($_SERVER['QUERY_STRING']);

            // It's a POST request of the proper content-type, so parse POST
            // parameters and add those overriding any duplicates from GET
            if ($http_method == "POST"
                && @strstr($request_headers["Content-Type"],
                    "application/x-www-form-urlencoded")
            ) {
                $post_data = OAuthUtil::parse_parameters(
                    file_get_contents(self::$POST_INPUT)
                );
                $parameters = array_merge($parameters, $post_data);
            }

            // We have a Authorization-header with OAuth data. Parse the header
            // and add those overriding any duplicates from GET or POST
            if (@substr($request_headers['Authorization'], 0, 6) == "OAuth ") {
                $header_parameters = OAuthUtil::split_header(
                    $request_headers['Authorization']
                );
                $parameters = array_merge($parameters, $header_parameters);
            }

        }

        return new OAuthRequest($http_method, $http_url, $parameters);
    }

    /**
     * pretty much a helper function to set up the request
     */
    public static function from_consumer_and_token($consumer, $token, $http_method, $http_url, $parameters=NULL) {
        @$parameters or $parameters = array();
        $defaults = array("oauth_version" => OAuthRequest::$version,
            "oauth_nonce" => OAuthRequest::generate_nonce(),
            "oauth_timestamp" => OAuthRequest::generate_timestamp(),
            "oauth_consumer_key" => $consumer->key);
        if ($token)
            $defaults['oauth_token'] = $token->key;

        $parameters = array_merge($defaults, $parameters);

        return new OAuthRequest($http_method, $http_url, $parameters);
    }

    public function set_parameter($name, $value, $allow_duplicates = true) {
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
        return isset($this->parameters[$name]) ? $this->parameters[$name] : NULL;
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

        if (isset($params['image'])) {
            unset($params['image']);
        }

        // Remove oauth_signature if present
        // Ref: Spec: 9.1.1 ("The oauth_signature parameter MUST be excluded.")
        if (isset($params['oauth_signature'])) {
            unset($params['oauth_signature']);
        }

        return OAuthUtil::build_http_query($params);
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

        $parts = OAuthUtil::urlencode_rfc3986($parts);

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
    //echo "multi=" . $multi . '`';
    if( $multi )
        return OAuthUtil::build_http_query_multi($this->parameters);
    else
        return OAuthUtil::build_http_query($this->parameters);
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
                throw new OAuthException('Arrays not supported in headers');
            }
            $out .= ',' .
                OAuthUtil::urlencode_rfc3986($k) .
                '="' .
                OAuthUtil::urlencode_rfc3986($v) .
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

/**
 * @ignore
 */
class OAuthServer {
    protected $timestamp_threshold = 300; // in seconds, five minutes
    protected $version = 1.0;             // hi blaine
    protected $signature_methods = array();

    protected $data_store;

    function __construct($data_store) {
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
            throw new OAuthException("OAuth version '$version' not supported");
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
                throw new OAuthException(
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
            throw new OAuthException("Invalid consumer key");
        }

        $consumer = $this->data_store->lookup_consumer($consumer_key);
        if (!$consumer) {
            throw new OAuthException("Invalid consumer");
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
            throw new OAuthException("Invalid $token_type token: $token_field");
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
            throw new OAuthException("Invalid signature");
        }
    }

    /**
     * check that the timestamp is new enough
     */
    private function check_timestamp($timestamp) {
        // verify that timestamp is recentish
        $now = time();
        if ($now - $timestamp > $this->timestamp_threshold) {
            throw new OAuthException(
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
            throw new OAuthException("Nonce already used: $nonce");
        }
    }

}

/**
 * @ignore
 */
class OAuthDataStore {
    function lookup_consumer($consumer_key) {
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

/**
 * @ignore
 */
class OAuthUtil {

    public static $boundary = '';

    public static function urlencode_rfc3986($input) {
        if (is_array($input)) {
            return array_map(array('OAuthUtil', 'urlencode_rfc3986'), $input);
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
                $params[$header_name] = OAuthUtil::urldecode_rfc3986($header_content);
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
            $parameter = OAuthUtil::urldecode_rfc3986($split[0]);
            $value = isset($split[1]) ? OAuthUtil::urldecode_rfc3986($split[1]) : '';

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
        //return NULL;

        // Urlencode both keys and values
        $keys = array_keys($params);
        $values = array_values($params);
        //$keys = OAuthUtil::urlencode_rfc3986(array_keys($params));
        //$values = OAuthUtil::urlencode_rfc3986(array_values($params));
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

        if( in_array($parameter, array('pic', 'image')) && $value{0} == '@' )
        {
            $url = ltrim( $value , '@' );
            $content = file_get_contents( $url );
            $array = explode( '?' , basename( $url ) );
            $filename = $array[0];
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


            /*
            if (is_array($value)) {
                // If two or more parameters share the same name, they are sorted by their value
                // Ref: Spec: 9.1.1 (1)
                natsort($value);
                foreach ($value as $duplicate_value) {
                    $pairs[] = $parameter . '=' . $duplicate_value;
                }
            } else {
                $pairs[] = $parameter . '=' . $value;
            }*/

        }

        $multipartbody .=  $endMPboundary;
        // For each parameter, the name is separated from the corresponding value by an '=' character (ASCII code 61)
        // Each name-value pair is separated by an '&' character (ASCII code 38)
        // echo $multipartbody;
        return $multipartbody;
    }

    public static function build_http_query($params) {
        if (!$params) return '';

        // Urlencode both keys and values
        $keys = OAuthUtil::urlencode_rfc3986(array_keys($params));
        $values = OAuthUtil::urlencode_rfc3986(array_values($params));
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


/**
 * 新浪微博操作类
 *
 * 使用前需要先手工调用saet.ex.class.php <br />
 * Demo程序：http://apidoc.sinaapp.com/demo/saetdemo.zip <br />
 * Demo使用说明：
 *  - 下载,然后解压,修改config.php中的key
 *  - 打开index.php,将13行最后一个url改成你网站对应的callback.php的url
 *  - 上传到SAE平台即可
 *
 * @package sae
 * @author Easy Chen, Elmer Zhang
 * @version 1.0
 */
class SaeTClient
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
    function __construct( $akey , $skey , $accecss_token , $accecss_token_secret )
    {
        $this->oauth = new SaeTOAuth( $akey , $skey , $accecss_token , $accecss_token_secret );
    }

    /**
     * 开启调试信息
     *
     * 开启调试信息后，SDK会将每次请求微博API所发送的POST Data、Headers以及请求信息、返回内容输出出来。
     *
     * @access public
     * @param bool $enable 是否开启调试信息
     * @return void
     */
    function set_debug( $enable )
    {
        return $this->oauth->set_debug( $enable );
    }

    /**
     * 设置用户IP
     *
     * SDK默认将会通过$_SERVER['REMOTE_ADDR']获取用户IP，在请求微博API时将用户IP附加到Request Header中。但某些情况下$_SERVER['REMOTE_ADDR']取到的IP并非用户IP，而是一个固定的IP（例如使用SAE的Cron或TaskQueue服务时），此时就有可能会造成该固定IP达到微博API调用频率限额，导致API调用失败。此时可使用本方法设置用户IP，以避免此问题。
     *
     * @access public
     * @param string $ip 用户IP
     * @return bool IP为非法IP字符串时，返回false，否则返回true
     */
    function set_remote_ip( $ip )
    {
        return $this->oauth->set_remote_ip( $ip );
    }

    /**
     * 获取最新的公共微博消息
     *
     * 返回最新的20条公共微博。返回结果非完全实时，最长会缓存60秒
     * <br />对应API：statuses/public_timeline
     *
     * @access public
     * @param int $count 每次返回的记录数。缺省值20，最大值200。可选。
     * @param int $base_app 是否基于当前应用来获取数据。1为限制本应用微博，0为不做限制。默认为0。可选。
     * @return array
     */
    function public_timeline( $count = 20, $base_app = 0 )
    {
        $params = array();
        $params['count'] = intval($count);
        $params['base_app'] = intval($base_app);
        return $this->oauth->get('http://api.t.sina.com.cn/statuses/public_timeline.json', $params);
    }

    /**
     * 获取当前登录用户及其所关注用户的最新微博消息。
     *
     * 获取当前登录用户及其所关注用户的最新微博消息。和用户登录 http://t.sina.com.cn 后在“我的首页”中看到的内容相同。同home_timeline()
     * <br />对应API：statuses/home_timeline
     *
     * @access public
     * @param int $page 指定返回结果的页码。根据当前登录用户所关注的用户数及这些被关注用户发表的微博数，翻页功能最多能查看的总记录数会有所不同，通常最多能查看1000条左右。默认值1。可选。
     * @param int $count 每次返回的记录数。缺省值20，最大值200。可选。
     * @param int64 $since_id 若指定此参数，则只返回ID比since_id大的微博消息（即比since_id发表时间晚的微博消息）。可选。
     * @param int64 $max_id 若指定此参数，则返回ID小于或等于max_id的微博消息。可选。
     * @param int $base_app 是否基于当前应用来获取数据。1为限制本应用微博，0为不做限制。默认为0。可选。
     * @param int $feature 微博类型，0全部，1原创，2图片，3视频，4音乐. 返回指定类型的微博信息内容。转为为0。可选。
     * @return array
     */
    function friends_timeline( $page = 1, $count = 20, $since_id = NULL, $max_id = NULL, $base_app = 0, $feature = 0 )
    {
        return $this->home_timeline( $page, $count, $since_id, $max_id, $base_app, $feature );
    }

    /**
     * 获取当前登录用户及其所关注用户的最新微博消息。
     *
     * 获取当前登录用户及其所关注用户的最新微博消息。和用户登录 http://t.sina.com.cn 后在“我的首页”中看到的内容相同。同friends_timeline()
     * <br />对应API：statuses/home_timeline
     *
     * @access public
     * @param int $page 指定返回结果的页码。根据当前登录用户所关注的用户数及这些被关注用户发表的微博数，翻页功能最多能查看的总记录数会有所不同，通常最多能查看1000条左右。默认值1。可选。
     * @param int $count 每次返回的记录数。缺省值20，最大值200。可选。
     * @param int64 $since_id 若指定此参数，则只返回ID比since_id大的微博消息（即比since_id发表时间晚的微博消息）。可选。
     * @param int64 $max_id 若指定此参数，则返回ID小于或等于max_id的微博消息。可选。
     * @param int $base_app 是否基于当前应用来获取数据。1为限制本应用微博，0为不做限制。默认为0。可选。
     * @param int $feature 微博类型，0全部，1原创，2图片，3视频，4音乐. 返回指定类型的微博信息内容。转为为0。可选。
     * @return array
     */
    function home_timeline( $page = 1, $count = 20, $since_id = NULL, $max_id = NULL, $base_app = 0, $feature = 0 )
    {
        $params = array();
        if ($since_id) {
            $this->id_format($since_id);
            $params['since_id'] = $since_id;
        }
        if ($max_id) {
            $this->id_format($max_id);
            $params['max_id'] = $max_id;
        }
        $params['base_app'] = intval($base_app);
        $params['feature'] = intval($feature);

        return $this->request_with_pager('http://api.t.sina.com.cn/statuses/home_timeline.json', $page, $count, $params );
    }

    /**
     * 获取@当前用户的微博列表
     *
     * 返回最新n条提到登录用户的微博消息（即包含@username的微博消息）
     * <br />对应API：statuses/mentions
     *
     * @access public
     * @param int $page 返回结果的页序号。
     * @param int $count 每次返回的最大记录数（即页面大小），不大于200，默认为20。
     * @param int64 $since_id 若指定此参数，则只返回ID比since_id大的微博消息（即比since_id发表时间晚的微博消息）。可选。
     * @param int64 $max_id 若指定此参数，则返回ID小于或等于max_id的提到当前登录用户微博消息。可选。
     * @return array
     */
    function mentions( $page = 1 , $count = 20, $since_id = NULL, $max_id = NULL )
    {
        $params = array();
        if ($since_id) {
            $this->id_format($since_id);
            $params['since_id'] = $since_id;
        }
        if ($max_id) {
            $this->id_format($max_id);
            $params['max_id'] = $max_id;
        }

        return $this->request_with_pager( 'http://api.t.sina.com.cn/statuses/mentions.json' , $page , $count, $params );
    }

    /**
     * 发表微博
     *
     * 发布一条微博信息。请求必须用POST方式提交。为防止重复，发布的信息与当前最新信息一样话，将会被忽略。<br />
     * 注意：lat和long参数需配合使用，用于标记发表微博消息时所在的地理位置，只有用户设置中geo_enabled=true时候地理位置信息才有效。
     * <br />对应API：statuses/update
     *
     * @access public
     * @param string $status 要更新的微博信息。信息内容不超过140个汉字,为空返回400错误。
     * @param int64 $reply_id @ 需要回复的微博信息ID, 这个参数只有在微博内容以 @username 开头才有意义。（即将推出）。可选
     * @param float $lat 纬度，发表当前微博所在的地理位置，有效范围 -90.0到+90.0, +表示北纬。可选。
     * @param float $long 经度。有效范围-180.0到+180.0, +表示东经。可选。
     * @param mixed $annotations 可选参数。元数据，主要是为了方便第三方应用记录一些适合于自己使用的信息。每条微博可以包含一个或者多个元数据。请以json字串的形式提交，字串长度不超过512个字符，或者数组方式，要求json_encode后字串长度不超过512个字符。具体内容可以自定。例如：'[{"type2":123},{"a":"b","c":"d"}]'或array(array("type2"=>123), array("a"=>"b", "c"=>"d"))。
     * @return array
     */
    function update( $status, $reply_id = NULL, $lat = NULL, $long = NULL, $annotations = NULL )
    {
        //  http://api.t.sina.com.cn/statuses/update.json
        $params = array();
        $params['status'] = $status;
        if ($reply_id) {
            $this->id_format($reply_id);
            $params['in_reply_to_status_id'] = $reply_id;
        }
        if ($lat) {
            $params['lat'] = floatval($lat);
        }
        if ($long) {
            $params['long'] = floatval($long);
        }
        if (is_string($annotations)) {
            $params['annotations'] = $annotations;
        } elseif (is_array($annotations)) {
            $params['annotations'] = json_encode($annotations);
        }

        return $this->oauth->post( 'http://api.t.sina.com.cn/statuses/update.json' , $params );
    }

    /**
     * 发表图片微博
     *
     * 上传图片及发布微博信息。请求必须用POST方式提交。为防止重复，发布的信息与当前最新信息一样话，将会被忽略。目前上传图片大小限制为<5M。<br />
     * 注意：lat和long参数需配合使用，用于标记发表微博消息时所在的地理位置，只有用户设置中geo_enabled=true时候地理位置信息才有效。
     * <br />对应API：statuses/upload
     *
     * @access public
     * @param string $status 要更新的微博信息。信息内容不超过140个汉字,为空返回400错误。
     * @param string $pic_path 要发布的图片路径,支持url。[只支持png/jpg/gif三种格式,增加格式请修改get_image_mime方法]
     * @param float $lat 纬度，发表当前微博所在的地理位置，有效范围 -90.0到+90.0, +表示北纬。可选。
     * @param float $long 可选参数，经度。有效范围-180.0到+180.0, +表示东经。可选。
     * @return array
     */
    function upload( $status , $pic_path, $lat = NULL, $long = NULL )
    {
        //  http://api.t.sina.com.cn/statuses/update.json
        $params = array();
        $params['status'] = $status;
        $params['pic'] = '@'.$pic_path;
        if ($lat) {
            $params['lat'] = floatval($lat);
        }
        if ($long) {
            $params['long'] = floatval($long);
        }

        return $this->oauth->post( 'http://api.t.sina.com.cn/statuses/upload.json' , $params , true );
    }

    /**
     * 根据ID获取单条微博信息内容
     *
     * 获取单条ID的微博信息，作者信息将同时返回。
     * <br />对应API：statuses/show
     *
     * @access public
     * @param int64 $sid 要获取已发表的微博ID,如ID不存在返回空
     * @return array
     */
    function show_status( $sid )
    {
        $this->id_format($sid);
        return $this->oauth->get( 'http://api.t.sina.com.cn/statuses/show/' . $sid . '.json' );
    }

    /**
     * 删除一条微博
     *
     * 删除微博。注意：只能删除自己发布的信息。
     * <br />对应API：statuses/destroy
     *
     * @access public
     * @param int64 $sid 要删除的微博ID
     * @return array
     */
    function delete( $sid )
    {
        $this->id_format($sid);
        return $this->destroy( $sid );
    }

    /**
     * 删除一条微博
     *
     * 删除微博。注意：只能删除自己发布的信息。
     * <br />对应API：statuses/destroy
     *
     * @access public
     * @param int64 $sid 要删除的微博ID
     * @return array
     */
    function destroy( $sid )
    {
        $this->id_format($sid);
        return $this->oauth->post( 'http://api.t.sina.com.cn/statuses/destroy/' . $sid . '.json' );
    }

    /**
     * 根据用户UID或昵称获取用户资料
     *
     * 按用户UID或昵称返回用户资料，同时也将返回用户的最新发布的微博。
     * <br />对应API：users/show
     *
     * @access public
     * @param mixed $uid_or_name 用户UID或微博昵称。
     * @return array
     */
    function show_user( $uid_or_name )
    {
        return $this->request_with_uid( 'http://api.t.sina.com.cn/users/show.json' ,  $uid_or_name );
    }

    /**
     * 获取用户关注对象列表及最新一条微博信息
     *
     * 获取用户关注列表及每个关注用户最新一条微博，返回结果按关注时间倒序排列，最新关注的用户在最前面。
     * <br />对应API：statuses/friends
     *
     * @access public
     * @param int $cursor 单页只能包含100个关注列表，为了获取更多则cursor默认从-1开始，通过增加或减少cursor来获取更多的关注列表。可选。
     * @param int $count 每次返回的最大记录数（即页面大小），不大于200,默认返回20。可选。
     * @param mixed $uid_or_name 用户UID或微博昵称。不提供时默认返回当前用户的关注列表。可选。
     * @return array
     */
    function friends( $cursor = NULL , $count = 20 , $uid_or_name = NULL )
    {
        return $this->request_with_uid( 'http://api.t.sina.com.cn/statuses/friends.json' ,  $uid_or_name , NULL , $count , $cursor );
    }

    /**
     * 获取用户粉丝列表及每个粉丝用户最新一条微博
     *
     * 返回用户的粉丝列表，并返回粉丝的最新微博。按粉丝的关注时间倒序返回，每次返回100个。注意目前接口最多只返回5000个粉丝。
     * <br />对应API：statuses/followers
     *
     * @access public
     * @param int $cursor 单页只能包含100个粉丝列表，为了获取更多则cursor默认从-1开始，通过增加或减少cursor来获取更多的，如果没有下一页，则next_cursor返回0。可选。
     * @param int $count 每次返回的最大记录数（即页面大小），不大于200,默认返回20。可选。
     * @param mixed $uid_or_name 要获取粉丝的 UID或微博昵称。不提供时默认返回当前用户的关注列表。可选。
     * @return array
     */
    function followers( $cursor = NULL , $count = NULL , $uid_or_name = NULL )
    {
        return $this->request_with_uid( 'http://api.t.sina.com.cn/statuses/followers.json' ,  $uid_or_name , NULL , $count , $cursor );
    }

    /**
     * 关注一个用户
     *
     * 关注一个用户。成功则返回关注人的资料，目前的最多关注2000人，失败则返回一条字符串的说明。如果已经关注了此人，则返回http 403的状态。关注不存在的ID将返回400。
     * <br />对应API：friendships/create
     *
     * @access public
     * @param mixed $uid_or_name 要关注的用户UID或微博昵称
     * @return array
     */
    function follow( $uid_or_name )
    {
        return $this->request_with_uid( 'http://api.t.sina.com.cn/friendships/create.json' ,  $uid_or_name ,  NULL , NULL , NULL , true  );
    }

    /**
     * 取消关注某用户
     *
     * 取消关注某用户。成功则返回被取消关注人的资料，失败则返回一条字符串的说明。
     * <br />对应API：friendships/destroy
     *
     * @access public
     * @param mixed $uid_or_name 要取消关注的用户UID或微博昵称
     * @return array
     */
    function unfollow( $uid_or_name )
    {
        return $this->request_with_uid( 'http://api.t.sina.com.cn/friendships/destroy.json' ,  $uid_or_name ,  NULL , NULL , NULL , true);
    }

    /**
     * 根据微博ID和用户ID返回到单条微博页面地址
     *
     * 返回单条微博的Web地址。可以通过此url跳转到微博对应的Web网页。
     * <br />对应API：user/statuses/id
     *
     * @access public
     * @param int64 $sid 微博消息的ID
     * @param int64 $uid 微博消息的发布者ID。可选。
     * @return array
     */
    function get_status_url( $sid, $uid = NULL )
    {
        $this->id_format($sid);
        if ( !$uid ) {
            $status_info = $this->show_status($sid);
            if ($status_info) {
                $uid = $status_info['user']['id'];
                $this->id_format($uid);
            } else {
                return false;
            }
        }

        return "http://api.t.sina.com.cn/$uid/statuses/$sid";
    }

    /**
     * 更新当前登录用户所关注的某个好友的备注信息
     *
     * 只能修改当前登录用户所关注的用户的备注信息。否则将给出400错误。
     * <br />对应API：friends/update_remark
     *
     * @access public
     * @param int64 $uid 需要修改备注信息的用户ID。
     * @param string $remark 备注信息。
     * @return array
     */
    function update_remark( $uid, $remark )
    {
        $this->id_format($uid);

        $params = array();
        $params['user_id'] = $uid;
        $params['remark'] = $remark;

        return $this->oauth->post( 'http://api.t.sina.com.cn/user/friends/update_remark.json' , $params );
    }

    /**
     * 获取系统推荐用户
     *
     * 返回系统推荐的用户列表。
     * <br />对应API：users/hot
     *
     * @access public
     * @param string $category 分类，可选参数，返回某一类别的推荐用户，默认为 default。如果不在以下分类中，返回空列表：<br />
     *  - default:人气关注
     *  - ent:影视名星
     *  - hk_famous:港台名人
     *  - model:模特
     *  - cooking:美食&健康
     *  - sport:体育名人
     *  - finance:商界名人
     *  - tech:IT互联网
     *  - singer:歌手
     *  - writer：作家
     *  - moderator:主持人
     *  - medium:媒体总编
     *  - stockplayer:炒股高手
     * @return array
     */
    function hot_users( $category = "default" )
    {
        $params = array();
        $params['category'] = $category;

        return $this->oauth->get( 'http://api.t.sina.com.cn/users/hot.json' , $params );
    }

    /**
     * 获取表情列表
     *
     * 返回新浪微博官方所有表情、魔法表情的相关信息。包括短语、表情类型、表情分类，是否热门等。
     * <br />对应API：emotions
     *
     * @access public
     * @param string $type 表情类别。"face":普通表情，"ani"：魔法表情，"cartoon"：动漫表情。默认为"face"。可选。
     * @param string $language 语言类别，"cnname"简体，"twname"繁体。默认为"cnname"。可选
     * @return array
     */
    function emotions( $type = "face", $language = "cnname" )
    {
        $params = array();
        $params['type'] = $type;
        $params['language'] = $language;

        return $this->oauth->get( 'http://api.t.sina.com.cn/emotions.json' , $params );
    }

    /**
     * 未读消息数清零
     *
     * 将当前登录用户的某种新消息的未读数为0。可以清零的计数类别有：1. 评论数，2. @me数，3. 私信数，4. 关注数
     * <br />对应API：statuses/reset_count
     *
     * @access public
     * @param int $type 需要清零的计数类别，值为下列四个之一：1. 评论数，2. @me数，3. 私信数，4. 关注数
     * @return array
     */
    function reset_count( $type )
    {
        $params = array();
        $params['type'] = intval($type);

        return $this->oauth->get( 'http://api.t.sina.com.cn/statuses/reset_count.json' , $params );
    }

    /**
     * 返回两个用户关系的详细情况
     *
     * 如果用户已登录，此接口将自动使用当前用户ID作为source_id。但是可强制指定source_id来查询关系<br />
     * 如果源用户或目的用户不存在，将返回http的400错误
     * <br />对应API：friendships/show
     *
     * @access public
     * @param mixed $target 要查询的用户UID或微博昵称
     * @param mixed $source 源用户UID或源微博昵称，可选
     * @return array
     */
    function is_followed( $target, $source = NULL )
    {
        $this->id_format($target);
        $params = array();
        if( is_numeric( $target ) ) $params['target_id'] = $target;
        else $params['target_screen_name'] = $target;

        if ( $source != NULL ) {
            $this->id_format($source);
            if( is_numeric( $source ) ) $params['source_id'] = $source;
            else $params['source_screen_name'] = $source;
        }

        return $this->oauth->get( 'http://api.t.sina.com.cn/friendships/show.json' , $params );
    }

    /**
     * 获取用户发布的微博信息列表
     *
     * 返回用户的发布的最近n条信息，和用户微博页面返回内容是一致的。此接口也可以请求其他用户的最新发表微博。
     * <br />对应API：statuses/user_timeline
     *
     * @access public
     * @param int $page 页码
     * @param int $count 每次返回的最大记录数，最多返回200条，默认20。
     * @param mixed $uid_or_name 指定用户UID或微博昵称
     * @param int64 $since_id 若指定此参数，则只返回ID比since_id大的微博消息（即比since_id发表时间晚的微博消息）。可选。
     * @param int64 $max_id 若指定此参数，则返回ID小于或等于max_id的提到当前登录用户微博消息。可选。
     * @return array
     */
    function user_timeline( $page = 1 , $count = 20 , $uid_or_name = NULL , $since_id = NULL, $max_id = NULL)
    {
        $params = array();
        if ($since_id) {
            $this->id_format($since_id);
            $params['since_id'] = $since_id;
        }
        if ($max_id) {
            $this->id_format($max_id);
            $params['max_id'] = $max_id;
        }

        return $this->request_with_uid( 'http://api.t.sina.com.cn/statuses/user_timeline.json' ,  $uid_or_name , $page , $count , NULL , true, $params );
    }

    /**
     * 获取当前用户最新私信列表
     *
     * 返回用户的最新n条私信，并包含发送者和接受者的详细资料。
     * <br />对应API：direct_messages
     *
     * @access public
     * @param int $page 页码
     * @param int $count 每次返回的最大记录数，最多返回200条，默认20。
     * @param int64 $since_id 返回ID比数值since_id大（比since_id时间晚的）的私信。可选。
     * @param int64 $max_id 返回ID不大于max_id(时间不晚于max_id)的私信。可选。
     * @return array
     */
    function list_dm( $page = 1 , $count = 20, $since_id = NULL, $max_id = NULL )
    {
        $params = array();
        if ($since_id) {
            $this->id_format($since_id);
            $params['since_id'] = $since_id;
        }
        if ($max_id) {
            $this->id_format($max_id);
            $params['max_id'] = $max_id;
        }

        return $this->request_with_pager( 'http://api.t.sina.com.cn/direct_messages.json' , $page , $count, $params );
    }

    /**
     * 获取当前用户发送的最新私信列表
     *
     * 返回登录用户已发送最新20条私信。包括发送者和接受者的详细资料。
     * <br />对应API：direct_messages/sent
     *
     * @access public
     * @param int $page 页码
     * @param int $count 每次返回的最大记录数，最多返回200条，默认20。
     * @param int64 $since_id 返回ID比数值since_id大（比since_id时间晚的）的私信。可选。
     * @param int64 $max_id 返回ID不大于max_id(时间不晚于max_id)的私信。可选。
     * @return array
     */
    function list_dm_sent( $page = 1 , $count = 20, $since_id = NULL, $max_id = NULL )
    {
        $params = array();
        if ($since_id) {
            $this->id_format($since_id);
            $params['since_id'] = $since_id;
        }
        if ($max_id) {
            $this->id_format($max_id);
            $params['max_id'] = $max_id;
        }

        return $this->request_with_pager( 'http://api.t.sina.com.cn/direct_messages/sent.json' , $page , $count, $params );
    }

    /**
     * 发送私信
     *
     * 发送一条私信。成功将返回完整的发送消息。
     * <br />对应API：direct_messages/new
     *
     * @access public
     * @param mixed $uid_or_name UID或微博昵称
     * @param string $text 要发生的消息内容，文本大小必须小于300个汉字。
     * @return array
     */
    function send_dm( $uid_or_name , $text )
    {
        $this->id_format($uid_or_name);
        $params = array();
        $params['text'] = $text;
        $params['id'] = $uid_or_name;

        return $this->oauth->post( 'http://api.t.sina.com.cn/direct_messages/new.json' , $params  );
    }

    /**
     * 删除一条私信
     *
     * 按ID删除私信。操作用户必须为私信的接收人。
     * <br />对应API：direct_messages/destroy
     *
     * @access public
     * @param int64 $did 要删除的私信主键ID
     * @return array
     */
    function delete_dm( $did )
    {
        $this->id_format($did);
        return $this->oauth->post( 'http://api.t.sina.com.cn/direct_messages/destroy/' . $did . '.json' );
    }

    /**
     * 批量删除私信
     *
     * 批量删除当前登录用户的私信。出现异常时，返回HTTP400错误。
     * <br />对应API：direct_messages/destroy_batch
     *
     * @access public
     * @param mixed $dids 欲删除的一组私信ID，用半角逗号隔开，或者由一组评论ID组成的数组。最多20个。例如："4976494627,4976262053"或array(4976494627,4976262053);
     * @return array
     */
    function delete_dms( $dids )
    {
        $params = array();
        if (is_array($dids) && !empty($dids)) {
            foreach($dids as $k => $v) {
                $this->id_format($dids[$k]);
            }
            $params['ids'] = join(',', $dids);
        } else {
            $params['ids'] = $dids;
        }

        return $this->oauth->post( 'http://api.t.sina.com.cn/direct_messages/destroy_batch.json' , $params );
    }

    /**
     * 获取用户最新转发的n条微博消息
     *
     * 对应API：statuses/repost_by_me
     *
     * @access public
     * @param int64 $uid 要获取转发微博列表的用户ID。
     * @param int $page 页码。可选。
     * @param int $count 每次返回的最大记录数，最多返回200条，默认20。可选。
     * @param int64 $since_id 若指定此参数，则只返回ID比since_id大的记录（比since_id发表时间晚）。可选。
     * @param int64 $max_id 若指定此参数，则返回ID小于或等于max_id的记录。可选。
     * @return array
     */
    function repost_by_me( $uid, $page = 1 , $count = 20, $since_id = NULL, $max_id = NULL )
    {
        $this->id_format($uid);

        $params = array();
        $params['id'] = $uid;
        if ($since_id) {
            $this->id_format($since_id);
            $params['since_id'] = $since_id;
        }
        if ($max_id) {
            $this->id_format($max_id);
            $params['max_id'] = $max_id;
        }

        return $this->request_with_pager( 'http://api.t.sina.com.cn/statuses/repost_by_me.json' , $page , $count , $params );
    }

    /**
     * 返回一条原创微博的最新n条转发微博信息
     *
     * 对应API：statuses/repost_timeline
     *
     * @access public
     * @param int64 $sid 要获取转发微博列表的原创微博ID。
     * @param int $page 页码。可选。
     * @param int $count 每次返回的最大记录数，最多返回200条，默认20。可选。
     * @param int64 $since_id 若指定此参数，则只返回ID比since_id大的记录（比since_id发表时间晚）。可选。
     * @param int64 $max_id 若指定此参数，则返回ID小于或等于max_id的记录。可选。
     * @return array
     */
    function repost_timeline( $sid, $page = 1 , $count = 20, $since_id = NULL, $max_id = NULL )
    {
        $this->id_format($sid);

        $params = array();
        $params['id'] = $sid;
        if ($since_id) {
            $this->id_format($since_id);
            $params['since_id'] = $since_id;
        }
        if ($max_id) {
            $this->id_format($max_id);
            $params['max_id'] = $max_id;
        }

        return $this->request_with_pager( 'http://api.t.sina.com.cn/statuses/repost_timeline.json' , $page , $count , $params );
    }

    /**
     * 转发一条微博信息。
     *
     * 可加评论。为防止重复，发布的信息与最新信息一样话，将会被忽略。
     * <br />对应API：statuses/repost
     *
     * @access public
     * @param int64 $sid 转发的微博ID
     * @param string $text 添加的评论信息。可选。
     * @param int $is_comment 是否在转发的同时发表评论。1表示发表评论，0表示不发表。默认为0。可选。
     * @return array
     */
    function repost( $sid , $text = NULL, $is_comment = 0 )
    {
        $this->id_format($sid);

        $params = array();
        $params['id'] = $sid;
        $params['is_comment'] = $is_comment;
        if( $text ) $params['status'] = $text;

        return $this->oauth->post( 'http://api.t.sina.com.cn/statuses/repost.json' , $params  );
    }

    /**
     * 对一条微博信息进行评论
     *
     * 为防止重复，发布的信息与最后一条评论信息一样话，将会被忽略。
     * <br />对应API：statuses/comment
     *
     * @access public
     * @param int64 $sid 要评论的微博id
     * @param string $text 评论内容
     * @param int64 $cid 要评论的评论id
     * @return array
     */
    function send_comment( $sid , $text , $cid = NULL )
    {
        $this->id_format($sid);

        $params = array();
        $params['id'] = $sid;
        $params['comment'] = $text;
        if( $cid ) {
            $this->id_format($cid);
            $params['cid'] = $cid;
        }

        return $this->oauth->post( 'http://api.t.sina.com.cn/statuses/comment.json' , $params  );

    }

    /**
     * 批量删除当前用户的微博评论信息
     *
     * 批量删除评论。注意：只能删除登录用户自己发布的评论，不可以删除其他人的评论。
     * <br />对应API：comment/destroy_batch
     *
     * @access public
     * @param mixed $cids 欲删除的一组评论ID，用半角逗号隔开，或者由一组评论ID组成的数组。最多20个。例如："4976494627,4976262053"或array(4976494627,4976262053);
     * @return array
     */
    function comment_destroy_batch( $cids )
    {
        $params = array();
        if (is_array($cids) && !empty($cids)) {
            foreach ($cids as $k => $v) {
                $this->id_format($cids[$k]);
            }
            $params['ids'] = join(',', $cids);
        } else {
            $params['ids'] = $cids;
        }

        return $this->oauth->post( 'http://api.t.sina.com.cn/statuses/comment/destroy_batch.json' , $params );
    }

    /**
     * 获取当前用户未读消息数
     *
     * 获取当前用户Web未读消息数，包括@我的, 新评论，新私信，新粉丝数。
     * <br />对应API：statuses/unread
     *
     * @access public
     * @param int $with_new_status 1表示结果中包含new_status字段，0表示结果不包含new_status字段。new_status字段表示是否有新微博消息，1表示有，0表示没有。默认为0，可选。
     * @param int64 $since_id 参数值为微博id。该参数需配合with_new_status参数使用，返回since_id之后，是否有新微博消息产生。可选。
     * @return array
     */
    function unread( $with_new_status = 0, $since_id = NULL )
    {
        $params = array();
        if ( $with_new_status ) {
            $params['with_new_status'] = $with_new_status;
            if ( $since_id ) {
                $this->id_format($since_id);
                $params['since_id'] = $since_id;
            }
        }

        return $this->oauth->get( 'http://api.t.sina.com.cn/statuses/unread.json' , $params );
    }

    /**
     * 对一条微博评论信息进行回复。
     *
     * 为防止重复，发布的信息与最后一条评论/回复信息一样话，将会被忽略。
     * <br />对应API：statuses/reply
     *
     * @access public
     * @param int64 $sid 微博id
     * @param string $text 评论内容。
     * @param int64 $cid 评论id
     * @return array
     */
    function reply( $sid , $text , $cid )
    {
        $this->id_format($sid);
        $this->id_format($cid);
        $params = array();
        $params['id'] = $sid;
        $params['comment'] = $text;
        $params['cid'] = $cid;

        return $this->oauth->post( 'http://api.t.sina.com.cn/statuses/reply.json' , $params  );

    }

    /**
     * 获取当前用户的收藏列表
     *
     * 返回用户的发布的最近20条收藏信息，和用户收藏页面返回内容是一致的。
     * <br />对应API：favorites
     *
     * @access public
     * @param int $page 返回结果的页序号。可选。
     * @return array
     */
    function get_favorites( $page = NULL )
    {
        $params = array();
        if( $page ) $params['page'] = intval($page);

        return $this->oauth->get( 'http://api.t.sina.com.cn/favorites.json', $params );
    }

    /**
     * 删除当前用户的微博评论信息。
     *
     * 注意：只能删除自己发布的评论，发部微博的用户不可以删除其他人的评论。
     * <br />对应API：statuses/comment_destroy
     *
     * @access public
     * @param int64 $cid 要删除的评论id
     * @return array
     */
    function comment_destroy( $cid )
    {
        $this->id_format($cid);
        return $this->oauth->post( 'http://api.t.sina.com.cn/statuses/comment_destroy/' . $cid . '.json' );
    }

    /**
     * 获取当前用户收到的评论
     *
     * 对应API：statuses/comments_to_me
     *
     * @access public
     * @param int $page 页码
     * @param int $count 每次返回的最大记录数，最多返回200条，默认20。
     * @param int64 $since_id 若指定此参数，则只返回ID比since_id大的评论（比since_id发表时间晚）。可选。
     * @param int64 $max_id 若指定此参数，则返回ID小于或等于max_id的评论。可选。
     * @return array
     */
    function comments_to_me( $page = 1 , $count = 20, $since_id = NULL, $max_id = NULL )
    {
        $params = array();
        if ($since_id) {
            $this->id_format($since_id);
            $params['since_id'] = $since_id;
        }
        if ($max_id) {
            $this->id_format($max_id);
            $params['max_id'] = $max_id;
        }

        return $this->request_with_pager( 'http://api.t.sina.com.cn/statuses/comments_to_me.json' , $page , $count , $params );
    }


    /**
     * 获取当前用户发出的评论
     *
     * 对应API：statuses/comments_by_me
     *
     * @access public
     * @param int $page 页码
     * @param int $count 每次返回的最大记录数，最多返回200条，默认20。
     * @param int64 $since_id 若指定此参数，则只返回ID比since_id大的评论（比since_id发表时间晚）。可选。
     * @param int64 $max_id 若指定此参数，则返回ID小于或等于max_id的评论。可选。
     * @return array
     */
    function comments_by_me( $page = 1 , $count = 20, $since_id = NULL, $max_id = NULL )
    {
        $params = array();
        if ($since_id) {
            $this->id_format($since_id);
            $params['since_id'] = $since_id;
        }
        if ($max_id) {
            $this->id_format($max_id);
            $params['max_id'] = $max_id;
        }

        return $this->request_with_pager( 'http://api.t.sina.com.cn/statuses/comments_by_me.json' , $page , $count , $params );
    }

    /**
     * 最新评论(按时间)
     *
     * 返回最新n条发送及收到的评论。
     * <br />对应API：statuses/comments_timeline
     *
     * @access public
     * @param int $page 页码
     * @param int $count 每次返回的最大记录数，最多返回200条，默认20。
     * @param int64 $since_id 若指定此参数，则只返回ID比since_id大的评论（比since_id发表时间晚）。可选。
     * @param int64 $max_id 若指定此参数，则返回ID小于或等于max_id的评论。可选。
     * @return array
     */
    function comments_timeline( $page = 1 , $count = 20, $since_id = NULL, $max_id = NULL )
    {
        $params = array();
        if ($since_id) {
            $this->id_format($since_id);
            $params['since_id'] = $since_id;
        }
        if ($max_id) {
            $this->id_format($max_id);
            $params['max_id'] = $max_id;
        }

        return $this->request_with_pager( 'http://api.t.sina.com.cn/statuses/comments_timeline.json' , $page , $count , $params );
    }

    /**
     * 单条微博的评论列表
     *
     * 对应API：statuses/comments
     *
     * @access public
     * @param mixed $sid 指定的微博ID
     * @param int $page 页码
     * @param int $count 每次返回的最大记录数，最多返回200条，默认20。
     * @return array
     */
    function get_comments_by_sid( $sid , $page = 1 , $count = 20 )
    {
        $this->id_format($sid);
        $params = array();
        $params['id'] = $sid;
        if( $page ) $params['page'] = $page;
        if( $count ) $params['count'] = $count;

        return $this->oauth->get('http://api.t.sina.com.cn/statuses/comments.json' , $params );

    }

    /**
     * 批量获取一组微博的评论数及转发数
     *
     * 批量统计微博的评论数，转发数，一次请求最多获取100个。
     * <br />对应API：statuses/counts
     *
     * @access public
     * @param mixed $sids 微博ID号列表，用逗号隔开。或使用数据传递一组微博ID。如："32817222,32817223"或array(32817222, 32817223)
     * @return array
     */
    function get_count_info_by_ids( $sids )
    {
        $params = array();
        if (is_array($sids) && !empty($sids)) {
            foreach ($sids as $k => $v) {
                $this->id_format($sids[$k]);
            }
            $params['ids'] = join(',', $sids);
        } else {
            $params['ids'] = $sids;
        }

        return $this->oauth->get( 'http://api.t.sina.com.cn/statuses/counts.json' , $params );
    }

    /**
     * 收藏一条微博信息
     *
     * 对应API：favorites/create
     *
     * @access public
     * @param int64 $sid 收藏的微博id
     * @return array
     */
    function add_to_favorites( $sid )
    {
        $this->id_format($sid);
        $params = array();
        $params['id'] = $sid;

        return $this->oauth->post( 'http://api.t.sina.com.cn/favorites/create.json' , $params );
    }

    /**
     * 批量删除微博收藏。
     *
     * 批量删除当前登录用户的收藏。出现异常时，返回HTTP400错误。
     * <br />对应API：favorites/destroy_batch
     *
     * @access public
     * @param mixed $fids 欲删除的一组私信ID，用半角逗号隔开，或者由一组评论ID组成的数组。最多20个。例如："231101027525486630,201100826122315375"或array(231101027525486630,201100826122315375);
     * @return array
     */
    function remove_from_favorites_batch( $fids )
    {
        $params = array();
        if (is_array($fids) && !empty($fids)) {
            foreach ($fids as $k => $v) {
                $this->id_format($fids[$k]);
            }
            $params['ids'] = join(',', $fids);
        } else {
            $params['ids'] = $fids;
        }

        return $this->oauth->post( 'http://api.t.sina.com.cn/favorites/destroy_batch.json' , $params );
    }

    /**
     * 删除微博收藏。
     *
     * 对应API：favorites/destroy
     *
     * @access public
     * @param int64 $id 要删除的收藏微博信息ID.
     * @return array
     */
    function remove_from_favorites( $id )
    {
        $this->id_format($id);
        return $this->oauth->post( 'http://api.t.sina.com.cn/favorites/destroy/' . $id . '.json'  );
    }

    /**
     * 验证当前用户身份是否合法
     *
     * 如果用户新浪通行证身份验证成功且用户已经开通微博则返回 http状态为 200；如果是不则返回401的状态和错误信息。此方法用了判断用户身份是否合法且已经开通微博。
     * <br />对应API：account/verify_credentials
     *
     * @access public
     * @return array
     */
    function verify_credentials()
    {
        return $this->oauth->get('http://api.t.sina.com.cn/account/verify_credentials.json');
    }

    /**
     * 获取当前用户API访问频率限制
     *
     * 关于API的访问频率限制。返回当前小时还能访问的次数。频率限制是根据用户请求来做的限制，具体可以参加频率限制说明。
     * <br />对应API：account/rate_limit_status
     *
     * @access public
     * @return array
     */
    function rate_limit_status()
    {
        return $this->oauth->get('http://api.t.sina.com.cn/account/rate_limit_status.json');
    }

    /**
     * 当前用户退出登录
     *
     * 清除已验证用户的session，退出登录，并将cookie设为NULL。主要用于widget等web应用场合。
     * <br />对应API：account/end_session
     *
     * @access public
     * @return array
     */
    function end_session()
    {
        return $this->oauth->post('http://api.t.sina.com.cn/account/end_session.json');
    }

    /**
     * 设置隐私信息
     *
     * 对应API：account/update_privacy
     *
     * @access public
     * @param array $privacy_settings 要修改的隐私设置。格式：array('key1'=>'value1', 'key2'=>'value2', .....)。<br />
     * 支持设置的项：<br />
     *  - description 一句话介绍. 可选参数. 不超过160个汉字.
     *  - comment: 谁可以评论此账号的微薄。 0：所有人 1：我关注的人 默认为0
     *  - message:谁可以给此账号发私信。0：所有人 1：我关注的人 默认为1
     *  - realname 是否允许别人通过真实姓名搜索到我。0：允许，1：不允许，默认值1
     *  - geo 发布微博，是否允许微博保存并显示所处的地理位置信息。0：允许，1：不允许，默认值0
     *  - badge 勋章展现状态。0：公开状态，1：私密状态，默认值0
     * @return array
     */
    function update_privacy($privacy_settings)
    {
        return $this->oauth->post('http://api.t.sina.com.cn/account/update_privacy.json', $privacy_settings);
    }

    /**
     * 获取隐私信息设置情况
     *
     * 对应API：account/get_privacy
     *
     * @access public
     * @return array
     */
    function get_privacy()
    {
        return $this->oauth->post('http://api.t.sina.com.cn/account/get_privacy.json');
    }

    /**
     * 更改头像
     *
     * 对应API：account/update_profile_image
     *
     * @access public
     * @param string $image_path 要上传的头像路径,支持url。[只支持png/jpg/gif三种格式,增加格式请修改get_image_mime方法]
     * @return array
     */
    function update_profile_image($image_path)
    {
        $params = array();
        $params['image'] = "@{$image_path}";

        return $this->oauth->post('http://api.t.sina.com.cn/account/update_profile_image.json', $params, true);
    }

    /**
     * 更改用户资料
     *
     * 对应API：account/update_profile
     *
     * @access public
     * @param array $profile 要修改的资料。格式：array('key1'=>'value1', 'key2'=>'value2', .....)。<br />
     * 支持修改的项：<br />
     *  - name 昵称，可选参数.不超过20个汉字<br />
     *  - gender 性别，可选参数. m,男，f,女。<br />
     *  - province 所在省. 可选参数. 参考省份城市编码表<br />
     *  - city 所在城市. 可选参数. 参考省份城市编码表,1000为不限<br />
     *  - description 一句话介绍. 可选参数. 不超过160个汉字.
     * @return array
     */
    function update_profile($profile)
    {
        return $this->oauth->post('http://api.t.sina.com.cn/account/update_profile.json', $profile);
    }

    /**
     * 省份城市编码表
     *
     * 获取省份及城市编码ID与文字对应。由于微博接口用户province, city字段设置及返回都是ID，API调用方需要显示时转换成对应文字。转换关系如下
     * <br />对应API：provinces
     *
     * @access public
     * @return array
     */
    function provinces()
    {
        return $this->oauth->get('http://api.t.sina.com.cn/provinces.json');
    }

    /**
     * 返回用户关注对象uid列表
     *
     * 如果没有提供cursor参数，将只返回最前面的5000个关注id
     * <br />对应API：friends/ids
     *
     * @access public
     * @param int $cursor 单页只能包含5000个id，为了获取更多则cursor默认从-1开始，通过增加或减少cursor来获取更多的关注列表
     * @param int $count 每次返回的最大记录数（即页面大小），不大于5000,默认返回500。
     * @param mixed $uid_or_name  要获取的 UID或微博昵称
     * @return array
     */
    function friends_ids( $cursor = NULL , $count = 500 , $uid_or_name = NULL )
    {
        return $this->request_with_uid( 'http://api.t.sina.com.cn/friends/ids.json' ,  $uid_or_name , false , $count , $cursor );
    }

    /**
     * 返回用户粉丝uid列表
     *
     * 如果没有提供cursor参数，将只返回最前面的5000个粉丝id
     * <br />对应API：followers/ids
     *
     * @access public
     * @param int $cursor 单页只能包含5000个id，为了获取更多则cursor默认从-1开始，通过增加或减少cursor来获取更多的粉丝列表
     * @param int $count 每次返回的最大记录数（即页面大小），不大于5000,默认返回500。
     * @param mixed $uid_or_name  要获取的 UID或微博昵称
     * @return array
     */
    function followers_ids( $cursor = NULL , $count = 500 , $uid_or_name = NULL )
    {
        return $this->request_with_uid( 'http://api.t.sina.com.cn/followers/ids.json' ,  $uid_or_name , false , $count , $cursor );
    }

    /**
     * 将用户加入黑名单
     *
     * 对应API：blocks/create
     *
     * @access public
     * @param int64 $user_id 要加入黑名单的用户ID。可选。$user_id和$screen_name至少填一个。
     * @param string $screen_name 要加入黑名单的用户微博昵称，可选。$user_id和$screen_name至少填一个。
     * @return array
     */
    function add_to_blocks( $user_id = NULL, $screen_name = NULL )
    {
        $this->id_format($user_id);

        $params = array();
        if ( $user_id ) $params['user_id'] = $user_id;
        if ( $screen_name ) $params['screen_name'] = $screen_name;

        return $this->oauth->post( 'http://api.t.sina.com.cn/blocks/create.json' , $params );
    }

    /**
     * 将用户移出黑名单
     *
     * 对应API：blocks/destroy
     *
     * @access public
     * @param int64 $user_id 要移出黑名单的用户ID。可选。$user_id和$screen_name至少填一个。
     * @param string $screen_name 要移出黑名单的用户微博昵称，可选。$user_id和$screen_name至少填一个。
     * @return array
     */
    function remove_from_blocks( $user_id = NULL, $screen_name = NULL )
    {
        $this->id_format($user_id);

        $params = array();
        if ( $user_id ) $params['user_id'] = $user_id;
        if ( $screen_name ) $params['screen_name'] = $screen_name;

        return $this->oauth->post( 'http://api.t.sina.com.cn/blocks/destroy.json' , $params );
    }

    /**
     * 检测是否是黑名单用户
     *
     * 对应API：blocks/exists
     *
     * @access public
     * @param int64 $user_id 要检查的用户ID。可选。$user_id和$screen_name至少填一个。
     * @param string $screen_name 要检查的用户微博昵称，可选。$user_id和$screen_name至少填一个。
     * @return array
     */
    function in_blocks( $user_id = NULL, $screen_name = NULL )
    {
        $this->id_format($user_id);

        $params = array();
        if ( $user_id ) $params['user_id'] = $user_id;
        if ( $screen_name ) $params['screen_name'] = $screen_name;

        return $this->oauth->post( 'http://api.t.sina.com.cn/blocks/exists.json' , $params );
    }

    /**
     * 列出黑名单用户(输出用户详细信息)。
     *
     * 对应API：blocks/blocking
     *
     * @access public
     * @param int $page 指定返回结果的页码。可选。
     * @param int $count 单页大小。缺省值20，最大值200。可选。
     * @return array
     */
    function get_blocks( $page = 1, $count = 20 )
    {
        return $this->request_with_pager( 'http://api.t.sina.com.cn/blocks/blocking.json' , $page , $count );
    }

    /**
     * 列出黑名单用户(只输出id)。
     *
     * 对应API：blocks/blocking/ids
     *
     * @access public
     * @param int $page 指定返回结果的页码。可选。
     * @param int $count 单页大小。缺省值20，最大值200。可选。
     * @return array
     */
    function get_block_ids( $page = 1, $count = 20 )
    {
        return $this->request_with_pager( 'http://api.t.sina.com.cn/blocks/blocking/ids.json' , $page , $count );
    }

    /**
     * 返回指定用户的标签列表
     *
     * 对应API：tags
     *
     * @access public
     * @param int64 $user_id 查询用户的ID。默认为当前用户。可选。
     * @param int $page 指定返回结果的页码。可选。
     * @param int $count 单页大小。缺省值20，最大值200。可选。
     * @return array
     */
    function get_tags( $user_id = NULL, $page = 1, $count = 20 )
    {
        $params = array();
        if ($user_id) {
            $params['user_id'] = $user_id;
        } else {
            $user_info = $this->verify_credentials();
            $params['user_id'] = $user_info['id'];
        }
        $this->id_format($params['user_id']);
        return $this->request_with_pager( 'http://api.t.sina.com.cn/tags.json' , $page , $count , $params );
    }

    /**
     * 添加用户标签
     *
     * 对应API：tags/create
     *
     * @access public
     * @param mixed $tags 标签。多个标签之间用逗号间隔。或由多个标签构成的数组。如："abc,drf,efgh,tt"或array("abc","drf","efgh","tt")
     * @return array
     */
    function add_tags( $tags )
    {
        $params = array();
        if (is_array($tags) && !empty($tags)) {
            $params['tags'] = join(',', $tags);
        } else {
            $params['tags'] = $tags;
        }
        return $this->oauth->post( 'http://api.t.sina.com.cn/tags/create.json' , $params );
    }

    /**
     * 返回用户感兴趣的标签
     *
     * 对应API：tags/suggestions
     *
     * @access public
     * @param int $page 指定返回结果的页码。可选。
     * @param int $count 单页大小。缺省值10，最大值200。可选。
     * @return array
     */
    function get_suggest_tags( $page = 1, $count = 10 )
    {
        return $this->request_with_pager( 'http://api.t.sina.com.cn/tags/suggestions.json' , $page , $count );
    }

    /**
     * 删除标签
     *
     * 对应API：tags/destroy
     *
     * @access public
     * @param int $tag_id 标签ID，必填参数
     * @return array
     */
    function delete_tag( $tag_id )
    {
        $params = array();
        $params['tag_id'] = $tag_id;
        return $this->oauth->post( 'http://api.t.sina.com.cn/tags/destroy.json' , $params );
    }

    /**
     * 批量删除标签
     *
     * 对应API：tags/destroy_batch
     *
     * @access public
     * @param mixed $ids 必选参数，要删除的tag id，多个id用半角逗号分割，最多20个。或由多个tag id构成的数组。如：“553,554,555"或array(553,554,555)
     * @return array
     */
    function delete_tags( $ids )
    {
        $params = array();
        if (is_array($ids) && !empty($ids)) {
            $params['ids'] = join(',', $ids);
        } else {
            $params['ids'] = $ids;
        }
        return $this->oauth->post( 'http://api.t.sina.com.cn/tags/destroy_batch.json' , $params );
    }

    /**
     * 获取某用户的话题
     *
     * 对应API：trends
     *
     * @access public
     * @param int64 $user_id 查询用户的ID。默认为当前用户。可选。
     * @param int $page 指定返回结果的页码。可选。
     * @param int $count 单页大小。缺省值10。可选。
     * @return array
     */
    function get_trends( $user_id = NULL, $page = 1, $count = 20 )
    {
        $params = array();
        if ($user_id) {
            $params['user_id'] = $user_id;
        } else {
            $user_info = $this->verify_credentials();
            $params['user_id'] = $user_info['id'];
        }
        $this->id_format($params['user_id']);
        return $this->request_with_pager( 'http://api.t.sina.com.cn/trends.json' , $page , $count , $params );
    }

    /**
     * 获取某话题下的微博消息
     *
     * 对应API：trends/statuses
     *
     * @access public
     * @param string $trend_name 话题关键词。
     * @return array
     */
    function trends_timeline( $trend_name )
    {
        $params = array();
        $params['trend_name'] = $trend_name;

        return $this->oauth->get( 'http://api.t.sina.com.cn/trends/statuses.json' , $params );
    }

    /**
     * 关注某话题
     *
     * 对应API：trends/follow
     *
     * @access public
     * @param string $trend_name 要关注的话题关键词。
     * @return array
     */
    function follow_trends( $trend_name )
    {
        $params = array();
        $params['trend_name'] = $trend_name;

        return $this->oauth->post( 'http://api.t.sina.com.cn/trends/follow.json' , $params );
    }

    /**
     * 取消对某话题的关注
     *
     * 对应API：trends/destroy
     *
     * @access public
     * @param int64 $tid 要取消关注的话题ID。
     * @return array
     */
    function unfollow_trends( $tid )
    {
        $this->id_format($tid);

        $params = array();
        $params['trend_id'] = $tid;

        return $this->oauth->delete( 'http://api.t.sina.com.cn/trends/destroy.json' , $params );
    }

    /**
     * 返回最近一小时内的热门话题
     *
     * 对应API：trends/hourly
     *
     * @access public
     * @param int $base_app 是否基于当前应用来获取数据。1表示基于当前应用来获取数据，默认为1。可选。
     * @return array
     */
    function hourly_trends( $base_app = 1 )
    {
        $params = array();
        $params['base_app'] = $base_app;

        return $this->oauth->get( 'http://api.t.sina.com.cn/trends/hourly.json' , $params );
    }

    /**
     * 返回最近一天内的热门话题
     *
     * 对应API：trends/daily
     *
     * @access public
     * @param int $base_app 是否基于当前应用来获取数据。1表示基于当前应用来获取数据，默认为1。可选。
     * @return array
     */
    function daily_trends( $base_app = 1 )
    {
        $params = array();
        $params['base_app'] = $base_app;

        return $this->oauth->get( 'http://api.t.sina.com.cn/trends/daily.json' , $params );
    }

    /**
     * 返回最近一周内的热门话题
     *
     * 对应API：trends/weekly
     *
     * @access public
     * @param int $base_app 是否基于当前应用来获取数据。1表示基于当前应用来获取数据，默认为1。可选。
     * @return array
     */
    function weekly_trends( $base_app = 1 )
    {
        $params = array();
        $params['base_app'] = $base_app;

        return $this->oauth->get( 'http://api.t.sina.com.cn/trends/weekly.json' , $params );
    }

    // =========================================

    /**
     * @ignore
     */
    protected function request_with_pager( $url , $page = false , $count = false , $params = array() )
    {
        if( $page ) $params['page'] = $page;
        if( $count ) $params['count'] = $count;

        return $this->oauth->get($url , $params );
    }

    /**
     * @ignore
     */
    protected function request_with_uid( $url , $uid_or_name , $page = false , $count = false , $cursor = false , $post = false , $params = array())
    {
        if( $page ) $params['page'] = $page;
        if( $count ) $params['count'] = $count;
        if( $cursor )$params['cursor'] =  $cursor;

        if( $post ) $method = 'post';
        else $method = 'get';

        if ( $uid_or_name !== NULL ) {
            $this->id_format($uid_or_name);
            $params['id'] = $uid_or_name;
        }

        return $this->oauth->$method($url , $params );

    }

    protected function id_format(&$id) {
        if ( is_float($id) ) {
            $id = number_format($id, 0, '', '');
        } elseif ( is_string($id) ) {
            $id = trim($id);
        }
    }

}

/**
 * 新浪微博 OAuth 认证类
 *
 * @package sae
 * @author Easy Chen
 * @version 1.0
 */
class SaeTOAuth {
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
    public $host = "http://api.t.sina.com.cn/";
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
    public $ssl_verifypeer = FALSE;
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
    public $decode_json = TRUE;
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
    public $useragent = 'Sae T OAuth v0.2.0-beta2';

    /**
     * print the debug info
     *
     * @ignore
     */
    public $debug = FALSE;
    /* Immediately retry the API call if the response was not successful. */
    //public $retry = TRUE;

    /**
     * Set API URLS
     */
    /**
     * @ignore
     */
    function accessTokenURL()  { return 'http://api.weibo.com/oauth/access_token'; }
    /**
     * @ignore
     */
    function authenticateURL() { return 'http://api.weibo.com/oauth/authenticate'; }
    /**
     * @ignore
     */
    function authorizeURL()    { return 'http://api.weibo.com/oauth/authorize'; }
    /**
     * @ignore
     */
    function requestTokenURL() { return 'http://api.weibo.com/oauth/request_token'; }


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

    /**
     * construct WeiboOAuth object
     */
    function __construct($consumer_key, $consumer_secret, $oauth_token = NULL, $oauth_token_secret = NULL) {
        $this->sha1_method = new OAuthSignatureMethod_HMAC_SHA1();
        $this->consumer = new OAuthConsumer($consumer_key, $consumer_secret);
        if (!empty($oauth_token) && !empty($oauth_token_secret)) {
            $this->token = new OAuthConsumer($oauth_token, $oauth_token_secret);
        } else {
            $this->token = NULL;
        }
    }


    /**
     * Get a request_token from Weibo
     *
     * @return array a key/value array containing oauth_token and oauth_token_secret
     */
    function getRequestToken($oauth_callback = NULL) {
        $parameters = array();
        if (!empty($oauth_callback)) {
            $parameters['oauth_callback'] = $oauth_callback;
        }

        $request = $this->oAuthRequest($this->requestTokenURL(), 'GET', $parameters);
        $token = OAuthUtil::parse_parameters($request);
        $this->token = new OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
        return $token;
    }

    /**
     * Get the authorize URL
     *
     * @return string
     */
    function getAuthorizeURL($token, $sign_in_with_Weibo = TRUE , $url) {
        if (is_array($token)) {
            $token = $token['oauth_token'];
        }
        if (empty($sign_in_with_Weibo)) {
            return $this->authorizeURL() . "?oauth_token={$token}&oauth_callback=" . urlencode($url);
        } else {
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
    function getAccessToken($oauth_verifier = FALSE, $oauth_token = false) {
        $parameters = array();
        if (!empty($oauth_verifier)) {
            $parameters['oauth_verifier'] = $oauth_verifier;
        }


        $request = $this->oAuthRequest($this->accessTokenURL(), 'GET', $parameters);
        $token = OAuthUtil::parse_parameters($request);
        $this->token = new OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
        return $token;
    }

    /**
     * GET wrappwer for oAuthRequest.
     *
     * @return mixed
     */
    function get($url, $parameters = array()) {
        $response = $this->oAuthRequest($url, 'GET', $parameters);
        if ($this->format === 'json' && $this->decode_json) {
            return json_decode($response, true);
        }
        return $response;
    }

    /**
     * POST wreapper for oAuthRequest.
     *
     * @return mixed
     */
    function post($url, $parameters = array() , $multi = false) {
        $response = $this->oAuthRequest($url, 'POST', $parameters , $multi );
        if ($this->format === 'json' && $this->decode_json) {
            return json_decode($response, true);
        }
        return $response;
    }

    /**
     * DELTE wrapper for oAuthReqeust.
     *
     * @return mixed
     */
    function delete($url, $parameters = array()) {
        $response = $this->oAuthRequest($url, 'DELETE', $parameters);
        if ($this->format === 'json' && $this->decode_json) {
            return json_decode($response, true);
        }
        return $response;
    }

    /**
     * Format and sign an OAuth / API request
     *
     * @return string
     */
    function oAuthRequest($url, $method, $parameters , $multi = false) {

        if (strrpos($url, 'http://') !== 0 && strrpos($url, 'http://') !== 0) {
            $url = "{$this->host}{$url}.{$this->format}";
        }

        // echo $url ;
        $request = OAuthRequest::from_consumer_and_token($this->consumer, $this->token, $method, $url, $parameters);
        $request->sign_request($this->sha1_method, $this->consumer, $this->token);
        switch ($method) {
        case 'GET':
            //echo $request->to_url();
            return $this->http($request->to_url(), 'GET');
        default:
            return $this->http($request->get_normalized_http_url(), $method, $request->to_postdata($multi) , $multi );
        }
    }

    /**
     * Make an HTTP request
     *
     * @return string API results
     */
    function http($url, $method, $postfields = NULL , $multi = false) {
        $this->http_info = array();
        $ci = curl_init();
        /* Curl settings */
        curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ci, CURLOPT_USERAGENT, $this->useragent);
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout);
        curl_setopt($ci, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ci, CURLOPT_ENCODING, "");
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifypeer);
        curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, 1);
        curl_setopt($ci, CURLOPT_HEADERFUNCTION, array($this, 'getHeader'));
        curl_setopt($ci, CURLOPT_HEADER, FALSE);

        switch ($method) {
        case 'POST':
            curl_setopt($ci, CURLOPT_POST, TRUE);
            if (!empty($postfields)) {
                curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
                //echo "=====post data======\r\n";
                //echo $postfields;
            }
            break;
        case 'DELETE':
            curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
            if (!empty($postfields)) {
                $url = "{$url}?{$postfields}";
            }
        }

        $headers=array();
        if( $multi )
            $headers = array("Content-Type: multipart/form-data; boundary=" . OAuthUtil::$boundary , "Expect: ");
        if ( !empty($this->remote_ip) ) {
            if ( defined('SAE_ACCESSKEY') ) {
                $headers[] = "SaeRemoteIP: " . $this->remote_ip;
            } else {
                $headers[] = "API-RemoteIP: " . $this->remote_ip;
            }
        } else {
            if ( !defined('SAE_ACCESSKEY') ) {
                $headers[] = "API-RemoteIP: " . $_SERVER['REMOTE_ADDR'];
            }
        }

        curl_setopt($ci, CURLOPT_URL, $url );
        curl_setopt($ci, CURLOPT_HTTPHEADER, $headers );
        curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE );

        $response = curl_exec($ci);
        $this->http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
        $this->http_info = array_merge($this->http_info, curl_getinfo($ci));
        $this->url = $url;

        if ( $this->debug ) {
            echo "=====post data======\r\n";
            var_dump($postfields);

            echo "=====headers======\r\n";
            print_r($headers);

            echo '=====request info====='."\r\n";
            print_r( curl_getinfo($ci) );

            echo '=====response====='."\r\n";
            print_r( $response );
        }

        curl_close ($ci);
        return $response;
    }

    /**
     * Get the header info to store.
     *
     * @return int
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

    /**
     * 设置用户IP
     *
     * SDK默认将会通过$_SERVER['REMOTE_ADDR']获取用户IP，在请求微博API时将用户IP附加到Request Header中。但某些情况下$_SERVER['REMOTE_ADDR']取到的IP并非用户IP，而是一个固定的IP（例如使用SAE的Cron或TaskQueue服务时），此时就有可能会造成该固定IP达到微博API调用频率限额，导致API调用失败。此时可使用本方法设置用户IP，以避免此问题。
     *
     * @access public
     * @param string $ip 用户IP
     * @return bool IP为非法IP字符串时，返回false，否则返回true
     */
    function set_remote_ip( $ip )
    {
        if ( ip2long($ip) !== false ) {
            $this->remote_ip = $ip;
            return true;
        } else {
            return false;
        }
    }

    /**
     * 开启调试信息
     *
     * 开启调试信息后，SDK会将每次请求微博API所发送的POST Data、Headers以及请求信息、返回内容输出出来。
     *
     * @access public
     * @param bool $enable 是否开启调试信息
     * @return void
     */
    function set_debug( $enable )
    {
        $this->debug = $enable;
    }
}

/**
 * 新浪微博 OAuth 认证类(旧)
 *
 * @ignore
 */
class SaeT extends SaeTOAuth
{
    function __construct( $consumer_key, $consumer_secret, $oauth_token = NULL, $oauth_token_secret = NULL )
    {
        parent::__construct( $consumer_key, $consumer_secret, $oauth_token , $oauth_token_secret );
    }
}

/**
 * 新浪微博 XAuth 认证类
 *
 * 使用Demo如下：
 * <code>
 * <?php
 *
 * session_start();
 * include_once( 'config.php' );
 * include_once( 'saet.ex.class.php' );
 *
 * $o = new SaeTXAuth(WB_AKEY, WB_SKEY);
 * $keys = $o->getRequestToken();
 * unset($o);
 *
 * $o = new SaeTXAuth(WB_AKEY, WB_SKEY, $keys['oauth_token'], $keys['oauth_token_secret']);
 * $last_key = $o->getAccessToken('username@sina.com', 'password') ;
 *
 * $_SESSION['keys'] = $keys;
 * $_SESSION['last_key'] = $last_key;
 *
 * $c = new SaeTClient( WB_AKEY , WB_SKEY , $_SESSION['last_key']['oauth_token'] , $_SESSION['last_key']['oauth_token_secret']  );
 * $ms  = $c->home_timeline();
 *
 * ?>
 * </code>
 *
 * @package sae
 * @author Bruce Chen
 * @version 1.0
 *
 */

class SaeTXAuth extends SaeTOAuth {

    /**
     * 使用用户名、密码请求access token
     *
     * @param string $x_auth_username 用户名
     * @param string $x_auth_password 密码
     * @return array token
     */
    function getAccessToken($x_auth_username, $x_auth_password) {

        $parameters = array();

        $parameters['x_auth_username'] = $x_auth_username;
        $parameters['x_auth_password'] = $x_auth_password;
        $parameters['x_auth_mode'] = 'client_auth';

        $request = $this->oAuthRequest($this->accessTokenURL(), 'POST', $parameters);
        $token = OAuthUtil::parse_parameters($request);
        $this->token = new OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
        return $token;
    }
}