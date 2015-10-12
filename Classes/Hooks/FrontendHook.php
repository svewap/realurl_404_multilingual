<?php
namespace WapplerSystems\Realurl404Multilingual\Hooks;

/***************************************************************
 * Copyright notice
 *
 * (c) 2004-2015 Sven Wappler <typo3YYYY@wapplersystems.de>
 * Based on extension error_404_multilingual
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3
 * project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by
 * the Free Software Foundation; either version 2 of the
 * License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be
 * useful,
 * but WITHOUT ANY WARRANTY; without even the implied
 * warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See
 * the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the
 * script!
 ***************************************************************/

use \TYPO3\CMS\Core\Utility\HttpUtility;

/**
 *
 */
class FrontendHook {
    
    /**
     * use redirect
     */
    const MODE_REDIRECT = '1';

    /**
     * use curl
     */
    const MODE_CURL = '2';

    /**
     * host
     * @var string $host current host
     */
    protected $host = '_DEFAULT';

    /**
     * typo3 conf var 404
     * @var array $this->typo3_conf_var_404 EXTCONF
     */
    protected $typo3_conf_var_404 = NULL;

    /**
     * initialize host and this->typo3_conf_var_404
     */
    public function __construct() {
        $this->host = \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('HTTP_HOST');
        // define config for error_404_multilingual
        $this->typo3_conf_var_404 = $this->getConfiguration($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl_404_multilingual']);
        if (!is_array($this->typo3_conf_var_404)) {
            // set the default
            $this->typo3_conf_var_404 = array('errorPage' => '404','redirects' => array(),'stringConversion' => 'none',);
        }
    }

    /*
     *
     *
     *
     */
    public function pageErrorHandler(&$params,&$obj) {
        // get extconf
        $extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['realurl_404_multilingual']);

        $currentUrl = $params['currentUrl'];

        $url404 = $this->parseCurrentUrl($currentUrl);

        switch ($extConf['mode']) {
            case self::MODE_CURL:
                $this->get404PageAndDispaly($url404);
                break;
            case self::MODE_REDIRECT:
            default:
                HttpUtility::redirect($url404,HttpUtility::HTTP_STATUS_301);
                break;
        }
    }

    /**
     * Returns the URI without leading slashes
     *
     * @param string $url
     * @return string
     */
    function getUri($url = "") {
        if (preg_match("/^\/(.*)/i",$url,$reg)) {
            return $reg[1];
        }
        return $url;
    }

    /**
     * Returns the related configuration
     *
     * @param $array array
     * @return string
     */
    function getConfiguration($array = array()) {
        if (is_array($array) && array_key_exists($this->host,$array)) {
            $domain_key = $key;
        } else {
            $domain_key = '_DEFAULT';
        }
        if (is_array($array[$domain_key])) {
            return $array[$domain_key];
        } else {
            return $array[$array[$domain_key]];
        }
    }

    /**
     * get page and echo it
     * @param  string $url404 404 page url
     * @return void
     */
    private function get404PageAndDispaly($url404) {
        
        // header 404
        $error_header = $GLOBALS['TYPO3_CONF_VARS']['FE']['pageNotFound_handling_statheader'];
        $error_header = ($error_header ? $error_header : "HTTP/1.0 404 Not Found");
        header($error_header);

        // check cache
        $cache = $GLOBALS['typo3CacheManager']->getCache('realurl_404_multilingual');
        $cacheKey = hash('sha1',$url404 . ($GLOBALS['TSFE']->fe_user->user ? $GLOBALS['TSFE']->fe_user->user['uid'] : ''));
        
        if ($cache->has($cacheKey))
        {
            $urlcontent = $cache->get($cacheKey);
        }
        elseif ($GLOBALS['TYPO3_CONF_VARS']['SYS']['curlUse']) {
            // Open url by curl
            $ch = curl_init();
            curl_setopt($ch,CURLOPT_URL,$url404);
            curl_setopt($ch,CURLOPT_HEADER,false);
            curl_setopt($ch,CURLOPT_POST,true);
            curl_setopt($ch,CURLOPT_POSTFIELDS,'tx_realurl404multilingual=1' . $this->addFESeesionKeyStringIfLoggedIn());
            
            //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
            curl_setopt($ch,CURLOPT_TIMEOUT,5);

            //set user agent if needed
            if (\TYPO3\CMS\Core\Utility\GeneralUtility::inList($GLOBALS['TYPO3_CONF_VARS']['FE']['lockHashKeyWords'], 'useragent')
                && $GLOBALS['TSFE']->fe_user->user
                ) {
                curl_setopt($ch, CURLOPT_USERAGENT, \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('HTTP_USER_AGENT'));
            }
            
            if ($GLOBALS['TYPO3_CONF_VARS']['SYS']['curlProxyTunnel']) {
                curl_setopt($ch,CURLOPT_HTTPPROXYTUNNEL,$GLOBALS['TYPO3_CONF_VARS']['SYS']['curlProxyTunnel']);
            }
            if ($GLOBALS['TYPO3_CONF_VARS']['SYS']['curlProxyServer']) {
                curl_setopt($ch,CURLOPT_PROXY,$GLOBALS['TYPO3_CONF_VARS']['SYS']['curlProxyServer']);
            }
            if ($GLOBALS['TYPO3_CONF_VARS']['SYS']['curlProxyUserPass']) {
                curl_setopt($ch,CURLOPT_PROXYUSERPWD,$GLOBALS['TYPO3_CONF_VARS']['SYS']['curlProxyUserPass']);
            }
            $urlcontent = curl_exec($ch);
            curl_close($ch);

            // save to cache
            $cache->set($cacheKey,$urlcontent,array());
        } else {
            // Open url by fopen
            set_time_limit(5);
            // FE user session could not work, because HTTP_USER_AGENT check
            $urlcontent = file_get_contents($url404.'?tx_realurl404multilingual=1' . $this->addFESeesionKeyStringIfLoggedIn());

            // save to cache
            $cache->set($cacheKey,$urlcontent,array());
        }
        
        switch ($this->typo3_conf_var_404['stringConversion']) {
            case 'utf8_encode' : {
                echo utf8_encode($urlcontent);
                break;
            }
            case 'utf8_decode' : {
                echo utf8_decode($urlcontent);
                break;
            }
            default : {
                echo $urlcontent;
                break;
            }
        }
    }

