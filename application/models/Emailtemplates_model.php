<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Emailtemplates_model extends CI_Model
{
    public $table = 'email_templates';
    public $campaign_table = 'campaigns';
    public $resource_table = 'resources';
    
    function __construct()
    {
        parent::__construct();
        $this->load->database();
    }
    public function getTemplateList($logged_tm_office,$logged_user_type)
    {
        $sql = "SELECT et.id,et.resource_name,et.resource_id,et.campaign_id,et.template_name,et.created_at,et.email_sender, c.name,c.status FROM ".$this->table." et LEFT JOIN ".$this->campaign_table." c ON c.id = et.campaign_id";
        $add_condition = "c.module_type='tm'";
        if ($logged_user_type != 'admin') {
            $sql .= " JOIN `campaign_tm_offices` cto ON c.id = cto.campaign_id ";
            $sql .= " AND cto.tm_office = '" . $logged_tm_office . "' AND $add_condition";
        }else{
            $sql .= " WHERE $add_condition";
        }
        $sql .= " order by et.created_at desc,et.id desc";

        $query = $this->db->query($sql);      
        return $query->result();
    }
    public function get_one_template($id,$logged_user_type="",$logged_tm_office=""){
        $sql = "select cto.tm_office,et.* ,c.name,c.status,c.site_name,c.eg_campaign_id,c.id as campaign_id  from ".$this->table." et LEFT JOIN ".$this->campaign_table." c ON c.id = et.campaign_id";
        $sql .= " LEFT JOIN `campaign_tm_offices` cto ON c.id = cto.campaign_id ";
        if ($logged_user_type != 'admin') {
            $sql .= " AND cto.tm_office = '" . $logged_tm_office . "' ";
        }
        $sql .= "where et.id = ? ";
        $query = $this->db->query($sql,array($id));    

        $array = $query->result();
        if (!empty($array)) {
            $array = $array[0];
        }
        return $array;
    }

    public function insert_template($array){
        $result = $this->db->insert($this->table, $array);
        if ($result) {
            $user_id = $this->db->insert_id();
        } else {
            $user_id = 0;
        }
        return $user_id;
    }
    function update_template($Id, $data)
    {
        $this->db->where('id', $Id);
        return $this->db->update($this->table, $data);
    }
    function delete_template($ids){
        $sql = "delete from ".$this->table." where id in ?";
        return $this->db->query($sql,array(explode(',', $ids)));
    }
    function get_emailTemplate_by_campaignResource($campaignId,$resourceId){
        $sql = "select * from ".$this->table." where campaign_id = ? and resource_id = ? ORDER BY id DESC LIMIT 0,1 ";
        $query = $this->db->query($sql,array($campaignId,$resourceId)); 
       // echo $this->db->last_query();
        $array = $query->result();
        if (!empty($array)) {
            $array = $array[0];
        }
        return $array;
    }
    function get_emailTemplate_by_ID($emailTemplateId){
        $sql = "select * from ".$this->table." where id=? ORDER BY id DESC LIMIT 0,1 ";
        $query = $this->db->query($sql,array($emailTemplateId)); 
       // echo $this->db->last_query();
        $array = $query->result();
        if (!empty($array)) {
            $array = $array[0];
        }
        return $array;
    }
}

class Templates
{
    public $campaign_id;
    public $resource_id;
    public $resource_name;
    public $subject_line;
    public $body;
    public $signature_line;

}
