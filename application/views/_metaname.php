<?php

$sever_name = explode(".", $_SERVER['SERVER_NAME']);
if(strpos($sever_name[0], "dev") !== false || strpos($sever_name[0], "stage") !== false){
    echo '<META NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW">';
}