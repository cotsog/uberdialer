<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');
    include 'Contacts.php';

class Api_contacts extends Contacts
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Api_model');
        $this->load->model('Calls_model');
    }

    // Start RP UAD-86 - for get auto hopper value set 1 and non workable contacts
    public function getAutoHopperNonWorkableContacts()
    {
        $campaignId = $this->input->post('campaign_id');
        $contactsData = $this->Api_model->getHopperNonWorkableContacts($campaignId);

        $callLimit = $this->config->item('call_limit');

        // for get contacts whose limit exceed for day
        $callExceedContactsData = $this->Calls_model->CheckTodayCallDialledLimit(0,$callLimit,1,$campaignId);
        
        $finalContacts = array_merge($contactsData, $callExceedContactsData);
        
        if (isset($finalContacts) && ! empty($finalContacts)) {
            $data['data']   = $finalContacts;
            $data['status'] = 200;
        } else {
            $data['data']   = $finalContacts;
            $data['status'] = 404;
        }

        echo json_encode($data);
    }
}
?>