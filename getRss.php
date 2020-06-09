<?php

$str = $_GET["rss"];

$str = file_get_contents(base64_decode($str));
/*
$str = preg_replace(["/window\.location[ ]*=[ ]*\"[^\"]*\"[ ]*;/",
    "/window\.location[ ]*=[ ]*'[^']*'[ ]*;/",
    "/window\.location[ ]*=[ ]*`[^`]*`[ ]*;/",
    "/window\.onload\(\);/"], "", $str);
/**/

//echo base64_encode($str);
echo $str;
