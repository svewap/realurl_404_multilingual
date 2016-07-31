<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}


$TYPO3_CONF_VARS['FE']['pageNotFound_handling'] = 'USER_FUNCTION:EXT:'.$_EXTKEY.'/Classes/Hooks/FrontendHook.php:WapplerSystems\\Realurl404Multilingual\\Hooks\\FrontendHook->pageErrorHandler';


// Caching the 404 pages - default expire 3600 seconds
if (!is_array($TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['realurl_404_multilingual'])) {
    $TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['realurl_404_multilingual'] = array(
        'frontend' => 'TYPO3\\CMS\\Core\\Cache\\Frontend\\VariableFrontend',
        'backend' => 'TYPO3\\CMS\\Core\\Cache\\Backend\\FileBackend'
    );
}
// Check if request was made from realurl_404_multilingual and session key was pass
$checkIfNeedToDisableIPCheck = function() {
    if (\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('tx_realurl404multilingual') == '1'
        && \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('FE_SESSION_KEY')
        && $_SERVER['SERVER_ADDR'] == \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('REMOTE_ADDR')
    ) {
        $fe_sParts = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode('-', \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('FE_SESSION_KEY'),1);
        // If the session key hash check is OK:
        if (!strcmp(md5(($fe_sParts[0] . '/' . $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'])), $fe_sParts[1])) {
            //disable IP check
            $GLOBALS['TYPO3_CONF_VARS']['FE']['lockIP'] = '0';
        }
    }
};
$checkIfNeedToDisableIPCheck();
unset($checkIfNeedToDisableIPCheck);

?>