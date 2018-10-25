<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
    function IsAdminAuthorized($loggedInUserType)
    {
        if ($loggedInUserType == 'admin') {
            return true;
        } else{
            return false;
        }
           
    }

    function IsAdminTLAuthorized($loggedInUserType)
    {
        if ($loggedInUserType == 'team_leader' || $loggedInUserType == 'admin') {
            return true;
        } else{
            return false;
        }

    }

    function IsAdminManagerAuthorized($loggedInUserType)
    {
        if ($loggedInUserType == 'manager' || $loggedInUserType == 'admin') {
            return true;
        } else{
            return false;
        }

    }
    
    function IsManagerUpperManagementAuthorized($loggedInUserType)
    {
        $allowUserTypes = array(
            'manager',
            'admin', 
            'senior_manager', 
            'production_leader', 
            'nesting_leader');
        
        return in_array($loggedInUserType, $allowUserTypes);

    }
    
    function IsAdminTLManagerAuthorized($loggedInUserType)
    {
        if ($loggedInUserType == 'team_leader' || $loggedInUserType == 'manager' || $loggedInUserType == 'admin') {
            return true;
        } else{
            return false;
        }
           
    }
    
    function IsTLManagerUpperManagementAuthorized($loggedInUserType){
        $allowUserTypes = array(
            'team_leader',
            'manager',
            'admin', 
            'senior_manager', 
            'production_leader', 
            'nesting_leader');
        
        return in_array($loggedInUserType, $allowUserTypes);
    }
    
    function IsAdminTLManagerAgentAuthorized($loggedInUserType)
    {
        if ($loggedInUserType == 'team_leader' || $loggedInUserType == 'manager' || $loggedInUserType == 'admin' || $loggedInUserType == 'agent') {
            return true;
        } else{
            return false;
        }
           
    }

    function IsAdminTLManagerQAAuthorized($loggedInUserType)
    {
        if ($loggedInUserType == 'team_leader' || $loggedInUserType == 'manager' || $loggedInUserType == 'admin' || $loggedInUserType == 'qa') {
            return true;
        } else{
            return false;
        }
           
    }
    
    function IsTLManagerQAUpperManagementAuthorized($loggedInUserType){
        $allowUserTypes = array(
            'team_leader',
            'manager',
            'qa',
            'admin', 
            'senior_manager', 
            'production_leader', 
            'nesting_leader');
        
        return in_array($loggedInUserType, $allowUserTypes);
    }

    function IsAdminQAManagerAuthorized($loggedInUserType)
    {
        if ($loggedInUserType == 'qa' || $loggedInUserType == 'manager' || $loggedInUserType == 'admin') {
            return true;
        } else{
            return false;
        }

    }

    function IsAdminTLQAAuthorized($loggedInUserType)
    {
        if ($loggedInUserType == 'team_leader' || $loggedInUserType == 'admin' || $loggedInUserType == 'qa') {
            return true;
        } else{
            return false;
        }

    }

    function IsManagerAuthorized($loggedInUserType)
    {
        if ($loggedInUserType == 'manager') {
            return true;
        } else{
            return false;
        }
        
    }

    function IsTLAuthorized($loggedInUserType)
    {
        if ($loggedInUserType == 'team_leader') {
            return true;
        } else{
            return false;
        }
        
    }

    function IsDataResearchUserAuthorized($loggedInUserType)
    {
        if ($loggedInUserType == 'dataresearch_user') {
            return true;
        } else{
            return false;
        }

    }

    function IsAdminDataResearchUserAuthorized($loggedInUserType)
    {
        if ($loggedInUserType == 'dataresearch_user' || $loggedInUserType == 'admin') {
            return true;
        } else{
            return false;
        }

    }

    function getUserStatusValues(){
        $userStatusValues = array(
            "Active" =>"Active",
            "Resigned" => "Resigned",
            "Released" => "Released"
        );
        return $userStatusValues;
    }

    function getUserTierValues(){
        $userTierValues = array(
            "1" =>"1",
            "2" => "2",
            "3" => "3"
        );
        return $userTierValues;
    }
    
    function getUserProjectsValues(){
        $userProjectValues = array(
            "LeadGen" =>"LG",
            "HQL" => "HQL",
            "MDG" => "MDG",
            "CSTC" => "CSTC"
        );
        return $userProjectValues;
    }
    
    function getUserScheduleValues(){
        $userScheduleValues = array(
            "9am-6pm EST" =>"9am-6pm EST",
            "8am-5pm EST" => "8am-5pm EST",
            "10am-7pm EST" => "10am-7pm EST"
        );
        return $userScheduleValues;
    }

    // Converts array elements to a CSV string
    function getCSVFromArrayElement($array)
    {
        $array = (array)$array;
        $csv = "";
        for ($i = 0; $i < count($array); $i++) {
            $csv .= '"' . str_replace('"', ' ', $array[$i]) . '"';
            if ($i < count($array) - 1) $csv .= ",";
        }
        return $csv;
    }

    function _get_user_ip() {
        $ip_address = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ip_address = $_SERVER['HTTP_CLIENT_IP'];
        else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_X_FORWARDED']))
            $ip_address = $_SERVER['HTTP_X_FORWARDED'];
        else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ip_address = $_SERVER['HTTP_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_FORWARDED']))
            $ip_address = $_SERVER['HTTP_FORWARDED'];
        else if(isset($_SERVER['REMOTE_ADDR']))
            $ip_address = $_SERVER['REMOTE_ADDR'];
        else
            $ip_address = 'UNKNOWN';
        return $ip_address;
    }
   
    /*
     * Email Template Resource Type wise subject Line / Body content / Signature Line  Settings 
     */
   
    function EmailTemplate_WhitepaperEBook_Type($type,$resourceName,$company,$url,$discription){
        $templateValues = array(
            "SubjectLine" => "Your ".$type." from ".$company,
            "SignatureLine"=> "#AGENTNAME#",
            "Body" => "<p>Thank you for taking the time to speak with me earlier today.</p>
                    <p>As discussed, you can access the ".$type." titled ".$resourceName." from ".$company." by clicking below:</p>
                    <p><a href='".$url."'>".$url."</a></p><p>".$discription."</p>
                    <p>".$company." will be contacting you in the near future.</p>
                    <p>Thank you and have a great day.</p>
        ");
        return $templateValues;
    }
    function EmailTemplate_Webcast_Type($type,$resourceName,$company,$url,$discription){
        $templateValues = array(
            "SubjectLine" =>$company." Webinar to be held on #DATETIME#",
            "SignatureLine"=> "#AGENTNAME#",
            "Body" =>"<p>Thank you for taking the time to speak with me earlier today.</p>
                    <p>We appreciate your interest in the webinar titled ".$resourceName." from ".$company." to be held on #DATETIME#. Please click below for additional information:</p>
                    <p><a href='".$url."'>".$url."</a></p><p>".$discription."</p>
                    <p>".$company." will be contacting you in the near future.</p>
                    <p>Thank you and have a great day.</p>"
        );
       //strip_tags($templateValues['Body'],'<code><p><b>') ;
       return $templateValues;
    }
    function EmailTemplate_Other_Type($type,$resourceName,$company,$url,$discription){
        $templateValues = array(
            "SubjectLine" =>"Your resource form ".$company,
            "SignatureLine"=> "#AGENTNAME#",
            "Body" => "<p>Thank you for taking the time to speak with me earlier today.</p>
                    <p>As discussed, you can access the ".$type." titled ".$resourceName." from ".$company." by clicking below:</p>
                    <p><a href='".$url."'>".$url."</a></p><p>".$discription."</p>
                    <p>".$company." will be contacting you in the near future.</p>
                    <p>Thank you and have a great day</p>
        ");
        return $templateValues;
    }
    function EmailTemplate_WebcastOnDemand_Type($type,$resourceName,$company,$url,$discription){
        $templateValues = array(
            "SubjectLine" =>$company." On Demand Webinar",
            "SignatureLine"=> "#AGENTNAME#",
            "Body" =>"<p>Thank you for taking the time to speak with me earlier today.</p>
                    <p>We appreciate your interest in the webinar titled ".$resourceName." from ".$company." which can be accessed at your convenience by clicking the link below:</p>
                    <p><a href='".$url."'>".$url."</a></p><p>".$discription."</p>
                    <p>".$company." will be contacting you in the near future.</p>
                    <p>Thank you and have a great day.</p>
        ");
        return $templateValues;        
    }
    
    /*
     *  End Email Template Settings
    */
    function filter($element)
    {
           return substr($element, 0, -3);
    }
    
    function country_filter($element)
    {
           return substr($element, 0, -2);
    }
    
    
    function format_from_offset($from_offset)
    {
        if (strpos($from_offset, ".") === false) {
            if (strpos($from_offset, "-") === false) {
                if(strlen($from_offset)>1){return "+".$from_offset."00";}else{return "+0".$from_offset."00";}
            } else {
                if(strlen($from_offset)>2){return $from_offset."00";}else{return "-0".substr($from_offset,-1)."00";}
            }
        }
        else
        {
            $value=explode(".",$from_offset);
            if (strpos($value[0], "-") === false) {
                if(strlen($value[0])>1){$frmdata= "+".$value[0];}else{$frmdata= "+0".$value[0];}
            } else {
                if(strlen($value[0])>2){$frmdata= "-".$value[0];}else{$frmdata= "-0".$value[0];}
            }
    
            if($value[1]==5){$frmdata.="30";}else if($value[1]==75){$frmdata.="45";}
            return $frmdata;
        }
    }

    /* 
     * Set email library as per Resource Type
    */
    
    /*
    function send_mail_swift_mailer($email_to,$subject,$body_content,$selected_resource="",$user_name){
             
        if(!empty($selected_resource->time_zone)){
            $timezone= $selected_resource->time_zone;
        }
        else{
            $timezone= "US/Eastern";
        }
        date_default_timezone_set($timezone);

        $to_offset_time = date('O');

        $timezone_abbr=date('T'); // => EET

        if(date('I')==1)
        {
            $from_offset = timezone_offset_get( timezone_open( $timezone_abbr ), new DateTime() )/3600;
            $from_offset_time = format_from_offset($from_offset);
        }
        else
        {
            $from_offset_time = date('O');
        }

        $event_location = "";
        if($selected_resource->location){
            $event_location = $selected_resource->location;
        }
        $event_info = "";
        if(isset($selected_resource->event_info)){
            $event_info = $selected_resource->event_info;
        }
        $event_date = "";
        if($selected_resource->event_date){
            $event_date = $selected_resource->event_date;
            $event_date_cal = date('Ymd\THis', strtotime($event_date));
        }
        $event_start = "";
        if($selected_resource->event_start){
            $event_start = $event_date." ".$selected_resource->event_start;
            $event_start = date('Ymd\THis', strtotime($event_start));
        }
        $event_end = "";
        if($selected_resource->event_end){
            $event_end = $event_date." ".$selected_resource->event_end;
            $event_end = date('Ymd\THis', strtotime($event_end));
        }
		$currentdate=date("Ymd\TGis");
       // $email_to = "nilesh.vaghela@indusa.com";
        // attached Calander
        $ics = "BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Service Provider Inc//NONSGML kigkonsult.se iCalcreator 2.18//
METHOD:REQUEST
BEGIN:VTIMEZONE
TZID:$timezone
X-LIC-LOCATION:$timezone
BEGIN:DAYLIGHT
TZOFFSETFROM:$from_offset_time
TZOFFSETTO:$to_offset_time
TZNAME:$timezone_abbr
DTSTART:$event_start
RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=2SU
END:DAYLIGHT
BEGIN:STANDARD
TZOFFSETFROM:$from_offset_time
TZOFFSETTO:$to_offset_time
TZNAME:$timezone_abbr
DTSTART:$event_start
RRULE:FREQ=YEARLY;BYMONTH=11;BYDAY=1SU
END:STANDARD
END:VTIMEZONE
BEGIN:VEVENT
UID:20160713T070425EDT-3998irwScV@pureb2bnetwork.com
DTSTAMP:$currentdate
ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT=NEEDS-ACTION;RSVP=
 TRUE;CN=$user_name:MAILTO:$email_to
DESCRIPTION:
DTSTART;TZID=$timezone:$event_start
DTEND;TZID=$timezone:$event_end
LOCATION:$event_location
ORGANIZER:MAILTO:noreply@pureb2bnetwork.com
SEQUENCE:0
SUMMARY:$event_info
END:VEVENT
END:VCALENDAR";

         //Load Library
        require_once 'application/libraries/swiftmailer/lib/swift_required.php';
        
        // Create the SMTP configuration
        
        $transport = Swift_SmtpTransport::newInstance("smtp.sparkpostmail.com", 587, 'TLS');
        $transport->setUsername("SMTP_Injection");
        $transport->setPassword("08cccd0e3385786ec3882bcda9de1e7811828af0");

        // Create the Mailer Object using your created Transport
        $mailer = Swift_Mailer::newInstance($transport);
        //Build Message Object
        $message = Swift_Message::newInstance();
        
        $message->addPart("<p>$body_content</p>", "text/html");
        $message->setSubject($subject);
        $message->setFrom("noreply@pureb2bnetwork.com");

        //$email_to = "nilesh.vaghela@indusa.com";
        $message->setTo(array($email_to));
        
        $message->setContentType("multipart/alternative");
        $ics_attachment = Swift_Attachment::newInstance()
            ->setBody(trim($ics))
            ->setEncoder(Swift_Encoding::get7BitEncoding());
        $headers = $ics_attachment->getHeaders();
        $content_type_header = $headers->get("Content-Type");
        $content_type_header->setValue("text/calendar");
        $content_type_header->setParameters(array(
            'charset' => 'UTF-8',
            'method' => 'REQUEST'
        ));
        $headers->remove('Content-Disposition');
        $message->attach($ics_attachment);
        
        $result = $mailer->send($message);
        date_default_timezone_set( 'US/Eastern' );

        if ($result)
            return array('status'=>1);
         else
            return array('status'=>0);
        
    }*/

    function send_mail_swift_mailer($email_to,$subject,$body_content,$selected_resource="",$user_name){
            
			
			
        if(!empty($selected_resource->time_zone)){
            $timezone= $selected_resource->time_zone;
        }
        else{
            $timezone= "US/Eastern";
        }
        //date_default_timezone_set($timezone);
		$src_tz = new DateTimeZone($timezone);
//echo $src_tz->getName();
		date_default_timezone_set($src_tz->getName());
        $to_offset_time = date('O');

        $timezone_abbr=date('T'); // => EET

        if(date('I')==1)
        {
            $from_offset = timezone_offset_get( timezone_open( $timezone_abbr ), new DateTime() )/3600;
            $from_offset_time = format_from_offset($from_offset);
        }
        else
        {
            $from_offset_time = date('O');
        }

        $event_location = "";
        if($selected_resource->location){
            $event_location = $selected_resource->location;
        }
        $event_info = "";
        if(isset($selected_resource->event_info)){
            $event_info = $selected_resource->event_info;
        }
		$event_reminder = "";
                $reminder = "";
        if(isset($selected_resource->reminder)){
            $event_reminder = $selected_resource->reminder;
			$reminder = "P0DT0H".$event_reminder."M0S";
        }
        $event_date = "";
        if($selected_resource->event_date){
            $event_date = $selected_resource->event_date;
            $event_date_cal = date('Ymd\THis', strtotime($event_date));
        }
        $event_start = "";
        if($selected_resource->event_start){
            $event_start = $event_date." ".$selected_resource->event_start;
            $event_start = date('Ymd\THis', strtotime($event_start));
        }
        $event_end = "";
        if($selected_resource->event_end){
            $event_end = $event_date." ".$selected_resource->event_end;
            $event_end = date('Ymd\THis', strtotime($event_end));
        }
		$currentdate=date("Ymd\TGis");
		
		$repeat_rule = "";
		//$repeat_rule = $nl . "RRULE:FREQ=WEEKLY;COUNT=2;BYDAY=WE,FR";
		//$repeat_rule = $nl . "RRULE:FREQ=WEEKLY;UNTIL=20170228T040000Z;BYDAY=MO,TU,WE,TH,FR";
		//$repeat_rule = $nl . "RRULE:FREQ=MONTHLY;COUNT=4;BYMONTHDAY=4";

$eventStatus = "CONFIRMED"; /* TENTATIVE,CONFIRMED,CANCELLED */

$html = "<p>".$body_content."</p>";
$from_address = "noreply@pureb2bnetwork.com";
$organizer = $from_address;
$from = array($from_address);
$email_to = (string)$email_to; 
$to = array($email_to);
$nl = "\r\n";
$attendee = "";
$to_string = "";
foreach ($to as $e => $n) {
    $e = is_integer($e) ? $n : $e;
    $attendee .= "ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;";
    $attendee .= "PARTSTAT=NEEDS-ACTION;RSVP=TRUE;CN=$n;X-NUM-GUESTS=0:mailto:$e$nl";
    $to_string .= $e . ",";
}
$to_string = substr($to_string, 0, strlen($to_string) - 1);
$topic = $subject;
$summary = $subject;
$description = $event_info;
$location = $event_location;
$path = $_SERVER['DOCUMENT_ROOT'] . '/js/pagejs/appt/calls/calender.txt';
$calendar_invite = file_get_contents($path);

$calendar_invite = str_replace("__FROM__", $from_address, $calendar_invite);
$calendar_invite = str_replace("__TO__", $to_string, $calendar_invite);
$calendar_invite = str_replace("__SUBJECT__", $subject, $calendar_invite);
$calendar_invite = str_replace("__TOPIC__", $topic, $calendar_invite);
$calendar_invite = str_replace("__ORGANIZER__", $organizer, $calendar_invite);
$calendar_invite = str_replace("__ATTENDEE__", $attendee, $calendar_invite);
$calendar_invite = str_replace("__DESCRIPTION__", $description, $calendar_invite);
$calendar_invite = str_replace("__LOCATION__", $location, $calendar_invite);
$calendar_invite = str_replace("__SUMMARY__", $summary, $calendar_invite);
$calendar_invite = str_replace("__EVENT_STATUS__", $eventStatus, $calendar_invite);
$calendar_invite = str_replace("__HTML__", $html, $calendar_invite);
$calendar_invite = str_replace("__DTSTART__", $event_start, $calendar_invite);
$calendar_invite = str_replace("__DTEND__", $event_end, $calendar_invite);
$calendar_invite = str_replace("__DTSTAMP__", $currentdate, $calendar_invite);
$calendar_invite = str_replace("__REPEAT_RULE__", $repeat_rule, $calendar_invite);
$calendar_invite = str_replace("__BOUNDARY__", md5(time()) . rand(0, 99999999), $calendar_invite);
$calendar_invite = str_replace("__TIMEZONE__", $timezone, $calendar_invite);
$calendar_invite = str_replace("_FROM_OFFSET_TIME_", $from_offset_time, $calendar_invite);
$calendar_invite = str_replace("_TO_OFFSET_TIME_", $to_offset_time, $calendar_invite);
$calendar_invite = str_replace("_TIMEZONE_ABBR_", $timezone_abbr, $calendar_invite);
$calendar_invite = str_replace("_REMINDER_", $reminder, $calendar_invite);

//Load Library
        require_once 'application/libraries/swiftmailer/lib/swift_required.php';
        
        // Create the SMTP configuration

$transport = Swift_SmtpTransport::newInstance("smtp.sparkpostmail.com", 587, 'TLS');
$transport->setUsername("SMTP_Injection");
$transport->setPassword("08cccd0e3385786ec3882bcda9de1e7811828af0");
$swift = Swift_Mailer::newInstance($transport);

$messageObject = new Swift_MyMessage();
$messageObject->setFrom($from);
$messageObject->setTo($to);
$messageObject->setRawContent($calendar_invite);


if ($recipients = $swift->send($messageObject, $failures)) {
    date_default_timezone_set( 'US/Eastern' );
   return array('status'=>1);
}
else {
    date_default_timezone_set( 'US/Eastern' );
   return array('status'=>0);
    /* echo "There was an error:\n";
    print_r($failures);*/
}
        
    }
    
    
    function send_email_sparkpost($email_to,$subject,$body_content,$sender='noreply@pureb2bnetwork.com'){
       
        $CI =& get_instance();       
        $CI->load->library('Sparkpostapi'); // load library 
       
        $mail = new Sparkpostapi();
        
        // set url & api key 
        $mail->api_call('https://api.sparkpost.com/api/v1/transmissions','22fa82786b023379834ff8f38a510ea2f6012024');

        $mail->from(array(
            'email' => $sender,
           // 'name'  => 'Sender Name'
        ));
        
        //$email_to =  array("nilesh.vaghela@indusa.com",'komal@mailinator.com');
        $mail->subject($subject);
        $mail->html($body_content);
        $mail->setTo(array($email_to));
      
        try{
            $response = $mail->send();
            if(!empty($response))
                  $result['data'] = $response->results->id;

            $result['status'] = 1;
        }
        catch (Exception $e) {
            $result['status'] = 0;
            $result['error_msg'] = $e;  
        }
        $mail->close();

        return $result;
    }

	function format_appt_time($start_date,$timezone)
	{		
		$start_date=date('h:i A /m/d/Y', strtotime($start_date));
		$start_time=explode("/",$start_date);
		if($timezone=="US/Eastern"){$disp_tz="EST";}
		if($timezone=="US/Central"){$disp_tz="CST";}
		if($timezone=="US/Mountain"){$disp_tz="MST";}
		if($timezone=="US/Pacific"){$disp_tz="PST";}
		return $start_time[0]." ".$disp_tz;
	}


