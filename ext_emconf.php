<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "realurl_404_multilingual".
 *
 * Auto generated 13-03-2013 23:01
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
	'title' => 'RealURL 404 multilingual error page',
	'description' => 'Shows the defined error page of the given language if the requested page or file could not be found',
	'category' => 'fe',
	'shy' => 0,
	'version' => '1.0.4',
	'conflicts' => 'error_404_handling',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'stable',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Sven Wappler',
	'author_email' => 'typo3YYYY@wappler.systems',
	'author_company' => 'WapplerSystems',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'realurl' => '',
			'typo3' => '6.0.0-7.9.99',
		),
		'conflicts' => array(
			'error_404_handling' => '',
		),
		'suggests' => array(
		),
	),
	'suggests' => array(
	),
	'_md5_values_when_last_written' => 'a:4:{s:12:"ext_icon.gif";s:4:"789d";s:17:"ext_localconf.php";s:4:"4d38";s:30:"Classes/Hooks/FrontendHook.php";s:4:"ac32";s:14:"doc/manual.sxw";s:4:"839f";}',
);

?>