<?php 

class Email extends MY_Controller {
	function __construct() {
		parent::__construct();
		$protected_methods = array('update_sparkpost_bounce_data');
        if (!in_array($this->router->method, $protected_methods)) {
            if (!$this->session->userdata('uid')) {
                $this->session->set_flashdata('prev_action', 'loginfail');
                redirect('/login');
            }
        }
		$this->load->model('Email_model');
	}
	
	/**
	* Main function for receiving and processing webhooks from sparkpost
	*
	* @var $raw_post_data JSON object of post data
	* @var $decoded_post_data JSON-decoded object of post data
	* @var $sparkpost_message_id String/Numeric The message id being processed
	* @var $email_history Class object containing email_history update data
	* Of note, we're specifically setting the following objects:
	* sparkpost_event_type The message event type (bounce, delivery, delay, etc)
	* sparkpost_message_datetime UNIX timestamp assigned to the message
	* sparkpost_bounce_code Numeric code assigned to bounce types
	* sparkpost_message_event JSON-encoded version of the entire inner post data
	* updated_at Datetime in Y-m-d H:i:s Indicate that the email_history record was updated at this time
	*/
	public function update_sparkpost_bounce_data() {
		$email_model = new Email_model();
		$email_history_id = '';
		$raw_post_data = !empty(file_get_contents('php://input')) ? file_get_contents('php://input') : $this->input->post();
		if(!empty($raw_post_data)) {
			$decoded_post_data = json_decode($raw_post_data);
			foreach($decoded_post_data as $post_data) {
				$email_history = new Email_history();
				$sparkpost_message_id = '';
				// This post contains Message Events - delivery, bounce, spam complaint
				if(isset($post_data->msys->message_event)) {
					// set the message_id; this is what we'll use to update the db table
					$sparkpost_message_id =  $post_data->msys->message_event->transmission_id;
					$rcpt_to = $post_data->msys->message_event->rcpt_to;
					$timestamp = $post_data->msys->message_event->timestamp;
					// set the object properties/values
					$email_history = $this->_assemble_email_history($email_history, $post_data->msys->message_event);
				}
				// This post contains Track Events - open and click
				if(isset($post_data->msys->track_event)) {
					// set the message_id; this is what we'll use to update the db table
					$sparkpost_message_id =  $post_data->msys->track_event->transmission_id;
					$rcpt_to = $post_data->msys->track_event->rcpt_to;
					$timestamp = '';
					// set the object properties/values
					$email_history = $this->_assemble_email_history($email_history, $post_data->msys->track_event);
				}
				if(!empty($sparkpost_message_id)) {
					// First locate the email history record using message id; if we can't find it we need to use another method of locating this particular record
					$email_history_id = $email_model->locate_by_sparkpost_message_id($sparkpost_message_id);
					if(empty($email_history_id)) { 
						// if we can't locate it using message id, try and locate it by the email address and timestamp of the message send
						$email_history_id = $email_model->locate_by_email_timestamp($rcpt_to, $timestamp);
					} elseif(!is_integer($email_history_id)) {
						//$this->_send_devteam_email($email_history_id);
					}
					// now we should have an email_history id, so let's update this shit
					if(!empty($email_history_id)) {
						$email_history->updated_at = date('Y-m-d H:i:s');
						$update_response = $email_model->update_email_history($email_history, $email_history_id);
						if($update_response != 1) {
							//$this->_send_devteam_email($update_response);
						}
					} elseif(!is_integer($email_history_id)) {
						//$this->_send_devteam_email($email_history_id);
					}
				} else {
					echo 'Sparkpost message id empty. Nothing to update';
				}
			}
		} else {
			echo 'Post data empty. Nothing to update';
		}
	}
	
	/**
	* Helper function to assemble the class object from webhook post data
	* @param $raw_data Object The webhook post data
	* Two options for this at the moment are $post_data->msys->message_event and $post_data->msys->track_event
	*/
	function _assemble_email_history($email_history,$raw_data) {
		// now set object values
		if($raw_data->type == 'click') {
			$email_history->click = 1;
			$email_history->sparkpost_track_event = json_encode($raw_data);
		} elseif($raw_data->type == 'open') {
			$email_history->open = 1;
			$email_history->sparkpost_track_event = json_encode($raw_data);
		} elseif(!empty($raw_data->type)) {
			$email_history->sparkpost_event_type = $raw_data->type;
			$email_history->sparkpost_message_datetime = date('Y-m-d H:i:s', $raw_data->timestamp);
			$email_history->sparkpost_bounce_code = isset($raw_data->bounce_class) ? $raw_data->bounce_class : NULL;
			$email_history->sparkpost_message_event = json_encode($raw_data);
		}
		return $email_history;
	}
	
	/**
	* We had an error. Send an email to the devteam; we'll use Postmark for this
	* @param $error_message The message we need to send
	*/
	/*function _send_devteam_email($error_message) {
		$this->load->library('postmark');
		$mail = new Postmark();
		$mail->addTo('devteam_pure');
	}*/
	
}

class Email_history {
	public $sparkpost_event_type;
	public $sparkpost_message_datetime;
	public $sparkpost_bounce_code;
	public $sparkpost_message_event;
	public $sparkpost_track_event;
	public $updated_at;
	public $click;
	public $open;
}