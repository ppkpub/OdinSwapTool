<?php
//Set language
$g_currentLang=$_COOKIE['swaptool_lang'];

if($g_currentLang=='en') 
    $g_currentLang='cn';
else
    $g_currentLang='en';

setcookie("swaptool_lang", $g_currentLang, time()+3600);

header('location:./');