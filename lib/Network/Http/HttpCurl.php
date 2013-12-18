<?php

class HttpCurl {

    /**
     * When one activates the $quirksMode by setting it to true, all checks meant to
     * enforce RFC 2616 (HTTP/1.1 specs).
     * will be disabled and additional measures to deal with non-standard responses will be enabled.
     *
     * @var boolean
     */
    public $quirksMode = false;
    /**
     * Contain information about the last request (read only)
     *
     * @var array
     */
    public $request = array(
        'method' => 'GET',
        'uri' => array(
            'scheme' => 'http',
            'host' => null,
            'port' => 80,
            'user' => null,
            'pass' => null,
            'path' => null,
            'query' => null,
            'fragment' => null
        ),
        'version' => '1.1',
        'body' => '',
        'line' => null,
        'header' => array(
            'User-Agent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:22.0) Gecko/20100101 Firefox/22.0'
        ),
        'raw' => null,
        'cookies' => array()
    );
    /**
     * Contain information about the last response (read only)
     *
     * @var array
     */
    public $response = null;
    /**
     * Response classname
     *
     * @var string
     */
    public $responseClass = 'HttpResponse';
    /**
     * Configuration settings for the HttpSocket and the requests
     *
     * @var array
     */
    public $config = array(
        'persistent' => false,
        'host' => 'localhost',
        'protocol' => 'tcp',
        'port' => 80,
        'timeout' => 30,
        'request' => array(
            'uri' => array(
                'scheme' => 'http',
                'host' => 'localhost',
                'port' => 80
            ),
            'cookies' => array()
        )
    );
    /**
     * Authentication settings
     *
     * @var array
     */
    protected $_auth = array();
    /**
     * Proxy settings
     *
     * @var array
     */
    protected $_proxy = array();
    /**
     * Resource to receive the content of request
     *
     * @var mixed
     */
    protected $_contentResource = null;

    /**
     * Build an HTTP Socket using the specified configuration.
     *
     * You can use a url string to set the url and use default configurations for
     * all other options:
     *
     * `$http = new HttpSocket('http://cakephp.org/');`
     *
     * Or use an array to configure multiple options:
     *
     * {{{
     * $http = new HttpSocket(array(
     *    'host' => 'cakephp.org',
     *    'timeout' => 20
     * ));
     * }}}
     *
     * See HttpSocket::$config for options that can be used.
     *
     * @param mixed $config Configuration information, either a string url or an array of options.
     */
    public function __construct($config = array()) {
        if (is_string($config)) {
            $this->_configUri($config);
        } elseif (is_array($config)) {
            if (isset($config['request']['uri']) && is_string($config['request']['uri'])) {
                $this->_configUri($config['request']['uri']);
                unset($config['request']['uri']);
            }
            $this->config = Set::merge($this->config, $config);
        }
    }

    /**
     * Set authentication settings
     *
     * @param string $method Authentication method (ie. Basic, Digest). If empty, disable authentication
     * @param mixed $user Username for authentication. Can be an array with settings to authentication class
     * @param string $pass Password for authentication
     * @return void
     */
    public function configAuth($method, $user = null, $pass = null) {
        if (empty($method)) {
            $this->_auth = array();
            return;
        }
        if (is_array($user)) {
            $this->_auth = array($method => $user);
            return;
        }
        $this->_auth = array($method => compact('user', 'pass'));
    }

    /**
     * Set proxy settings
     *
     * @param mixed $host Proxy host. Can be an array with settings to authentication class
     * @param integer $port Port. Default 3128.
     * @param string $method Proxy method (ie, Basic, Digest). If empty, disable proxy authentication
     * @param string $user Username if your proxy need authentication
     * @param string $pass Password to proxy authentication
     * @return void
     */
    public function configProxy($host, $port = 3128, $method = null, $user = null, $pass = null) {
        if (empty($host)) {
            $this->_proxy = array();
            return;
        }
        if (is_array($host)) {
            $this->_proxy = $host + array('host' => null);
            return;
        }
        $this->_proxy = compact('host', 'port', 'method', 'user', 'pass');
    }

