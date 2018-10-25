<?php

class Registration
{
    public $member_id;
    public $user_type;
    public $user_email;
    public $user_data;
    public $form_type;
    public $reg_xml;
    public $reg_rules;
    public $reg_form;
    public $edit_form;
    public $field_list;
    public $cq;
    public $campaign_sql;
    public $CI;
    public $distinct_leads;
    public $member;

    public function __construct()
    {
        //Get reference to CodeIgniter:
        //http://ellislab.com/codeigniter/user-guide/general/creating_libraries.html
        $this->CI = &get_instance();

        //set default form type
        $this->form_type = 'standard';
    }

    function set_user_info($member_record)
    {
        $this->member = $member_record;
    }

    private function _find_response($responses, $id)
    {
        foreach ($responses as $value) {
            if ($value->question_id == $id) {
                return $value;
            }
        }
        return null;
    }

    function custom_questions($fields, $responses)
    {
        $reg_form = '';
        $ctr = 1;
        $rsp_ctr = 0;

        foreach ($fields as $field) {

            $cq_response = $this->_find_response($responses, $field->id);
            $field_name = 'question' . $ctr;
            if (!empty($cq_response) && !empty($cq_response->response)) {
                $response = $cq_response->response;
            } else {
                if (!empty($_POST[$field_name])) {
                    $response = $_POST[$field_name];
                } else {
                    $response = '';
                }

            }
            $reg_form .= '<div class="custom-questions-form">';
            $reg_form .= '<label for="';
            $reg_form .= $field_name;
            $reg_form .= '">';
            $reg_form .= $field->question;
            //$reg_form .=' <span class="required">*</span>';
            $reg_form .= '</label>';
            if ($field->form_type == 'TextBox' || $field->form_type == 'input') {

                $reg_form .= '<div class="form-input">';
                $reg_form .= $this->build_input_field($field_name, 200, false, $response);
                $reg_form .= ' </div>';
                $reg_form .= ' </div>';
            } elseif ($field->form_type == 'Combo' || $field->form_type == 'Combo_other' || $field->form_type == 'dropdown' || $field->form_type == 'dropdown_other') {

                $reg_form .= '<div class="styled select-dropdown">';
//                $reg_form .= $this->build_cq_dropdown($field_name, $field->options, false, $response);
                $reg_form .= $this->build_dropdown_other_field($field_name, $field->options, false, '',  $response);
                $reg_form .= ' </div>';
                $reg_form .= ' </div>';
            }  elseif (substr($field->form_type, 0, 5) == 'Check' || $field->form_type == 'checkbox_prechecked'  || $field->form_type == 'checkbox') {
                $reg_form .= '<div class="form-input">';
                $reg_form .= $this->build_cq_check_field($field_name, $field->options, '', $field->form_type, $response);
                $reg_form .= ' </div>';
                $reg_form .= ' </div>';
            } elseif (substr($field->form_type, 0, 5) == 'Radio' || $field->form_type == 'radio_prechecked' || $field->form_type == 'radio') {
                $reg_form .= '<div class="form-input" id="cq_radio">';
                $reg_form .= $this->build_cq_radio_field($field_name, $field->options, $response, $field->form_type, "");
                $reg_form .= ' </div>';
                $reg_form .= ' </div>';
            }
            $ctr++;
            $rsp_ctr++;
        }
        return $reg_form;
    }

