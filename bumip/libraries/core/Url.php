<?php
namespace Bumip\Core;

/**
 * Class Url
 *
 */
class Url
{
    /**
     * Properties
     *
     * @var [type]
     */
    public $self;
    public $fullSelf;
    public $index;
    public $indexOffset = 1;
    private $config;
    public $referer;
    public $referrer;
    public $querystring;
    public $mode;


    public function __construct($config = null)
    {
        $this->self = $_SERVER['REQUEST_URI'];
        $protocol = !empty($_SERVER['HTTPS'])? 'https' : 'http';
        $this->fullSelf = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        if (isset($_SERVER['PATH_INFO'])) {
            $this->mode = "pathinfo";
            $this->pathinfo = $_SERVER['PATH_INFO'];
        } else {
            $this->mode = "requesturi";
            $this->pathinfo = str_replace('?' . ($_SERVER['QUERY_STRING'] ?? ''), '', $_SERVER['REQUEST_URI']);
        }
        $scriptname = trim(str_replace('index.php', "", $_SERVER['SCRIPT_NAME']));
        $this->pathinfo = trim(str_replace($scriptname, "/", $this->pathinfo));

        if (isset($_SERVER['HTTP_REFERER'])) {
            $this->referer = $_SERVER['HTTP_REFERER'];
            $this->referrer = $this->referer;
        }
        $this->makeIndexes();

        $avail_lang = $config->get("availableLanguages");
        $this->config = $config;
        /**
         * @1 URL SWITCHING
         * @2 COOKIE
         * @3 BROWSER LANGUAGE
         * @4 DEFAUL LANGUAGE
         */
        if (MULTILANG) {
            if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
                $browser_lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
            } else {
                $browser_lang = 'BUMIP_NO_BROWSER_LANG';
            }
            if (isset($avail_lang[$this->index(1)])) {
                if (!defined("CURRENT_LANGUAGE")) {
                    $lang_prefix = array_shift($this->index);
                } else {
                    array_shift($this->index);
                }
                $this->indexRebuild();
                if (!count($this->index)) {
                    if (!OMIT_MAIN) {
                        $this->index = explode("/", DEFAULT_PATH_INFO);
                    } else {
                        $this->index = array(1 => "index");
                    }
                }
            } elseif (isset($_COOKIE['CURRENT_LANGUAGE_PREFIX'])) {
                $lang_prefix = $_COOKIE['CURRENT_LANGUAGE_PREFIX'];
            } elseif (isset($avail_lang[$browser_lang])) {
                $lang_prefix = $browser_lang;
            } else {
                $lang_prefix = array_search(DEFAULT_LANGUAGE, $avail_lang);
            }
            if (!defined('CURRENT_LANGUAGE_PREFIX')) {
                DEFINE('CURRENT_LANGUAGE', $avail_lang[$lang_prefix]);
                DEFINE('CURRENT_LANGUAGE_PREFIX', $lang_prefix);
                setcookie("CURRENT_LANGUAGE_PREFIX", $lang_prefix, time() + (86400 * 30), '/');
            }
            if (!defined("DEFINED_LOCALE")) {
                set_locale();
            }
        }
    }

    /*	 * ********************************************************************** */

    /**
     *
     * @param string $url URL to set the header location...
     */
    public function redirect($url = "", $http_response_header = 301)
    {
        header("Location: " . $url, true, $http_response_header);
        exit();
    }
    /**
     * toArgs:
     * Converts segments to an array of arguments: you can use an array or a string
     * @example project/hello/world you input [greetings => 1, subject => 2] or 1:greetings/2:subject and you get
     * [greetings => hello, subject => world]
     *
     * @param mixed $params
     * @param integer $offset
     * @return array
     */
    public function toArgs($params = null, $offset = 2)
    {
        $args = [];
        if ($params) {
            if (is_string($params)) {
                $params = explode('/', trim($params, '/'));
                foreach ($params as $k => $v) {
                    $pos = strpos($v, ':');
                    if ($pos !== -1) {
                        $key = explode(":", $v);
                        if ($pos == 0) {
                            $newParams[$key[1]] = $k + $offset;
                        } else {
                            $newParams[$key[1]] = $key[0];
                        }
                    }
                }
                if (!empty($newParams)) {
                    $params = $newParams;
                }
            }
            if (count($params)) {
                foreach ($params as $k => $v) {
                    $args[$k] = $this->index($v);
                }
            }
        }
        return $args;
    }
    /**
     * @return string with the language starting segment ex. /hello => /it/hello
     */

    public function localeUrl($url = false, $language = false)
    {
        if (!$url) {
            $url = $this->index;
        } elseif (is_array($url)) {
        } elseif (is_object($url)) {
            $url = $this->index;
        } else {
            $url = explode("/", $url);
        }
        if (defined("DEFAULT_LANGUAGE")) {
            $lang = DEFAULT_LANGUAGE;
        }
        if (defined("CURRENT_LANGUAGE")) {
            $lang = CURRENT_LANGUAGE;
        }
        if ($language) {
            $lang = $language;
        } else {
            $avail_lang = $this->config->get("availableLanguages");
            $lang = array_search($lang, $avail_lang);
        }
        $url[-1] = $lang;
        ksort($url);
        return implode("/", $url);
    }

    /* -----------------------------------------------------------------
      |		Method : makeIndexes
      ---------------------------------------------------------------- */

    public function makeIndexes()
    {
        $this->index = array();
        $indexes = explode("/", $this->pathinfo);
        foreach ($indexes as $k => $v) {
            if ($v != "") {
                $this->index[$k] = $v;
            }
        }
        /**
         * If there is nothing after the project_name or the domain_name the index will still be built.
         * If you have @final OMIT_MAIN set true you will have index that you will use as action in the controller. If it's false
         * the default_path_info will be exploded resulting in ["main", "index"]
         */
        if (!count($this->index)) {
            if (!OMIT_MAIN) {
                $this->index = explode("/", DEFAULT_PATH_INFO);
            } else {
                $this->index = array(1 => "index");
            }
        }
    }
    public function setOffset($offset)
    {
        $this->indexOffset = $offset;
    }
    /* -----------------------------------------------------------------
      |		Method : index
      ---------------------------------------------------------------- */

    public function index($id, $default = false)
    {
        $id += ($this->indexOffset - 1);
        if (isset($this->index[$id])) {
            return $this->index[$id];
        } else {
            //return false;
            return $default;
        }
    }

    /* -----------------------------------------------------------------
      |		Method : search
      ---------------------------------------------------------------- */

    public function search($str)
    {
        return array_search($str, $this->index);
    }

    /* -----------------------------------------------------------------
      |		Method : delete
      ---------------------------------------------------------------- */

    public function delete($str)
    {
        unset($this->index[array_search($str, $this->index)]);
        $this->indexRebuild();
    }

    /* -----------------------------------------------------------------
      |		Method : indexRebuild
      ---------------------------------------------------------------- */

    public function indexRebuild()
    {
        $ar = $this->index;
        $this->index = array();
        $k = 1;
        foreach ($ar as $v) {
            $this->index[$k] = $v;
            $k++;
        }
    }

    /* -----------------------------------------------------------------
      |		Method : querystring
      ---------------------------------------------------------------- */

    public function querystring($qm = true)
    {
        if ($qm) {
            $qm = "?";
        }
        if (isset($_SERVER["QUERY_STRING"]) && trim($_SERVER["QUERY_STRING"]) != "") {
            return $qm . $_SERVER["QUERY_STRING"];
        } else {
            return false;
        }
    }

    /* -----------------------------------------------------------------
      |		Method : rebuild
      ---------------------------------------------------------------- */

    public function rebuild($index = false, $qs = true)
    {
        if (!$index) {
            $index = $this->index;
        }
        if ($qs) {
            $qs = $this->querystring();
        }
        return $this->pinfoRebuilded = implode("/", $index) . $qs;
    }

    /* -----------------------------------------------------------------
      |		Method : pairSet
      ---------------------------------------------------------------- */

    public function pairSet($k, $v)
    {
        if ($i = $this->search($k)) {
            $this->index[$i + 1] = $v;
        } else {
            $c = count($this->index);
            $this->index[$c + 1] = $k;
            $this->index[$c + 2] = $v;
        }
    }

    /* -----------------------------------------------------------------
      |		Method : pairGetValue
      ---------------------------------------------------------------- */

    public function pairGetValue($k)
    {
        if ($i = $this->search($k)) {
            return $this->index($i + 1);
        } else {
            return false;
        }
    }

    public function last_index()
    {
        if (empty($this->index) or !$this->index) {
            return false;
        } else {
            $ix = $this->index;
            return array_pop($ix);
        }
    }
}
