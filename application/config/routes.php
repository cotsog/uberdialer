<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
//$route['default_controller'] = 'welcome';
//echo "<pre>",print_r($_SERVER), "</pre>";
//echo $this->uri->segment(2).$this->uri->segment(1) . '/' . $this->uri->segment(3)."<br>";
$route['default_controller'] = "root";
$route['login'] = 'root/login';
$route['php_info'] = 'root/php_info';
$route['login'] = 'root/login';
$route['logout'] = 'root/logout';
$route['password'] = 'root/password';
$route['passwordreset/(:any)'] = 'root/passwordreset/$1';
$route['404_override'] = 'root/login';
//$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;
//$route['mpg/'.$this->uri->segment(2)] = $this->uri->segment(2).$this->uri->segment(1) . ((!empty($this->uri->segment(3))) ? '/' . $this->uri->segment(3) : '');
$uri_original = $_SERVER['REQUEST_URI'];
$uri_split = explode('?', $uri_original);
$uri = $uri_split[0];
$uri_parts = explode('/', $uri);
$route_value = array();
$param_ctr = 0;
foreach($uri_parts as $idx => $uri_name){
    if($uri_name == 'mpg'){
        unset($uri_parts[$idx]);
    }else{
        $param = "";
        if(is_numeric($uri_name)){
            $uri_parts[$idx] = '(:any)';
            $param_ctr++;
            $param = '$'.$param_ctr;
            $route_value[] = $param;
        }else if(!empty($uri_name)){
            $route_value[] = $uri_name;
        }
        
    }
}
$query_string = "";
$query_string_route = "";
if(!empty($uri_split[1]) && !isset($_GET['switch'])){
    $param_ctr++;
    $query_string_route = '/?$'.$param_ctr;
    $query_string = "?(:any)";
}
$uri_route = implode('/', $uri_parts);
$route_values = implode('/', $route_value);
$route['mpg'.$uri_route.$query_string] = $route_values.$query_string_route;