    function intent_custom_questions($fields, $responses)
    {
        $reg_form = '';
        $ctr = 1;
        $rsp_ctr = 0;

        foreach ($fields as $field) {

            $cq_response = $this->_find_response($responses, $field->id);
            $field_name = 'qid_' . $field->id;
            if (!empty($cq_response) && !empty($cq_response->response)) {
                $response = $cq_response->response;
            } else {
                if (!empty($_POST[$field_name])) {
                    $response = $_POST[$field_name];
                } else {
                    $response = '';
                }

            }
            $reg_form .= '<div class="custom-questions-form">';
            $reg_form .= '<label for="';
            $reg_form .= $field_name;
            $reg_form .= '">';
            $reg_form .= $field->question;
            //$reg_form .=' <span class="required">*</span>';
            $reg_form .= '</label>';
            if ($field->form_type == 'TextBox') {

                $reg_form .= '<div class="form-input">';
                $reg_form .= $this->build_input_field($field_name, 200, false, $response);
                $reg_form .= ' </div>';
                $reg_form .= ' </div>';
            } elseif ($field->form_type == 'Combo' || $field->form_type == 'Combo_other') {

                $reg_form .= '<div class="styled select-dropdown">';
                $reg_form .= $this->build_cq_dropdown($field_name, $field->options, false, $response);
                $reg_form .= ' </div>';
                $reg_form .= ' </div>';
            } elseif ($field->form_type == 'dropdown_other') {
//                $reg_form .= '<div class="styled select-dropdown">';
//                $reg_form .= $this->build_dropdown_other_field($field_name, $field->options, false, $field->trigger, $this->member->$fieldname);
//                $reg_form .= ' </div>';
//                $reg_form .= ' </div>';
            } elseif ($field->form_type == 'Combo_other') {
//                $reg_form .= '<div class="styled select-dropdown">';
//                $reg_form .= $this->build_dropdown_other_field($field_name, $field->options, false, '',  $response);
//                $reg_form .= ' </div>';
//                $reg_form .= ' </div>';
            } elseif (substr($field->form_type, 0, 5) == 'Check') {
                $reg_form .= '<div class="form-input">';
                $reg_form .= $this->build_cq_check_field($field_name, $field->options, '', $field->form_type, $response);
                $reg_form .= ' </div>';
                $reg_form .= ' </div>';
            } elseif (substr($field->form_type, 0, 5) == 'Radio') {
                $reg_form .= '<div class="form-input" id="cq_radio">';
                $reg_form .= $this->build_cq_radio_field($field_name, $field->options, $response, $field->form_type, "");
                $reg_form .= ' </div>';
                $reg_form .= ' </div>';
            }
            $ctr++;
            $rsp_ctr++;
        }
        return $reg_form;
    }

    /**
     * This is a helper function to build the survey question form fields
     *
     * @param Mixed  $fields      An array of field objects, containing the data
     *                            necessary to construct the appropriate form
     *                            field types
     * @param Mixed  $responses   An array of response objects, containing the
     *                            data necessary to determine a pre-answered
     *                            survey question. A pre-answered question
     *                            applies to questions for which the user has
     *                            already provided a response.
     * @param String $reg_form    A string contaning the HTML necessary to show
     *                            the form fields for survey questions,
     *                            including any pre-answered question responses.
     * @param String $cq_response A response (if any) that has already been
     *                            provided for the current question.
     * @param String $field_name  The HTML name value of the field being built.
     * @param String $response    Placeholder for the selected/pre-filled
     *                            response value for the current question.
     *
     * @return String $reg_form
     */
    function survey_custom_questions($fields, $responses)
    {
        $reg_form = '';
        foreach($fields as $field){
            $field->options = $field->options . "|Not Asked";
            if ($field->form_type == 'Button') {
                $field->form_type = 'Combo';
            }
            $reg_form .= '<div style="margin-bottom: 12px;">';
            $cq_response = $this->_find_response($responses, $field->id);
            $field_name = 'qid_'.$field->id;
            if(!empty($cq_response) && !empty($cq_response->response)){
                $response = $cq_response->response;
            }else{
                if(!empty($_POST[$field_name])){
                    $response = $_POST[$field_name];
                }else{
                    $response = '';
                }
                
            }
            if (!empty($field->question_header)) {
                $reg_form .='<label for="';
                $reg_form .=$field_name;
                $reg_form .='">';
                $reg_form .= '<em>'.$field->question_header.'</em>';
                $reg_form .='</label><br />';
            }
            $reg_form .='<label for="';
            $reg_form .=$field_name;
            $reg_form .='">';
            $reg_form .= '<strong>'.$field->question_intro_text.'</strong>';
            $reg_form .='</label><br />';
            if ($field->form_type == 'TextBox') {
                $reg_form .= $this->build_input_field(
                    $field_name,
                    200,
                    false,
                    $response
                );
            } elseif ($field->form_type == 'Combo' || $field->form_type == 'dropdown_other' || $field->form_type == 'Combo_other') {
                $reg_form .= $this->build_dropdown_other_field(
                    $field_name,
                    $field->options,
                    false,
                    "",
                    $response
                );
            } elseif (substr($field->form_type, 0, 5) == 'Check') {
                $reg_form .= $this->build_cq_check_field(
                    $field_name,
                    $field->options,
                    '',
                    $field->form_type,
                    $response
                );
            } elseif (substr($field->form_type, 0, 5) == 'Radio') {
                $reg_form .= $this->build_cq_radio_field(
                    $field_name,
                    $field->options,
                    $response,
                    $field->form_type,
                    ""
                );
            }
            if (!empty($field->question_outro_text)) {
                $reg_form .='<em style="display:block;" for="';
                $reg_form .=$field_name;
                $reg_form .='">';
                $reg_form .= $field->question_outro_text;
                $reg_form .='</em><br />';
            }
            $reg_form .= '</div>';
        }
        return $reg_form;
    }

