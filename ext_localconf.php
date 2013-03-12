<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}


$TYPO3_CONF_VARS['FE']['pageNotFound_handling'] = 'USER_FUNCTION:EXT:'.$_EXTKEY.'/Classes/Hooks/FrontendHook.php:TYPO3\\CMS\\Realurl404Multilingual\\Hook\\FrontendHook->pageErrorHandler';


?>