    /**
     * Set the resource to receive the request content. This resource must support fwrite.
     *
     * @param mixed $resource Resource or false to disable the resource use
     * @return void
     * @throw SocketException
     */
    public function setContentResource($resource) {
        if ($resource === false) {
            $this->_contentResource = null;
            return;
        }
        if (!is_resource($resource)) {
            throw new SocketException(__d('cake_dev', 'Invalid resource.'));
        }
        $this->_contentResource = $resource;
    }
    
    private $redirect_times = 0;

    /**
     * Issue the specified request. HttpSocket::get() and HttpSocket::post() wrap this
     * method and provide a more granular interface.
     *
     * @param mixed $request Either an URI string, or an array defining host/uri
     * @return mixed false on error, HttpResponse on success
     */
    public function request($request = array()) {
        $this->reset(false);
        if (is_string($request)) {
            $request = array('uri' => $request);
        } elseif (!is_array($request)) {
            return false;
        }

        if (!isset($request['uri'])) {
            $request['uri'] = null;
        }
        $uri = $this->_parseUri($request['uri']);
        if (!isset($uri['host'])) {
            $host = $this->config['host'];
        }
        if (isset($request['host'])) {
            $host = $request['host'];
            unset($request['host']);
        }
        //$request['uri'] = $this->url($request['uri']);
        $request['uri'] = $this->_parseUri($request['uri'], true);
        $this->request = Set::merge($this->request, array_diff_key($this->config['request'], array('cookies' => true)), $request);
        $this->_configUri($this->request['uri']);

        $Host = $this->request['uri']['host'];
        if (!empty($this->config['request']['cookies'][$Host])) {
            if (!isset($this->request['cookies'])) {
                $this->request['cookies'] = array();
            }
            if (!isset($request['cookies'])) {
                $request['cookies'] = array();
            }
            $this->request['cookies'] = array_merge($this->request['cookies'], $this->config['request']['cookies'][$Host], $request['cookies']);
        }

        if (isset($host)) {
            $this->config['host'] = $host;
        }
//         $this->_setProxy();
//         $this->request['proxy'] = $this->_proxy;

        $cookies = null;

        if (is_array($this->request['header'])) {
            if (!empty($this->request['cookies'])) {
                $cookies = $this->buildCookies($this->request['cookies']);
            }
            $schema = '';
            $port = 0;
            if (isset($this->request['uri']['schema'])) {
                $schema = $this->request['uri']['schema'];
            }
            if (isset($this->request['uri']['port'])) {
                $port = $this->request['uri']['port'];
            }
            if (
                    ($schema === 'http' && $port != 80) ||
                    ($schema === 'https' && $port != 443) ||
                    ($port != 80 && $port != 443)
            ) {
                $Host .= ':' . $port;
            }
            $this->request['header'] = array_merge(compact('Host'), $this->request['header']);
        }

        if (isset($this->request['uri']['user'], $this->request['uri']['pass'])) {
            $this->configAuth('Basic', $this->request['uri']['user'], $this->request['uri']['pass']);
        }
        $this->_setAuth();
        $this->request['auth'] = $this->_auth;

        if (is_array($this->request['body'])) {
            $this->request['body'] = $this->_httpSerialize($this->request['body']);
        }
		

        if (!empty($this->request['body']) && !isset($this->request['header']['Content-Type'])) {
            $this->request['header']['Content-Type'] = 'application/x-www-form-urlencoded';
        }

        if (!empty($this->request['body']) && !isset($this->request['header']['Content-Length'])) {
            $this->request['header']['Content-Length'] = strlen($this->request['body']);
        }

        $connectionType = null;
        if (isset($this->request['header']['Connection'])) {
            $connectionType = $this->request['header']['Connection'];
        }
        //$this->request['header'] = $this->_buildHeader($this->request['header']) . $cookies;

        if (empty($this->request['line'])) {
            $this->request['line'] = $this->_buildRequestLine($this->request);
        }

        if ($this->quirksMode === false && $this->request['line'] === false) {
            return false;
        }
        
        $response = $this->curl_execute($this->url($request['uri']),$this->request);

        list($plugin, $responseClass) = pluginSplit($this->responseClass, true);
        App::uses($this->responseClass, $plugin . 'Network/Http');
        if (!class_exists($responseClass)) {
            throw new SocketException(__d('cake_dev', 'Class %s not found.', $this->responseClass));
        }
        $responseClass = $this->responseClass;
        if(empty($response)){
        	$this->response = new $responseClass(null);
        }
        else{
//         	echo $response;
//         	exit;
        	if(strpos($response,'HTTP/1.1 302 Found')!==false){
        		// 302跳转时含2个头
        		// HTTP/1.1 302 Found
        		// HTTP/1.1 200 OK        		
        		$tmp = new $responseClass($response);
        		$this->response = new $responseClass($tmp->body());
        	}
        	else{
        		$this->response = new $responseClass($response);
        	}
        }
        if (!empty($this->response->cookies)) {
            if (!isset($this->config['request']['cookies'][$Host])) {
                $this->config['request']['cookies'][$Host] = array();
            }
            $this->config['request']['cookies'][$Host] = array_merge($this->config['request']['cookies'][$Host], $this->response->cookies);
        }
        return $this->response;
    }
    
