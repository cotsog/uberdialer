<?php

class Util
{

    public function addLog($fileName, $msg = '')
    {
        $CI = & get_instance();
        $conf = $CI->config->config;
        $log = $msg;
        $filePut = file_put_contents($conf['log_path'] . $fileName, $log, FILE_APPEND);
    }
}
