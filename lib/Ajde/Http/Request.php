<?php

class Ajde_Http_Request extends Ajde_Object_Standard
{
    const TYPE_STRING = 1;
    const TYPE_HTML = 2;
    const TYPE_INTEGER = 3;
    const TYPE_FLOAT = 4;
    const TYPE_RAW = 5;

    const FORM_MIN_TIME = 0;    // minimum time to have a post form returned (seconds)
    const FORM_MAX_TIME = 3600;    // timeout of post forms (seconds)

    /**
     * @var Ajde_Core_Route
     */
    protected $_route = null;
    protected $_postData = [];

    /**
     * @throws Ajde_Core_Exception_Security
     *
     * @return Ajde_Http_Request
     */
    public static function fromGlobal()
    {
        $instance = new self();
        $post = self::globalPost();
        if (!empty($post) && self::requirePostToken() && !self::_isWhitelisted()) {

            // Measures against CSRF attacks
            $session = new Ajde_Session('AC.Form');
            if (!isset($post['_token']) || !$session->has('formTime')) {
                $exception = new Ajde_Core_Exception_Security('No form token received or no form time set, bailing out to prevent CSRF attack');
                if (config('app.debug') === true) {
                    Ajde_Http_Response::setResponseType(Ajde_Http_Response::RESPONSE_TYPE_FORBIDDEN);
                    throw $exception;
                } else {
                    // Prevent inf. loops
                    unset($_POST);
                    unset($_REQUEST);
                    // Rewrite
                    Ajde_Exception_Log::logException($exception);
                    Ajde_Http_Response::dieOnCode(Ajde_Http_Response::RESPONSE_TYPE_FORBIDDEN);
                }
            }

            $formToken = $post['_token'];
            if (!self::verifyFormToken($formToken) || !self::verifyFormTime()) {
                // TODO:
                if (!self::verifyFormToken($formToken)) {
                    $exception = new Ajde_Core_Exception_Security('No matching form token (got '.self::_getHashFromSession($formToken).', expected '.self::_tokenHash($formToken).'), bailing out to prevent CSRF attack');
                } else {
                    $exception = new Ajde_Core_Exception_Security('Form token timed out, bailing out to prevent CSRF attack');
                }
                if (config('app.debug') === true) {
                    Ajde_Http_Response::setResponseType(Ajde_Http_Response::RESPONSE_TYPE_FORBIDDEN);
                    throw $exception;
                } else {
                    // Prevent inf. loops
                    unset($_POST);
                    unset($_REQUEST);
                    // Rewrite
                    Ajde_Exception_Log::logException($exception);
                    Ajde_Http_Response::dieOnCode(Ajde_Http_Response::RESPONSE_TYPE_FORBIDDEN);
                }
            }
        }

        // Security measure, protect $_POST
        $global = self::globalGet();
        foreach ($global as $key => $value) {
            $instance->set($key, $value);
        }

        $instance->_postData = self::globalPost();
        if (!empty($instance->_postData)) {
            Ajde_Cache::getInstance()->disable();
        }

        return $instance;
    }

    public static function getRefferer()
    {
        return @$_SERVER['HTTP_REFERER'];
    }

    public static function globalGet()
    {
        return isset($_GET) ? $_GET : (isset($_REQUEST) ? $_REQUEST : []);
    }

    public static function globalPost()
    {
        return isset($_POST) ? $_POST : (isset($_REQUEST) ? $_REQUEST : []);
    }

