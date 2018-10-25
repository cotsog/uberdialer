<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Dashboards_model extends CI_Model
{
    public $campaign_lists = 'campaign_lists';
    public $voip_communications = 'voip_communications';
    
    function __construct()
    {
        parent::__construct();
        $this->load->database();
    }
    
    /* 
     * Agent Dashboard Region Start 
     */
    public function  get_assign_campaign_by_agent($agentID){
        $sql = "SELECT c.id , c.name FROM `campaign_contacts` cc  
            LEFT JOIN `call_disposition_history` al ON al.campaign_contact_id = cc.id 
            LEFT JOIN `campaigns` c ON c.id = cc.`campaign_id` 
            WHERE al.user_id = ?  
            AND DATE_FORMAT(cc.updated_at,'%Y-%m-%d') = CURDATE() AND al.call_history_id > 0
            GROUP BY c.id " ;
        
        $query = $this->db->query($sql,array($agentID));
        return $query->result_array();
        
    }
    
    public function getAgentDials($logedInUser,$campaignID=0){
        $sql = "SELECT  COUNT(*) AS TotalDials , DATE_FORMAT(created_at, '%l %p') AS dial_hour 
    FROM call_history WHERE add_diff = 0 and user_id = ? AND (DATE_FORMAT(created_at,'%Y-%m-%d') = CURDATE()) AND campaign_id = ? GROUP BY (HOUR(created_at))";
        $query = $this->db->query($sql,array($logedInUser,$campaignID));
        return $query->result_array();
    }
     
    // Fetch total lead / Approved Lead / Rejected Lead recods from Database 
    public function get_agent_leads($logedInUser,$campaignID=0,$customdDate,$status=0)
    {
        $sql = "SELECT COUNT(*) AS TotalLeads , DATE_FORMAT(lh.".$customdDate.", '%l %p') AS leads_hour FROM `lead_history` lh ";
              
        if($status != 'Pending' ){

             $sql .=  " LEFT JOIN ( SELECT a1.*
                            FROM call_disposition_history AS a1
                            WHERE id IN ( SELECT MAX(a.id) FROM  call_disposition_history  AS a
                            LEFT JOIN lead_history AS a2
                                ON a.campaign_contact_id = a2.campaign_contact_id WHERE a2.status = '".$status."'  GROUP BY a2.id ) ORDER BY a1.id DESC
                                ) AS cdh ON cdh.campaign_contact_id =  lh.campaign_contact_id";
        }else{
            $sql .=  "Left join call_disposition_history cdh on cdh.campaign_contact_id = lh.campaign_contact_id  AND DATE_FORMAT(cdh.created_at,'%Y-%m-%d') = CURDATE() ";
        }                
        
        $sql .= " WHERE cdh.user_id = ".$logedInUser."   AND DATE_FORMAT(lh.".$customdDate.",'%Y-%m-%d') = CURDATE() ";
        if($status != 'Pending'){
           $sql .= " AND lh.status = '".$status."' AND cdh.call_history_id >0";
        }else{
            $sql .=  ' AND cdh.call_disposition_id = 1 AND cdh.call_history_id >0';
        }
        if($campaignID){
            $sql .= " AND lh.campaign_id = ".$campaignID;
        }
        $sql .= " GROUP BY (HOUR(lh.".$customdDate."))";
        $query = $this->db->query($sql);
       //echo $sql;exit;
        return $query->result_array();
    }
    
    public function get_agent_followupleads($logedInUser,$campaignID=0){
        $sql = "SELECT COUNT(*) AS TotalFollowUp FROM `lead_history` lh LEFT JOIN `call_disposition_history` cdh ON cdh.campaign_contact_id = lh.campaign_contact_id 
            and cdh.id = (select max(id) from call_disposition_history where campaign_contact_id = lh.campaign_contact_id AND call_history_id > 0)
                WHERE cdh.user_id = ?  AND lh.status = 'Follow-up' AND lh.campaign_id = ? and cdh.call_history_id > 0"; 
        $query = $this->db->query($sql,array($logedInUser,$campaignID));        
        $array = $query->result();
        if (!empty($array)) {
            $array = $array[0];
        }
        return $array;       
    }
    
     /* 
     * Agent Dashboard Region Start 
     */
    
    
    /* Dev_NV Start*/
    
    function get_campaign_dials_by_day($from_date, $to_date,$loggedUserID,$user_type) {
        $logged_tm_office = $this->session->userdata('telemarketing_offices');

        $assign_team_id = " cl.`user_id` = '".$loggedUserID."'";
        if (($user_type == 'manager' && ($loggedUserID == $this->session->userdata('uid'))) || $user_type == 'admin') {
            $assign_team_id = " 1= 1 ";
        }
        $team_leader_id = " 1= 1 ";
        if (($user_type == 'manager' && ($loggedUserID == $this->session->userdata('uid'))) || $user_type == 'admin') {
            $team_leader_id = " 1= 1 ";
        }
        $campaign_assign = " (ca.agent_id = al.agent_id || ca.teamleader_id = '".$loggedUserID."') ";
        if (($user_type == 'manager' && ($loggedUserID == $this->session->userdata('uid'))) || $user_type == 'admin') {
            $campaign_assign = " 1= 1 ";
        }
        $dispo_count_select = "";
        $dispo_count_join = "";
        if($user_type == 'admin'){
            $dispo_count_select = "COALESCE(dispo.dispo_dials, 0) AS total_dials_dispo_count,";
            $dispo_count_join = "LEFT JOIN (SELECT 
                        ch.campaign_id,
                            DATE(cdh.created_at) AS created_at,
                            ch.user_id,
                            COUNT(DISTINCT (ch.id)) AS dispo_dials,
                            cl.user_id AS tl_id
                    FROM
                        call_disposition_history cdh
                        join call_history ch on ch.id = cdh.call_history_id
                    JOIN `campaigns` c ON c.id = ch.campaign_id
                    LEFT JOIN `campaign_assign_tl` cl ON cl.`campaign_id` = c.id
                    WHERE
                        DATE(cdh.created_at) BETWEEN '{$from_date}' AND '{$to_date}'
                    GROUP BY DATE(cdh.created_at) , ch.`user_id`) dispo ON dispo.user_id = al.agent_id
                        AND dispo.created_at = DATE(ls.created_at)";
        }
        $sql = "SELECT lh.campaign_id AS campaign_id,
                DATE(ls.created_at) AS today_date,
                COALESCE(chd.dials,0) AS today_dials_count,
                COUNT(DISTINCT agent_l.id) AS today_leads_count ,
                COUNT(DISTINCT agent_approve.id) AS today_approve_leads,
                {$dispo_count_select}
                al.agent_id, CONCAT(u.first_name,' ',u.last_name) AS full_name
                FROM `agent_lead` al
                LEFT JOIN lead_history lh ON al.lead_id = lh.id
                LEFT JOIN `lead_status` ls ON lh.id = ls.lead_history_id
                LEFT JOIN
                    (SELECT a1.id,a1.agent_id,a1.lead_id, a1.submitted_at
                    FROM agent_lead AS a1
                    WHERE id IN ( SELECT MAX(a.id) FROM agent_lead AS a
                    INNER JOIN lead_history AS a2 ON a.lead_id = a2.id AND a2.call_disposition_id = '1' GROUP BY a2.id )
                    ORDER BY a1.id DESC )
                    AS agent_l ON lh.id = agent_l.lead_id AND DATE(agent_l.submitted_at) = DATE(ls.created_at) AND al.agent_id = agent_l.agent_id
                LEFT JOIN 
                    (SELECT b1.id,b1.agent_id,b1.lead_id
                    FROM agent_lead AS b1
                    WHERE id IN ( SELECT MAX(b.id)
                    FROM agent_lead AS b
                    INNER JOIN lead_history AS b2 ON b.lead_id = b2.id GROUP BY b2.id )
                    ORDER BY b1.id DESC ) AS agent_approve
                    ON lh.id = agent_approve.lead_id  AND ls.status = 'Approve' AND al.agent_id = agent_approve.agent_id
                LEFT JOIN `campaign_assign` ca ON lh.campaign_id = ca.campaign_id  AND $campaign_assign
                {$dispo_count_join}
                LEFT JOIN
                    (SELECT ch.campaign_id,DATE(ch.created_at) AS created_at,ch.user_id,COUNT(DISTINCT(ch.id)) AS dials,cl.user_id AS tl_id
                        FROM call_history ch
                        JOIN `campaigns` c ON c.id = ch.campaign_id
                        LEFT JOIN `campaign_assign_tl` cl ON cl.`campaign_id`= c.id
                        AND $assign_team_id
                        WHERE DATE(ch.created_at) BETWEEN '".$from_date."' AND '".$to_date."'
                        GROUP BY  DATE(ch.created_at), ch.`user_id`
                    ) chd ON chd.user_id=al.agent_id AND chd.created_at = DATE(ls.created_at)
                JOIN users u ON al.agent_id = u.id AND (u.parent_id = '".$loggedUserID."' || u.id = '".$loggedUserID."') AND u.`status` = 'Active'
                LEFT JOIN campaigns c ON lh.campaign_id = c.id
                LEFT JOIN `campaign_tm_offices` cto ON c.id = cto.campaign_id

                    WHERE DATE(ls.created_at) BETWEEN '".$from_date."' AND '".$to_date."'
                    AND $team_leader_id and c.business = '{$this->app}' AND c.module_type = '".$this->app_module_type."'";
        if ($user_type != 'admin' && $user_type != 'qa') {
            $sql .= " AND cto.tm_office = '" . $logged_tm_office . "' ";
        }
        $sql .= " GROUP BY DATE(ls.created_at),al.agent_id";
        $sql.=' ORDER BY DATE(ls.created_at) DESC ';
        $query = $this->db->query($sql);
        return $query->result_array();
    }
    
    function getCampaignDialsByDay($from_date, $to_date,$loggedUserID,$user_type) {
        $logged_tm_office = $this->session->userdata('telemarketing_offices');

        
        $agent_ids = $loggedUserID;
        $join_campaign_office = "";
        //get agents base on logged user id
        $agents_sql = "select group_concat(id) as agents from users where parent_id = ?";
        $query = $this->db->query($agents_sql,array($loggedUserID));        
        $array = $query->result();
        if (!empty($array[0]->agents)) {
            $agent_ids = $array[0]->agents;
            $agent_ids .= ",{$loggedUserID}";
        }else{
            $agent_ids = $loggedUserID;
        }
        $office_where = "";
        if ($user_type != 'admin' && $user_type != 'qa') {
            $join_campaign_office = " JOIN `campaign_tm_offices` cto ON c.id = cto.campaign_id ";
            $office_where = " AND cto.tm_office = '" . $logged_tm_office . "' ";
        }
        $sql = "SELECT 
                dials.*,ifnull(dispo_counts.today_leads_count,0) as today_leads_count, ifnull(dispo_counts.today_approve_leads,0) as today_approve_leads
                , CONCAT(u.first_name,' ',u.last_name) AS full_name 
            FROM
                (SELECT 
                    ch.campaign_id,
                        DATE(ch.created_at) AS today_date,
                        ch.user_id as agent_id,
                        COUNT(DISTINCT ( case when ch.add_diff =0 then ch.id else null end)) AS today_dials_count,
                        COUNT(DISTINCT (cdh.call_history_id)) AS total_dials_dispo_count,
                        cl.user_id AS tl_id
                FROM
                    call_history ch
                LEFT JOIN call_disposition_history cdh ON cdh.call_history_id = ch.id
                LEFT JOIN `campaign_assign_tl` cl ON cl.`campaign_id` = ch.campaign_id
                    AND 1 = 1
                WHERE
                     ch.created_at >= '{$from_date} 00:00:00' AND ch.created_at <= '{$to_date} 23:59:59'
                        and ch.user_id in ({$agent_ids}) 
                        and ch.module_type = 'tm'
                        AND ch.campaign_id > 0
                GROUP BY DATE(ch.created_at) , ch.`user_id`) AS dials
                LEFT JOIN
                (SELECT 
                    DATE(lh.updated_at) AS updated_at,
                        cdh.user_id as agent_id,
                        SUM(CASE
                            WHEN cdh.call_disposition_id = 1 THEN 1
                            ELSE 0
                        END) AS today_leads_count,
                        SUM(CASE
                            WHEN lh.status = 'Approve' THEN 1
                            ELSE 0
                        END) AS today_approve_leads
                FROM
                  call_disposition_history cdh
               LEFT JOIN lead_history lh ON cdh.campaign_contact_id = lh.campaign_contact_id  
					AND (lh.status = 'Approve'
						OR cdh.call_disposition_id = 1)
						AND cdh.id IN (SELECT 
							MAX(a.id)
						FROM
							call_disposition_history AS a
						WHERE
                        a.created_at >= '{$from_date} 00:00:00' AND  a.created_at <= '{$to_date} 23:59:59'
								AND a.call_disposition_id = 1 AND a.call_history_id > 0
						GROUP BY a.campaign_contact_id)
                WHERE
                     lh.updated_at >= '{$from_date} 00:00:00' AND lh.updated_at <= '{$to_date} 23:59:59'
                     and cdh.user_id in ({$agent_ids}) 
                     and cdh.call_history_id > 0   
                GROUP BY DATE(lh.updated_at) , cdh.user_id) AS dispo_counts ON dispo_counts.agent_id = dials.agent_id and dispo_counts.updated_at = dials.today_date 
                join users u on u.id = dials.agent_id
                JOIN campaigns c ON dials.campaign_id = c.id
                -- JOIN `campaign_assign` ca ON dials.campaign_id = ca.campaign_id and dials.agent_id = ca.agent_id
                {$join_campaign_office}
                where c.module_type = 'tm'
            ";
        if ($user_type != 'admin' && $user_type != 'qa') {
            $sql .= " AND cto.tm_office = '" . $logged_tm_office . "' ";
        }
        $sql .=" order by dials.today_date desc";
        $query = $this->db->query($sql);
        
        return $query->result_array();
    }

    function get_dials_campaign_by_user($date,$user_id,$loggedUserID,$user_type) {
        $logged_tm_office = $this->session->userdata('telemarketing_offices');

        $assign_team_id = " cl.`user_id` = '".$loggedUserID."' ";
        if (($user_type == 'manager' && ($loggedUserID == $this->session->userdata('uid'))) || $user_type == 'admin') {
            $assign_team_id = " 1= 1 ";
        }
        $team_leader_id = " 1= 1 ";
        if (($user_type == 'manager' && ($loggedUserID == $this->session->userdata('uid'))) || $user_type == 'admin') {
            $team_leader_id = " 1= 1 ";
        }
        $campaign_assign = " (ca.agent_id = al.agent_id || ca.teamleader_id = '".$loggedUserID."') ";
        if (($user_type == 'manager' && ($loggedUserID == $this->session->userdata('uid'))) || $user_type == 'admin') {
            $campaign_assign = " 1= 1 ";
        }
        $sql = "SELECT lh.campaign_id,c.eg_campaign_id,c.name,c.type,
                COALESCE(chd.dials,0) AS today_dial_count,
                CONCAT(u.first_name,' ',u.last_name) AS full_name,
                COUNT(DISTINCT agent_l.id) AS today_lead_count,
                COUNT(DISTINCT agent_approve.id) AS today_approve_lead_count
                FROM lead_history lh
                JOIN `agent_lead` al ON al.lead_id = lh.id
                LEFT JOIN `lead_status` ls ON lh.id = ls.lead_history_id
                LEFT JOIN
                    (SELECT a1.id,a1.agent_id,a1.lead_id, a1.submitted_at
                    FROM agent_lead AS a1
                    WHERE id IN ( SELECT MAX(a.id) FROM agent_lead AS a
                    INNER JOIN lead_history AS a2 ON a.lead_id = a2.id AND a2.call_disposition_id = '1' GROUP BY a2.id )
                    ORDER BY a1.id DESC ) AS agent_l ON lh.id = agent_l.lead_id AND DATE(agent_l.submitted_at) = DATE(ls.created_at)
                    AND al.agent_id = agent_l.agent_id
                LEFT JOIN
                    (SELECT b1.id,b1.agent_id,b1.lead_id
                    FROM agent_lead AS b1
                    WHERE id IN ( SELECT MAX(b.id)
                    FROM agent_lead AS b
                    INNER JOIN lead_history AS b2 ON b.lead_id = b2.id GROUP BY b2.id )
                    ORDER BY b1.id DESC ) AS agent_approve
                ON lh.id = agent_approve.lead_id  AND ls.status = 'Approve' AND al.agent_id = agent_approve.agent_id
                JOIN users u ON al.agent_id = u.id AND u.status = 'Active'
                LEFT JOIN `campaign_assign` ca ON lh.campaign_id = ca.campaign_id  AND $campaign_assign
                JOIN campaigns c ON lh.campaign_id = c.id AND (u.parent_id = '".$loggedUserID."' || u.id = '".$loggedUserID."')
                LEFT JOIN `campaign_tm_offices` cto ON c.id = cto.campaign_id
                LEFT JOIN (SELECT ch.campaign_id,DATE(ch.created_at) AS created_at,ch.user_id,COUNT(DISTINCT(ch.id)) AS dials, cl.user_id AS tl_id
                    FROM call_history ch
                    JOIN `campaigns` c ON c.id = ch.campaign_id LEFT JOIN `campaign_assign_tl` cl ON cl.`campaign_id`= c.id  AND $assign_team_id
                    WHERE DATE(ch.created_at) = '".$date."'
                    GROUP BY ch.campaign_id,ch.`user_id`
                ) chd ON chd.campaign_id=lh.campaign_id AND chd.user_id=al.agent_id
                            WHERE DATE(ls.created_at) = '".$date."' AND  al.agent_id = '".$user_id."' AND $team_leader_id
                            AND c.module_type = '".$this->app_module_type."' ";

        if ($user_type != 'admin' && $user_type != 'qa') {
            $sql .= " AND cto.tm_office = '" . $logged_tm_office . "' ";
        }
        $sql .=" GROUP BY lh.campaign_id";
        $query = $this->db->query($sql);
        return $query->result();
    }

    function get_dials_campaign_by_user2($date,$user_id,$loggedUserID,$user_type) {
        $sql = "SELECT 
                dials.*,c.eg_campaign_id,c.name,c.type,ifnull(dispo_counts.today_lead_count,0) as today_lead_count, ifnull(dispo_counts.today_approve_lead_count,0) as today_approve_lead_count
            FROM
                (SELECT 
                    ch.campaign_id,
                        DATE(ch.created_at) AS today_date,
                        ch.user_id as agent_id,
                        COUNT(DISTINCT (ch.id)) AS today_dial_count
                FROM
                    call_history ch
                WHERE
                        ch.created_at >= '{$date} 00:00:00' 
                        and ch.created_at <= '{$date} 23:59:59'
                        AND ch.add_diff = 0
                        and ch.user_id  = {$user_id}
                        and ch.module_type = 'tm'
                GROUP BY DATE(ch.created_at) , ch.`user_id`, ch.campaign_id) AS dials
                    LEFT JOIN
                (SELECT
                    lh.campaign_id,
                    DATE(lh.updated_at) AS updated_at,
                        cdh.user_id as agent_id,
                        SUM(CASE
                            WHEN cdh.call_disposition_id = 1 THEN 1
                            ELSE 0
                        END) AS today_lead_count,
                        SUM(CASE
                            WHEN lh.status = 'Approve' THEN 1
                            ELSE 0
                        END) AS today_approve_lead_count
                FROM
                  call_disposition_history cdh 
                JOIN lead_history lh ON cdh.campaign_contact_id = lh.campaign_contact_id  
                    AND (lh.status = 'Approve'
                        OR cdh.call_disposition_id = 1)
                        AND cdh.id IN (SELECT 
                            MAX(a.id)
                        FROM
                            call_disposition_history AS a
                        WHERE
                            a.created_at >= '{$date} 00:00:00' and a.created_at <= '{$date} 23:59:59'
                                AND a.call_disposition_id = 1 
                            AND a.call_history_id >0
                        GROUP BY a.campaign_contact_id)
                WHERE
                     lh.updated_at >= '{$date} 00:00:00' and lh.updated_at <= '{$date} 23:59:59'
                        and cdh.user_id = {$user_id}
                GROUP BY DATE(lh.updated_at) , cdh.user_id, lh.campaign_id) as dispo_counts on dials.campaign_id = dispo_counts.campaign_id
                join campaigns c on dials.campaign_id = c.id";
        $query = $this->db->query($sql);
        return $query->result();

    }

    function get_campaign_by_agent($from_date, $to_date,$agentID,$loggedUserID,$user_type) {
        $logged_tm_office = $this->session->userdata('telemarketing_offices');
            $team_leader_id = " 1= 1 ";
        //$team_leader_id = " cl.`user_id` = '".$loggedUserID."' ";
        if (($user_type == 'manager' && ($loggedUserID == $this->session->userdata('uid'))) || $user_type == 'admin') {
            $team_leader_id = " 1= 1 ";
        }
            $campaign_assign = " 1= 1 ";
        //$campaign_assign = " (ca.agent_id IN ($agentID) || ca.teamleader_id IN ($agentID)) ";
        if (($user_type == 'manager' && ($loggedUserID == $this->session->userdata('uid'))) || $user_type == 'admin') {
            $campaign_assign = " 1= 1 ";
        }

        //AND c.status = 'active'
        $sql = "SELECT lh.campaign_id,c.name,c.type
                FROM `agent_lead` al
                JOIN lead_history lh ON al.lead_id = lh.id
                JOIN campaigns c ON lh.campaign_id = c.id
                LEFT JOIN `campaign_tm_offices` cto ON c.id = cto.campaign_id
                LEFT JOIN `campaign_assign_tl` cl ON cl.`campaign_id`= c.id AND $team_leader_id
                LEFT JOIN `campaign_assign` ca ON lh.campaign_id = ca.campaign_id
                WHERE DATE(al.submitted_at) BETWEEN '".$from_date."' AND '".$to_date."' AND $campaign_assign
                AND al.agent_id IN ($agentID) AND c.module_type = '".$this->app_module_type."'";

         if ($user_type != 'admin' && $user_type != 'qa') {
             $sql .= " AND cto.tm_office = '" . $logged_tm_office . "' ";
         }
        $sql .="GROUP BY lh.campaign_id";
        $sql.=' ORDER BY DATE(al.submitted_at) DESC ';

        $query = $this->db->query($sql);
        return $query->result_array();
    }

    function get_agent_detail_by_campaign($from_date, $to_date,$agentID,$loggedUserID,$user_type) {
        //$team_leader_id = " c.assign_team_id = $loggedUserID ";
        $logged_tm_office = $this->session->userdata('telemarketing_offices');
        //$team_leader_id = " cl.`user_id` = '".$loggedUserID."' ";
            $team_leader_id = " 1= 1 ";
        if (($user_type == 'manager' && ($loggedUserID == $this->session->userdata('uid'))) || $user_type == 'admin') {
            $team_leader_id = " 1= 1 ";
        }
            $campaign_assign = " 1= 1 ";
        //$campaign_assign = " (ca.agent_id IN ($agentID) || ca.teamleader_id IN ($agentID)) ";
        if (($user_type == 'manager' && ($loggedUserID == $this->session->userdata('uid'))) || $user_type == 'admin') {
            $campaign_assign = " 1= 1 ";
        }

        // -- AND ca.agent_id = al.agent_id
        // AND c.status = 'active'

        $sql = "SELECT COALESCE(chd.dials,0) AS today_dial_count,
                    lh.campaign_id,c.name,c.type,
                    CONCAT(u.first_name,' ',u.last_name) AS full_name,
                    COUNT(DISTINCT agent_l.id) AS today_lead_count,DATE(ls.created_at) AS today_date,
                    COUNT(DISTINCT agent_approve.id) AS today_agent_approve_lead_count
                    FROM lead_history lh
                    JOIN `agent_lead` al ON al.lead_id = lh.id
                    LEFT JOIN `lead_status` ls ON lh.id = ls.lead_history_id
                    LEFT JOIN
                        (SELECT a1.id,a1.agent_id,a1.lead_id, a1.submitted_at
                            FROM agent_lead AS a1
                            WHERE id IN ( SELECT MAX(a.id) FROM agent_lead AS a
                            INNER JOIN lead_history AS a2 ON a.lead_id = a2.id AND a2.call_disposition_id = '1' GROUP BY a2.id )
                            ORDER BY a1.id DESC ) AS agent_l ON lh.id = agent_l.lead_id AND DATE(agent_l.submitted_at) = DATE(ls.created_at)
                            AND al.agent_id = agent_l.agent_id

                    LEFT JOIN 
                        (SELECT b1.id,b1.agent_id,b1.lead_id
                        FROM agent_lead AS b1
                        WHERE id IN ( SELECT MAX(b.id)
                        FROM agent_lead AS b
                        INNER JOIN lead_history AS b2 ON b.lead_id = b2.id GROUP BY b2.id )
                        ORDER BY b1.id DESC ) AS agent_approve
                    ON lh.id = agent_approve.lead_id  AND ls.status = 'Approve' AND al.agent_id = agent_approve.agent_id
                    
                    JOIN users u ON al.agent_id = u.id AND u.status = 'Active'

                    LEFT JOIN `campaign_assign` ca ON lh.campaign_id = ca.campaign_id
                    JOIN campaigns c ON lh.campaign_id = c.id
                    LEFT JOIN `campaign_tm_offices` cto ON c.id = cto.campaign_id
                    LEFT JOIN `campaign_assign_tl` cl ON cl.`campaign_id`= c.id AND $team_leader_id
                   LEFT JOIN (SELECT ch.campaign_id,DATE(ch.created_at) AS created_at,ch.user_id,COUNT(DISTINCT(ch.id)) AS dials
                        FROM call_history ch
                        JOIN `campaigns` c ON c.id = ch.campaign_id LEFT JOIN `campaign_assign_tl` cl ON cl.`campaign_id`= c.id  AND $team_leader_id
                        WHERE DATE(ch.created_at) BETWEEN '".$from_date."' AND '".$to_date."'
                        GROUP BY ch.campaign_id,ch.`user_id`,DATE(ch.created_at)
                    ) chd ON chd.campaign_id=lh.campaign_id AND chd.user_id=al.agent_id AND chd.created_at = DATE(ls.created_at)
                    WHERE DATE(ls.created_at) BETWEEN '".$from_date."' AND '".$to_date."' AND $campaign_assign AND al.agent_id IN ($agentID)
                    AND c.module_type = '".$this->app_module_type."'";

          if ($user_type != 'admin' && $user_type != 'qa') {
             $sql .= " AND cto.tm_office = '" . $logged_tm_office . "' ";
         }
         $sql .= " GROUP BY DATE(ls.created_at),lh.campaign_id,al.agent_id";
        $sql.=' ORDER BY lh.campaign_id ASC ';

        $query = $this->db->query($sql);
        return $query->result_array();
    }

    public function getAllManagerList(){
        $sql = "SELECT id,email,CONCAT(first_name,' ',last_name) AS first_name,user_type FROM users WHERE user_type = 'manager' ORDER BY first_name ASC";
        $query = $this->db->query($sql);
        $array = $query->result();
        return $array;
    }

    /* Dev_NV End */
    
    function get_agent_calls_today($loggedInUserType, $logged_tm_office, $tm_team_leaders = ''){
        $call_filter = ($this->plivo_switch) ? 'AND (ch.agent_id = u.id OR ch.user_id = u.id)) AS count_calls_today,' : 'AND (ch.agent_id = u.id OR ch.user_id = u.id)) AS count_calls_today,';
        $call_table = ($this->plivo_switch) ? "JOIN {$this->voip_communications} pc ON pc.call_history_id = ch.id" : '' ;

        $sql = "SELECT 
            u.id,
            u.telemarketing_offices AS office,
            CONCAT(u.first_name, ' ', u.last_name) AS agent,
            c.eg_campaign_id AS campaign_id,
            c.name AS campaign_name,
            (SELECT COUNT(ch.id)
                FROM
                    `call_history` ch
                     {$call_table}
                WHERE
                    DATE_FORMAT(ch.created_at, '%Y-%m-%d') = DATE(NOW())
                        AND ch.campaign_id = c.id
                        {$call_filter}
            (SELECT 
                IF(ch.call_start_datetime IS NULL AND ch.call_end_datetime IS NULL,
                    CONCAT(FLOOR(TIMESTAMPDIFF(SECOND,
                                            ch.created_at,
                                            NOW()) / 60),
                                ':',
                                CEIL((MOD(TIMESTAMPDIFF(SECOND,
                                                ch.created_at,
                                                NOW()) / 60,
                                            1) * 100) * 0.60)),
                    CONCAT(FLOOR(TIMESTAMPDIFF(SECOND,
                                            ch.call_end_datetime,
                                            NOW()) / 60),
                                ':',
                                CEIL((MOD(TIMESTAMPDIFF(SECOND,
                                                ch.call_end_datetime,
                                                NOW()) / 60,
                                            1) * 100) * 0.60)))
                FROM
                    `call_history` ch
                       {$call_table}
                WHERE
                    DATE_FORMAT(ch.created_at, '%Y-%m-%d') = DATE(NOW())
                        AND ch.campaign_id = c.id
                        AND ch.user_id = u.id order by ch.id desc limit 1) AS latest_call_duration,
             (SELECT CONCAT(case when ch.call_start_datetime IS NULL AND ch.call_end_datetime IS NULL then 'Incall' else 'Idle' end,'_',ch.id)
                        FROM
                            `call_history` ch
                                {$call_table}
                        WHERE
                            DATE_FORMAT(ch.created_at, '%Y-%m-%d') = DATE(NOW())
                                AND ch.campaign_id = c.id
                                AND ch.user_id = u.id
                                order by ch.id desc limit 1) AS agent_state
        FROM
            user_sessions us
                JOIN
            users u ON u.id = us.user_id
                LEFT JOIN
            campaign_assign ca ON ca.agent_id = us.user_id
                JOIN
            campaigns c ON c.id = ca.campaign_id
        WHERE
            us.is_session_active = 1
                AND u.user_type = 'agent'
                AND concat(u.id,'-',c.id) IN (SELECT 
                    concat(user_id,'-',campaign_id)
                FROM
                    agent_sessions
                WHERE
                    (session_end > NOW() - INTERVAL 15 MINUTE
                        OR session_end IS NULL)
                        AND is_session_deactive <> 1
                GROUP BY user_id)
                AND us.last_activity > NOW() - INTERVAL 1 HOUR";
        if($loggedInUserType == 'manager' || $loggedInUserType == 'team_leader'){
            $subOffices = $this->session->userdata('sub_telemarketing_offices');
            $subOfficeFilter = "";
            if(!empty($subOffices)){
                foreach ($subOffices as $subTmOffice) {
                    $subOfficeFilter .= " OR u.telemarketing_offices = '" . $subTmOffice . "'";
                }
            }
            $sql .= " AND (u.telemarketing_offices = '{$logged_tm_office}' {$subOfficeFilter} ) ";
        }
        if($tm_team_leaders != '') {
            $sql .= " AND u.parent_id IN ({$tm_team_leaders})";
        }

        $sql .= " and u.module in ('tm','tm,appt') and c.module_type = 'tm' ";
        
        $query = $this->db->query($sql);
        $array = $query->result();
        return $array;
    }
    
    function get_agent_counts($loggedInUserType, $logged_tm_office, $tm_team_leaders = ''){
        $agent_counts = array();
        $get_agents = "SELECT COUNT(u.id) AS agent_time_in, GROUP_CONCAT(u.id) AS agents FROM user_sessions us JOIN users u ON u.id = us.user_id 
            WHERE us.is_session_active = 1 AND u.user_type = 'agent' AND us.last_activity > NOW() - INTERVAL 1 HOUR";
        if($loggedInUserType == 'manager' || $loggedInUserType == 'team_leader'){
            $get_agents .= " AND u.telemarketing_offices = '{$logged_tm_office}'";
        }
        if($tm_team_leaders != '') {
            $get_agents .= " AND u.parent_id IN ({$tm_team_leaders})";
        }
        $query = $this->db->query($get_agents);
        $agents = $query->result();
        if(!empty($agents) && $agents[0]->agents <> NULL){
            $agent_counts['agent_time_in'] = $agents[0]->agent_time_in;
            return $agent_counts;
        }
        return null;
        
    }
    
}