    function build_label($label_name, $field_name)
    {

        $label = '';
        $label .= '<label for="';
        $label .= $field_name;
        $label .= '">';
        $label .= $label_name;
        /*if ($field->required == 'true') {
            $label .=' <span class="required">*</span>';
        }*/
        $label .= '</label>';
        return $label;
    }

    function build_input_field($field_name, $max_length, $required, $user_data = '')
    {
        $f = '<input type="';
        if ($field_name == 'email' || $field_name == 'password') {
            $f .= $field_name;
        } else {
            $f .= 'text';
        }
        $f .= '" id="';
        $f .= $field_name;
        $f .= '" name="';
        $f .= $field_name;
        $f .= '" maxlength="';
        $f .= $max_length;
        $f .= '"';
        if ($required == 'true') {
            $f .= ' required="required"';
        }

        if (isset($_POST[$field_name]) && $_POST[$field_name] != '') {
            $f .= 'value="';
            $f .= $_POST[$field_name];
            $f .= '"';
        } elseif ($user_data != '') {
            $f .= 'value="';
            $f .= $user_data;
            $f .= '"';
        }
        if ($field_name == 'password') {
            $f .= ' pattern="^[a-zA-Z0-9]+$" ';
            $f .= ' data-message="Please Complete this mandatory field (Only alpha-numeric)" ';
        }

        $f .= ' />';

        return $f;
    }

    function build_cq_dropdown($field_name, $question_options, $required, $user_data = '')
    {
        $f = '<select name="';
        $f .= $field_name;
        $f .= '" id="';
        $f .= $field_name;
        $f .= '"';
        if ($required == 'true') {
            $f .= ' required="required"';
        }
        if ($user_data == '') {
            $f .= '"><option value="" selected="selected">--SELECT--</option>';
        } else {
            $f .= '"><option value="">--SELECT--</option>';
        }

        $option_array = explode('|', $question_options);
        foreach ($option_array as $value) {
            $f .= '<option value="';
            $f .= $value;
            $f .= '"';
            if ($user_data == $value) {
                $f .= ' selected=selected';
            }
            $f .= '>';
            $f .= $value;
            $f .= '</option>';
        }

        $f .= '</select>';

        return $f;

    }

