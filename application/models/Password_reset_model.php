<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

class Password_reset_model extends CI_Model {
 
    public $table = 'password_reset';

    function __construct() {
        parent::__construct();
        $this->load->database();
    }

    function insert($member) {
		$result = $this->db->insert($this->table, $member);
        if ($result) {
            $id = $this->db->insert_id();
        } else {
            $id = 0;
        }
        return $id;
    }

    function update($member) {
        $member = $this->unset_nulls($member);
        return $this->db->update('password_reset', $member, array('id' => $member->id));
    }

    function get_by_token($token){

        //$sql = 'SELECT *,HOUR(TIMEDIFF(now(),created_at)) AS hour FROM password_reset WHERE token = ? AND is_reset = 0 group by hour HAVING hour < 25';
        $expiry_date = date('Y-m-d h:s:m', time()-86401);// 60 * 60 * 24 to get 86400
        $sql = "SELECT * FROM password_reset WHERE token = ? AND is_reset = 0 and created_at > ?";
        $query = $this->db->query($sql,array($token,$expiry_date));
            
        return $query->result();            
    }
		
	function unset_nulls($obj) {

        foreach ($obj as $key => $value) {
            if ($value == NULL) {
                unset($obj->$key);
            }
        }
        return $obj;
    }
}

class Passwordreset {

    public $id;
    public $user_id;
    public $token;
    public $is_reset;
    public $created_at;
    public $updated_at;  
}