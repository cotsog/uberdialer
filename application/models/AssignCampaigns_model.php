<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class AssignCampaigns_model extends CI_Model
{
    public $table = 'campaign_assign';
    public $campaign_table = 'campaigns';
    public $users_table = 'users';
    public $tl_campaign_assign = 'campaign_assign_tl';
    
    function __construct()
    {
        parent::__construct();
        $this->load->database();
    }
    
    public function get_campaign_by_TL($id=0)
    {
        $logged_tm_office = $this->session->userdata('telemarketing_offices');
        $logged_user_type = $this->session->userdata('user_type');

        $sql = "SELECT c.id,c.name FROM {$this->campaign_table} c JOIN {$this->tl_campaign_assign} cl ON cl.campaign_id = c.id ";
        if($logged_user_type != 'admin'){
            $sql .= " LEFT JOIN `campaign_tm_offices` cto ON c.id = cto.campaign_id ";
        }
        $sql .= " WHERE c.status ='Active' ";
        if($logged_user_type != 'admin'){
           $sql .= " AND cto.tm_office = '" . $logged_tm_office . "' ";
        }
        if ($id) {
            $sql .= " and cl.user_id = ? ";
        }
        $sql .= " AND c.module_type = '" . $this->app_module_type . "' and c.business = '{$this->app}' ";
        $sql .= " ORDER BY c.name";
        $query = $this->db->query($sql, array($id));
        return $query->result();
    }
    
    public function get_agent_by_campaign($campaignId, $tl, $searchByEgCampaignId = 0)
    {
        $logged_tm_office = $this->session->userdata('telemarketing_offices');
        $logged_user_type = $this->session->userdata('user_type');
        if($searchByEgCampaignId){
            $campaignFilter = "c.eg_campaign_id = ?";
        }else{
            $campaignFilter = "c.id = ?";
        }
        $sql = "SELECT u.id, CONCAT(u.first_name,' ',u.last_name) AS agent_name, c.id as campaign_id, c.eg_campaign_id 
                FROM `users` u 
                LEFT JOIN `campaign_assign_tl` cl ON cl.user_id = u.parent_id 
                LEFT JOIN `campaigns` c ON c.id = cl.campaign_id   
                WHERE u.user_type = 'agent' AND u.status = 'Active' AND {$campaignFilter}  and cl.user_id = ? and c.id != 764 ";
        if($logged_user_type != 'admin'){
            $sub_telemarketing_offices = $this->session->userdata('sub_telemarketing_offices');
            $subOfficeWhere = "";
            if(!empty($sub_telemarketing_offices)){
                foreach ($sub_telemarketing_offices as $sub_telemarketing_office) {
                    $subOfficeWhere .= " OR u.telemarketing_offices = '" . $sub_telemarketing_office . "'";
                }
            }
            $sql .= " AND (u.telemarketing_offices = '" . $logged_tm_office . "' {$subOfficeWhere})";
        }
        $sql .= " AND FIND_IN_SET('".$this->app_module_type."',u.module) ";

        $query = $this->db->query($sql, array($campaignId, $tl));

        return $query->result();                 
    }
    
    public function get_assign_campaign_data($campaignId,$tl){
        $sql = "SELECT  GROUP_CONCAT(agent_id) AS agent_ids FROM `campaign_assign`  WHERE campaign_id = ?  AND teamleader_id = ?  GROUP BY campaign_id,teamleader_id ";
        $query = $this->db->query($sql,array($campaignId,$tl));
        $array = $query->result();
        if (!empty($array)) {
            $array = $array[0];
        }
        return $array;
    }
    
    public function insert_assign_campaign($string)
    {
        $sql = "INSERT INTO " . $this->table . " (campaign_id,teamleader_id,agent_id,created_at,modified_by,updated_at) VALUES %s";
        $sql = sprintf($sql, $string);
        $sql .= '  ON DUPLICATE KEY UPDATE agent_id = VALUES(agent_id),modified_by = VALUES(modified_by),updated_at = VALUES(updated_at)';
        try {
            $this->db->query($sql);
            $result = 1;                                   
        } catch (Exception $e) {
            $result = 0;
        }
        return $result;
    }   
    
    public function delete_assign_agents($customwhere)
    {
        $sql = "DELETE FROM `campaign_assign` WHERE agent_id in ? AND campaign_id = ? and teamleader_id = ? ";
        $query = $this->db->query($sql, array($customwhere['agent_id'], $customwhere['campaignid'], $customwhere['teamleader_id']));
        return $query;
    }

    public function delete_assign_agents_by_group_team($custom_where)
    {
        $sql = "DELETE FROM `campaign_assign` WHERE agent_id = ? AND teamleader_id = ? ";
        $query = $this->db->query($sql, array($custom_where['agent_id'], $custom_where['teamleader_id']));
        return $query;
}
}