    function build_cq_check_field($field_name, $question_options, $response_options, $form_type, $user_data = '')
    {
        $user_data_arry = array();
        if (!empty($user_data) && is_string($user_data)) {
            $user_data_arry = explode("|", $user_data);
        }
        $otherValue = "";
        if(!empty($user_data_arry)){
            foreach($user_data_arry as $key => $res){
                if(substr(strtolower(trim($res)), 0, 5) === 'other'){
                    $getOtherLabel = explode(":", $res);
                    $otherValue = !empty($getOtherLabel[1]) ? $getOtherLabel[1] : "";
                    $user_data_arry[$key] = $getOtherLabel[0];
                    break;
                }
            }
        }
        
        $options_array = explode('|', $question_options);
        $f = '';
        $other_field = $field_name . "_other";
        $div_other = 'div_' . $field_name . "_other";
        $selectedValue = "";
        if (!empty($response_options)) {
            //We need to write a hidden field with same name as checkbox field to hold the unchecked value:
            //http://planetozh.com/blog/2008/09/posting-unchecked-checkboxes-in-html-forms/
            $vals = explode('|', $response_options);

            if (count($vals) == 2) {
                $v = str_replace('[[', '<', $options_array[0]);
                $value = str_replace(']]', '>', $v);
                $f .= '<input type="hidden" name="';
                $f .= $field_name;
                $f .= '" value="';
                $f .= $vals[1];
                $f .= '"><input type="checkbox" name="';
                $f .= $field_name;
                $f .= '" value="';
                $f .= $vals[0];
                $f .= '" ';
                if (!empty($user_data_arry)) {
                    if (in_array($value, $user_data_arry)) {
                        $f .= 'checked="checked"';
                        $selectedValue = $vals[0];
                    }
                } else {
                    if ($form_type == 'Check_prechecked') {
                        $f .= 'checked="checked"';
                    }
                }
                if(substr(strtolower(trim($value)), 0, 5) === 'other'){
                    $f .='" id="'.$field_name.'" onclick="show_other(\'' . $field_name . '\',\'' . $div_other . '\',false);"';
                }
                $f .= ' />';
                $f .= $value;
                $f .= '<br/>';
            }
        } else {
            $fieldId = $field_name;
            if (count($options_array) > 1) {
                $field_name .= '[]';
            }
            foreach ($options_array as $key => $value) {
                $v = str_replace('[[', '<', $value);
                $value = str_replace(']]', '>', $v);
                $f .= '<input type="checkbox" name="';
                $f .= $field_name;
                $f .= '" value="';
                $f .= $value;
                $f .= '" ';
                if (!empty($user_data_arry)) {
                    if (in_array($value, $user_data_arry)) {
                        $f .= 'checked="checked"';
                        $selectedValue = $value;
                    }
                } else {
                    if ($form_type == 'Check_prechecked') {
                        $f .= 'checked="checked"';
                    }
                }
                if(substr(strtolower(trim($value)), 0, 5) === 'other'){
                    $f .='" id="'.$fieldId.'" onclick="show_other(\'' . $fieldId . '\',\'' . $div_other . '\',false);"';
                }

                $f .= ' />';
                $f .= $value;
                $f .= '<br/>';
            }
        }
        $selectedValue = strtolower($selectedValue);
        if(substr($selectedValue, 0, 5) === 'other'){
            $f .='<div id="' . $div_other . '" style="display:block;">';
            $f .='<label>If other:</label><input class="dropdown_other" type="text" value="'.$otherValue.'" id="';
        }else{
            $f .='<div id="' . $div_other . '" style="display:none;">';
            $f .='<label>If other:</label><input class="dropdown_other" type="text" id="';
            
        }

        $f .= $other_field;
        $f .='" name="';
        $f .= $other_field;
        $f .='" onblur="append_other(\'' . $field_name . '\',\'' . $other_field . '\');"/></div>';
        
        
        $this->field_list .= $field_name . '|';
//        $this->edit_field_list .= $field_name . '|';
        return $f;
    }

    function build_cq_radio_field($field_name, $question_options, $response_options, $form_type, $prechecked)
    {
        //<input type="checkbox" name="vehicle" value="Bike" /> I have a bike<br />
        $options_array = explode('|', $question_options);
        #print_r($options_array);exit();
        //this is an exception put in to handle On24 Campaigns that require a true or false
        //value depending on whether boxes are checked. In these cases we'll add a default_options
        //node to the the particular question in the reg_xml. It will have 1 pipe separated true/false value set.
        $f = '';
        $other_field = $field_name . "_other";
        $div_other = 'div_' . $field_name . "_other";
        $selectedValue = "";
        $otherValue = "";
        if (!empty($response_options)) {
            //We need to write a hidden field with same name as checkbox field to hold the unchecked value:
            //http://planetozh.com/blog/2008/09/posting-unchecked-checkboxes-in-html-forms/
            $vals = explode('|', $response_options);
            
            foreach($vals as $key => $res){
                if(substr(strtolower(trim($res)), 0, 5) === 'other'){
                    $getOtherLabel = explode(":", $res);
                    $otherValue = !empty($getOtherLabel[1]) ? $getOtherLabel[1] : "";
                    $vals[$key] = $getOtherLabel[0];
                    break;
                }
            }
            
            if (count($vals) == 2) {
                $v = str_replace('[[', '<', $options_array[0]);
                $value = str_replace(']]', '>', $v);

                $f .= '<input type="radio" name="';
                $f .= $field_name;
                $f .= '" value="';
                $f .= $vals[0];
                $f .= '" ';
                if (strtolower($form_type) == 'radio_prechecked' && $prechecked == $value) {
                    $f .= 'checked="checked"';
                    $selectedValue = $value;
                } else {
                    if ($value == $response_options) {
                        $f .= 'checked="checked"';
                        $selectedValue = $value;
                    }
                }
                    $f .='" id="'.$field_name.'" onclick="show_other(\'' . $field_name . '\',\'' . $div_other . '\',false);"';
                
                $f .= ' />';
                $f .= $vals[0];

                $f .= '<input type="radio" name="';
                $f .= $field_name;
                $f .= '" value="';
                $f .= $vals[1];
                $f .= '"/>';
                $f .= $vals[1];
                $f .= '<br/>';
            } else {
                foreach ($options_array as $key => $value) {
                    $fieldId = $field_name . "_" . $key;
                    $v = str_replace('[[', '<', $value);
                    $value = str_replace(']]', '>', $v);
                    $f .= '<input type="radio" name="';
                    $f .= $field_name;
                    $f .= '" value="';
                    $f .= $value;
                    $f .= '" ';
                    if (strtolower($form_type) == 'radio_prechecked' && $prechecked == $value) {
                        $f .= 'checked="checked"';
                        $selectedValue = $value;
                    } else {
                        if (in_array($value, $vals)) {
                            $f .= 'checked="checked"';
                            $selectedValue = $value;
                        }
                    }
                        $f .='" id="'.$fieldId.'" onclick="show_other(\'' . $fieldId . '\',\'' . $div_other . '\',false);"';
                    
                    $f .= ' />';
                    $f .= $value;
                    $f .= '<br/>';
                }
            }
        } else {
            foreach ($options_array as $key => $value) {
                $fieldId = $field_name . "_" . $key;
                $v = str_replace('[[', '<', $value);
                $value = str_replace(']]', '>', $v);
                $f .= '<input type="radio" name="';
                $f .= $field_name;
                $f .= '" value="';
                $f .= $value;
                $f .= '" ';
                if (strtolower($form_type) == 'radio_prechecked' || $prechecked == $value) {
                    $f .= 'checked="checked"';
                } else {
                    if ($value == $response_options) {
                        $f .= 'checked="checked"';
                    }
                }
                    $f .='" id="'.$fieldId.'" onclick="show_other(\'' . $fieldId . '\',\'' . $div_other . '\',false);"';
                
                $f .= ' />';
                $f .= $value;
                $f .= '<br/>';
            }
        }
        $selectedValue = strtolower($selectedValue);
        if(substr($selectedValue, 0, 5) === 'other'){
            $f .='<div id="' . $div_other . '" style="display:block;">';
            $f .='<label>If other:</label><input class="dropdown_other" type="text" value="'.$otherValue.'" id="';
        }else{
            $f .='<div id="' . $div_other . '" style="display:none;">';
            $f .='<label>If other:</label><input class="dropdown_other" type="text" id="';
            
        }

        $f .= $other_field;
        $f .='" name="';
        $f .= $other_field;
        $f .='" onblur="append_other(\'' . $field_name . '\',\'' . $other_field . '\');"/></div>';
        return $f;
    }

