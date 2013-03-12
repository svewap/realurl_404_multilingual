<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}


$TYPO3_CONF_VARS['FE']['pageNotFound_handling'] = 'USER_FUNCTION:EXT:'.$_EXTKEY.'/Classes/Hooks/FrontendHook.php:TYPO3\\CMS\\Realurl404Multilingual\\Hook\\FrontendHook->pageErrorHandler';

// Caching the 404 pages - default expire 3600 seconds
if (!is_array($TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['realurl_404_multilingual'])) {
	$TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['realurl_404_multilingual'] = array(
		'frontend' => 't3lib_cache_frontend_VariableFrontend',
		'backend' => 't3lib_cache_backend_fileBackend'
	);
}

?>