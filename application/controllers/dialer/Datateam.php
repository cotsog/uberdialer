<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Datateam extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->session->userdata('uid')) {
            $this->session->set_flashdata('prev_action', 'loginfail');
            redirect('/login');
        }
        $this->load->library(array('form_validation', 'session')); // load form lidation libaray & session library
        $this->load->helper(array('url', 'html', 'form', 'utils','common'));
    }

    public function index() {

        $isAuthorized = IsAdminDataResearchUserAuthorized($this->session->userdata('user_type'));
        if (!$isAuthorized) {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'You are unauthorized person for access this page.');
            redirect('/users/profile');
        }

        $data['meta_title'] = 'Data Research Component';
        $data['title'] = 'Data Research Component';
        $data['crumbs'] = 'Data Research Component';
        $data['main'] = $this->app_module_name.'/dataresearch/index';
        $this->load->vars($data);
        $this->load->view('layout');
    }
}
?>