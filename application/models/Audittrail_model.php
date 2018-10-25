<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * CREATE TABLE `audit_trail` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `action_type` varchar(20) DEFAULT NULL,
  `module` varchar(3) DEFAULT NULL,
  `sub_module` varchar(100) DEFAULT NULL,
  `qualifiers` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `ip` varchar(100) DEFAULT NULL,
  `user_id` bigint(20) NOT NULL,
  `log_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `module` (`module`),
  KEY `user_id` (`user_id`),
  KEY `log_date` (`log_date`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
 */
class Audittrail_model extends CI_Model {

    public $table = 'audit_trail';
    
    function __construct() {
        parent::__construct();
        $this->load->database();
    }

	public function log($action_type, $module, $sub_module, $qualifiers){
        $qualifiers = json_encode($qualifiers);
        $url =  "{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
        $ip = $_SERVER['REMOTE_ADDR'];
        $log_date = date('Y-m-d H:i:s', time());
        $user_id = $this->session->userdata('uid');
        
        $sql = "INSERT INTO {$this->table} (`action_type`, `module`, `sub_module`, `qualifiers`, `url`, `ip`, `user_id`, `log_date`) ";
        $sql .= "VALUES (?, ?, ?, ?, ?, ?, ?,?)";
        $data = array($action_type, $module, $sub_module, $qualifiers, $url, $ip, $user_id, $log_date);
        
        $query = $this->db->query($sql, $data);
        
        return $query;
    }
    
    public function getLogs($from_date, $to_date, $module = "", $action_type = "", $fields = "*") {
        $sql = "SELECT " . $fields . " ";
        $sql .= "FROM " . $this->table . " a ";
        $sql .= "LEFT JOIN users u ON a.user_id = u.id ";
        $sql .= "WHERE a.module = '" . $module . "' AND a.action_type = '" . $action_type . "' ";
        $sql .= "AND DATE(log_date) BETWEEN '" . $from_date . "' AND '" . $to_date . "' ORDER BY `log_date` DESC";
        
        $query =  $this->db->query($sql);
        $data = $query->result_array();
                
        return $data;
    }
}