    /**
     * 一次http请求，仅使用一个cookie文件。多次302跳转时的cookie记入一个文件。多个域名的cookie会记入一个文件中。
     * 淘宝折扣信息获取会多次跳转，cookie按域名记入多个文件时，会获取不到，故都记入一个文件中。
     * 如：
     *  # Netscape HTTP Cookie File
     *  # http://www.netscape.com/newsref/std/cookie_spec.html
     *  # This file was generated by libcurl! Edit at your own risk.
     *  
	 *	.taobao.com	TRUE	/	FALSE	0	_tb_token_	1w6TyF5ZhrEP
	 *	.taobao.com	TRUE	/	FALSE	0	cookie2	404c2de7bb1a8387ac8a2d6bba6e0b2f
	 *	.taobao.com	TRUE	/	FALSE	1360379162	t	01cb0736de52967d8fc15f71a02975b9
	 *	.tmall.com	TRUE	/	FALSE	0	t	01cb0736de52967d8fc15f71a02975b9
	 *	.tmall.com	TRUE	/	FALSE	0	cookie2	404c2de7bb1a8387ac8a2d6bba6e0b2f
	 *	.tmall.com	TRUE	/	FALSE	0	_tb_token_	1w6TyF5ZhrEP
	 *	.taobao.com	TRUE	/	FALSE	0	v	0
     * @var string
     */
    private $cookie_file_name = null;
    
    /**
     * 当各二级域名之间需要统一使用cookie信息时，使用setCookieFileName设置
     * RequestFacade::getHttpRequest()->setCookieFileName('kuaipan.cn');
     * @param unknown_type $file
     */
    public function setCookieFileName($file){
    	$this->cookie_file_name = $file;
    }
    
    private function curl_execute($url, $request=array()) {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5); // 302 跳转5次
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); //页面最大执行时间为10s
        
        $urlinfo = parse_url($url);
        if($this->cookie_file_name){
        	$cookie_file_name = $this->cookie_file_name;
        }
        else{
        	$this->cookie_file_name = $cookie_file_name = substr(md5($urlinfo['host']),0,10); // host md5值的前10位做为cookie文件名
        }
        
        if($request['proxy']){
        	curl_setopt($ch, CURLOPT_PROXY, $request['proxy']['host']);
        	curl_setopt($ch, CURLOPT_PROXYPORT, $request['proxy']['port']);
        }
        if($request['uri']['scheme']=='https'){
        	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        }

       $http_header = array();
       foreach($this->request['header'] as $key => $val){
            $http_header[] = $key.': '.$val;
       }       
       curl_setopt($ch, CURLOPT_HTTPHEADER, $http_header);
//        print_r($http_header);
       //         if ($request['header']['Referer']) {
       //             curl_setopt($ch, CURLOPT_REFERER, $request['header']['Referer']);
       //         }
       //         if ($request['header']['User-Agent']) {
       //             curl_setopt($ch, CURLOPT_USERAGENT, $request['header']['User-Agent']);
       //         }
