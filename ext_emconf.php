<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "realurl_404_multilingual".
 *
 * Auto generated 14-01-2013 01:26
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array (
	'title' => 'RealURL 404 multilingual error page',
	'description' => 'Shows the defined error page of the given language if the requested page or file could not be found',
	'category' => 'fe',
	'shy' => 0,
	'version' => '1.0.0',
	'dependencies' => '',
	'conflicts' => '',
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
	'author_email' => 'typo3YYYY@wapplersystems.de',
	'author_company' => '',
	'CGLcompliance' => NULL,
	'CGLcompliance_note' => NULL,
	'constraints' => 
	array (
		'depends' => 
		array (
			'realurl' => '',
			'php' => '5.0.0-0.0.0',
			'typo3' => '6.0.0-0.0.0',
		),
		'conflicts' => 
		array (
			'error_404_handling' => '',
		),
		'suggests' => 
		array (
		),
	),
);

?>