<?php
namespace WapplerSystems\Realurl404Multilingual\Hooks;

/***************************************************************
 * Copyright notice
 *
 * (c) 2004-2015 Sven Wappler <typo3YYYY@wapplersystems.de>
 * Based on extension error_404_multilingual
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the
 * script!
 ***************************************************************/

use TYPO3\CMS\Core\Http\RequestFactory;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Core\Utility\HttpUtility;

/**
 *
 */
class FrontendHook
{

    /**
     * show error page directly
     */
    const MODE_NOREDIRECT = '1';

    /**
     * use redirect
     */
    const MODE_REDIRECT = '2';


    /**
     * host
     * @var string current host
     */
    protected $host = '_DEFAULT';


    /**
     * typo3 conf var 404
     * @var array EXTCONF
     */
    protected $config = null;


    /**
     * initialize host and this->typo3_conf_var_404
     */
    public function __construct()
    {
        $this->host = GeneralUtility::getIndpEnv('HTTP_HOST');
        // define config for error_404_multilingual
        $this->config = $this->getConfiguration($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl_404_multilingual'],
            $this->host);
        if (!is_array($this->config)) {
            // set the default
            $this->config = array(
                'errorPage' => '404',
                'unauthorizedPage' => '404',
                'redirects' => array(),
                'stringConversion' => 'none',
            );
        }
    }


    /**
     * @param $params
     * @throws \Exception
     * @param \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController $obj
     */
    public function pageErrorHandler(&$params, \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController &$obj)
    {
        if (GeneralUtility::_GP('tx_realurl404multilingual') == '1')
        {
            // we are in a infinite redirect/request loop, which we need to stop
            throw new \Exception('404 page handler stuck in a redirect/request loop. Please check your configration',1474969985);
        }

        if (GeneralUtility::_GP('tx_realurl404multilingual') != null && intval(GeneralUtility::_GP('tx_realurl404multilingual')) == 1) {
            // Again landed here -> break
            header("HTTP/1.0 404 Not Found");
            echo $this->getProvisionally404Page();
            exit();
        }

        $extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['realurl_404_multilingual']);
        $currentUrl = $params['currentUrl'];
        $reasonText = $params['reasonText'];
        $pageAccessFailureReasons = $params['pageAccessFailureReasons'];
        $mode = $extConf['mode'];
        $statusCode = $GLOBALS['TYPO3_CONF_VARS']['FE']['pageNotFound_handling_statheader'];

        if (isset($pageAccessFailureReasons['fe_group']) && array_shift($pageAccessFailureReasons['fe_group']) != 0) {

            $unauthorizedPage = $this->config['unauthorizedPage'];
            $unauthorizedPage = (!$unauthorizedPage ? '401' : $unauthorizedPage);
            $destinationUrl = $this->getDestinationUrl($currentUrl, $unauthorizedPage);
            $destinationUrl .= "?return_url=".urlencode($currentUrl)."&tx_realurl404multilingual=1";
            //$header = "HTTP/1.0 401 Unauthorized";
            header("Cache-Control: no-store, no-cache, must-revalidate");
            header("Pragma: no-cache");
            $mode = self::MODE_REDIRECT; // force redirect
        } else {

            // define the page name
            $errorpage = $this->config['errorPage'];
            $errorpage = ($errorpage == '' ? '404' : $errorpage);
            $destinationUrl = $this->getDestinationUrl($currentUrl, $errorpage);
        }