//    	if(!empty($this->_auth)){
//        	foreach($this->_auth as $key => $val){
//        		curl_setopt($ch, CURLOPT_HTTPAUTH,CURLAUTH_ANY);
//        		curl_setopt($ch, CURLOPT_USERPWD, $val['user'].':'.$val['pass']);
//        		break;
//        	}
//        }		

        if (!empty($request['body'])) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request['body']);
        }
        $cookie_file = DATA_PATH . $cookie_file_name.'.cookie'; //TMP or DATA_PATH.
        if(defined('IN_SAE')){
        	//mc,kv等wrapper模拟的文件系统无法成功写入cookie
        	//使用tmpfs临时文件，不过此文件仅在当前一次php请求中有效。
        	// 将kv中的保存到tmpfs
        	if(file_exists($cookie_file)){
        		file_put_contents(SAE_TMP_PATH . $cookie_file_name.'.cookie',file_get_contents($cookie_file));
        	}
        	$cookie_file = SAE_TMP_PATH . $cookie_file_name.'.cookie';
        }
	        	
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
        $content = curl_exec($ch);
        $content = str_replace("HTTP/1.1 100 Continue\r\n", '', $content);
        $http_info = curl_getinfo($ch);
        if (curl_errno($ch)) {
            echo " curl error in HttpCurl::curl_execute.url is $url. msg=".curl_error($ch)."<br/>";
        }
        curl_close($ch);
