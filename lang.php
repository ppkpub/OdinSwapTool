<?php
//Set language
$g_currentLang=@$_COOKIE['swaptool_lang'];

if(strlen($g_currentLang)==0 ) //Default is chinese
    $g_currentLang='cn';

require_once 'lang_'.$g_currentLang.'.php';

//setcookie("swaptool_lang", $g_currentLang, time()+3600);
