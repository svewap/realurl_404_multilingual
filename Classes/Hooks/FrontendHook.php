<?php
namespace TYPO3\CMS\Realurl404Multilingual\Hook;

/***************************************************************
 * Copyright notice
 *
 * (c) 2004-2013 Sven Wappler <typo3YYYY@wapplersystems.de>
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

/**
 *
 */
class FrontendHook {

	/*
	 *
	 *
	 *
	 */
	public function pageErrorHandler(&$params,&$obj) {

		$currentUrl = $params['currentUrl'];
		$reasonText = $params['reasonText'];
		$pageAccessFailureReasons = $params['pageAccessFailureReasons'];

		if (\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('tx_realurl404multilingual') || !is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl_404_multilingual'])) {
			// If the defined errorpage does not exist, the normal
			// errorpage is shown...
			$urlcontent = $obj->pageNotFoundHandler(true,$GLOBALS['TYPO3_CONF_VARS']['FE']['pageNotFound_handling_statheader'],'');
		} else {

			// extract the domain
			$host = \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('HTTP_HOST');
			$uri = $this->getUri($currentUrl);
			list($script,$option) = explode("?",$uri);

			// define config for error_404_multilingual
			$typo3_conf_var_404 = $this->getConfiguration($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl_404_multilingual'],$host);
			if (!is_array($typo3_conf_var_404)) {
				// set the default
				$typo3_conf_var_404 = array('errorPage' => '404','redirects' => array(),'stringConversion' => 'none',);
			}

			// define config for realurl
			$typo3_conf_var_realurl = $this->getConfiguration($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl'],$host);

			// removes all leading slashes in array
			if (count($typo3_conf_var_404['redirects']) > 0) {
				$redirects = array();
				foreach ($typo3_conf_var_404['redirects'] as $key => $val) {
					$redirects[$this->getUri($key)] = $this->getUri($val);
				}
				$typo3_conf_var_404['redirects'] = $redirects;
			}

			// fallback if typo3_conf_var_404 not an array
			if (!is_array($typo3_conf_var_404['redirects'])) {
				$typo3_conf_var_404['redirects'] = array();
			}

			// First element will be the host
			$url_array = array();
			$url_array[] = $host;
			if (is_array($typo3_conf_var_404['redirects']) && array_key_exists($uri,$typo3_conf_var_404['redirects'])) {
				// There is a redirect defined for this request URI, so the
				// value is taken
				$url_array[] = $typo3_conf_var_404['redirects'][$uri];
			} elseif (is_array($typo3_conf_var_404['redirects']) && array_key_exists($script,$typo3_conf_var_404['redirects'])) {
				// There is a redirect defined for this script, so the value
				// is taken
				$url_array[] = $typo3_conf_var_404['redirects'][$script];
			} else {
				// Normaly no alternative is defined, so the 404 site will be
				// taken
				// extract the language
				$reg = array();
				preg_match("/^\/([a-zA-Z]*)\/(.*)/",\TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('REQUEST_URI'),$reg);
				$lang = $reg[1];
				// define the page name
				$errorpage = $typo3_conf_var_404['errorPage'];
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

			$url = "http://".implode("/",$url_array);
			// header 404
			$error_header = $GLOBALS['TYPO3_CONF_VARS']['FE']['pageNotFound_handling_statheader'];
			$error_header = ($error_header ? $error_header : "HTTP/1.0 404 Not Found");
			header($error_header);

			// check cache
			$cache = $GLOBALS['typo3CacheManager']->getCache('realurl_404_multilingual');
			$cacheKey = hash('sha1',$url);
			if ($cache->has($cacheKey))
			{
				$urlcontent = $cache->get($cacheKey);
			}
			elseif ($GLOBALS['TYPO3_CONF_VARS']['SYS']['curlUse']) {
				// Open url by curl
				$ch = curl_init();
				curl_setopt($ch,CURLOPT_URL,$url);
				curl_setopt($ch,CURLOPT_HEADER,false);
				curl_setopt($ch,CURLOPT_POST,true);
				curl_setopt($ch,CURLOPT_POSTFIELDS,'tx_realurl404multilingual=1');
				//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
				curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
				curl_setopt($ch,CURLOPT_TIMEOUT,5);
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
				$urlcontent = file_get_contents($url.'?tx_realurl404multilingual=1');

				// save to cache
				$cache->set($cacheKey,$urlcontent,array());
			}
		}

		switch ($typo3_conf_var_404['stringConversion']) {
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
	 * @param $key string
	 * @return string
	 */
	function getConfiguration($array = array(),$key = '_DEFAULT') {
		if (is_array($array) && array_key_exists($key,$array)) {
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

}

?>