    // From http://stackoverflow.com/a/10372836/938297
    public static function getRealIp()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } else {
            if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                return $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else {
                return $_SERVER['REMOTE_ADDR'];
            }
        }
    }

    /**
     * Security.
     */
    private static function autoEscapeString()
    {
        return config('security.autoEscapeString') == true;
    }

    private static function autoCleanHtml()
    {
        return config('security.autoCleanHtml') == true;
    }

    private static function requirePostToken()
    {
        return config('security.csrf.requirePostToken') == true;
    }

    /**
     * CSRF prevention token.
     */
    public static function getFormToken()
    {
        static $token;
        if (!isset($token)) {
            Ajde_Cache::getInstance()->disable();
            $token = md5(uniqid(rand(), true));
            $session = new Ajde_Session('AC.Form');
            $tokenDictionary = self::_getTokenDictionary($session);
            $tokenDictionary[$token] = self::_tokenHash($token);
            $session->set('formTokens', $tokenDictionary);
        }
        self::markFormTime();

        return $token;
    }

    public static function verifyFormToken($requestToken)
    {
        return self::_tokenHash($requestToken) === self::_getHashFromSession($requestToken);
    }

    private static function _tokenHash($token)
    {
        return md5($token.$_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT'].config('security.secret'));
    }

    private static function _isWhitelisted()
    {
        $route = issetor($_GET['_route'], false);
        foreach (config('security.csrf.postWhitelistRoutes') as $whitelist) {
            if (stripos($route, $whitelist) === 0) {
                return true;
            }
        }

        return false;
    }

    private static function _getTokenDictionary(&$session = null)
    {
        if (!isset($session)) {
            $session = new Ajde_Session('AC.Form');
        }
        $tokenDictionary = ($session->has('formTokens') ? $session->get('formTokens') : []);
        if (!is_array($tokenDictionary)) {
            $tokenDictionary = [];
        }

        return $tokenDictionary;
    }

    private static function _getHashFromSession($token)
    {
        $tokenDictionary = self::_getTokenDictionary();

        return issetor($tokenDictionary[$token], '');
    }

    public static function markFormTime()
    {
        $time = time();
        $session = new Ajde_Session('AC.Form');
        $session->set('formTime', $time);

        return $time;
    }

    public static function verifyFormTime()
    {
        $session = new Ajde_Session('AC.Form');
        $sessionTime = $session->get('formTime');
        if ((time() - $sessionTime) < self::FORM_MIN_TIME ||
            (time() - $sessionTime) > self::FORM_MAX_TIME
        ) {
            return false;
        } else {
            return true;
        }
    }

    public static function isAjax()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }

    /**
     * @return string Lowercase request method
     */
    public static function method()
    {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }

    /**
     * Helpers.
     */
    public function get($key)
    {
        return $this->getParam($key);
    }

    public function getParam($key, $default = null, $type = self::TYPE_STRING, $post = false)
    {
        $data = $this->_data;
        if ($post === true) {
            $data = $this->getPostData();
        }
        if (isset($data[$key])) {
            switch ($type) {
                case self::TYPE_HTML:
                    if ($this->autoCleanHtml() === true) {
                        return Ajde_Component_String::clean($data[$key]);
                    } else {
                        return $data[$key];
                    }
                    break;
                case self::TYPE_INTEGER:
                    return (int) $data[$key];
                    break;
                case self::TYPE_FLOAT:
                    return (float) $data[$key];
                    break;
                case self::TYPE_RAW:
                    return $data[$key];
                    break;
                case self::TYPE_STRING:
                default:
                    if ($this->autoEscapeString() === true) {
                        if (is_array($data[$key])) {
                            array_walk($data[$key], ['Ajde_Component_String', 'escape']);

                            return $data[$key];
                        } else {
                            return Ajde_Component_String::escape($data[$key]);
                        }
                    } else {
                        return $data[$key];
                    }
            }
        } else {
            if (isset($default)) {
                return $default;
            } else {
                // TODO:
                throw new Ajde_Exception("Parameter '$key' not present in request and no default value given");
            }
        }
    }

    public function getStr($key, $default)
    {
        return $this->getString($key, $default);
    }

    public function getInt($key, $default)
    {
        return $this->getInteger($key, $default);
    }

    public function getString($key, $default = null)
    {
        return $this->getParam($key, $default, self::TYPE_STRING);
    }

    public function getHtml($key, $default = null)
    {
        return $this->getParam($key, $default, self::TYPE_HTML);
    }

    public function getInteger($key, $default = null)
    {
        return $this->getParam($key, $default, self::TYPE_INTEGER);
    }

    public function getFloat($key, $default = null)
    {
        return $this->getParam($key, $default, self::TYPE_FLOAT);
    }

    public function getRaw($key, $default = null)
    {
        return $this->getParam($key, $default, self::TYPE_RAW);
    }

    /**
     * FORM.
     */
    public function getCheckbox($key, $post = true)
    {
        if ($this->getParam($key, false, self::TYPE_RAW, $post) === 'on') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * POST.
     */
    public function getPostData()
    {
        return $this->_postData;
    }

    public function getPostParam($key, $default = null, $type = self::TYPE_STRING)
    {
        return $this->getParam($key, $default, $type, true);
    }

    public function getPostRaw($key, $default = null)
    {
        return $this->getParam($key, $default, self::TYPE_RAW, true);
    }

    public function hasPostParam($key)
    {
        return array_key_exists($key, $this->_postData);
    }

    /**
     * @return Ajde_Core_Route
     */
    public function getRoute()
    {
        if (!isset($this->_route)) {
            $route = $this->extractRoute();
            $this->_route = new Ajde_Core_Route($route);
            foreach ($this->_route->values() as $part => $value) {
                if (!$this->hasNotEmpty($part)) {
                    $this->set($part, $value);
                }
            }
        }

        return $this->_route;
    }

    private function extractRoute()
    {
        // Strip query string
        $URIComponents = explode('?', $_SERVER['REQUEST_URI']);
        $requestURI = reset($URIComponents);

        // Route is the part after our base path
        $baseURI = str_replace('index.php', '', $_SERVER['PHP_SELF']);

        // Strip public from request and base
        $requestURI = str_replace(PUBLIC_DIR, '', $requestURI);
        $baseURI = str_replace(PUBLIC_DIR, '', $baseURI);

        // TODO: potential bug when baseuri is something like /node (now all requests with /node/node will return '')
        return $baseURI !== '/' ? str_replace($baseURI, '', $requestURI) : trim($requestURI, '/');
    }

    public function initRoute()
    {
        $route = $this->getRoute();

        return $route;
    }

    public static function getClientIP()
    {
        if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            if (array_key_exists('REMOTE_ADDR', $_SERVER)) {
                return $_SERVER['REMOTE_ADDR'];
            } else {
                if (array_key_exists('HTTP_CLIENT_IP', $_SERVER)) {
                    return $_SERVER['HTTP_CLIENT_IP'];
                }
            }
        }

        return '';
    }
}