    function build_survey_check_field($field_name, $question_options, $response_options, $form_type, $user_data='')
    {
        $user_data_arry = array();
        $other_field = $field_name . "_other";
        $div_other = 'div_' . $field_name . "_other";
        if (!empty($user_data) && is_string($user_data)) {
            $user_data_arry = explode("|", $user_data);
            $user_data_arry = array_map('trim', $user_data_arry);
        }
        $otherValue = "";
        if(!empty($user_data_arry)){
            foreach($user_data_arry as $key => $res){
                if(substr(strtolower(trim($res)), 0, 5) === 'other'){
                    $getOtherLabel = explode(":", $res);
                    $otherValue = !empty($getOtherLabel[1]) ? $getOtherLabel[1] : "";
                    $user_data_arry[$key] = $getOtherLabel[0];
                    break;
                }
            }
        }
        $options_array = explode('|',$question_options);
        $f = '';
        if (!empty($response_options)) {
            //We need to write a hidden field with same name as checkbox field to hold the unchecked value:
            //http://planetozh.com/blog/2008/09/posting-unchecked-checkboxes-in-html-forms/
            $vals = explode('|', $response_options);

            if (count($vals) == 2) {
                $v = str_replace('[[', '<', $options_array[0]);
                $value = str_replace(']]', '>', $v);
                $value = trim($value);
                $f .='<input type="hidden" name="';
                $f .=$field_name;
                $f .='" value="';
                $f .= $vals[1];
                $f .='"><input type="checkbox" name="';
                $f .=$field_name;
                $f .='" value="';
                $f .= $vals[0];
                $f .='" ';
                if (!empty($user_data_arry)) {
                    if(in_array($value, $user_data_arry)){
                        $f .= 'checked="checked"';
                    }
                } else {
                    if ($form_type == 'Check_prechecked') {
                        $f .= 'checked="checked"';
                    }
                }
                $f .=' />';
                $f .= $value;
                $f .='<br/>';
            }
        } else {
            if (count($options_array) > 1) {
                $field_name .= '[]';
            }
            foreach ($options_array as $key => $value) {
                $v = str_replace('[[', '<', $value);
                $value = str_replace(']]', '>', $v);
                $value = trim($value);
                $f .='<input type="checkbox" name="';
                $f .=$field_name;
                $f .='" id="';
                $f .=$field_name;
                $f .= '" value="';
                $f .= $value;
                $f .='" ';
                if (!empty($user_data_arry)) {
                    if (in_array($value, $user_data_arry)) {
                        $f .= 'checked="checked"';
                    }
                } else {
                    if ($form_type == 'Check_prechecked') {
                        $f .= 'checked="checked"';
                    }
                }
                if ($value == "Others") {
                    $f .=' onchange="showOtherSR(\'' . $div_other . '\');"';
                }
                $f .=' />';
                $f .= $value;
                $f .='<br/>';
                if ($value == 'Others') {
                    $f .='<div id="' . $div_other . '" style="display: none;">';
                    $f .='<label>If other, please specify:</label><input class="dropdown_other" type="text" id="';
                    $f .= $other_field;
                    $f .='" name="';
                    $f .= $other_field;
                    if (!empty($otherValue)) {
                        $f .= '" value="';
                        $f .= $otherValue;
                    }
                    
                    if (isset($field->trigger)) {
                        $f .='" onblur="append_trigger(\'' . $field_name . '\',\'' . $other_field . '\',\'' . $trigger[0] . '\');"/></div>';
                    } else {
                        $f .='" onblur="append_other(\'' . $field_name . '\',\'' . $other_field . '\');"/></div>';
                    }
                }
            }
        }
        $this->field_list .= $field_name . '|';
        return $f;
    }

