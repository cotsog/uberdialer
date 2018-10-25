<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Offices_model extends CI_Model
{
    public $officeTable = 'offices';

    /* Dev_NV Region Start */

    function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function get_all($filter = '',$select = '')
    {
        $columns = !empty($select) ? $select : 'id,name';

        $this->db->select($columns);
        $this->db->from($this->officeTable);

        if(!empty($filter)){
            $this->db->where($filter);    
        }
        
        $this->db->order_by("id");
        $query = $this->db->get();
        
        return $query->result_array();
    }
    
    public function getByFilter($filter=' is_active = 1 ')
    {
        $this->db->select('id,name,parent_id');
        $this->db->from($this->officeTable);
        $this->db->where($filter);
        $this->db->order_by("id");
        $query = $this->db->get();

        return $query->result_array();
    }

    public function get($id)
    {
        $this->db->select('id,name,parent_id');
        $this->db->from($this->officeTable);
        $this->db->where('id',$id);
        $this->db->order_by("id");
        $query = $this->db->get();

        return $query->result_array();
    }

    public function getByName($name, $filter='')
    {
        $add_filter = "name = '{$name}' ".$filter;

        $this->db->select('id,name,is_active');
        $this->db->from($this->officeTable);
        $this->db->where($add_filter);
        $this->db->order_by("id");
        $query = $this->db->get();

        return $query->result_array();
    }

    public function getCampaignsAndUsersBySite($name)
    {
        $sql = "(SELECT cto.id FROM campaign_tm_offices cto JOIN campaigns c WHERE tm_office='{$name}' and status<>'completed' limit 1) UNION ALL (SELECT u.id FROM users u WHERE telemarketing_offices='{$name}' and status = 'Active' limit 1) limit 1";
        $query = $this->db->query($sql);

        return $query->result_array();
    }

    public function create($data)
    {
        return $this->db->insert($this->officeTable, $data); 
    }

    public function update($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update($this->officeTable, $data); 
    }
        
    public function remove($id)
    {
        $data = array('is_active' => 0);
        $this->db->where('id', $id);
        $this->db->update($this->officeTable, $data); 
    }
    
    public function getSubOffices($offices) {
        if (is_array($offices)) {
            $where = array();
            foreach ($offices as $office) {
                $where[] = "`name` = '" . $office . "'";
            }
            
            $sql = "SELECT `name` from `offices` "; 
            $sql .= "WHERE `parent_id` IN (SELECT `id` FROM `offices` where " . implode(" OR ", $where) .")";
        } else {
            $sql = "SELECT `name` from `offices` "; 
            $sql .= "WHERE `parent_id` = (SELECT `id` FROM `offices` where `name` = '" . $offices . "')";
        }
                
        $query = $this->db->query($sql);

        return $query->result_array();
    }
}

class OfficeTable
{
    public $id;
    public $name;
    public $created_at;
    public $created_by;
    public $updated_at;
    public $updated_by;
}

?>
