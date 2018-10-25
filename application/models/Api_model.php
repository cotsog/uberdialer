<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Api_model extends CI_Model
{
    public $contactsTable = 'contacts';
    public $campaignContactsTable = 'campaign_contacts';

    function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    // Start RP - UAD-86 get non workable contact which has alreday added in auto_hopper table
    public function getHopperNonWorkableContacts($campaignId)
    {
        $query = $this->db->query("SELECT cc.id as campaign_contact_id FROM " . $this->campaignContactsTable . " cc
                                    JOIN " . $this->contactsTable . " c ON c.id = cc.contact_id
                                    WHERE (c.do_not_call_ever= 1 OR cc.workable_status='NW')AND cc.auto_added_as_hopper='1'
                                    AND cc.campaign_id=$campaignId  GROUP BY cc.id"
                                );
        $array = $query->result();
        return $array;
    }
}

?>