    private function parseCurrentUrl($currentUrl) {

        $uri = $this->getUri($currentUrl);
        list($script,$option) = explode("?",$uri);

        // define config for realurl
        $typo3_conf_var_realurl = $this->getConfiguration($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl']);

        // removes all leading slashes in array
        if (count($this->typo3_conf_var_404['redirects']) > 0) {
            $redirects = array();
            foreach ($this->typo3_conf_var_404['redirects'] as $key => $val) {
                $redirects[$this->getUri($key)] = $this->getUri($val);
            }
            $this->typo3_conf_var_404['redirects'] = $redirects;
        }

        // fallback if this->typo3_conf_var_404 not an array
        if (!is_array($this->typo3_conf_var_404['redirects'])) {
            $this->typo3_conf_var_404['redirects'] = array();
        }

        // First element will be the host
        $url_array = array();
        if (is_array($this->typo3_conf_var_404['redirects']) && array_key_exists($uri,$this->typo3_conf_var_404['redirects'])) {
            // There is a redirect defined for this request URI, so the
            // value is taken
            $url_array[] = $this->typo3_conf_var_404['redirects'][$uri];
        } elseif (is_array($this->typo3_conf_var_404['redirects']) && array_key_exists($script,$this->typo3_conf_var_404['redirects'])) {
            // There is a redirect defined for this script, so the value
            // is taken
            $url_array[] = $this->typo3_conf_var_404['redirects'][$script];
        } else {
            // Normaly no alternative is defined, so the 404 site will be
            // taken
            // extract the language
            $reg = array();
            preg_match("/^\/([a-zA-Z]*)\/(.*)/",\TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('REQUEST_URI'),$reg);
            $lang = $reg[1];
            // define the page name
            $errorpage = $this->typo3_conf_var_404['errorPage'];
            if (!$errorpage) {
                $errorpage = $typo3_conf_var_realurl['404page'];
            }
            $errorpage = ($errorpage == '' ? '404' : $errorpage);

            // find language key
            foreach ($typo3_conf_var_realurl['preVars'] as $key => $val) {
                if (isset($typo3_conf_var_realurl['preVars'][$key]['GETvar']) && $typo3_conf_var_realurl['preVars'][$key]['GETvar'] == "L") {

                    if (is_array($typo3_conf_var_realurl['preVars'][$key]['valueMap']) && array_key_exists($lang,$typo3_conf_var_realurl['preVars'][$key]['valueMap'])) {
                        $url_array[] = $lang;
                    } elseif ($typo3_conf_var_realurl['preVars'][$key]['valueDefault']) {
                        $url_array[] = $typo3_conf_var_realurl['preVars'][$key]['valueDefault'];
                    }
                }
            }

            $url_array[] = $errorpage;
        }

        return \TYPO3\CMS\Core\Utility\GeneralUtility::locationHeaderUrl(implode("/",$url_array));


    }

    /**
     * add session key if user is logged in
     *
     * @return string session key
     */
    private function addFESeesionKeyStringIfLoggedIn() {
        if($GLOBALS['TSFE']->fe_user->user) {
            return '&FE_SESSION_KEY=' . 
                        rawurlencode(
                            $GLOBALS['TSFE']->fe_user->id . 
                            '-' . 
                            md5(
                                $GLOBALS['TSFE']->fe_user->id . 
                                '/' . 
                                $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey']
                            )
                        );
        }

        return '';
    }

}

?>