        switch ($mode) {
            case self::MODE_REDIRECT:
                HttpUtility::redirect($destinationUrl, HttpUtility::HTTP_STATUS_301);
                break;
            default:
                $this->getPageAndDisplay($destinationUrl, ($statusCode ? $statusCode : "HTTP/1.0 404 Not Found"));
                break;
        }
    }


    /**
     * get page and echo it
     * @param $url404 string $url404 404 page url
     * @param $header string http header
     * @return void
     */
    private function getPageAndDisplay($url404, $header)
    {

        header($header);

        // check cache
        /** @var \TYPO3\CMS\Core\Cache\Frontend\FrontendInterface $cache */
        $cache = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Cache\\CacheManager')->getCache('realurl_404_multilingual');
        $cacheKey = hash('sha1',
            $url404 . '-' . ($GLOBALS['TSFE']->fe_user->user ? $GLOBALS['TSFE']->fe_user->user['uid'] : ''));
        if ($cache->has($cacheKey)) {
            $content = $cache->get($cacheKey);
        }
        if (empty($content)) {
            $content = $this->getUrl($url404);
            if (!empty($content)) {
                switch ($this->config['stringConversion']) {
                    case 'utf8_encode' : {
                        $content = utf8_encode($content);
                        break;
                    }
                    case 'utf8_decode' : {
                        $content = utf8_decode($content);
                        break;
                    }
                }

                $cache->set($cacheKey, $content, array());
            }
        }

        /* print out content of 404 page */
        echo $content;
    }

    /**
     * Returns the URI without leading slashes
     *
     * @param string $url
     * @return string
     */
    private function getUri($url = "")
    {
        if (preg_match("/^\/(.*)/i", $url, $reg)) {
            return $reg[1];
        }
        return $url;
    }

    /**
     * Returns the related configuration
     *
     * @param $array array
     * @param $key string
     * @return string
     */
    private function getConfiguration($array = array(), $key = '_DEFAULT')
    {
        if (is_array($array) && array_key_exists($key, $array)) {
            $domain_key = $key;
        } else {
            $domain_key = '_DEFAULT';
        }
        if (is_array($array[$domain_key])) {
            return $array[$domain_key];
        }
        return $array[$array[$domain_key]];
    }


    /**
     * @param string $currentUrl
     * @param string $suffix
     * @return string
     */
    private function getDestinationUrl($currentUrl = "", $suffix)
    {

        $host = \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('HTTP_HOST');

        $uri = $this->getUri($currentUrl);
        list($script, $option) = explode("?", $uri);

        // get config for realurl
        $config_realurl = $this->getConfiguration($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl'], $host);

        // removes all leading slashes in array
        if (count($this->config['redirects']) > 0) {
            $redirects = array();
            foreach ($this->config['redirects'] as $key => $val) {
                $redirects[$this->getUri($key)] = $this->getUri($val);
            }
            $this->config['redirects'] = $redirects;
        }

        // fallback if typo3_conf_var_404 not an array
        if (!is_array($this->config['redirects'])) {
            $this->config['redirects'] = array();
        }

        // First element will be the host
        $url_array = array();
        $url_array[] = $host;
        $sitePath = \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_SITE_PATH');
        if ($sitePath && strlen(trim($sitePath, '/')) > 0) {
            $url_array[] = trim($sitePath, '/');
        }
        if (is_array($this->config['redirects']) && array_key_exists($uri, $this->config['redirects'])) {
            // There is a redirect defined for this request URI, so the value is taken
            $url_array[] = $this->config['redirects'][$uri];
        } elseif (is_array($this->config['redirects']) && array_key_exists($script, $this->config['redirects'])) {
            // There is a redirect defined for this script, so the value is taken
            $url_array[] = $this->config['redirects'][$script];
        } else {
            // Normaly no alternative is defined, so the 404 site will be taken extract the language
            $uriSegments = explode('/', $uri);
            $lang = reset($uriSegments);


            // find language key
            if (is_array($config_realurl['preVars'])) {
                foreach ($config_realurl['preVars'] as $key => $val) {
                    if (isset($config_realurl['preVars'][$key]['GETvar']) && $config_realurl['preVars'][$key]['GETvar'] == "L") {

                        if ($lang != false && is_array($config_realurl['preVars'][$key]['valueMap']) && array_key_exists($lang,
                                $config_realurl['preVars'][$key]['valueMap'])
                        ) {
                            $url_array[] = $lang;
                        } elseif ($config_realurl['preVars'][$key]['valueDefault']) {
                            $url_array[] = $config_realurl['preVars'][$key]['valueDefault'];
                        }
                    }
                }
            }

            $url_array[] = $suffix;
        }

        $useHttps = $_SERVER['HTTPS'];

        return ((!empty($useHttps) && $useHttps !== 'off') ? "https" : "http") . "://" . implode("/", $url_array);
    }


    /**
     * @param string $url
     * @return string
     */
    private function getUrl($url = "")
    {
        $options = [
            'query' => ['tx_realurl404multilingual' => 1]
        ];

        if ($GLOBALS['TSFE']->fe_user->user) {
            $options['query']['FE_SESSION_KEY'] = $GLOBALS['TSFE']->fe_user->id .
                '-' .
                md5(
                    $GLOBALS['TSFE']->fe_user->id .
                    '/' .
                    $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey']
                )
            ;
        }

        $requestFactory = GeneralUtility::makeInstance(RequestFactory::class);
        $response = $requestFactory->request($url, 'POST', $options);
        $responseContents = $response->getBody()->getContents();

        if (empty($responseContents)) {
            /* display own 404 page, because the real 404 page couldn't be loaded */
            $responseContents = $this->getProvisionally404Page();
        }

        return $responseContents;
    }

    /**
     * TODO: Generate nice 404 page
     * @return string
     */
    private function getProvisionally404Page() {
        return "Sorry, but the 404 page was not found! Please check the path to the 404 page.";
    }


}

