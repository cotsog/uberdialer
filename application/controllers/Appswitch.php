<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of hook
 *
 * @author Lenovo X1 Carbon
 */
class Appswitch extends MY_Controller {
    //put your code here
    
    var $CI;
    var $redir='';
    function __construct(){
        $this->CI =& get_instance();
    }

    function toggle()
    {
        $switch = $this->CI->input->get('switch');
        $uri = uri_string();
        $uri_parts = explode('/', $uri);
        // check if the previous url contain 'mpg' in the url, if so then redirect this to /mpg/uri
        if(strpos($uri, 'mpg') === false && isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'mpg') !== false && !$switch && $uri != 'login' && $uri != 'logout'){
            if ((isset($msg) && $msg != '') || $this->CI->session->flashdata('msg') != '') {
                    $this->CI->session->set_flashdata('class', $this->CI->session->flashdata('class'));
                    $this->CI->session->set_flashdata('msg', $this->CI->session->flashdata('msg'));
            }
            foreach($uri_parts as $idx => $uri_name){
                if($uri_name == 'mpg'){
                    unset($uri_parts[$idx]);
                }
            }
            $query_string = !empty($_SERVER['QUERY_STRING']) ? "?{$_SERVER['QUERY_STRING']}" : "";
            $this->redir = '/mpg/'. implode('/', $uri_parts) . $query_string;
        }
        if(!empty($this->redir)) {
            redirect($this->redir);
        }
        
    }
}
