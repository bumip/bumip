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
    public $index;
    public $referer;
    public $referrer;
    public $querystring;
    public $mode;
    /* -----------------------------------------------------------------
      |		Constructor : url()
      ---------------------------------------------------------------- */

    public function __construct()
    {
        $this->self = $_SERVER['REQUEST_URI'];
        $this->full_self = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        if (isset($_SERVER['PATH_INFO'])) {
            $this->mode = "pathinfo";
            $this->pathinfo = $_SERVER['PATH_INFO'];
        } else {
            $this->mode = "requesturi";
            $this->pathinfo = str_replace('?' . $_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']);
        }
        $scriptname = trim(str_replace('index.php', "", $_SERVER['SCRIPT_NAME']));
        $this->pathinfo = trim(str_replace($scriptname, "/", $this->pathinfo));

        if (isset($_SERVER['HTTP_REFERER'])) {
            $this->referer = $_SERVER['HTTP_REFERER'];
            $this->referrer = $this->referer;
        }
        $this->makeIndexes();

        global $avail_lang;
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
            /*if (isset($avail_lang[$this->index(1)])) {
                if (!defined("CURRENT_LANGUAGE")) {
                    $lang_prefix = array_shift($this->index);
                    DEFINE('CURRENT_LANGUAGE', $avail_lang[$lang_prefix]);
                    DEFINE('CURRENT_LANGUAGE_PREFIX', $lang_prefix);
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
            } else {
                if ($ref = explode("/", str_replace(ROOT_EXT, '', $this->referrer)) AND isset($avail_lang[$ref[0]])) {
                    if (!defined("CURRENT_LANGUAGE")) {
                        $lang_prefix = $ref[0];
                        DEFINE('CURRENT_LANGUAGE', $avail_lang[$lang_prefix]);
                        DEFINE('CURRENT_LANGUAGE_PREFIX', $lang_prefix);
                    } else {

                    }
                } else {
                    if (!defined('CURRENT_LANGUAGE'))
                        define('CURRENT_LANGUAGE', DEFAULT_LANGUAGE);
                    if (!defined('CURRENT_LANGUAGE_PREFIX'))
                        define('CURRENT_LANGUAGE_PREFIX', array_search(DEFAULT_LANGUAGE, $avail_lang));
                }
            }
            if (!defined("DEFINED_LOCALE")) {
                set_locale();
            }*/
        }
    }

    /*	 * ********************************************************************** */

    /**
     *
     * @param string $url URL to set the header location...
     */
    public function redirect($url = "")
    {
        header("Location: " . $url, true, 301);
        exit();
    }

    // redirect

    /*	 * ********************************************************************** */

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
            global $avail_lang;
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

    /* -----------------------------------------------------------------
      |		Method : index
      ---------------------------------------------------------------- */

    public function index($id, $default = false)
    {
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

    /* -----------------------------------------------------------------
      |		End of class
      ---------------------------------------------------------------- */
}
