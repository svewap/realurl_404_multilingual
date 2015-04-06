.. include:: Images.txt

.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. ==================================================
.. DEFINE SOME TEXTROLES
.. --------------------------------------------------
.. role::   underline
.. role::   typoscript(code)
.. role::   ts(typoscript)
   :class:  typoscript
.. role::   php(code)


Installation
------------

1. Write the configuration into the realurl\_conf.php file:

::

   $TYPO3_CONF_VARS['EXTCONF']['realurl_404_multilingual'] = array(
       '_DEFAULT' => array(
            'errorPage' => '404/',
       ),
   );

In this example I used the URL alias “404”.

2. Create a page in your pagetree and translate it. Set the URL alias
to 404:

|img-3|


3. I use the following RealURL config:

::

   'preVars' => array(
        array(
            'GETvar' => 'L',
            'valueMap' => array(
                   'de' => '0',
                   'en' => '1',
                   'nl' => '2',
                   'cn' => '3',
            ),
            'valueDefault' => 'de',
            'noMatch' => 'bypass',
       ),
   ….
   …
   
   


