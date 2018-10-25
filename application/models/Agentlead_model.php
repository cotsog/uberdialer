<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Agentlead_model extends CI_Model
{
    public $table = 'agent_lead';

    public function __construct()
    {
        parent::__construct();

        $this->load->database();

    }

    public function insert($obj)
    {
        $result = $this->db->insert($this->table, $obj);

        if ($result) {
            $new_id = $this->db->insert_id();
        } else {
            $new_id = 0;
        }

        return $new_id;

    }

    public function get_agent_lead_count($agent_lead_array)
    {
        $this->db->select('*');
        $this->db->from($this->table);
        $this->db->where('lead_id', $agent_lead_array['lead_id']);
        $this->db->where('agent_id', $agent_lead_array['agent_id']);
        $query = $this->db->get();

        $result = $query->result();

        return $result;

}
}

?>