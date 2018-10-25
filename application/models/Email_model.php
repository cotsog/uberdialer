<?php 

class Email_model extends CI_model {
	public $table = 'email_history';
	
	public function __construct(){
        parent::__construct();
        $this->load->database();
    }
	
	function locate_by_sparkpost_message_id($message_id) {
		try {
			$this->db->select('id');
			$query = $this->db->get_where($this->table, array('sparkpost_message_id' => $message_id));
			$db_result = $query->result_array();
			return (!empty($db_result) ? $db_result[0]['id'] : array());
		} catch(Exception $e) {
			return $e->getMessage();
		}
	}
	
	function locate_by_email_timestamp($email, $timestamp) {
		try {
			$sql = "SELECT id FROM email_history WHERE campaign_contact_id IN (SELECT id FROM campaign_contacts WHERE contact_id = (SELECT id FROM contacts WHERE email = '".$email."'))";
			if($timestamp != '') { 
				$sql .= " AND created_at = from_unixtime(".$timestamp.")";
			}
			$query = $this->db->query($sql);
			$db_result = $query->result_array();
			return (!empty($db_result) ? $db_result[0]['id'] : array());
		} catch(Exception $e) {
			return $e->getMessage();
		}
	}
	
	function update_email_history($email_history_record,$email_history_id) {
		// first, unset nulls
		// we don't want to overwrite existing tracking data for this record
		$email_history = $this->unset_nulls($email_history_record);
		try {
			$this->db->where('id', $email_history_id);
			return $this->db->update($this->table, $email_history);
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}
	
	function unset_nulls ($obj) {
		foreach($obj as $k => $v) {
			if($v == null || $v == '') {
				unset($obj->$k);
			}
		}
		return $obj;
	}
	
}