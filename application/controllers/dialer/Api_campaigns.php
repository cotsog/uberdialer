<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');
    include 'Campaigns.php';

class Api_campaigns extends Campaigns
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Campaigns_model');
    }

    // Start RP UAD-27 - get Campaigns data
    public function getCampaignData()
    {
        $campaignId = $this->input->post('campaign_id');
        $campaignData = $this->Campaigns_model->get_one($campaignId);
        
        if (isset($campaignData) && ! empty($campaignData)) {
            $data['data']   = $campaignData;
            $data['status'] = 200;
        } else {
            $data['data']   = $campaignData;
            $data['status'] = 404;
        }

        echo json_encode($data);
    }
}
?>