//         print_r($http_info);
//         echo "\n\n$cookie_file---$cookie_file_name\n\n";
//         echo file_get_contents($cookie_file);
        if(defined('IN_SAE')){
        	// 将tmpfs中的文件，写回到kv
        	file_put_contents(DATA_PATH . $cookie_file_name.'.cookie',file_get_contents($cookie_file));
        }
        if ($http_info['http_code'] == '302') {
            $this->redirect_times++;
            if ($this->redirect_times < 5) { // 最多递归循环5次，避免无限循环
                echo $url . " httpcode:302 . recursive in<br/>";
                return $this->curl_execute($http_info['url'], $request);
            }
            else{
            	echo $url . "http 302 too much.<br/>";
            }
        }
        return $content;//header与内容的分割为两个换行符。
    }
    

    /**
     * Issues a GET request to the specified URI, query, and request.
     *
     * Using a string uri and an array of query string parameters:
     *
     * `$response = $http->get('http://google.com/search', array('q' => 'cakephp', 'client' => 'safari'));`
     *
     * Would do a GET request to `http://google.com/search?q=cakephp&client=safari`
     *
     * You could express the same thing using a uri array and query string parameters:
     *
     * {{{
     * $response = $http->get(
     *     array('host' => 'google.com', 'path' => '/search'),
     *     array('q' => 'cakephp', 'client' => 'safari')
     * );
     * }}}
     *
     * @param mixed $uri URI to request. Either a string uri, or a uri array, see HttpSocket::_parseUri()
     * @param array $query Querystring parameters to append to URI
     * @param array $request An indexed array with indexes such as 'method' or uri
     * @return mixed Result of request, either false on failure or the response to the request.
     */
    public function get($uri = null, $query = array(), $request = array()) {
        if (!empty($query)) {
            $uri = $this->_parseUri($uri, $this->config['request']['uri']);
            if (isset($uri['query'])) {
                $uri['query'] = array_merge($uri['query'], $query);
            } else {
                $uri['query'] = $query;
            }
            $uri = $this->_buildUri($uri);
        }
        $request = Set::merge(array('method' => 'GET', 'uri' => $uri), $request);
        return $this->request($request);
    }

    /**
     * Issues a POST request to the specified URI, query, and request.
     *
     * `post()` can be used to post simple data arrays to a url:
     *
     * {{{
     * $response = $http->post('http://example.com', array(
     *     'username' => 'batman',
     *     'password' => 'bruce_w4yne'
     * ));
     * }}}
     *
     * @param mixed $uri URI to request. See HttpSocket::_parseUri()
     * @param array $data Array of POST data keys and values.
     * @param array $request An indexed array with indexes such as 'method' or uri
     * @return mixed Result of request, either false on failure or the response to the request.
     */
    public function post($uri = null, $data = array(), $request = array()) {
        $request = Set::merge(array('method' => 'POST', 'uri' => $uri, 'body' => $data), $request);
        return $this->request($request);
    }

    /**
     * Issues a PUT request to the specified URI, query, and request.
     *
     * @param mixed $uri URI to request, See HttpSocket::_parseUri()
     * @param array $data Array of PUT data keys and values.
     * @param array $request An indexed array with indexes such as 'method' or uri
     * @return mixed Result of request
     */
    public function put($uri = null, $data = array(), $request = array()) {
        $request = Set::merge(array('method' => 'PUT', 'uri' => $uri, 'body' => $data), $request);
        return $this->request($request);
    }

    /**
     * Issues a DELETE request to the specified URI, query, and request.
     *
     * @param mixed $uri URI to request (see {@link _parseUri()})
     * @param array $data Query to append to URI
     * @param array $request An indexed array with indexes such as 'method' or uri
     * @return mixed Result of request
     */
    public function delete($uri = null, $data = array(), $request = array()) {
        $request = Set::merge(array('method' => 'DELETE', 'uri' => $uri, 'body' => $data), $request);
        return $this->request($request);
    }

    /**
     * Normalizes urls into a $uriTemplate. If no template is provided
     * a default one will be used. Will generate the url using the
     * current config information.
     *
     * ### Usage:
     *
     * After configuring part of the request parameters, you can use url() to generate
     * urls.
     *
     * {{{
     * $http->configUri('http://www.cakephp.org');
     * $url = $http->url('/search?q=bar');
     * }}}
     *
     * Would return `http://www.cakephp.org/search?q=bar`
     *
     * url() can also be used with custom templates:
     *
     * `$url = $http->url('http://www.cakephp/search?q=socket', '/%path?%query');`
     *
     * Would return `/search?q=socket`.
     *
     * @param mixed $url Either a string or array of url options to create a url with.
     * @param string $uriTemplate A template string to use for url formatting.
     * @return mixed Either false on failure or a string containing the composed url.
     */
    public function url($url = null, $uriTemplate = null) {
        if (is_null($url)) {
            $url = '/';
        }
        if (is_string($url)) {
            if ($url{0} == '/') {
                $url = $this->config['request']['uri']['host'] . ':' . $this->config['request']['uri']['port'] . $url;
            }
            if (!preg_match('/^.+:\/\/|\*|^\//', $url)) {
                $url = $this->config['request']['uri']['scheme'] . '://' . $url;
            }
        } elseif (!is_array($url) && !empty($url)) {
            return false;
        }

        $base = array_merge($this->config['request']['uri'], array('scheme' => array('http', 'https'), 'port' => array(80, 443)));
        $url = $this->_parseUri($url, $base);

        if (empty($url)) {
            $url = $this->config['request']['uri'];
        }

        if (!empty($uriTemplate)) {
            return $this->_buildUri($url, $uriTemplate);
        }
        return $this->_buildUri($url);
    }

    /**
     * Set authentication in request
     *
     * @return void
     * @throws SocketException
     */
    protected function _setAuth() {
        if (empty($this->_auth)) {
            return;
        }
        $method = key($this->_auth);
        list($plugin, $authClass) = pluginSplit($method, true);
        $authClass = Inflector::camelize($authClass) . 'Authentication';
        App::uses($authClass, $plugin . 'Network/Http');

        if (!class_exists($authClass)) {
            throw new SocketException(__d('cake_dev', 'Unknown authentication method.'));
        }
        if (!method_exists($authClass, 'authentication')) {
            throw new SocketException(sprintf(__d('cake_dev', 'The %s do not support authentication.'), $authClass));
        }
        call_user_func_array("$authClass::authentication", array($this, &$this->_auth[$method]));
    }

    /**
     * Set the proxy configuration and authentication
     *
     * @return void
     * @throws SocketException
     */
    protected function _setProxy() {
        if (empty($this->_proxy) || !isset($this->_proxy['host'], $this->_proxy['port'])) {
            return;
        }
        $this->config['host'] = $this->_proxy['host'];
        $this->config['port'] = $this->_proxy['port'];

        if (empty($this->_proxy['method']) || !isset($this->_proxy['user'], $this->_proxy['pass'])) {
            return;
        }
        list($plugin, $authClass) = pluginSplit($this->_proxy['method'], true);
        $authClass = Inflector::camelize($authClass) . 'Authentication';
        App::uses($authClass, $plugin . 'Network/Http');

        if (!class_exists($authClass)) {
            throw new SocketException(__d('cake_dev', 'Unknown authentication method for proxy.'));
        }
        if (!method_exists($authClass, 'proxyAuthentication')) {
            throw new SocketException(sprintf(__d('cake_dev', 'The %s do not support proxy authentication.'), $authClass));
        }
        call_user_func_array("$authClass::proxyAuthentication", array($this, &$this->_proxy));
    }

    /**
     * Parses and sets the specified URI into current request configuration.
     *
     * @param mixed $uri URI, See HttpSocket::_parseUri()
     * @return boolean If uri has merged in config
     */
    protected function _configUri($uri = null) {
        if (empty($uri)) {
            return false;
        }

        if (is_array($uri)) {
            $uri = $this->_parseUri($uri);
        } else {
            $uri = $this->_parseUri($uri, true);
        }

        if (!isset($uri['host'])) {
            return false;
        }
        $config = array(
            'request' => array(
                'uri' => array_intersect_key($uri, $this->config['request']['uri'])
            )
        );
        $this->config = Set::merge($this->config, $config);
        $this->config = Set::merge($this->config, array_intersect_key($this->config['request']['uri'], $this->config));
        return true;
    }

    /**
     * Takes a $uri array and turns it into a fully qualified URL string
     *
     * @param mixed $uri Either A $uri array, or a request string. Will use $this->config if left empty.
     * @param string $uriTemplate The Uri template/format to use.
     * @return mixed A fully qualified URL formated according to $uriTemplate, or false on failure
     */
    protected function _buildUri($uri = array(), $uriTemplate = '%scheme://%user:%pass@%host:%port/%path?%query#%fragment') {
        if (is_string($uri)) {
            $uri = array('host' => $uri);
        }
        $uri = $this->_parseUri($uri, true);

        if (!is_array($uri) || empty($uri)) {
            return false;
        }

        $uri['path'] = preg_replace('/^\//', null, $uri['path']);
        $uri['query'] = $this->_httpSerialize($uri['query']);
        $stripIfEmpty = array(
            'query' => '?%query',
            'fragment' => '#%fragment',
            'user' => '%user:%pass@',
            'host' => '%host:%port/'
        );

        foreach ($stripIfEmpty as $key => $strip) {
            if (empty($uri[$key])) {
                $uriTemplate = str_replace($strip, null, $uriTemplate);
            }
        }

        $defaultPorts = array('http' => 80, 'https' => 443);
        if (array_key_exists($uri['scheme'], $defaultPorts) && $defaultPorts[$uri['scheme']] == $uri['port']) {
            $uriTemplate = str_replace(':%port', null, $uriTemplate);
        }
        foreach ($uri as $property => $value) {
            $uriTemplate = str_replace('%' . $property, $value, $uriTemplate);
        }

        if ($uriTemplate === '/*') {
            $uriTemplate = '*';
        }
        return $uriTemplate;
    }

    /**
     * Parses the given URI and breaks it down into pieces as an indexed array with elements
     * such as 'scheme', 'port', 'query'.
     *
     * @param string $uri URI to parse
     * @param mixed $base If true use default URI config, otherwise indexed array to set 'scheme', 'host', 'port', etc.
     * @return array Parsed URI
     */
    protected function _parseUri($uri = null, $base = array()) {
        $uriBase = array(
            'scheme' => array('http', 'https'),
            'host' => null,
            'port' => array(80, 443),
            'user' => null,
            'pass' => null,
            'path' => '/',
            'query' => null,
            'fragment' => null
        );

        if (is_string($uri)) {
            $uri = parse_url($uri);
        }
        if (!is_array($uri) || empty($uri)) {
            return false;
        }
        if ($base === true) {
            $base = $uriBase;
        }

        if (isset($base['port'], $base['scheme']) && is_array($base['port']) && is_array($base['scheme'])) {
            if (isset($uri['scheme']) && !isset($uri['port'])) {
                $base['port'] = $base['port'][array_search($uri['scheme'], $base['scheme'])];
            } elseif (isset($uri['port']) && !isset($uri['scheme'])) {
                $base['scheme'] = $base['scheme'][array_search($uri['port'], $base['port'])];
            }
        }

        if (is_array($base) && !empty($base)) {
            $uri = array_merge($base, $uri);
        }

        if (isset($uri['scheme']) && is_array($uri['scheme'])) {
            $uri['scheme'] = array_shift($uri['scheme']);
        }
        if (isset($uri['port']) && is_array($uri['port'])) {
            $uri['port'] = array_shift($uri['port']);
        }

        if (array_key_exists('query', $uri)) {
            $uri['query'] = $this->_parseQuery($uri['query']);
        }

        if (!array_intersect_key($uriBase, $uri)) {
            return false;
        }
        return $uri;
    }

    /**
     * This function can be thought of as a reverse to PHP5's http_build_query(). It takes a given query string and turns it into an array and
     * supports nesting by using the php bracket syntax. So this menas you can parse queries like:
     *
     * - ?key[subKey]=value
     * - ?key[]=value1&key[]=value2
     *
     * A leading '?' mark in $query is optional and does not effect the outcome of this function.
     * For the complete capabilities of this implementation take a look at HttpSocketTest::testparseQuery()
     *
     * @param mixed $query A query string to parse into an array or an array to return directly "as is"
     * @return array The $query parsed into a possibly multi-level array. If an empty $query is
     *     given, an empty array is returned.
     */
    protected function _parseQuery($query) {
        if (is_array($query)) {
            return $query;
        }
        $parsedQuery = array();

        if (is_string($query) && !empty($query)) {
            $query = preg_replace('/^\?/', '', $query);
            $items = explode('&', $query);

            foreach ($items as $item) {
                if (strpos($item, '=') !== false) {
                    list($key, $value) = explode('=', $item, 2);
                } else {
                    $key = $item;
                    $value = null;
                }

                $key = urldecode($key);
                $value = urldecode($value);

                if (preg_match_all('/\[([^\[\]]*)\]/iUs', $key, $matches)) {
                    $subKeys = $matches[1];
                    $rootKey = substr($key, 0, strpos($key, '['));
                    if (!empty($rootKey)) {
                        array_unshift($subKeys, $rootKey);
                    }
                    $queryNode = & $parsedQuery;

                    foreach ($subKeys as $subKey) {
                        if (!is_array($queryNode)) {
                            $queryNode = array();
                        }

                        if ($subKey === '') {
                            $queryNode[] = array();
                            end($queryNode);
                            $subKey = key($queryNode);
                        }
                        $queryNode = & $queryNode[$subKey];
                    }
                    $queryNode = $value;
                } else {
                    $parsedQuery[$key] = $value;
                }
            }
        }
        return $parsedQuery;
    }

    /**
     * Builds a request line according to HTTP/1.1 specs. Activate quirks mode to work outside specs.
     *
     * @param array $request Needs to contain a 'uri' key. Should also contain a 'method' key, otherwise defaults to GET.
     * @param string $versionToken The version token to use, defaults to HTTP/1.1
     * @return string Request line
     * @throws SocketException
     */
    protected function _buildRequestLine($request = array(), $versionToken = 'HTTP/1.1') {
        $asteriskMethods = array('OPTIONS');

        if (is_string($request)) {
            $isValid = preg_match("/(.+) (.+) (.+)\r\n/U", $request, $match);
            if (!$this->quirksMode && (!$isValid || ($match[2] == '*' && !in_array($match[3], $asteriskMethods)))) {
                throw new SocketException(__d('cake_dev', 'HttpSocket::_buildRequestLine - Passed an invalid request line string. Activate quirks mode to do this.'));
            }
            return $request;
        } elseif (!is_array($request)) {
            return false;
        } elseif (!array_key_exists('uri', $request)) {
            return false;
        }

        $request['uri'] = $this->_parseUri($request['uri']);
        $request = array_merge(array('method' => 'GET'), $request);
        if (!empty($this->_proxy['host'])) {
            $request['uri'] = $this->_buildUri($request['uri'], '%scheme://%host:%port/%path?%query');
        } else {
            $request['uri'] = $this->_buildUri($request['uri'], '/%path?%query');
        }

        if (!$this->quirksMode && $request['uri'] === '*' && !in_array($request['method'], $asteriskMethods)) {
            throw new SocketException(__d('cake_dev', 'HttpSocket::_buildRequestLine - The "*" asterisk character is only allowed for the following methods: %s. Activate quirks mode to work outside of HTTP/1.1 specs.', implode(',', $asteriskMethods)));
        }
        return $request['method'] . ' ' . $request['uri'] . ' ' . $versionToken . "\r\n";
    }

    /**
     * Serializes an array for transport.
     *
     * @param array $data Data to serialize
     * @return string Serialized variable
     */
    protected function _httpSerialize($data = array()) {
        if (is_string($data)) {
            return $data;
        }
        if (empty($data) || !is_array($data)) {
            return false;
        }
        return substr(Router::queryString($data), 1);
    }

    /**
     * Builds the header.
     *
     * @param array $header Header to build
     * @param string $mode
     * @return string Header built from array
     */
    protected function _buildHeader($header, $mode = 'standard') {
        if (is_string($header)) {
            return $header;
        } elseif (!is_array($header)) {
            return false;
        }

        $fieldsInHeader = array();
        foreach ($header as $key => $value) {
            $lowKey = strtolower($key);
            if (array_key_exists($lowKey, $fieldsInHeader)) {
                $header[$fieldsInHeader[$lowKey]] = $value;
                unset($header[$key]);
            } else {
                $fieldsInHeader[$lowKey] = $key;
            }
        }

        $returnHeader = '';
        foreach ($header as $field => $contents) {
            if (is_array($contents) && $mode == 'standard') {
                $contents = implode(',', $contents);
            }
            foreach ((array) $contents as $content) {
                $contents = preg_replace("/\r\n(?![\t ])/", "\r\n ", $content);
                $field = $this->_escapeToken($field);

                $returnHeader .= $field . ': ' . $contents . "\r\n";
            }
        }
        return $returnHeader;
    }

    /**
     * Builds cookie headers for a request.
     *
     * @param array $cookies Array of cookies to send with the request.
     * @return string Cookie header string to be sent with the request.
     * @todo Refactor token escape mechanism to be configurable
     */
    public function buildCookies($cookies) {
        $header = array();
        foreach ($cookies as $name => $cookie) {
            $header[] = $name . '=' . $this->_escapeToken($cookie['value'], array(';'));
        }
        return $this->_buildHeader(array('Cookie' => implode('; ', $header)), 'pragmatic');
    }

    /**
     * Escapes a given $token according to RFC 2616 (HTTP 1.1 specs)
     *
     * @param string $token Token to escape
     * @param array $chars
     * @return string Escaped token
     * @todo Test $chars parameter
     */
    protected function _escapeToken($token, $chars = null) {
        $regex = '/([' . implode('', $this->_tokenEscapeChars(true, $chars)) . '])/';
        $token = preg_replace($regex, '"\\1"', $token);
        return $token;
    }

    /**
     * Gets escape chars according to RFC 2616 (HTTP 1.1 specs).
     *
     * @param boolean $hex true to get them as HEX values, false otherwise
     * @param array $chars
     * @return array Escape chars
     * @todo Test $chars parameter
     */
    protected function _tokenEscapeChars($hex = true, $chars = null) {
        if (!empty($chars)) {
            $escape = $chars;
        } else {
            $escape = array('"', "(", ")", "<", ">", "@", ",", ";", ":", "\\", "/", "[", "]", "?", "=", "{", "}", " ");
            for ($i = 0; $i <= 31; $i++) {
                $escape[] = chr($i);
            }
            $escape[] = chr(127);
        }

        if ($hex == false) {
            return $escape;
        }
        foreach ($escape as $key => $char) {
            $escape[$key] = '\\x' . str_pad(dechex(ord($char)), 2, '0', STR_PAD_LEFT);
        }
        return $escape;
    }

    /**
     * Resets the state of this HttpSocket instance to it's initial state (before Object::__construct got executed) or does
     * the same thing partially for the request and the response property only.
     *
     * @param boolean $full If set to false only HttpSocket::response and HttpSocket::request are reseted
     * @return boolean True on success
     */
    public function reset($full = true) {
        static $initalState = array();
        if (empty($initalState)) {
            $initalState = get_class_vars(__CLASS__);
        }
        if (!$full) {
            $this->request = $initalState['request'];
            $this->response = $initalState['response'];
            return true;
        }
        parent::reset($initalState);
        return true;
    }

}
