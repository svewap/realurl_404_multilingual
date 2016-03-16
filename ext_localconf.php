<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}


$TYPO3_CONF_VARS['FE']['pageNotFound_handling'] = 'USER_FUNCTION:EXT:'.$_EXTKEY.'/Classes/Hooks/FrontendHook.php:WapplerSystems\\Realurl404Multilingual\\Hooks\\FrontendHook->pageErrorHandler';


// Caching the 404 pages - default expire 3600 seconds
if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['realurl_404_multilingual'])) {
	$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['realurl_404_multilingual'] = array();
}


$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearCachePostProc'][] = 'EXT:realurl_404_multilingual/Classes/Hooks/ClearCachePostProc.php:&WapplerSystems\\Realurl404Multilingual\\Hooks\\ClearCachePostProc->clearCachePostProc';


?>