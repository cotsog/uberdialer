<?php if (!empty($todayCallDiallerMessage)) { ?>
    <div id="divErrorMsg" class="error-msg bad margin-top-15">
        <p><span><i class="fa fa-times-circle"></i></span><?php echo $todayCallDiallerMessage; ?></p>
    </div>
<?php }?>

<section class="section-content-main-area">
    <div class="content-main-area">
	<div class="column-header query-list">
				<div style="width: 790px" class="alignleft">
					<div class="alignleft">
						<span class="column-title"><strong><?php echo $contactCallDetail->name." (".$contactCallDetail->eg_campaign_id .")" ?> </strong></span>
					</div>
					
				</div>
			
	</div>
        <form method="post" role="form" id="call_detail_form" class="call-history-form" name="form" autocomplete="off"
              novalidate="novalidate" action="/dialer/calls/index/<?php echo $contactCallDetail->id; ?>">

            <div class="col-lg-5  pad-15-b">
                <div class="pad-15-t pad-15-l row-left-pad call-row-title">
                    <div class="column-header alignleft contact-detail-header">
                        <p class="alignleft">Contact Details</p>
                        <?php if (isset($_GET['action']) && $_GET['action'] == 'qa' && isset($status) && isset($contactCallDetail->qa) && ($this->session->userdata('uid') == $contactCallDetail->qa) && !$isAddPage && ($status == 'Reject' || $status == 'Follow-up')) { ?>
                            <a title="Retract Contact" href="/dialer/calls/retractContact/<?php echo $contactCallDetail->lead_id; ?>" class="general-btn"
                               id="retract_button">Retract</a>
                        <?php } ?>
                        <?php
                        if (!empty($contactCallDetail->logged_user_type) && $contactCallDetail->logged_user_type != 'qa' && (!$Qaing) ) {
                            if (!$isViewPage && !$isAddPage) {
                                $agentSessionCampaignId = $this->session->userdata('AgentSessionCampaignID');
                                if ((!empty($agentSessionCampaignId) && ($agentSessionCampaignId == $contactCallDetail->campaign_id)) || $contactCallDetail->logged_user_type == 'manager') {
                                if (($contactCallDetail->locked_by == $this->session->userdata('uid')) || ($contactCallDetail->edit_lead_status == 0 || $contactCallDetail->edit_lead_status == false)) { ?>
                                <?php  }
                            }
                            }
                        }if (!empty($contactCallDetail->logged_user_type) &&  $contactCallDetail->logged_user_type != 'qa' && (!$Qaing) ) {
                            if ($isAddPage) {?>
                                <a id="AddCallnewContactAccess" class="btn-is-disabled"  onclick="this.disabled=true;return false;" href="javascript:void(0)"><i class="fa fa-phone-square list-edit-font alignright call-edit-icon" aria-hidden="true" ></i></a>
                            <?php }
                        }                        
                        ?>                        
                    </div>
                </div>
                <div class="pad-15-t pad-15-l row-left-pad">
                    <table class="table table-bordered row vertical-tbl <?php if((!$isViewPage) || ($save_contact_visible)){ ?> contact-tbl <?php }?>" id="editableContact">
                        <tbody>
                            <?php  //echo "<pre>",print_r($regXml->fieldset), "</pre>";exit;
                            $num_beds_standalone = "false";
                            if(!empty($regRules['displayed_vals']['qid_242'])){
                                $num_beds_standalone = "true";
                                unset($regRules['displayed_vals']['qid_242']);
                            }
        
                            $profession_html = "";
                                foreach($regXml->fieldset->field as $field){
                                    $field_name = (string) $field->name;
                                    if((!isset($field->include) || (isset($field->include) && $field->include == 'true')) && $field_name != 'password'){
                                    ?>
                                <tr>
                                    <th><?=$field->label?></th>
                                    <td>
                                        <?php if($field->form_type == 'input'){ ?>
                                        <input type="text" id="<?=$field_name?>" name="<?=$field_name?>"
                                                                         maxlength="100"
                                                                         placeholder="<?=$field->label?>"
                                                                         value="<?php if (!empty($contactCallDetail->$field_name)) {
                                                                             if($field_name == 'phone' && isset($contactCallDetail->dial_code)) {
                                                                                echo $contactCallDetail->dial_code;
                                                                            }
                                                                                echo $contactCallDetail->$field_name;
                                                                            
                                                                         } ?>"/>
                                        <?php }else if($field->form_type =='radio'){
                                            if(!empty($field->question_options)){
                                                echo "<div class='form-input' id='cq_{$field->form_type}'>";
                                                $options = unserialize($field->question_options);
                                                $checked = '';
                                                foreach($options as $option){
                                                    if(!empty($contactCallDetail->$field_name) && $option == $contactCallDetail->$field_name){
                                                        $checked = "checked='checked'";
                                                    }
                                                    echo "<input type='{$field->form_type}' name='{$field_name}' value='{$option}' {$checked}>{$option}<br>";
                                                }
                                                echo '</div>';
                                            }else{
                                                echo "no options setup";
                                            }
                                        } else if($field->form_type =='checkbox'){ 
                                            if(!empty($field->question_options)){
                                                echo "<div class='form-input' id='cq_{$field->form_type}'>";
                                                $options = unserialize($field->question_options);
                                                $checked = '';
                                                foreach($options as $option){
                                                    if(!empty($contactCallDetail->$field_name) && $option == $contactCallDetail->$field_name){
                                                        $checked = "checked='checked'";
                                                    }
                                                    echo "<input type='{$field->form_type}' name='{$field_name}[]' value='{$option}' {$checked}>{$option}<br>";
                                                }
                                                echo '</div>';
                                            }else{
                                                echo "no options setup";
                                            }
                                        }else if($field->form_type == 'dropdown'){ ?>
                                            <div class="styled select-dropdown table-dropdown">
                                                <select name="<?=$field_name?>" id="<?=$field_name?>">
                                                    <option value="">--SELECT--</option>
                                                    <?php
                                                    $optionValues = array();
                                                    switch($field_name){
                                                        case 'state':
                                                            $optionValues = get_states_array();
                                                            break;
                                                        case 'country':
                                                            $optionValues = get_countries_array();
                                                            break;
                                                        default:
                                                            $optionValues = unserialize($field->question_options);
                                                            $optionValues = array_combine($optionValues, $optionValues);
                                                            break;
                                                    }
                                                    
                                                    if (!empty($optionValues)) {
                                                        foreach ($optionValues as $optionValue => $optionLabel) {
                                                            if (isset($contactCallDetail->$field_name) && $optionValue == $contactCallDetail->$field_name)
                                                                $selected = "selected";
                                                            else
                                                                $selected = "";

                                                            echo '<option role="option" value="' . $optionValue . '" ' . $selected . '>' . $optionLabel . '</option>';
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                    </td>
                                </tr>
                                        <?php 
                                            $dynamic_questions = 'false';
                                            $practice_size_created = false;
                                            $dynamic_options = array();
                                            if($field['type'] == 'question' && !empty($field->professions)){
                                                $dynamic_questions = 'true';
                                                $professions = $field->professions;
                                                #echo '<pre>';print_r($professions);exit;
                                                //we would create profession related html here and return it.
                                                $main_profession = "";
                                                foreach ($professions->profession as $profession) {
                                                    $profession_name = "";
                                                    $profession_name = (string) ($profession['name']);
                                                    $id = "";
                                                    $id = strtolower(str_replace(array(" ", "/"), "_", $profession_name));
                                                    $main_profession.='<span id="' . $id . '" style="display:none;">';
                                                    
                                                    $question_span = "";
                                                    $fields = $profession->fields;
                                                    #echo '<pre>';print_r($profession);exit;
                                                    foreach ($fields->field as $field) {
                                                        $bool_show_field = true;

                                                        $field_name = (string) $field->name;
                                                        $display_value = "none";
                                                        //check if field name array key exists in $member array
                                                        if (!empty($cq_responses)) {
                                                            //it exists, but do we have a value for it?
                                                            if (!empty($cq_responses[$field_name]) && trim($cq_responses[$field_name]) != '') {
                                                                //Yes
                                                                $display_value = "";
                                                            }
                                                        }
                                                        
                                                        if ($bool_show_field && !isset($dynamic_options[$field_name])) {
                                                            $field_arr[] = $field_name;
                                                            $dynamic_options[$field_name] = true;
                                                            if($field_name == 'qid_46'){
                                                                
                                                                $question_span .='<tr id="span_med_location" style="display:'.$display_value.';">';
                                                                $question_span .="<th>Medical School Location</th>";
                                                                $question_span .= "<td>";
                                                                $question_span .= '<div class="styled select-dropdown table-dropdown">';
                                                                $question_span .="<select  id='med_location' name='med_location' onchange='fill_med_schools(\"state\");'></select>";
                                                                $question_span .= "</div></td></tr>";
                                                                
                                                                $display_country = ($med_location == 'Other Non-USA') ? '' : 'none';
                                                                        
                                                                $question_span .='<tr id="span_med_country" style="display:'.$display_country.';">';
                                                                $question_span .="<th>Medical School Country</th>";
                                                                $question_span .= "<td>";
                                                                $question_span .= '<div class="styled select-dropdown table-dropdown">';
                                                                $question_span .="<select  id='med_country' name='med_country' onchange='fill_med_schools(\"country\");'></select>";
                                                                $question_span .= "</div></td></tr>";
                                                            }
                                                        
                                                             $question_span.='<tr id="span_' . $field_name . '" style="display:'.$display_value.';">';
                                                             $field_label = ($field->required == 'true') ? $field->label . '*' : $field->label;
                                                            if (($profession['name'] == "Healthcare Business and Administration" || $profession['name'] == "Healthcare IT Professional") && ($field_name == "qid_5" || $field_name == "qid_25" || $field_name == "qid_16"|| $field_name == "qid_242")) {
//                                                                echo "{$field_name}<br>";
//                                                                $question_span.='<tr id="span_' . $field_name . '" class="' . $id . '" style="display:none;">';
                                                            } else {
//                                                                $question_span.='<tr id="span_' . $field_name . '" class="' . $id . '">';
                                                            }
                                                            
                                                            $question_span .="<th>{$field_label}</th>";

                                                            //write form field
                                                            $question_span .= "<td>";
                                                            if ($field->form_type == 'input') {
                                                                $question_span .='<input type="';
                                                                $question_span .='text';
                                                                $question_span .='" id="';

                                                                $question_span .=$field_name;
                                                                $question_span .='" name="';
                                                                $question_span .=$field_name;
                                                                $question_span .='" maxlength="';
                                                                $question_span .= $field->max_length;
                                                                $question_span .='"';
                                                                if ($field->required == 'true') {
                                                                    $question_span .=' required="required" ';
                                                                }
                                                                
                                                                if (isset($_POST[$field_name]) && $_POST[$field_name] != '') {
                                                                    $question_span .='value="'.$_POST[$field_name].'"';
                                                                }else if(!empty($cq_responses[$field_name])){
                                                                    $question_span .='value="'.$cq_responses[$field_name].'"';
                                                                }

                                                                $question_span .=' />';
                                                            } elseif ($field->form_type == 'dropdown') {
                                                                $question_span .= '<div class="styled select-dropdown table-dropdown">';
                                                                $question_span .='<select name="';
                                                                $question_span .=$field_name;
                                                                $question_span .='" id="';
                                                                $question_span .=$field_name;
                                                                $question_span .='"';
                                                                if ($field->required == 'true') {
                                                                    $question_span .=' required="required" ';
                                                                }
                                                                $question_span .='">';
                                                                $question_span .='<option value="">--SELECT--</option>';
                                                                $options_array = unserialize($field->question_options);
                                                                if (!empty($options_array)) {
                                                                    foreach ($options_array as $key => $value) {
                                                                        $question_span .='<option value="';
                                                                        if ($field_name == "country") {
                                                                            $question_span .= $key;
                                                                            $sel_match = $key;
                                                                        } else {
                                                                            $question_span .= $value;
                                                                            $sel_match = $value;
                                                                        }
                                                                        $question_span .='"';
                                                                        if (isset($_POST[$field_name]) && $_POST[$field_name] != '') {
                                                                            if ($_POST[$field_name] == $sel_match)
                                                                                $question_span .=' selected=selected';
                                                                        }else if(!empty($cq_responses[$field_name]) && $cq_responses[$field_name] == $sel_match){
                                                                            $question_span .=' selected=selected';
                                                                        }
                                                                        $question_span .='>';
                                                                        $question_span .=$value;
                                                                        $question_span .='</option>';
                                                                    }
                                                                }

                                                                $question_span .='</select>';
                                                                $question_span .='</div>';
                                                            } elseif ($field->form_type == 'dropdown_other') {
                                                                $other_field = $field_name . "_other";
                                                                $div_other = 'div_' . $field_name . "_other";

                                                                $question_span .='<select name="';
                                                                $question_span .=$field_name;
                                                                $question_span .='" id="';
                                                                $question_span .=$field_name;
                                                                $question_span .='" onchange="show_other(\'' . $field_name . '\',\'' . $div_other . '\');"';
                                                                if ($field->required == 'true') {
                                                                    $reg_form .=' required="required" ';
                                                                }
                                                                $question_span .='">';
                                                                $question_span .='<option value="" selected="selected">--SELECT--</option>';
                                                                $options_array = unserialize($field->question_options);

                                                                foreach ($options_array as $key => $value) {
                                                                    $question_span .='<option value="';
                                                                    if ($field_name == "country") {
                                                                        $question_span .= $key;
                                                                    } else {
                                                                        $question_span .= $value;
                                                                    }
                                                                    $question_span .='"';
                                                                    if (isset($_POST[$field_name]) && $_POST[$field_name] != '') {
                                                                        $question_span .=' selected=selected';
                                                                    }
                                                                    $question_span .='>';
                                                                    $question_span .=$value;
                                                                    $question_span .='</option>';
                                                                }
                                                                $question_span .='</select>';

                                                                $question_span .='<div id="' . $div_other . '" style="display:none;"><label>If other:</label><input class="dropdown_other" type="text" id="';
                                                                $question_span .= $other_field;
                                                                $question_span .='" name="';
                                                                $question_span .= $other_field;
                                                                $question_span .='" onblur="append_other(\'' . $field_name . '\',\'' . $other_field . '\');"/></div>';
                                                            } elseif (substr($field->form_type, 0, 5) == 'check') {
                                                                //<input type="checkbox" name="vehicle" value="Bike" /> I have a bike<br />
                                                                $options_array = unserialize($field->question_options);
                                                                #print_r($options_array);exit();
                                                                //this is an exception put in to handle On24 Campaigns that require a true or false
                                                                //value depending on whether boxes are checked. In these cases we'll add a default_options
                                                                //node to the the particular question in the reg_xml. It will have 1 pipe separated true/false value set.
                                                                if (isset($field->response_options)) {
                                                                    //We need to write a hidden field with same name as checkbox field to hold the unchecked value:
                                                                    //http://planetozh.com/blog/2008/09/posting-unchecked-checkboxes-in-html-forms/
                                                                    $vals = explode('|', $field->response_options);
                                                                    if (count($vals) == 2) {
                                                                        $question_span .='<input type="hidden" name="';
                                                                        $question_span .=$field_name;
                                                                        $question_span .='" value="';
                                                                        $question_span .= $vals[1];
                                                                        $question_span .='">';

                                                                        $question_span .='<input type="checkbox" name="';
                                                                        $question_span .=$field_name;
                                                                        $question_span .='" value="';
                                                                        $question_span .= $vals[0];
                                                                        $question_span .='" ';
                                                                        if ($field->form_type == 'checkbox_prechecked') {
                                                                            $question_span .= 'checked="checked"';
                                                                        }
                                                                        $question_span .=' />';
                                                                        $question_span .= $options_array[0];
                                                                        $question_span .='<br/>';
                                                                    }
                                                                } else {
                                                                    if (count($options_array) > 1) {
                                                                        //if multiple checkbox options then append array to checkbox name
                                                                        $field_name .= '[]';
                                                                    }

                                                                    foreach ($options_array as $key => $value) {
                                                                        $question_span .='<input type="checkbox" name="';
                                                                        $question_span .=$field_name;
                                                                        $question_span .='" value="';
                                                                        $question_span .= $value;
                                                                        $question_span .='" ';
                                                                        if ($field->form_type == 'checkbox_prechecked') {
                                                                            $question_span .= 'checked="checked"';
                                                                        }
                                                                        $question_span .=' />';
                                                                        $question_span .= $value;
                                                                        $question_span .='<br/>';
                                                                    }
                                                                }
                                                            } elseif (substr($field->form_type, 0, 5) == 'radio') {
                                                                //<input type="checkbox" name="vehicle" value="Bike" /> I have a bike<br />
                                                                $options_array = unserialize($field->question_options);
                                                                #print_r($options_array);exit();
                                                                //this is an exception put in to handle On24 Campaigns that require a true or false
                                                                //value depending on whether boxes are checked. In these cases we'll add a default_options
                                                                //node to the the particular question in the reg_xml. It will have 1 pipe separated true/false value set.
                                                                if (isset($field->response_options)) {
                                                                    //We need to write a hidden field with same name as checkbox field to hold the unchecked value:
                                                                    //http://planetozh.com/blog/2008/09/posting-unchecked-checkboxes-in-html-forms/
                                                                    $vals = explode('|', $field->response_options);
                                                                    if (count($vals) == 2) {
                                                                        $question_span .='<input type="radio" name="';
                                                                        $question_span .=$field_name;
                                                                        $question_span .='" value="';
                                                                        $question_span .= $vals[0];
                                                                        $question_span .='" ';
                                                                        if ($field->form_type == 'radio_prechecked') {
                                                                            $question_span .= 'checked="checked"';
                                                                        }
                                                                        $question_span .=' />';
                                                                        $question_span .= $vals[0];

                                                                        $question_span .='<input type="radio" name="';
                                                                        $question_span .=$field_name;
                                                                        $question_span .='" value="';
                                                                        $question_span .= $vals[1];
                                                                        $question_span .='"/>';
                                                                        $question_span .= $vals[1];
                                                                        $question_span .='<br/>';
                                                                    }
                                                                } else {
                                                                    if (count($options_array) > 1) {
                                                                        //if multiple checkbox options then append array to checkbox name
                                                                        //$field_name .= '[]';
                                                                    }
                                                                    $i = 0;
                                                                    foreach ($options_array as $key => $value) {
                                                                        $question_span .='<input type="radio" id=radio';
                                                                        $question_span .=' name="';
                                                                        $question_span .=$field_name;
                                                                        $question_span .='" value="';
                                                                        $question_span .=$value;
                                                                        $question_span .='" ';
                                                                        if ($field->form_type == 'checkbox_prechecked') {
                                                                            $question_span .= 'checked="checked"';
                                                                        }

                                                                        if ($field->required == 'true') {
                                                                            $question_span .=' required="required" ';
                                                                        }
                                                                        $question_span .=' />';
                                                                        $question_span .= $value;
                                                                        $question_span .='<br/>';
                                                                        $i++;
                                                                    }
                                                                }
                                                                
                                                            }
                                                            
                                                        } else {
                                                            $exits[] = $field_name;
                                                        }
                                                        $question_span .= "</td></tr>";
                                                        
                                                        if($field_name == 'qid_37' && !$practice_size_created){
                                                            $practice_size_created = true;
                                                            $display_practice_size = ($med_location == 'Other Non-USA') ? '' : 'none';
                                                            $question_span.='<tr id="span_qid_101' . '" style="display:none;">';
                                                            $question_span .="<th>Practice Size</th>";
                                                            $question_span .= "<td>";
                                                            $question_span .= '<div class="styled select-dropdown table-dropdown">';
                                                            $question_span .= "<select  id='qid_101' name='qid_101'>";
                                                            $question_span .= " 
                                                                <option value=''>--SELECT--</option>
                                                                <option value='1 to 5'>1 to 5</option>
                                                                <option value='6 to 10'>6 to 10</option>
                                                                <option value='11 to 15'>11 to 15</option>
                                                                <option value='16 to 20'>16 to 20</option>
                                                                <option value='21 to 25'>21 to 25</option>
                                                                <option value='26 to 50'>26 to 50</option>
                                                                <option value='50 plus'>50 plus</option>
                                                                    ";
                                                            $question_span .= "</select>";
                                                            $question_span .= "</div></td></tr>";
                                                        }
                                                        
                                                    }
                                                    echo $question_span;
                                                    $main_profession.=$question_span;
                                                    $main_profession.='</span>';
                                                }
//                                                if (!empty($field_arr)) {
//                                                    $field_arr = array_unique($field_arr);
//                                                    $this->profession_field_list = implode("|", $field_arr);
//                                                }
//                                                if (!empty($exits)) {
//                                                    $this->questions_exits = array_unique($exits);
//                                                }


                                                $profession_html = $main_profession;
                                            }
                                        
                                        } 
                                        ?>
                                    
                                <?php 
                                    }
                            }
                            ?>
                        <tr>
                            <th>Custom Questions:</th>
                            <td class="editableformlabel nospaceword-break"><?php if (!empty($contactCallDetail->custom_question_value)) {
                                    echo $contactCallDetail->custom_question_value;
                                } else {
                                    echo 'None';
                                } ?></td>
                            <td class="editableformfield nospaceword-break">
                                <?php if (!empty($contactCallDetail->custom_question_value)) {
                                    echo $contactCallDetail->custom_question_value;
                                } else {
                                    echo 'None';
                                } ?></td>
                        </tr>
                        <input type="hidden" id="contact_id" name="contact_id"
                               value="<?php echo isset($contactCallDetail->id) ? $contactCallDetail->id : set_value('id'); ?>">
                        </tbody>
                    </table>
                    <?php 
                    $practice_speciality = explode("|", $question_options);
                    foreach ($practice_speciality as $k => $ps) {
                        $newps[$k] = trim($ps);
                    }
//                    echo '<div class="profession_htl">' . $profession_html . '</div>';
                    ?>
                </div>
                
                <?php $this->load->view('/dialer/calls/common_view'); ?>

        </form>
    </div>
<div class="clearfix"></div>
</section>

<script type="text/javascript">
    var contact_id = <?php echo isset($contactCallDetail->id) ? $contactCallDetail->id:''; ?>;
    var logged_user_type = '<?php echo $contactCallDetail->logged_user_type;?>';
    var IsAddPage = '<?php echo $isAddPage;?>';
    var IsViewPage = '<?php echo $isViewPage;?>';
    var CallStatus = '<?php if(!empty($status)){echo $status;} else { "";} ;?>';
    var IsCallbackSelected = '<?php if(!empty($contactCallDetail->call_disposition_id)){ echo $contactCallDetail->call_disposition_id; }else{'';};?>';
    var action_qa = '<?php if(!empty($Qaing) && $Qaing == '1'){echo $Qaing;} ;?>';
    var qa_accepted_by_user = '<?php if(!empty($qa_accepted_by_user) && ($qa_accepted_by_user == '1' || $qa_accepted_by_user == true)){echo $qa_accepted_by_user;} ;?>';
</script>
<link rel="stylesheet" href="<?=$this->config->item('static_url')?>/css/jquery.datetimepicker.css<?=$this->cache_buster?>"/>
<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/jquery.datetimepicker.full.min.js<?=$this->cache_buster?>"></script>
<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/pagejs/calls/contactcalldetail.js<?=$this->cache_buster?>"></script>
<script src="<?=$this->config->item('static_url')?>/js/jquery.easytabs.min.js<?=$this->cache_buster?>" type="text/javascript"></script>
<!--<script src="/js/timer.jquery.min.js" type="text/javascript"></script>-->
<style>
    /* Example Styles for Demo */
    .etabs {
        margin: 0;
        padding: 0;
    }

    .tab {
        display: inline-block;
        zoom: 1;
        *display: inline;
        background: #0093e7 none repeat scroll 0 0;
        border: solid 1px #999;
        border-bottom: none;
        -moz-border-radius: 4px 4px 0 0;
        -webkit-border-radius: 4px 4px 0 0;
    }

    .tab a {
        font-size: 14px;
        line-height: 2em;
        display: block;
        padding: 0 10px;
        outline: none;
        color: #fff
    }

    .tab a:hover {
        text-decoration: none;
    }

    .tab.active {
        background: #0093e7 none repeat scroll 0 0;
        position: relative;
        top: 1px;
        border-color: #666;
    }

    .tab a.active {
        font-weight: bold;
    }

    .tab-container .panel-container {
        background: #fff;
        border: solid #666 1px;
        padding: 10px;
        -moz-border-radius: 0 4px 4px 4px;
        -webkit-border-radius: 0 4px 4px 4px;
    }

    .panel-container {
        margin-bottom: 10px;
    }
</style>

<script type="text/javascript">
    $(document).ready(function () {
        $('#tab-container').easytabs();
		$('#history-tab-container').easytabs();
    });
</script>
<script type="text/javascript" name="mpg">
    var member_id =  '<?= !empty($member_id) ? $member_id : '' ?>';
    var js_profession_post_val = '<?= (!empty($_POST['qid_0']) ? strtolower(str_replace(array(" ", "/"), "_", $_POST['qid_0'])) : !empty($cq_responses['qid_0']) ? strtolower(str_replace(array(" ", "/"), "_", $cq_responses['qid_0'])) : '') ?>';
    var js_post_med_location = '<?=$med_location ?>';
    var js_post_med_country = '<?=$med_country ?>';
    var js_post_qid_46 = '<?= (!empty($_POST['qid_46']) ? $_POST['qid_46'] : !empty($cq_responses['qid_46']) ? $cq_responses['qid_46'] : '') ?>';
    var js_post_qid_37 = '<?= (empty($post_qid_37) ? '' : strtolower(str_replace(array(" ", "/"), "_", $post_qid_37))) ?>';
    var js_post_qid_101 = '<?= (empty($post_qid_101) ? '' : $post_qid_101) ?>';
    var js_post_qid_173 = '<?= (empty($post_qid_173) ? '' : $post_qid_173) ?>';
    var js_post_qid_0 = '';
    var js_qid_242 = '<?= (!empty($member) && !empty($member["qid_242"])) ? $member["qid_242"] : '' ?>';
    var dynamic_questions = '<?= $dynamic_questions ?>';
    var js_qid_242_standalone = '<?= $num_beds_standalone ?>';
    $(document).ready(function() {
<?php //(isset($jsRegRules) ? $jsRegRules : '') ?>
        var location_dropdown =  get_med_location_dropdown();
        $("#med_location").html(location_dropdown);
        
        $("#qid_0").change(function(e,js_profession){       
            js_selected_val="";
            if(typeof($("#qid_0 :selected").val())!="undefined"){
                js_selected_val = $("#qid_0 :selected").val();
                js_selected_val = js_selected_val.toLowerCase().replace(/ /g, "_").replace("/","_");
            }
            if(js_selected_val==""){
                js_selected_val = js_profession;
            }
            $(".hideclass").hide();
            $(".error").remove();
            
            
            $("#span_qid_34,#span_qid_35,#span_qid_37,#span_qid_46,#span_qid_40,#span_qid_36,#span_qid_39,#span_qid_44,#span_qid_45,#span_qid_42,#span_qid_43,#span_qid_38,#span_qid_5,#span_qid_25,#span_qid_242,#span_qid_16,#span_med_location,#span_med_country").hide();
            if(member_id == ''){
                $("#qid_34,#qid_35,#qid_37,#qid_46,#qid_40,#qid_44,#qid_45,#qid_42,#qid_43,#qid_5,#qid_25,#qid_242,#qid_16").val("");
            }
            
            if(js_selected_val == "physician"){
                $("#span_qid_34,#span_qid_35,#span_qid_37,#span_qid_46,#span_med_location").show();
                $("#qid_34,#qid_35,#qid_37,#qid_46,#med_location").attr("required", "required");
            }
            if(js_selected_val == "physician_assistant"){
                $("#qid_37,#qid_39,#qid_45").attr("required", "required");
                $("#span_qid_37,#span_qid_39,#span_qid_45").show();
            }
            if(js_selected_val == "nurse_advanced_practice_nurse"){
                $("#qid_40,#qid_35,#qid_36,#qid_37,#qid_39").attr("required", "required");
                $("#span_qid_40,#span_qid_35,#span_qid_36,#span_qid_37,#span_qid_39").show();
            }
            if(js_selected_val == "pharmacist"){
                $("#qid_36,#qid_34,#qid_44").attr("required", "required");
                $("#span_qid_36,#span_qid_34,#span_qid_44").show();
            }
            if(js_selected_val == "medical_student"){
                $("#qid_42,#qid_43,#qid_38,#qid_46,#med_location").attr("required", "required");
                $("#span_qid_42,#span_qid_43,#span_qid_38,#span_qid_46,#span_med_location").show();
            }
            if(js_selected_val == "other_healthcare_provider"){
                $("#qid_36,#qid_37").attr("required", "required");
                $("#span_qid_36,#span_qid_37").show();
            }
            if(js_selected_val == "healthcare_business_and_administration"){
                $("#qid_36,#qid_37").attr("required", "required");
                $("#span_qid_36,#span_qid_37").show();
                $("#span_qid_5,#span_qid_25,#span_qid_242,#span_qid_16").hide();
            }
            if(js_selected_val == "healthcare_it_professional"){
                $("#qid_36,#qid_37").attr("required", "required");
                $("#span_qid_36,#span_qid_37").show();
                $("#span_qid_5,#span_qid_25,#span_qid_242,#span_qid_16").hide();
            }
            if(js_selected_val == "other"){                
                $("#qid_36,#qid_37").attr("required", "required");
                $("#span_qid_36,#span_qid_37").show();
            }
            $("#span_qid_101").hide();
//            $("#qid_37").bind("change",function(){showpracticesize();});
        
    });
            
            if(js_profession_post_val!=""){
                $("#qid_0").trigger("change",js_profession_post_val);
                if(js_profession_post_val=="physician" || js_profession_post_val=="medical_student"){
                    if(js_post_med_location == "Other Non-USA"){
                        $("#med_location").trigger("change","country");
                        $("#med_country").trigger("change","country");
                    }else{
                        $("#med_location").trigger("change","state");
                    }

                }
            }
    });
    
//    function showpracticesize(){
      $("#qid_37").change(function(e,js_orgtype){
        $("#span_qid_242").hide();
        $("#span_qid_101").hide();
        js_qid_37="";
        if(typeof($("#qid_37 :selected").val())!="undefined"){
            js_qid_37 = $("#qid_37 :selected").val()
            js_qid_37 = js_qid_37.toLowerCase().replace(/ /g, "_").replace("/","_");
        }
        js_qid_0="";
        if(typeof($("#qid_0 :selected").val())!="undefined"){
            js_qid_0 = $("#qid_0 :selected").val()
            js_qid_0 = js_qid_0.toLowerCase().replace(/ /g, "_").replace("/","_");
        }

        if(js_qid_37=="medical_group_practice"){
            $("#span_qid_101").show();
            if(js_qid_0=="healthcare_business_and_administration" || js_qid_0=="healthcare_it_professional")
            {
                $("#span_qid_5").hide();
                $("#span_qid_25").hide();
                $("#span_qid_16").hide();

                $("#span_qid_242").hide();
            }
            
        }else{
            if(js_qid_0=="healthcare_business_and_administration" || js_qid_0=="healthcare_it_professional")
            {
                $("#span_qid_16").show();
            }

            if(js_qid_37=="hospital_medical_center/multi-hospital_system") {

                $("#span_qid_242").show();
            }

            $(".error").remove();
            
            
        }
//        $("#reg_form").validator();
//    }
});
    
/*generate med school drop down on ajax call*/  
function fill_med_schools(js_location_op){         
    //#med_location,#med_country
    $("#span_qid_46").show();
    $("#qid_46").attr("required", "required");
    if(js_location_op == "country"){
        js_location = $("#med_country :selected").val();
    }else{
        js_location = $("#med_location :selected").val();
    }
            
    if(js_location!='' && js_location!='Other Non-USA'){
        $.ajax({
            url: "/mpg/dialer/calls/ajax_get_med_schools",
            data: {
                med_school_location: js_location,
                location_op:js_location_op
            },
            dataType: "json",
            type: "POST",
            success: function(data){                         
                var optionsValues='<option value="">--SELECT--</option>';
                jQuery.map(data, function(item) {                                
                                
                    /*When form posts back then populate school dropdown with earlier selected value*/
                    var sel_school='';
                    if(js_post_qid_46!='' && js_post_qid_46==item.val){
                        sel_school='selected';
                    }
                    /*ends here*/
                                
                    optionsValues += '<option value="' + item.val + '" '+sel_school+'>' + item.text + '</option>';
                })
                $('#span_qid_46').show();
                $('#qid_46').html(optionsValues);
                $("#qid_46").attr("required", "required");
                if(js_location_op != 'country'){
                    $('#span_med_country').hide();
                    $('#med_country').removeAttr("required");
                }  
                //$("#reg_form").validator();   
            }
        });
    }else if(js_location=='Other Non-USA'){ // if location is other than us/canada then generate other countries drop down.Also add dynamic javascript validations.
        $('#span_med_country').show();
        $('#med_country').attr("required", "required");
        js_country_dropdown = get_med_country_dropdown();
        $("#med_country").html(js_country_dropdown);
        $('#qid_46').find('option').remove().end().html('<option value="">--SELECT--</option>');
    }else{ // if no location is selected hide country and school dropdown
        $('#med_country').removeAttr("required");
        $('#span_med_country').hide();
        //$('#span_qid_46').hide();
        $("#med_location option:first").attr('selected','selected');
        $('#qid_46').find('option').remove().end().html('<option value="">--SELECT--</option>');
    }
            
}
/*end here*/

/*generate med location drop down*/
function get_med_location_dropdown()
{
    //span_med_location,#span_med_country
    var location_array=new Array("Alabama","Arizona","Arkansas","California","Colorado","Connecticut","District of Columbia","Florida","Georgia","Hawaii","Illinois","Indiana","Iowa","Kansas","Kentucky","Louisiana","Maine","Maryland","Massachusetts","Michigan","Minnesota","Mississippi","Missouri","Nebraska","Nevada","New Hampshire","New Jersey","New Mexico","New York","North Carolina","North Dakota","Ohio","Oklahoma","Oregon","Pennsylvania","Rhode Island","South Carolina","South Dakota","Tennessee","Texas","Utah","Vermont","Virginia","Washington","West Virginia","Wisconsin","Alberta","British Columbia","Manitoba","Newfoundland","Nova Scotia","Ontario","Quebec","Saskatchewan","Puerto Rico","Other Non-USA");
    var location_dropdown='';
    location_dropdown+='<select id="med_location" name="med_location" required="required" onchange="fill_med_schools(\'state\');">';
    location_dropdown+='<option value="">--SELECT--</option>';
    for (var location in location_array)
    {
        /*When form posts back then populate location dropdown with earlier selected value*/
        var sel_loc='';
        if(js_post_med_location!='' && js_post_med_location==location_array[location]){
            sel_loc='selected';
        }
        /*ends here*/
            
        location_dropdown+='<option value="'+location_array[location]+'" '+sel_loc+'>'+location_array[location]+'</option>';
    }
    location_dropdown+='</select>';
    return location_dropdown;
}
 /*ends here*/
 
 /*generate med country drop down on ajax call*/  
function get_med_country_dropdown()
{
    //span_med_location,#span_med_country
    var country_array=new Array("Afghanistan","Albania","Algeria","Angola","Antigua &amp; Barbuda","Argentina","Australia","Austria","Bahrain","Bangladesh","Barbados","Belgium","Belize","Benin","Bolivia","Bosnia &amp; Herzegovina","Brazil","Bulgaria","Burkina Faso","Burundi","Cambodia","Cameroon","Canada","Cayman Islands","Central African Republic","Chile","China","Colombia","Congo","Cook Islands","Costa Rica","Croatia","Cuba","Czech Republic","Denmark","Dominica","Dominican Republic","Ecuador","Egypt","El Salvador","Estonia","Ethiopia","Fiji","Finland","France","Gabon","Germany","Ghana","Greece","Grenada","Guatemala","Guinea","Guyana","Haiti","Honduras","Hong Kong","Hungary","Iceland","India","Indonesia","Iran","Iraq","Ireland","Israel","Italy","Ivory Coast","Jamaica","Japan","Jordan","Kenya","Korea (DPRK)","Korea, Republic of","Kuwait","Laos","Latvia","Lebanon","Liberia","Libya","Lithuania","Macedonia","Madagascar","Malawi","Malaysia","Mali","Malta","Mexico","Micronesia","Mongolia","Montserrat","Morocco","Mozambique","Myanmar","Nepal","Netherlands","Netherlands Antilles","New Zealand","Nicaragua","Niger","Nigeria","Norway","Oman","Pakistan","Palestine","Panama","Papua New Guinea","Paraguay","Peru","Philippines","Poland","Portugal","Romania","Russian Federation","Rwanda","Saudi Arabia","Senegal","Serbia","Seychelles","Sierra Leone","Singapore","Slovakia","Slovenia","Somalia","South Africa","Spain","Sri Lanka","St. Kitts &amp; Nevis","St. Lucia","Sudan","Suriname","Sweden","Switzerland","Syrian Arab Republic","Taiwan","Tanzania","Thailand","Togo","Trinidad &amp; Tobago","Tunisia","Turkey","Uganda","United Arab Emirates","United Kingdom","Uruguay","Venezuela","Viet Nam","Yemen","Zambia","Zimbabwe");
    var country_dropdown='';
    country_dropdown+='<option value="">--SELECT--</option>';
    for (var location in country_array)
    {
        /*When form posts back then populate country dropdown with earlier selected value*/
        var sel_country='';
        if(js_post_med_country!='' && js_post_med_country==country_array[location]){
            sel_country='selected';
        }
        /*ends here*/
            
        country_dropdown+='<option value="'+country_array[location]+'" '+sel_country+'>'+country_array[location]+'</option>';
    }
    return country_dropdown;
}
/*ends here*/
</script>