    function build_dropdown_other_field($field_name, $question_options, $required, $trigger, $response = '') {
        if (isset($trigger)) {
            $trigger = explode("|", $trigger);
}
        $other_field = $field_name . "_other";
        $div_other = 'div_' . $field_name . "_other";

        $f = '<select name="';
        $f .=$field_name;
        $f .='" id="';
        $f .=$field_name;
        if (!empty($trigger[0])) {
            $f .='" onchange="show_trigger(\'' . $field_name . '\',\'' . $div_other . '\',\'' . $trigger[0] . '\');"';
        } else {
            $f .='" onchange="show_other(\'' . $field_name . '\',\'' . $div_other . '\');"';
        }

        if ($required == 'true') {
            $f .=' required="required"';
        }
        if($response ==''){
            $f .='"><option value="">--SELECT--</option>';
        }else{
            $f .='"><option value="" selected="selected">--SELECT--</option>';
        }
        @$options_array = unserialize($question_options);
        if(empty($options_array)){
            $options_array = explode('|',$question_options);
        }

        foreach ($options_array as $key => $value) {
            $f .='<option value="';
            if ($field_name == "country") {
                $f .= $key;
            } else if(substr($value, 0, 5) === 'other'){
                $f .= $response;
            } else {
                $f .= $value;
            }
            $f .='"';

            if((substr(strtolower($response), 0, 5) === 'other' && substr(strtolower($value), 0, 5) === 'other') || $value == $response){
                $f .=' selected=selected';
            }
            $f .='>';
            $f .=$value;
            $f .='</option>';
        }
        $f .='</select>';
        if(substr(strtolower($response), 0, 5) === 'other'){
            $f .='<div id="' . $div_other . '" style="">';
            if (!empty($trigger[1])) {
                $f .='<label>' . $trigger[1] . ':</label><input class="dropdown_other" type="text" id="';
            } else {
                $othertxt = explode(':',$response);
                $otherVal = isset($othertxt[1]) ? $othertxt[1] : "";
                $f .='<label>If other:</label><input class="dropdown_other" type="text" value="'.$otherVal.'" id="';
            }
        }else{
            $f .='<div id="' . $div_other . '" style="display:none;">';
            if (!empty($trigger[1])) {
                $f .='<label>' . $trigger[1] . ':</label><input class="dropdown_other" type="text" id="';
            } else {
                $f .='<label>If other:</label><input class="dropdown_other" type="text" id="';
            }
        }

        $f .= $other_field;
        $f .='" name="';
        $f .= $other_field;
        if (isset($field->trigger)) {
            $f .='" onblur="append_trigger(\'' . $field_name . '\',\'' . $other_field . '\',\'' . $trigger[0] . '\');"/></div>';
        } else {
            $f .='" onblur="append_other(\'' . $field_name . '\',\'' . $other_field . '\');"/></div>';
        }

        return $f;
    }
}

?>