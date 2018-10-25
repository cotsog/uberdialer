<style>
    .incentive-label {
        width: 100% !important;
    }
    #survey_questions_id input{
        background: white !important;
    }
    #survey_error, #gdpr_error, #cq_error {
        color: #FD0000;
    }
    #newemail_error {
        color: #FD0000;
    }
    #resource_error {
        display: none;
    }
    .error-msg2 {
        margin-top: 0 !important;
    }

    #other-contacts {
        font-size: 13px;
        margin-bottom: 2%;
    }

    </style>
<?php if (!empty($todayCallDiallerMessage)) {
    $classIcon = 'times-circle';
    $errorTypeIcon = 'bad';
    if(!empty($errorType)){
        $classIcon = $errorTypeIcon = $errorType;
        $todayCallDiallerMessage = "<b>" . $todayCallDiallerMessage . "</b>";
    }
    
?>
    <div id="divErrorMsg" class="error-msg <?php echo $errorTypeIcon; ?> margin-top-15">
        <p><span><i class="fa fa-<?php echo $classIcon; ?>"></i></span><?php echo $todayCallDiallerMessage; ?></p>
    </div>
<?php } 
$readOnly = (!$editableFieldAll) ? "readonly" : "";
?>

<section class="section-content-main-area">
    <div class="content-main-area">
        <?php
        $setErrorMessage2 = ""; 
        if ((isset($msg) && $msg != '') || $this->session->flashdata('msg') != '') {
            if ($this->session->flashdata('class') == 'good'){
                $class = "class= 'error-msg good'";
                $classIcon = "fa-check-circle";
            }else{
                $class = "class='error-msg  bad'";
                $classIcon = "fa-times-circle";
            }
            echo('<div id="divErrorMsg" ' . $class . '>');
            echo (' <p><span><i class="fa '.$classIcon.'"></i></span>');
            echo $this->session->flashdata('msg');
            echo('</div>');
            $setErrorMessage2 = "error-msg2";
        }
        if(!empty ($gdprErrorMessage)){
            $class = "class='error-msg {$setErrorMessage2} bad '";
            echo('<div id="divErrorMsg" ' . $class . '>');
            echo (' <p><span><i class="fa fa-times-circle"></i></span>');
            echo $gdprErrorMessage;
            echo('</div>');
        }
        ?>
        <?php if (validation_errors() != '') { ?>
        <div id="divErrorMsg" class="alert alert-warning server-validation-msg">
            <p><strong>Please fix the following input errors:</strong></p><?php echo validation_errors(); ?>
        </div>
        <?php } ?>
    <div class="column-header query-list">
                <div style="width: 790px" class="alignleft">
                    <div class="alignleft">
                    <span
                        class="column-title"><strong><?php echo $contactCallDetail->name . " (" . $contactCallDetail->eg_campaign_id . ")" ?> </strong></span>
                    </div>

                </div>

    </div>
        <?php 
        $addDiffMessage = "<div style='color:red;font-size:12px;margin-top:50px;margin-left:10px'>*Added as a Different Person</div>";
        if (!$isAddPage) {
            if(!empty($contactCallDetail->original_list)){
                if(!empty($contactCallDetail->source) && $contactCallDetail->source == 'add_diff'){
                    echo "<div style='color:red;font-size:12px;margin-top:50px;margin-left:10px'>*Added as a Different Person but already exists on List '".$contactCallDetail->original_list."'. ";
                }else{
                    echo "<div style='color:red;font-size:12px;margin-top:50px;margin-left:10px'>*Manually Added Contact but already exists on List '".$contactCallDetail->original_list."'. ";
                }
                
                
                if(isset($contactCallDetail->call_disposition_id) && isset($nonWorkableDispositions) && in_array($contactCallDetail->call_disposition_id, $nonWorkableDispositions) && !empty($this->input->get('add_diff'))) {
                    echo "Based on the last disposition, contact is non-workable.";
                }
                echo "</div>";
            } else {
                if(!empty($contactCallDetail->source) && $contactCallDetail->source == 'add_diff'){
                        echo $addDiffMessage;
                }else if(!empty($contactCallDetail->source) && $contactCallDetail->source == 'form'){
                        echo "<div style='color:red;font-size:12px;margin-top:50px;margin-left:10px'>*Manually Added Contact</div>";
                }
            }
        }else{
            echo $addDiffMessage;
        }
        ?>
        <form method="post" role="form" id="call_detail_form" class="call-history-form" name="form" autocomplete="off"
              novalidate="novalidate" action="/dialer/calls/index/<?php echo $contactCallDetail->campaign_contact_id; ?>/<?php echo $contactCallDetail->list_id; ?>">
            <div class="col-lg-5  pad-15-b">
                <div class="pad-15-t pad-15-l row-left-pad call-row-title">
                    <div class="column-header alignleft contact-detail-header">
                        <p class="alignleft">Contact Details</p>
                        <?php if (isset($_GET['action']) && $_GET['action'] == 'qa' && isset($status) && isset($contactCallDetail->qa) && ($this->session->userdata('uid') == $contactCallDetail->qa) && !$isAddPage && ($status == 'Reject' || $status == 'Follow-up')) { ?>
                            <a title="Retract Contact"
                               href="/dialer/calls/retractContact/<?php echo $contactCallDetail->lead_id; ?>"
                               class="general-btn"
                               id="retract_button">Retract</a>
                        <?php } ?>

                    </div>
                </div>
                <div class="pad-15-t pad-15-l row-left-pad">
                    <table class="table table-bordered row vertical-tbl  <?php if((!$isViewPage) || ($save_contact_visible)){ ?> contact-tbl <?php }?>" id="editableContact">
                        <tbody>
                        <tr>
                            <th>First Name: <span class="alert-required">*</span></th>

                            <td>
                                <?php if($isViewPage && !$save_contact_visible){ ?>
                                    <?php if (!empty($contactCallDetail->first_name)) {
                                    echo $contactCallDetail->first_name;
                                } ?>
                                <?php } else{ ?>
                                <input type="text" id="first_name" name="first_name" maxlength="100"
                                       placeholder="First Name" <?=$readOnly?>
                                                                 value="<?php if (!empty($contactCallDetail->first_name)) {
                                                                     echo $contactCallDetail->first_name;
                                                                 } ?>"/>
                                <?php } ?>
                            </td>

                        </tr>
                        <tr>
                            <th>Last Name: <span class="alert-required">*</span></th>
                            <td>
                                <?php if($isViewPage && !$save_contact_visible){ ?>
                                <?php if (!empty($contactCallDetail->last_name)) {
                                    echo $contactCallDetail->last_name; }?>
                                 <?php } else{ ?>
                                <input type="text" id="last_name" name="last_name"
                                                                 maxlength="100"
                                       placeholder="Last Name" <?=$readOnly?>
                                                                 value="<?php if (!empty($contactCallDetail->last_name)) {
                                                                     echo $contactCallDetail->last_name;
                                                                 } ?>"/>
                                <?php } ?>
                            </td>
                        </tr>
                        <tr>
                            <th> Company:</th>
                            <td>
                                <?php if($isViewPage && !$save_contact_visible){ ?>
                                <?php if (!empty($contactCallDetail->company)) {
                                    echo $contactCallDetail->company; } ?>
                                <?php } else{ ?>
                                <input type="text" id="company" name="company" maxlength="100"
                                       placeholder="Company" <?=$readOnly?>
                                                                 value="<?php if (!empty($contactCallDetail->company)) {
                                                                     echo $contactCallDetail->company;
                                                                 } ?>"/>
                                <?php } ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Job Title:</th>
                            <td>
                                <?php if($isViewPage && !$save_contact_visible){ ?>
                                <?php if (!empty($contactCallDetail->job_title)) {
                                    echo $contactCallDetail->job_title; } ?>
                                <?php } else{ ?>
                                <input type="text" id="job_title" name="job_title"
                                                                 maxlength="255"
                                       placeholder="Job Title" <?=$readOnly?>
                                                                 value="<?php if (!empty($contactCallDetail->job_title)) {
                                                                     echo $contactCallDetail->job_title;
                                                                 } ?>"/>
                                <?php } ?>
                            </td>
                        </tr>
                        <?php if (!$isAddPage) { ?>
                        <tr>
                            <th>Job Function:</th>
                            <td><?php echo !empty($contactCallDetail->job_function) ? $contactCallDetail->job_function : ''; ?></td>
                        </tr>
                        <?php } ?>
                        <?php if (!$isAddPage) { ?>
                        <tr>
                            <th>Job Level:</th>
                            <td><?php echo !empty($contactCallDetail->job_level) ? $contactCallDetail->job_level : ''; ?></td>
                        </tr>
                        <?php } ?>
                        <tr>
                            <th>Phone No: <span class="alert-required">*</span></th>
                            <td style="background: #dddddd;">
                                <?php if($isViewPage && !$save_contact_visible){ ?>
                                    <?php if (!empty($contactCallDetail->phone)) {
                                        if (isset($contactCallDetail->dial_code)) {
                                            echo $contactCallDetail->dial_code;
                                        }
                                        echo $contactCallDetail->phone;
                                    } ?>
                                <?php } else {?>
                                <input type="text" id="phone-number" readonly name="phone" maxlength="20"
                                       placeholder="Phone" class="width_30"
                                       value="<?php if (!empty($contactCallDetail->phone)) {
                                           if (isset($contactCallDetail->dial_code)) {
                                               echo $contactCallDetail->dial_code;
                                           }
                                           echo $contactCallDetail->phone;
                                       } ?>"/>
                                 <?php if(!$Qaing) { ?>
                                <?php if($this->isConference && (empty($this->isConferenceOffices) || in_array($this->session->userdata('telemarketing_offices'), $this->isConferenceOffices) || in_array($contactCallDetail->logged_user_type, $this->config->item('upper_management_types')))){?>
                                <a href="javascript:void(0)"  class="btn general-btn disable-btn-make-call"
                                    <?php
                                    if (!empty($contactCallDetail->logged_user_type) && $contactCallDetail->logged_user_type != 'qa' ) {

                                        if (!$isTodayExceedCallDial && !$isViewPage  && (isset($contactCallDetail->locked_by) && ($contactCallDetail->locked_by == $this->session->userdata('uid')) ||  isset($contactCallDetail->edit_lead_status) && ($contactCallDetail->edit_lead_status == 0 || $contactCallDetail->edit_lead_status == false))) {
                                            if ($contactCallDetail->logged_user_type != 'qa') {
                                                $agentSessionCampaignId = $this->session->userdata('AgentSessionCampaignID');
                                                if ($contactCallDetail->logged_user_type != 'manager' && !empty($agentSessionCampaignId) && ($agentSessionCampaignId == $contactCallDetail->campaign_id)) {
                                                    ?> id="internalConfMakeCallBtn"
                                                <?php } else if($contactCallDetail->logged_user_type == 'manager' || in_array($contactCallDetail->logged_user_type, $this->config->item('upper_management_types'))){ ?> id="internalConfMakeCallBtn"
                                                <?php }
                                            } else {
                                            ?> id="internalConfMakeCallBtn" <?php }
                                        }
                                    } ?>
                                    > Direct <i class="fa fa-phone"></i></a>
<?php }else{ ?>
<a href="javascript:void(0)"  class="btn general-btn disable-btn-make-call"
                                    <?php
                                    if (!empty($contactCallDetail->logged_user_type) && $contactCallDetail->logged_user_type != 'qa' ) {

                                        if (!$isTodayExceedCallDial && !$isViewPage  && (isset($contactCallDetail->locked_by) && ($contactCallDetail->locked_by == $this->session->userdata('uid')) ||  isset($contactCallDetail->edit_lead_status) && ($contactCallDetail->edit_lead_status == 0 || $contactCallDetail->edit_lead_status == false))) {
                                            if ($contactCallDetail->logged_user_type != 'qa') {
                                                $agentSessionCampaignId = $this->session->userdata('AgentSessionCampaignID');
                                                if ($contactCallDetail->logged_user_type != 'manager' && !empty($agentSessionCampaignId) && ($agentSessionCampaignId == $contactCallDetail->campaign_id)) {
                                                    ?> id="editContactCallAccess"
                                                <?php } else if($contactCallDetail->logged_user_type == 'manager' || in_array($contactCallDetail->logged_user_type, $this->config->item('upper_management_types'))){ ?> id="editContactCallAccess"
                                                <?php }
                                            } else {
                                            ?> id="editContactCallAccess" <?php }
                                        }
                                    } ?>
                                    > Direct <i class="fa fa-phone"></i></a>
<?php } ?>

                                 <!--<a href="javascript:void(0)" class="btn general-btn disable-btn-make-call"
                                    <?php
                                    if (!empty($contactCallDetail->logged_user_type) && $contactCallDetail->logged_user_type != 'qa' ) {

                                        if (!$isTodayExceedCallDial && !$isViewPage  && (isset($contactCallDetail->locked_by) && ($contactCallDetail->locked_by == $this->session->userdata('uid')) ||  isset($contactCallDetail->edit_lead_status) && ($contactCallDetail->edit_lead_status == 0 || $contactCallDetail->edit_lead_status == false))) {
                                            if ($contactCallDetail->logged_user_type != 'qa') {
                                                $agentSessionCampaignId = $this->session->userdata('AgentSessionCampaignID');
                                                if ($contactCallDetail->logged_user_type != 'manager' && !empty($agentSessionCampaignId) && ($agentSessionCampaignId == $contactCallDetail->campaign_id)) {
                                                    ?> id="internalConfMakeCallBtn"
                                                <?php } else if($contactCallDetail->logged_user_type == 'manager' || in_array($contactCallDetail->logged_user_type, $this->config->item('upper_management_types'))){ ?> id="internalConfMakeCallBtn"
                                                <?php }
                                            } else {
                                                ?> id="internalConfMakeCallBtn"  <?php }
                                        }
                                    } ?>
                                    >Conference <i class="fa fa-phone"></i></a>-->
                                <?php } }?>
                            </td>
                        </tr>
                        <tr>
                            <th>Phone Extension: </th>
                            <td>
                                <?php if($isViewPage && !$save_contact_visible){ ?>
                                    <?php if (!empty($contactCallDetail->ext)) {
                                        echo $contactCallDetail->ext;
                                    } ?>
                                <?php } else { ?>
                                <input type="text" id="ext" <?=$readOnly?> name="ext" maxlength="20" placeholder="Ext" class="width_30" value="<?=$contactCallDetail->ext?>"/>
                                <?php } ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Alternate No:</th>
                            <td>
                                <?php if($isViewPage && !$save_contact_visible){ ?>
                                    <?php if (!empty($contactCallDetail->alternate_no)) {
                                        if (isset($contactCallDetail->dial_code)) {
                                            echo $contactCallDetail->dial_code;
                                        }
                                        echo $contactCallDetail->alternate_no;
                                    } ?>
                                <?php } else {?>
                                <input type="text" id="alternate_no" <?=$readOnly?> name="alternate_no" maxlength="20" onkeypress="return isNumberKey(event)"
                                       placeholder="Alternate No" class="width_30"
                                       value="<?php if (!empty($contactCallDetail->alternate_no)) {
                                           if (isset($contactCallDetail->dial_code)) {
                                               echo $contactCallDetail->dial_code;
                                           }
                                           echo $contactCallDetail->alternate_no;
                                       } ?>"/>
                                <?php if(!$Qaing) { ?>
                                    <a href="javascript:void(0)"  class="btn general-btn disable-btn-make-call"
                                    <?php
                                    if (!empty($contactCallDetail->logged_user_type) && $contactCallDetail->logged_user_type != 'qa' ) {
                                        if($this->isConference && (empty($this->isConferenceOffices) || in_array($this->session->userdata('telemarketing_offices'), $this->isConferenceOffices) || in_array($contactCallDetail->logged_user_type, $this->config->item('upper_management_types')))){
                                            $callType = "internalAltConfMakeCallBtn";
                                        }else{
                                            $callType = "editAltContactCallAccess";
                                        }
                                        if (!$isTodayExceedCallDial && !$isViewPage  && (isset($contactCallDetail->locked_by) && ($contactCallDetail->locked_by == $this->session->userdata('uid')) ||  isset($contactCallDetail->edit_lead_status) && ($contactCallDetail->edit_lead_status == 0 || $contactCallDetail->edit_lead_status == false))) {
                                            if ($contactCallDetail->logged_user_type != 'qa') {
                                                $agentSessionCampaignId = $this->session->userdata('AgentSessionCampaignID');
                                                if ($contactCallDetail->logged_user_type != 'manager' && !empty($agentSessionCampaignId) && ($agentSessionCampaignId == $contactCallDetail->campaign_id)) {
                                                    ?> id="<?php echo $callType; ?>"
                                                <?php } else if($contactCallDetail->logged_user_type == 'manager' || in_array($contactCallDetail->logged_user_type, $this->config->item('upper_management_types'))){ ?> id="<?php echo $callType; ?>"
                                                <?php }
                                            } else {
                                                ?> id="<?php echo $callType; ?>" <?php }
                                        }
                                    } ?>
                                        > Direct <i class="fa fa-phone"> </i></a>

                                <?php } } ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Email: <span class="alert-required">*</span></th>
                            <td <?php if (!$isAddPage){ ?>style="background: #dddddd;" <?php } ?>>
                                <?php if($isViewPage && !$save_contact_visible){ ?>
                                    <?php if (!empty($contactCallDetail->email)) {
                                    echo $contactCallDetail->email;
                                } ?>
                                <?php } else {?>
                                <input type="text" id="email" <?=$readOnly?> name="email" maxlength="100"
                                       placeholder="Email" <?php if (!$isAddPage){ ?>readonly<?php } ?>
                                       value="<?php if (!empty($contactCallDetail->email)) {
                                           echo $contactCallDetail->email;
                                       } ?>"/><span id='email_loader'></span>
                                <?php }?>
                            </td>
                        </tr>
                        <?php 
                        if ($isAddPage &&!$isViewPage && $Qaing && $contactCallDetail->logged_user_type == 'qa' && !empty($contactCallDetail->member_id)){ ?>
                            <tr id="email_changed">
                                <th>Email has changed:</th>
                                <td style="padding: 10px;">
                                    <div class="form-input">
                                        <input type="checkbox" tabindex="6" id="emailChange" class="css-checkbox"
                                               name="emailChange" value="1">
                                        <label class="css-label checkbox-label radGroup1 cst-export-lbl"
                                               for="emailChange"></label>
                                    </div>
                                </td>
                            </tr>
                            <tr style="display: none;" id="new_email_content_box">
                                <th>New Email: <span class="alert-required">*</span></th>
                                <td>
                                    <input type="text" id="newemail" name="newemail" maxlength="100"
                                           placeholder="New Email"
                                           value=""/>
                                </td>
                            </tr>
                        <?php }else if (!$isAddPage &&!$isViewPage && $Qaing && $contactCallDetail->logged_user_type == 'qa'){ ?>
                        <tr id="email_changed">
                                <th>Email has changed:</th>
                                <td style="padding: 10px;">
                                    <div class="form-input">
                                        <input type="checkbox" tabindex="6" id="emailChange" class="css-checkbox"
                                               name="emailChange" value="1">
                                        <label class="css-label checkbox-label radGroup1 cst-export-lbl"
                                               for="emailChange"></label>
                                    </div>
                                </td>
                            </tr>
                            <tr style="display: none;" id="new_email_content_box">
                                <th>New Email: <span class="alert-required">*</span></th>
                                <td>
                                    <input type="text" id="newemail" name="newemail" maxlength="100"
                                           placeholder="New Email"
                                           value=""/>
                                    <div id="newemail_error"></div>
                                </td>
                            </tr>
                        <?php }?>
                        <tr>
                            <th>Address:</th>
                            <td>
                                <?php if($isViewPage && !$save_contact_visible){ ?>
                                    <?php if (!empty($contactCallDetail->address)) {
                                    echo $contactCallDetail->address;
                                    } ?>
                                <?php } else {?>
                                <input type="text" id="address" <?=$readOnly?> name="address" maxlength="100"
                                       placeholder="Address" value="<?php if (!empty($contactCallDetail->address)) {
                                           echo $contactCallDetail->address;
                                       } ?>"/>
                                <?php }?>
                            </td>
                        </tr>
                        <tr>
                            <th>City:</th>
                            <td>
                                <?php if($isViewPage && !$save_contact_visible){ ?>
                                    <?php if (!empty($contactCallDetail->city)) {
                                    echo $contactCallDetail->city;
                                    } ?>
                                <?php } else {?>
                                <input type="text" id="city" <?=$readOnly?> name="city" maxlength="45"
                                       placeholder="City"
                                       value="<?php if (!empty($contactCallDetail->city)) {
                                           echo $contactCallDetail->city;
                                       } ?>"/>
                                <?php } ?>
                            </td>
                        </tr>
                        <tr>
                            <th>State:</th>
                            <td>
                                <?php if($isViewPage && !$save_contact_visible){ ?>
                                    <?php if (!empty($contactCallDetail->state)) {
                                    echo $contactCallDetail->state;
                                    } ?>
                                <?php } else {?>
                                <input type="text" id="state" <?=$readOnly?> name="state" maxlength="25"
                                       placeholder="State" value="<?php if (!empty($contactCallDetail->state)) {
                                           echo $contactCallDetail->state;
                                       } ?>"/>
                                <?php } ?>
                            </td>

                        </tr>
                        <tr>
                            <th>Postal Code:</th>
                            <td>
                                <?php if($isViewPage && !$save_contact_visible){ ?>
                                    <?php if (!empty($contactCallDetail->zip)) {
                                    echo $contactCallDetail->zip;
                                    } ?>
                                <?php } else {?>
                                <input type="text" id="zip" <?=$readOnly?> name="zip" maxlength="20"
                                       placeholder="Postal Code" value="<?php if (!empty($contactCallDetail->zip)) {
                                           echo $contactCallDetail->zip;
                                       } ?>"/>
                                <?php } ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Country:</th>
                            <td>
                                <?php if($isViewPage && !$save_contact_visible){ ?>
                                    <?php if (!empty($contactCallDetail->country)) {
                                        echo strtoupper($contactCallDetail->country);
                                    } ?>
                                <?php } else {?>
                                <div class="styled select-dropdown table-dropdown">
                                
                                    
                                    <?php
                                    $countryOptions = '<option selected="selected" value="">--SELECT--</option>';
                                    $countryCodeValue = "";
                                    $countryValue = "";
                                    if (!empty($countries)) {
                                        foreach ($countries as $country) {
                                            $countryCode = trim($country->country_code);
                                            $country = trim($country->country);
                                            $countryMatch = $contactCallDetail->country == $countryCode;
                                            $selectedCountry = $countryMatch ? 'selected="selected"' : '';
                                            if(!$editableFieldAll && !empty($contactCallDetail->country) && $countryMatch){
                                                $countryCodeValue = $countryCode;
                                                $countryValue = $country;
                                                break;    
                                            }else{
                                                $countryOptions .= "<option role='option' value='{$countryCode}' {$selectedCountry} >{$country}</option>";
                                            }
                                            ?>
                                            <!-- <option role="option" value="<?= $countryCode;?>" <?= strtolower($contactCallDetail->country) == $countryCode ? 'selected="selected"' : '' ?>><?= $country;?></option> -->
                                            
                                        <?php }
                                    }
                                    if($editableFieldAll){
                                    ?>
                                    <select name="country" id="country">
                                        <?php echo $countryOptions;?>
                                    </select>
                                    <?php }else{ ?>
                                        <input type="hidden" id="country"  name="country" maxlength="100"
                                       placeholder="Postal Code" value="<?php echo !empty($countryCodeValue) ? $countryCodeValue : '' ?>"/>
                                      <?php  
                                      echo !empty($countryValue) ? $countryValue : '';
                                      } ?>
                                    </div>
                                <?php }?>
                                <!-- <input type="text" id="country" name="country" maxlength="100"
                                       placeholder="Country"
                                       value="<?php /*if (!empty($contactCallDetail->country)) {
                                    echo $contactCallDetail->country;
                                       } */?>"/>-->
                            </td>
                        </tr>
                        <tr>
                            <th>Time Zone:</th>
                            <td>
                                <?php if($isViewPage && !$save_contact_visible){ ?>
                                    <?php if (!empty($contactCallDetail->time_zone)) {
                                    echo $contactCallDetail->time_zone;
                                    } ?>
                                <?php } else {?>
                                <input type="text" id="time_zone" <?=$readOnly?> name="time_zone" maxlength="100"
                                       placeholder="Time Zone" value="<?php if (!empty($contactCallDetail->time_zone)) {
                                           echo $contactCallDetail->time_zone;
                                       } ?>"/>
                               <?php } ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Industry:</th>
                            <td>
                                <?php if($isViewPage && !$save_contact_visible){ ?>
                                    <?php if (!empty($contactCallDetail->industry)) {
                                    echo $contactCallDetail->industry;
                                    } ?>
                                <?php } else {?>
                                    <?php if (!empty($industriesValues)) {
                                            if($editableField){
                                                ?>
                                                <div class="styled select-dropdown table-dropdown" id ="industry_validation">
                                                <select name="industry" id="industry">
                                                    <option selected="selected" value="">--SELECT--</option>
                                                    <?php
                                                    if (!empty($industriesValues)) {
                                                        foreach ($industriesValues as $industryValue) {
                                                            $industryValue = trim($industryValue);?>
                                                            <option role="option" value="<?= $industryValue;?>" <?= $contactCallDetail->industry == $industryValue ? 'selected="selected"' : '' ?>><?= $industryValue;?></option>
                                                        <?php }
                                                    }
                                            }else{
                                                ?>
                                                <input type="text" name="industry" id="industry" readonly value="<?php echo $contactCallDetail->industry; ?>" />
                                            <?php 
                                            }
                                        } ?>
                                <?php } ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Employee Size:</th>
                            <td>
                                <?php if($isViewPage && !$save_contact_visible){ ?>
                                    <?php if (!empty($contactCallDetail->company_size)) {
                                        if($contactCallDetail->company_size == '1-9'){
                                            $contactCallDetail->company_size = '1 to 9';
                                        }
                                        if($contactCallDetail->company_size == '10-24'){
                                            $contactCallDetail->company_size = '10 to 24';
                                        }
                                         echo $contactCallDetail->company_size;
                                    } ?>
                                <?php } else {?>
                                        <?php
                                        if (!empty($companySizeValues)) {
                                            if ($contactCallDetail->company_size == "1-9") {
                                           $contactCallDetail->company_size = '1 to 9';
                                        }
                                        if ($contactCallDetail->company_size == "10-24") {
                                           $contactCallDetail->company_size = '10 to 24';
                                        }
                                            if($editableField){
                                                ?>
                                                <div class="styled select-dropdown table-dropdown" id ="company_size_validation">
                                                <select name="company_size" id="company_size">
                                                    <option selected="selected" value="">--SELECT--</option>
                                                    <?php
                                                    if (!empty($companySizeValues)) {
                                                        foreach ($companySizeValues as $companySize) {
                                                            $companySize = trim($companySize);?>
                                                            <option role="option" value="<?= $companySize;?>" <?= $contactCallDetail->company_size == $companySize ? 'selected="selected"' : '' ?>><?= $companySize;?></option>
                                                        <?php }
                                                    }
                                            }else{
                                                
                                            ?>
                                                <input type="text" name="company_size" id="company_size" readonly value="<?php echo $contactCallDetail->company_size ?>" />
                                            <?php 
                                            
                                            }
                                        }
                                        ?>
                                </div>
                                <?php } ?>
                            </td>
                        </tr>
                        <?php if (!empty($companyRevenueForm)) {
                            $companyRevenueForm = explode('|', $companyRevenueForm);
                            ?>
                        <tr>
                            <th>Company Revenue:</th>
                            <td>
                                <?php if($isViewPage && !$save_contact_visible){ ?>
                                    <?php if (!empty($contactCallDetail->company_revenue)) {
                                    echo $contactCallDetail->company_revenue;
                                    } ?>
                                <?php } else {
                                    if($editableField){
                                                ?>
                                                <div class="styled select-dropdown table-dropdown">
                                                <select name="company_revenue" id="company_revenue">
                                                    <option selected="selected" value="">--SELECT--</option>
                                                    <?php
                                                    foreach ($companyRevenueForm as $companyRevenue) {
                                                        $companyRevenue = trim($companyRevenue);?>
                                                        <option role="option" value="<?= $companyRevenue;?>" <?= $contactCallDetail->company_revenue == $companyRevenue ? 'selected="selected"' : '' ?>><?= $companyRevenue;?></option>
                                                    <?php }
                                    }else {
                                        if (!empty($contactCallDetail->company_revenue)){
                                        ?>
                                        <input type="text" name="company_revenue" id="company_revenue" readonly value="<?php echo $contactCallDetail->company_revenue ?>" />
                                        <?php }else{ ?>
                                        <input type="text" name="company_revenue" id="company_revenue" value="" />
                                        <?php    
                                        } 
                                    } 
                                }
                                    ?>
                            </td>
                        </tr>
                        <?php } ?>
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
                        <tr>
                            <th>TM Brand:</th>
                            <td class="editableformlabel nospaceword-break">
                                <?php echo $contactCallDetail->site_name; ?>
                            </td>
                        </tr>
                        <input type="hidden" id="contact_id" name="contact_id"
                               value="<?php echo isset($contactCallDetail->id) ? $contactCallDetail->id : set_value('id'); ?>">
                        <input type="hidden" id="original_owner" name="original_owner"
                               value="<?php echo isset($contactCallDetail->original_owner) ? $contactCallDetail->original_owner : set_value('original_owner'); ?>">
                        </tbody>
                    </table>

                </div>
                <!--<div><input type="text" class="general-btn" id="testclick" value="ddd"/></div>-->
                <!-- Call History Email History-->

                <div class="pad-15-t pad-15-l row-left-pad">
                <div id="history-tab-container" class='tab-container'>
                        <ul class='etabs'>
                            <li class='tab'><a href="#call_history_tab">Call History</a></li>
                            <li class='tab'><a href="#email_history_tab">Email History</a></li>
                        </ul>

                            <div id="call_history_tab">
                            <?php if (!empty($contactCallHistoryList)) { 
                                if($contactCallDetail->status == 'Approve'){ $bgcolor = "#008000"; $color = "#fff";}
                                elseif($contactCallDetail->status == 'Reject'){ $bgcolor = "#FF0000"; $color = "#fff";}
                                elseif($contactCallDetail->status == 'QA in progress' || $contactCallDetail->status == 'Pending'){ $bgcolor = "#FFFF00"; $color = "#000";}
                                elseif($contactCallDetail->status == 'Duplicate Lead'){ $bgcolor = "#808080"; $color = "#fff";}
                                elseif($contactCallDetail->status == 'Follow-up'){ $bgcolor = "#FF5733"; $color = "#fff";}
                                else{$bgcolor = ""; $color = "";}
                            ?>
                            <table id="call-history-tbl" class="table table-bordered row call-history-tbl mar-b-0">
                        <thead>
                            <tr>
                                <th style='width:12%;'>Last call made</th>
                                <th style='width:10%;'>ID</th>
                                <th style='width:14%;'>Name</th>
                                <th style='width:12%;'>Result/ Status</th>
                                <th style='width:12%;'>Agent</th>
                                <th style='width:8%;'>Rec. link</th>
                                <th style='width:8%;'>Sec</th>
                                <th style='width:12%;'>TM Brand</th>
                                <th style='width:12%;'>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                                <?php 
                                $i = 0;
                                foreach ($contactCallHistoryList as $key => $contactCallHistory) {
                                if ($key < 5) {
                                    echo '<tr>';
                                $content_notes = limit_words($contactCallHistory->notes, 10);
                                ?>
                                <td><?php echo php_datetimeformat($contactCallHistory->created_at); ?></td>
                                <td><?php echo $contactCallHistory->campaign_id; ?></td>
                                <td><?php echo $contactCallHistory->campaignName; ?></td>
                                <?php if($contactCallHistory->result_Status == "Lead for Qualification") {?>
                                <td <?php if($contactCallHistory->campaign_id && $i==0){ $i++; echo "id='lastDispo' class='{$contactCallHistory->campaign_id}' ";} ?> style="background-color:<?php echo $bgcolor;?>; color:<?php echo $color;?>; "><?php echo $contactCallHistory->result_Status; ?></td>
                                <?php }else {?>    
                                    <td <?php if($contactCallHistory->campaign_id && $i==0){ $i++; echo "id='lastDispo' class='{$contactCallHistory->campaign_id}' ";} ?>><?php echo $contactCallHistory->result_Status; ?></td>
                                <?php }?>    
                                <td><?php echo $contactCallHistory->agent_tl_first_name; ?></td>
                                <td id="rec_link_<?php echo $contactCallHistory->plivo_id; ?>">
                                    <?php if (!empty($contactCallHistory->recording_url)) { ?>
                                        <a href="<?php echo $contactCallHistory->recording_url; ?>"
                                           target="_blank">Rec</a>
                                    <?php }else{ ?>
                                        <a id="retrieve_<?php echo $contactCallHistory->plivo_id; ?>"  href="javascript:" onclick="retrieve_recording('<?php echo $contactCallHistory->call_uuid; ?>','<?php echo $contactCallHistory->plivo_id; ?>','<?php echo $contactCallHistory->conf_sid; ?>')" style="color: #b10a11">Retrieve</a>
                                        <div id="message_<?php echo $contactCallHistory->plivo_id; ?>"></div>
                                    <?php } ?>
                                </td>
                                <td><?php echo $contactCallHistory->duration; ?></td>
                                <td>
                                    <?php echo $contactCallHistory->site_name; ?>
                                </td>
                                <td class="break-all-word"><p><?php echo $content_notes['start']; ?></p>
                                    <?php if ($content_notes['end'] != ""): ?>
                                    <a href="javascript:void(0)" id="example<?php echo $key; ?>-show"
                                       class="showLink"
                                           onclick="showHide('example<?php echo $key; ?>');return false;">See
                                            more.</a>
                                    <div id="example<?php echo $key; ?>" class="more">
                                        <p><?php echo $content_notes['end']; ?><?php /*echo substr($contactCallHistory->notes,11); */ ?></p>

                                                        <p><a href="javascript:void(0)" id="example-hide"
                                                              class="hideLink"
                                              onclick="showHide('example<?php echo $key; ?>');return false;">Hide
                                                this content.</a></p>
                                    </div>
                                <?php endif; ?>
                            </td>
                                <?php
                                    echo '</tr>';
                                }}?>

                        </tbody>
                    </table>
                            <?php if (count($contactCallHistoryList) > 5) {
                                echo '<div class="view_all_btn"><a class="general-btn" href="/dialer/calls/view_all_call_history/' . $contactCallDetail->id . '/'.$contactCallDetail->list_id. '" target="_blank">View All</a></div>';
                                }

                            } else {
                            echo "<div id ='call_history_no_record_div' class='no_record_found'>No record found</div>";
                            }?>
                            </div>
                            <div id="email_history_tab">
                               <table class="table table-bordered row mar-b-0">
                        <thead>
                        <?php
                        if (!empty($contact_email_history_list)) { ?>
                            <tr>
                                <th>User Name</th>
                                <th>Resource Name</th>
                                <th>Timestamp</th>
                            </tr>
                        <?php } else {
                            echo "<div class='no_record_found'>No record found</div>";
                        } ?>

                        </thead>
                        <tbody>
                        <?php if (!empty($contact_email_history_list)) {

                            foreach ($contact_email_history_list as $key => $email_history_list) {

                                if ($key < 5) {
                                    echo '<tr>';
                                    ?>
                                    <td><?php echo $email_history_list->agent_name; ?></td>
                                    <td><?php echo $email_history_list->resource_name; ?></td>
                                    <td><?php echo php_datetimeformat($email_history_list->created_at); ?></td>
                                    <?php
                                    echo '</tr>';
                                }
                            }
                        } ?>

                        </tbody>
                    </table>
                            <?php if (count($contact_email_history_list) > 5) {
                                echo '<div class="view_all_btn"><a class="general-btn" href="/dialer/calls/email_history/' . $contactCallDetail->campaign_contact_id . '/'.$contactCallDetail->list_id. '" target="_blank">View All</a></div>';
                                }
                                ?>
                            </div>

                    </div>
                </div>

               <!-- Call History Email History-->
            </div>

            <div class="col-lg-7 pad-15-b">
                <!-- Write Notes -->
                <div class="pad-15-lr pad-15-t  call-row-title">
                    <div class="column-header">
                        <p>Notes</p>
                    </div>
                </div>

                <div class="pad-15-lr row-with-pad pad-15-t">
                    <?php if(isset($contactCallDetail->member_id) && !empty($contactCallDetail->member_id)) { ?>
                    <div id="contact-notes-cont">
                        <a href="javascript:void" id="view_notes">[ VIEW NOTES ]</a>
                    </div>
                    <?php } ?>

                    <?php if(!$isViewPage){ ?>
                <textarea rows="5" cols="10" placeholder="Notes..."
                          id="notes" name="notes"
                          class="row textarea-border box-sizing-border mar-b-0"
                    ><?php echo set_value('notes'); ?></textarea>
                    <?php }?>
                </div>
                <!-- Write Notes -->

                <!-- display Script -->

                <div class="pad-15-lr pad-15-t  call-row-title">
                    <div id="tab-container" class='tab-container'>
                        <ul class='etabs'>
                            <li class='tab'><a href="#tabs1-script">Script 1</a></li>
                            <li class='tab'><a href="#tabs2-script">Script 2</a></li>
                        </ul>
                        <div class='panel-container'>
                            <div id="tabs1-script">
                                <textarea class="campaignscript-textarea" style="" readonly placeholder="Script Main..."
                                          id="script_main"
                                          name="script_main"><?php if (!empty($contactCallDetail->script_main)) {
                                        echo $contactCallDetail->script_main;
                                    } ?></textarea>
                            </div>
                            <div id="tabs2-script">
                                <textarea class="campaignscript-textarea" readonly placeholder="Script Alter..."
                                          id="script_alt"
                                          name="script_alt"><?php if (!empty($contactCallDetail->script_alt)) {
                                        echo $contactCallDetail->script_alt;
                                    } ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- display Script -->
                <div id="view-container" style="display:none">
                    <div class="pad-15-lr pad-15-t  call-row-title">
                        <div class="column-header">
                            <p>Other Contacts</p>
                        </div>
                    </div>
                    <div class="pad-15-lr row-with-pad pad-15-t">
                        <div id="other-contacts">
                            <a href="javascript:void" id="view_other_contacts">[ VIEW OTHER CONTACTS ]</a>
                        </div>
                    </div>
                </div>

                <div class="pad-15-lr custom_questions" id="customQuestions">
                    <?php if (!empty($contactCallDetail->question_element_html)) {
                        echo $contactCallDetail->question_element_html;
                    } 
                    if(!empty($customQuestions)){
                        $ctr=1;
                        foreach($customQuestions as $cqs){
                            if(!empty($cqs->serverValidation)){
                                echo "<input type='hidden' name='cqValidation[]' class='cqValidation' value='question{$ctr}|{$cqs->serverValidation}'>";
                            }
                            $ctr++;
                        }
                    }
                    
                    ?>
                </div>
                <div id="cq_error" class="pad-15-lr row-with-pad pad-15-t bottom-btns"></div>
                <?php if (!empty($contactCallDetail->intent_question_element_html)) { ?>
                <h3 class="intent_question_header">Intent Questions:</h3>
                <div class="pad-15-lr intent_custom_questions">
                    <?php echo $contactCallDetail->intent_question_element_html; ?>
                </div>
                <?php }
                if(!empty($pureB2bConsentLabel) && !empty($clientConsentLabel)){
                    $consentAnswers = array("yes","no");?>
                <div class="pad-15-lr row-with-pad pad-15-t"></div>
                <h3 class="intent_question_header">GDPR Questions:</h3>
                    <div id="gdpr_questions_id" class="pad-15-lr intent_custom_questions">
                        <div class="custom-questions-form">
                            <label for="pureb2bConsent"><?php echo $pureB2bConsentLabel; ?></label>
                            <div class="form-input">
                                <div class="styled select-dropdown table-dropdown">
                                    <select name="pureb2bConsent" id="pureb2bConsent">
                                        <option selected="selected" value="">--SELECT--</option>
                                        <?php
                                        foreach($consentAnswers as $ans){
                                            $ansSelected = (!empty($pureB2bConsent) && $pureB2bConsent == $ans) ? 'selected="selected"' : '';
                                            echo "<option value = '{$ans}' {$ansSelected}>".ucfirst($ans)."</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div> 
                        </div>
                        <div class="custom-questions-form">
                            <label for="clientbConsent"><?php echo $clientConsentLabel; ?></label>
                            <div class="form-input">
                                <div class="styled select-dropdown table-dropdown">
                                    <select name="clientConsent" id="clientConsent">
                                        <option selected="selected" value="">--SELECT--</option>
                                        <?php
                                        foreach($consentAnswers as $ans){
                                            $ansSelected = (!empty($clientConsent) && $clientConsent == $ans) ? 'selected' : '';
                                            echo "<option value = '{$ans}' {$ansSelected}>".ucfirst($ans)."</option>";
                                        }
                                        ?>
                                    </select>
                               </div>
                            </div> 
                        </div>               
                    </div><div id="gdpr_error" class="pad-15-lr row-with-pad pad-15-t bottom-btns"></div>
                <?php }
                // If we have survey questions, display them now
                if (!empty($contactCallDetail->survey_question_element_html)) { ?>
                <h3 class="intent_question_header">Survey Questions:</h3>
                <div id="survey_questions_id" class="pad-15-lr survey_questions">
                    <?php
                    echo $contactCallDetail->survey_question_element_html;
                    ?>
                </div>
                <div id="survey_error" class="pad-15-lr row-with-pad pad-15-t bottom-btns"></div>
                    <?php
                    if (isset($contactCallDetail->incentives_available) &&
                        $contactCallDetail->incentives_available > 0) {
                        if ($contactCallDetail->incentiveOffered == 1) {
                            $checked = ' checked="checked" ';
                        } else {
                            $checked = '';
                        } ?>
                <div class="pad-15-lr row-with-pad pad-15-t bottom-btns">
                    <div class="dialog-form">
                        <label style="width: 132px;">Incentive Offered:</label>
                        <div class="form-input">
                            <input type="checkbox" tabindex="6" 
                                id="incentiveOffered" class="css-checkbox"
                                name="incentiveOffered" value="1" 
                                <?=$checked?>/>
                            <label class="css-label checkbox-label 
                                radGroup1 cst-export-lbl incentive-label"
                                for="incentiveOffered"><?php echo $contactCallDetail->incentive; ?></label>
                        </div>
                    </div>
                </div>
                    <?php
                    }
                } ?>

                <div class="pad-15-lr row-with-pad pad-15-t bottom-btns">
                    <div class="dialog-form">
                        <?php if((!$isViewPage) || !empty($contactCallDetail->reference_link)){ ?>
                        <label>Reference Link:</label>
                        <?php } ?>

                        <div class="form-input">
                            <?php if($isViewPage){ ?>
                                <?php if (!empty($contactCallDetail->reference_link)) {
                                    echo $contactCallDetail->reference_link;
                                } ?>
                            <?php }else { ?>
                            <input type="text" id="reference_link" name="reference_link"
                                   value="<?php echo isset($contactCallDetail->reference_link) ? $contactCallDetail->reference_link : ""; ?>"
                                   placeholder="Reference Link"/>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <?php if (!empty($resources)) {?>
                    <div class="pad-15-lr row-with-pad pad-15-t bottom-btns">
                        <div class="select-dropdown styled pad-15-r  btn">
                            <select name="resource_id" id="resource_id_select">
                                <option value=""> --- SELECT Resource ---</option>
                                <?php
                                foreach ($resources as $resource) {
                                    if ($resource->id == $contactCallDetail->resource_id)
                                        $selected = "selected";
                                    else
                                        $selected = "";

                                    echo '<option value="' . $resource->id . '" ' . $selected . '>' . $resource->name . '</option>';
                                }
                                ?>
                            </select>
                            <?php if (!$isViewPage && ($contactCallDetail->logged_user_type != 'qa' && (!$Qaing))) {
                                $errorMsg = "resource required if LFQ";
                             } else { 
                                $errorMsg = "resource required if QA Approve";
                             } ?>
                            <span class="alert-required" style="font-size:10px" id="resource_error">* <i><?php echo $errorMsg; ?></i></span>    
                            <input type="hidden" name="resource_name" id="resource_name"
                                   value="<?php echo isset($contactCallDetail->resource_name) ? $contactCallDetail->resource_name : ""; ?>">
                        </div>
                        <?php if(!$isViewPage) {?>
                        <div class="pad-15-r btn btn">
                            <button type="button" id="email_resource" class="general-btn">Email Resource
                            </button>
                        </div><?php } ?>
                    </div>
                <?php } ?>

                <div class="pad-15-lr row-with-pad pad-15-t bottom-btns">
                    <?php if (!$isViewPage && ($contactCallDetail->logged_user_type != 'qa') && (!$Qaing)) { ?>
                        <div class="select-dropdown styled pad-15-r  btn" style="float: left;">
                            <select name="call_disposition" id="call_disposition"
                                    onchange="CallDispositionCallBack(this)">
                                <option value=""> --- Call Disposition ---</option>
                                <?php
                                $dispoValues = array();
                                $dispoOptions = array();
                                $dispoOptions[] = "<option value=''> --- Call Disposition ---</option>";
                                if (!empty($callDispositionList)) {
                                    foreach ($callDispositionList as $callDisposition) {
                                        $dispoValues[] = "'{$callDisposition->id}':'{$callDisposition->calldisposition_name}'";
                                        if (isset($contactCallDetail->call_disposition_id) && $callDisposition->id == $contactCallDetail->call_disposition_id)
                                            $selected = "selected";
                                        else
                                            $selected = "";
                                        if(!$isViewPage && $contactCallDetail->logged_user_type != 'qa' && ($selected == "selected" || $callDisposition->is_active)){
                                            echo '<option value="' . $callDisposition->id . '" ' . $selected . '>' . $callDisposition->calldisposition_name . '</option>';
                                            $dispoOptions[] = "<option value='{$callDisposition->id}'>{$callDisposition->calldisposition_name}</option>";
                                        }
                                    }
                                }
                                ?>
                            </select>
                 
                        </div>
                    <?php } ?>

                    <input type="hidden" id="hidden_Campaign_ID" name="campaign_id"
                           value="<?php echo $contactCallDetail->campaign_id; ?>"/>
                    <input type="hidden" id="hidden_list_id" name="hidden_list_id"
                           value="<?php echo isset($contactCallDetail->list_id) ? $contactCallDetail->list_id : "";?>"/>
                    <input type="hidden" id="hidden_phone" name="hidden_phone"
                           value="<?php echo isset($contactCallDetail->phone) ? $contactCallDetail->phone : "";?>"/>

                    <div class=" date-picker pad-15-r  btn" style="display: none;" id="call_disposition_datepicker">
                        <input type="text" id="call_disposition_update_date" name="call_disposition_update_date"
                               readonly
                               value="<?php
                               if (!empty($contactCallDetail->call_disposition_update_date)) {
                                   echo $contactCallDetail->call_disposition_update_date;
                               } else {
                                   echo $this->input->post('call_disposition_update_date');
                               }
                               ?>"/>
                    </div>

                    <?php if (!$isViewPage && ($contactCallDetail->logged_user_type != 'qa' && (!$Qaing))) { ?>
                        <div class="row pad-15-t">
                            <div class="pad-15-r btn alignleft btn">
                               <!-- <button type="submit" class="general-btn" id="call_history_btnSave">Submit
                                </button>-->
                                <input type="submit" class="general-btn" value="Submit" name="decision" id="call_history_btnSave">
                            </div>
                            <?php if(empty($this->input->get('manual_create'))){ ?>
                                <!-- Start HP UAD-16 login and start session -->
                            <?php if ($contactCallDetail->auto_dial == '0') { ?>
                            <div class="pad-15-r btn alignleft btn">
                                <input type="submit" class="general-btn" value="Submit and go to next Contact" name="decision" id="submit_go_next_btnSave">
                                 <!-- <button type="submit" class="general-btn" id="submit_go_next_btnSave">Submit and go to
                                    next Contact
                                </button> -->
                            </div>
                            <?php } ?>
                                <?php if(!$isAddPage){ ?>
                                    <div class="pad-15-r  btn btn-long alignright">
                                        <input type="submit" class="general-btn" value="Add as a different person" name="decision" id="add_as_diff_person_btnSave">
                                        <!--<button type="submit" id="add_as_diff_person_btnSave" class="general-btn">
                                            Add as a different person
                                        </button>-->
                                    </div>
                                <?php } ?>

                            <?php } ?>
                        </div>
                    <?php } ?>
                    
                    <?php 
                    if ($isViewPage && $contactCallDetail->lifted == 0 && (in_array($contactCallDetail->logged_user_type, $this->config->item('upper_management_types')) || $contactCallDetail->logged_user_type == 'manager')  && (isset($contactCallDetail->call_disposition_id) && $contactCallDetail->call_disposition_id != 1)) { ?>
                        <div class="row pad-15-t">
                            <div class="pad-15-r btn alignleft btn">
                                <input type="hidden" name="change_dispo" id="change_dispo" value="change_dispo">
                                <input type="submit" class="general-btn" value="Lift" name="decision" id="change_call_dispo">
                            </div>

                        </div>
                    <?php } ?>
                    <?php if (!$isViewPage && !$isAddPage && $qa) {
                        $is_agent = $this->session->userdata('user_type') == 'agent' ? true : false;
                        ?>
                        <?php if ($this->session->userdata('user_type') != 'agent' && ($Qaing)) { ?>

                            <!-- Call Flow Findings -->
                            <div class="pad-15-t pad-15-b call-row-title">
                                <div class="column-header">
                                    <p>Call Flow Findings</p>
                                </div>
                            </div>
                            <div class="row" id="main_call_flow_findings_region">

                                <div class="pad-15-lr pad-15-t">
                                    <label class="bold_text">A. Opening â€“ Greeting and Identification:</label>

                                    <div class="sub_call_flow_label">
                                        <label>Proper Greeting</label>

                                        <div class="form-input popup-radio-group">
                                            <ul>
                                                <li>
                                                    <input type="radio" id="proper_greeting_yes" value="1"
                                                        <?php if (isset($callFlowFindingsData[0]->call_flow_value) && $callFlowFindingsData[0]->call_flow_value == 1): ?> checked="checked" <?php endif; ?>
                                                           name="proper_greeting"/><label
                                                        for="proper_greeting_yes">Yes</label>
                                                    <input type="radio" id="proper_greeting_no" value="0"
                                                        <?php if ((isset($callFlowFindingsData[0]->call_flow_value) && $callFlowFindingsData[0]->call_flow_value == 0) || !isset($callFlowFindingsData[0]->call_flow_value)): ?> checked="checked" <?php endif; ?>
                                                           name="proper_greeting"/><label
                                                        for="proper_greeting_no">No</label>

                                                </li>
                                            </ul>
                                        </div>

                                    </div>
                                    <div class="sub_call_flow_label">
                                        <label>Right Decision Maker Identified</label>

                                        <div class="form-input popup-radio-group">
                                            <ul>
                                                <li>
                                                    <input type="radio" id="right_decision_maker_identified_yes"
                                                           value="1" <?php if (isset($callFlowFindingsData[1]->call_flow_value) && $callFlowFindingsData[1]->call_flow_value == 1): ?> checked="checked" <?php endif; ?>
                                                           name="right_decision_maker_identified"/><label
                                                        for="right_decision_maker_identified_yes">Yes</label>
                                                    <input type="radio" id="right_decision_maker_identified_no"
                                                           value="0" <?php if ((isset($callFlowFindingsData[1]->call_flow_value) && $callFlowFindingsData[1]->call_flow_value == 0) || !isset($callFlowFindingsData[1]->call_flow_value)): ?> checked="checked" <?php endif; ?>
                                                           name="right_decision_maker_identified"/><label
                                                        for="right_decision_maker_identified_no">No</label>

                                                </li>
                                            </ul>
                                        </div>

                                    </div>
                                </div>
                                <div class="pad-15-lr pad-15-t">
                                    <label class="bold_text">B. Statement, Product Knowledge and Interest:</label>

                                    <div class="sub_call_flow_label">
                                        <label>Proper Branding</label>

                                        <div class="form-input popup-radio-group">
                                            <ul>
                                                <li>
                                                    <input type="radio" id="proper_branding_yes" value="1"
                                                        <?php if (isset($callFlowFindingsData[2]->call_flow_value) && $callFlowFindingsData[2]->call_flow_value == 1): ?> checked="checked" <?php endif; ?>
                                                           name="proper_branding"/><label
                                                        for="proper_branding_yes">Yes</label>
                                                    <input type="radio" id="proper_branding_no" value="0"
                                                        <?php if ((isset($callFlowFindingsData[2]->call_flow_value) && $callFlowFindingsData[2]->call_flow_value == 0) || !isset($callFlowFindingsData[2]->call_flow_value)): ?> checked="checked" <?php endif; ?>
                                                           name="proper_branding"/><label
                                                        for="proper_branding_no">No</label>

                                                </li>
                                            </ul>
                                        </div>

                                    </div>
                                    <div class="sub_call_flow_label">
                                        <label>Clearly Stated the Purpose of the Call</label>

                                        <div class="form-input popup-radio-group">
                                            <ul>
                                                <li>
                                                    <input type="radio" id="clearly_stated_the_purpose_of_the_call_yes"
                                                           value="1" <?php if (isset($callFlowFindingsData[3]->call_flow_value) && $callFlowFindingsData[3]->call_flow_value == 1): ?> checked="checked" <?php endif; ?>
                                                           name="clearly_stated_the_purpose_of_the_call"/><label
                                                        for="clearly_stated_the_purpose_of_the_call_yes">Yes</label>
                                                    <input type="radio" id="clearly_stated_the_purpose_of_the_call_no"
                                                           value="0" <?php if ((isset($callFlowFindingsData[3]->call_flow_value) && $callFlowFindingsData[3]->call_flow_value == 0) || !isset($callFlowFindingsData[3]->call_flow_value)): ?> checked="checked" <?php endif; ?>
                                                           name="clearly_stated_the_purpose_of_the_call"/><label
                                                        for="clearly_stated_the_purpose_of_the_call_no">No</label>

                                                </li>
                                            </ul>
                                        </div>

                                    </div>
                                    <div class="sub_call_flow_label">
                                        <label>Provided a clear Overview of the Content of the Asset</label>

                                        <div class="form-input popup-radio-group">
                                            <ul>
                                                <li>
                                                    <input type="radio"
                                                           id="provided_a_clear_overview_of_the_content_of_the_asset_yes"
                                                           value="1" <?php if (isset($callFlowFindingsData[4]->call_flow_value) && $callFlowFindingsData[4]->call_flow_value == 1): ?> checked="checked" <?php endif; ?>
                                                           name="provided_a_clear_overview_of_the_content_of_the_asset"/><label
                                                        for="provided_a_clear_overview_of_the_content_of_the_asset_yes">Yes</label>
                                                    <input type="radio"
                                                           id="provided_a_clear_overview_of_the_content_of_the_asset_no"
                                                           value="0" <?php if ((isset($callFlowFindingsData[4]->call_flow_value) && $callFlowFindingsData[4]->call_flow_value == 0) || !isset($callFlowFindingsData[4]->call_flow_value)): ?> checked="checked" <?php endif; ?>
                                                           name="provided_a_clear_overview_of_the_content_of_the_asset"
                                                           /><label
                                                        for="provided_a_clear_overview_of_the_content_of_the_asset_no">No</label>

                                                </li>
                                            </ul>
                                        </div>

                                    </div>
                                    <div class="sub_call_flow_label">
                                        <label>Prospect agreed to receiving a copy of the Asset</label>

                                        <div class="form-input popup-radio-group">
                                            <ul>
                                                <li>
                                                    <input type="radio"
                                                           id="prospect_agreed_to_receiving_a_copy_of_the_asset_yes"
                                                           value="1" <?php if (isset($callFlowFindingsData[5]->call_flow_value) && $callFlowFindingsData[5]->call_flow_value == 1): ?> checked="checked" <?php endif; ?>
                                                           name="prospect_agreed_to_receiving_a_copy_of_the_asset"/><label
                                                        for="prospect_agreed_to_receiving_a_copy_of_the_asset_yes">Yes</label>
                                                    <input type="radio"
                                                           id="prospect_agreed_to_receiving_a_copy_of_the_asset_no"
                                                           value="0" <?php if ((isset($callFlowFindingsData[5]->call_flow_value) && $callFlowFindingsData[5]->call_flow_value == 0) || !isset($callFlowFindingsData[5]->call_flow_value)): ?> checked="checked" <?php endif; ?>
                                                           name="prospect_agreed_to_receiving_a_copy_of_the_asset"
                                                           /><label
                                                        for="prospect_agreed_to_receiving_a_copy_of_the_asset_no">No</label>

                                                </li>
                                            </ul>
                                        </div>

                                    </div>
                                    <div class="sub_call_flow_label">
                                        <label>Accurate and Effective Rebuttals</label>

                                        <div class="form-input popup-radio-group">
                                            <ul>
                                                <li>
                                                    <input type="radio" id="accurate_and_effective_rebuttals_yes"
                                                           value="1" <?php if (isset($callFlowFindingsData[6]->call_flow_value) && $callFlowFindingsData[6]->call_flow_value == 1): ?> checked="checked" <?php endif; ?>
                                                           name="accurate_and_effective_rebuttals"/><label
                                                        for="accurate_and_effective_rebuttals_yes">Yes</label>
                                                    <input type="radio" id="accurate_and_effective_rebuttals_no"
                                                           value="0" <?php if ((isset($callFlowFindingsData[6]->call_flow_value) && $callFlowFindingsData[6]->call_flow_value == 0) || !isset($callFlowFindingsData[6]->call_flow_value)): ?> checked="checked" <?php endif; ?>
                                                           name="accurate_and_effective_rebuttals"/><label
                                                        for="accurate_and_effective_rebuttals_no">No</label>
                                                    <input type="radio"
                                                           id="accurate_and_effective_rebuttals_notapplicable" value="2"
                                                        <?php if (isset($callFlowFindingsData[6]->call_flow_value) && $callFlowFindingsData[6]->call_flow_value == 2): ?> checked="checked" <?php endif; ?>
                                                           name="accurate_and_effective_rebuttals"/><label
                                                        for="accurate_and_effective_rebuttals_notapplicable">N/A</label>
                                                </li>
                                            </ul>
                                        </div>

                                        </div>
                                    </div>
                                <div class="pad-15-lr pad-15-t">
                                    <label class="bold_text">C. Probing and Custom Questions:</label>

                                    <div class="sub_call_flow_label">

                                        <label>Agent was able to Probe</label>

                                        <div class="form-input popup-radio-group">
                                            <ul>
                                                <li>
                                                    <input type="radio" id="agent_was_able_to_probe_yes" value="1"
                                                        <?php if (isset($callFlowFindingsData[7]->call_flow_value) && $callFlowFindingsData[7]->call_flow_value == 1): ?> checked="checked" <?php endif; ?>
                                                           name="agent_was_able_to_probe"/><label
                                                        for="agent_was_able_to_probe_yes">Yes</label>
                                                    <input type="radio" id="agent_was_able_to_probe_no" value="0"
                                                        <?php if ((isset($callFlowFindingsData[7]->call_flow_value) && $callFlowFindingsData[7]->call_flow_value == 0) || !isset($callFlowFindingsData[7]->call_flow_value)): ?> checked="checked" <?php endif; ?>
                                                           name="agent_was_able_to_probe"/><label
                                                        for="agent_was_able_to_probe_no">No</label>
                                                    <input type="radio" id="agent_was_able_to_probe_notapplicable"
                                                           value="2"
                                                        <?php if (isset($callFlowFindingsData[7]->call_flow_value) && $callFlowFindingsData[7]->call_flow_value == 2): ?> checked="checked" <?php endif; ?>
                                                           name="agent_was_able_to_probe"/><label
                                                        for="agent_was_able_to_probe_notapplicable">N/A</label>
                                                </li>
                                            </ul>
                                        </div>

                                    </div>
                                    <div class="sub_call_flow_label">
                                        <label>Clear Delivery of the Questions</label>

                                        <div class="form-input popup-radio-group">
                                            <ul>
                                                <li>
                                                    <input type="radio" id="clear_delivery_of_the_questions_yes"
                                                           value="1" <?php if (isset($callFlowFindingsData[8]->call_flow_value) && $callFlowFindingsData[8]->call_flow_value == 1): ?> checked="checked" <?php endif; ?>
                                                           name="clear_delivery_of_the_questions"/><label
                                                        for="clear_delivery_of_the_questions_yes">Yes</label>
                                                    <input type="radio" id="clear_delivery_of_the_questions_no"
                                                           value="0" <?php if ((isset($callFlowFindingsData[8]->call_flow_value) && $callFlowFindingsData[8]->call_flow_value == 0) || !isset($callFlowFindingsData[8]->call_flow_value)): ?> checked="checked" <?php endif; ?>
                                                           name="clear_delivery_of_the_questions"/><label
                                                        for="clear_delivery_of_the_questions_no">No</label>
                                                    <input type="radio"
                                                           id="clear_delivery_of_the_questions_notapplicable" value="2"
                                                        <?php if (isset($callFlowFindingsData[8]->call_flow_value) && $callFlowFindingsData[8]->call_flow_value == 2): ?> checked="checked" <?php endif; ?>
                                                           name="clear_delivery_of_the_questions"/><label
                                                        for="clear_delivery_of_the_questions_notapplicable">N/A</label>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="sub_call_flow_label">
                                        <label>Custom Questions were all Answered </label>

                                        <div class="form-input popup-radio-group">
                                            <ul>
                                                <li>
                                                    <input type="radio" id="custom_questions_were_all_answered_yes"
                                                           value="1" <?php if (isset($callFlowFindingsData[9]->call_flow_value) && $callFlowFindingsData[9]->call_flow_value == 1): ?> checked="checked" <?php endif; ?>
                                                           name="custom_questions_were_all_answered"/><label
                                                        for="custom_questions_were_all_answered_yes">Yes</label>
                                                    <input type="radio" id="custom_questions_were_all_answered_no"
                                                           value="0" <?php if ((isset($callFlowFindingsData[9]->call_flow_value) && $callFlowFindingsData[9]->call_flow_value == 0) || !isset($callFlowFindingsData[9]->call_flow_value)): ?> checked="checked" <?php endif; ?>
                                                           name="custom_questions_were_all_answered"/><label
                                                        for="custom_questions_were_all_answered_no">No</label>
                                                    <input type="radio"
                                                           id="custom_questions_were_all_answered_notapplicable"
                                                           value="2" <?php if (isset($callFlowFindingsData[9]->call_flow_value) && $callFlowFindingsData[9]->call_flow_value == 2): ?> checked="checked" <?php endif; ?>
                                                           name="custom_questions_were_all_answered"/><label
                                                        for="custom_questions_were_all_answered_notapplicable">N/A</label>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="pad-15-lr pad-15-t">
                                    <label class="bold_text">D. Verification and Closing:</label>

                                    <div class="sub_call_flow_label">
                                        <label>All Pertinent Information Verified</label>

                                        <div class="form-input popup-radio-group">
                                            <ul>
                                                <li>
                                                    <input type="radio" id="all_pertinent_information_verified_yes"
                                                           value="1" <?php if (isset($callFlowFindingsData[10]->call_flow_value) && $callFlowFindingsData[10]->call_flow_value == 1): ?> checked="checked" <?php endif; ?>
                                                           name="all_pertinent_information_verified"/><label
                                                        for="all_pertinent_information_verified_yes">Yes</label>
                                                    <input type="radio" id="all_pertinent_information_verified_no"
                                                           value="0" <?php if ((isset($callFlowFindingsData[10]->call_flow_value) && $callFlowFindingsData[10]->call_flow_value == 0) || !isset($callFlowFindingsData[10]->call_flow_value)): ?> checked="checked" <?php endif; ?>
                                                           name="all_pertinent_information_verified"/><label
                                                        for="all_pertinent_information_verified_no">No</label>

                                                </li>
                                            </ul>
                                        </div>

                                    </div>
                                    <div class="sub_call_flow_label">
                                        <label>Proper Closing</label>

                                        <div class="form-input popup-radio-group">
                                            <ul>
                                                <li>
                                                    <input type="radio" id="proper_closing_yes" value="1"
                                                        <?php if (isset($callFlowFindingsData[11]->call_flow_value) && $callFlowFindingsData[11]->call_flow_value == 1): ?> checked="checked" <?php endif; ?>
                                                           name="proper_closing"/><label
                                                        for="proper_closing_yes">Yes</label>
                                                    <input type="radio" id="proper_closing_no" value="0"
                                                        <?php if ((isset($callFlowFindingsData[11]->call_flow_value) && $callFlowFindingsData[11]->call_flow_value == 0) || !isset($callFlowFindingsData[11]->call_flow_value)): ?> checked="checked" <?php endif; ?>
                                                           name="proper_closing"/><label
                                                        for="proper_closing_no">No</label>

                                                </li>
                                            </ul>
                                        </div>

                                    </div>
                                    <div class="sub_call_flow_label">
                                        <label>Right Expectations were set</label>

                                        <div class="form-input popup-radio-group">
                                            <ul>
                                                <li>
                                                    <input type="radio" id="right_expectations_were_set_yes" value="1"
                                                        <?php if (isset($callFlowFindingsData[12]->call_flow_value) && $callFlowFindingsData[12]->call_flow_value == 1): ?> checked="checked" <?php endif; ?>
                                                           name="right_expectations_were_set"/><label
                                                        for="right_expectations_were_set_yes">Yes</label>
                                                    <input type="radio" id="right_expectations_were_set_no" value="0"
                                                        <?php if ((isset($callFlowFindingsData[12]->call_flow_value) && $callFlowFindingsData[12]->call_flow_value == 0) || !isset($callFlowFindingsData[12]->call_flow_value)): ?> checked="checked" <?php endif; ?>
                                                           name="right_expectations_were_set"/><label
                                                        for="right_expectations_were_set_no">No</label>

                                                </li>
                                            </ul>
                                        </div>

                                    </div>
                                </div>


                            </div>
                        <?php } ?>

                        <?php if (!$is_agent && (!empty($action) && $action == "qa" || (!empty($action) && $action == "qa" && isset($status) && $status == 'Follow-up' && $qa_accepted_by_user))) { ?>
                            <div class="row pad-15-t">

                            <div class="pad-15-r btn alignleft btn">
                               <!-- <button type="submit" id="approve" class="general-btn" disabled>Approve</button>-->
                                <input type="submit" class="general-btn" value="Approve" name="decision" id="approve">
                            </div>
                            <div class="pad-15-r btn alignleft btn">
                                <!--<button type="submit" id="follow-up" disabled="true" class="general-btn btn-is-disabled"
                                        disabled>Follow Up
                                </button>-->
                                <input type="submit" class="general-btn" value="Follow Up" name="decision" id="follow-up">
                            </div>
                            <div class="pad-15-r  btn btn-long alignleft">
                                <!--<button type="submit" id="reject" disabled="true" class="general-btn btn-is-disabled"
                                        disabled>Reject
                                </button>-->
                                <input type="submit" class="general-btn" value="Reject" name="decision" id="reject">
                            </div>
                            <div class="pad-15-r  btn btn-long alignleft">
                                <input type="submit" class="general-btn" value="Duplicate Lead" name="decision" id="duplicate_lead">
                            </div>
                        <?php } ?>

                        <?php if (isset($status) && $status == 'Follow-up' && !empty($action) && $action == "qa" && (!$is_agent && !$user_team_leader_id || $qa_accepted_by_user)) { ?>
                            <div class="pad-15-r btn alignleft btn">
                                <!--<button type="submit" id="update_and_submit" class="general-btn" disabled>Update and
                                    Submit
                                </button>-->
                                <input type="submit" class="general-btn" value="Update and Submit" name="decision" id="update_and_submit">
                            </div>
                            </div>
                        <?php } ?>
                        <?php if ($this->session->userdata('user_type') != 'agent' && isset($status) && ($status != '' && $status != 'In Progress') && ($Qaing)) { ?>
                            <!-- FollowUP reason -->
                            <div class="row pad-15-t" id="qa_follow_up_selector">
                                <div class="follow_up_input_fields_wrap" id="follow_up_input_fields_wrap">
                                    <label class="add_field_button" id="follow_up_add_field_button">Add another
                                        Follow-up
                                        Reason
                                    </label>

                                    <div class="followUpClonedInput">
                                        <div class="styled select-dropdown table-dropdown">
                                            <select name="follow_up_reason[]" id="follow_up_reason_1"
                                                    class="main_follow_up_reason_combo"
                                                    onchange="FollowUpReason(this)">
                                                <option value="">--Select Follow-up Reason--</option>
                                                <?php
                                                if (!empty($followupReason)) {
                                                    foreach ($followupReason as $follow_up_reason) {
                                                        echo '<option role="option" value="' . $follow_up_reason . '" >' . $follow_up_reason . '</option>';
                                                    }
                                                }
                                                ?>
                                            </select>

                                            <div class="styled select-dropdown table-dropdown sub_combo"
                                                 id="sub_follow_up_combo_1"></div>
                                            <div class="dialog-form others_label_input_text" style="display: none;"
                                                 id="follow_up_text_div_1">

                                                <div class="form-input">
                                                    <input type="text" id="follow_up_text_1" name="follow_up_text[]"
                                                           maxlength="100"
                                                           placeholder="Follow-Up Reason"/>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <input type="hidden" name="follow-up-status" id="follow-up-status">
                            </div>
                            <!-- Reject reason -->
                            <div class="row pad-15-t" id="qa_status_selector">
                                <div class="input_fields_wrap" id="input_fields_wrap">
                                    <label class="add_field_button" id="add_field_button">Add another Rejection Reason
                                    </label>

                                    <div class="clonedInput">
                                        <div class="styled select-dropdown table-dropdown">
                                            <select name="reject_reason[]" id="reject_reason_1"
                                                    class="main_reason_combo"
                                                    onchange="RejectedReason(this)">
                                                <option value="">--Select Rejection Reason--</option>
                                                <?php
                                                if (!empty($rejectedReason)) {
                                                    foreach ($rejectedReason as $reason) {
                                                        echo '<option role="option" value="' . $reason . '" >' . $reason . '</option>';
                                                    }
                                                }
                                                ?>
                                            </select>

                                            <div class="styled select-dropdown table-dropdown sub_combo"
                                                 id="sub_combo_1"></div>
                                            <div class="dialog-form others_label_input_text" style="display: none;"
                                                 id="reason_text_div_1">
                                                <!-- <label>Rejection Reason:</label>-->

                                                <div class="form-input">
                                                    <input type="text" id="reason_text_1" name="reason_text[]"
                                                           maxlength="100"
                                                           placeholder="Rejection Reason"/>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <input type="hidden" name="reject-status" id="reject-status">
                            </div>

                        <?php } ?>
                    <?php } ?>

                </div>
                <div class="pad-15-lr row-with-pad pad-15-t bottom-btns">
                    <div class="pad-15-r btn alignleft btn">
                        <?php if ($save_contact_visible) { ?>
                            <!-- <input type="submit" class="general-btn" value="Save Contact" name="decision" id="save_contact"> -->
                            <!-- <button type="submit" class="general-btn" id="save_contact">Save Contact</button>-->
                        <?php } ?>
                    </div>
                </div>
                <?PHP if (!$isAddPage && empty($this->input->get('add_diff'))) { ?>
                <div class="pad-15-lr row-with-pad pad-15-t bottom-btns">
                    <div class="pad-15-r btn alignleft btn">
                        <?php if (!empty($referrerURL)) {
                            if ($this->session->contactdata('ContactFilter')) {
                                if ((strpos($referrerURL, 'contacts')) && (!strpos($referrerURL, 'contactsort'))) {
                                    $referrerURL .= "/contactsort";
                            }
                            }
                            ?>
                            <a class="general-btn" id="btnBack" href="<?php echo $referrerURL; ?>">Back
                            </a>
                        <?php } else { ?>
                            <a class="general-btn" id="btnBack" href="<?php echo !empty($referrerURL)?$referrerURL: 'javascript:history.back()' ; ?>">Back
                            </a> <?php } ?>
                    </div>
                </div>
                <?php } ?>
                <input type="hidden" id="dial_code" name="dial_code"
                       value="<?php echo isset($contactCallDetail->dial_code) ? $contactCallDetail->dial_code : ""; ?>">

                <input type="hidden" id="calling_status" name="calling_status"
                       value="">

                <input type="hidden" id="campaign_site" name="campaign_site"
                       value="<?php echo isset($contactCallDetail->site_id) ? $contactCallDetail->site_id : ""; ?>">
                <input type="hidden" id="LeadEditBy" name="LeadEditBy"
                       value="<?php echo isset($contactCallDetail->qa) ? $contactCallDetail->qa : ""; ?>">
                <input type="hidden" id="lead_status" name="lead_status"
                       value="<?php echo isset($status) ? $status : ""; ?>">
                <input type="hidden" id="previous_agent_id" name="previous_agent_id"
                       value="<?php echo isset($contactCallDetail->agent_id) ? $contactCallDetail->agent_id : ""; ?>">
                <input type="hidden" id="previous_call_disposition_id" name="previous_call_disposition_id"
                       value="<?php echo isset($contactCallDetail->call_disposition_id) ? $contactCallDetail->call_disposition_id : ""; ?>">
                <input type="hidden" id="call_start_datetime" name="call_start_datetime"
                       value="<?php echo set_value('call_start_datetime'); ?>">
                <input type="hidden" id="call_end_datetime" name="call_end_datetime"
                       value="<?php echo set_value('call_end_datetime'); ?>">
                <input type="hidden" id="post_last_call_history_id" name="last_call_history_id"
                       value="<?php echo set_value('last_call_history_id'); ?>">
                <input type="hidden" id="all_call_history_id" name="all_call_history_id" value="">
                <input type="hidden" id="all_call_start_datetime" name="all_call_start_datetime" value="">
                <input type="hidden" id="all_call_end_datetime" name="all_call_end_datetime" value="">
                <input type="hidden" id="resource_id" name="email_resource_id"
                       value="<?php echo isset($resource_id) ? $resource_id : set_value('email_resource_id'); ?>">
                <input type="hidden" id="original_call_history_id" name="original_call_history_id"
                       value="<?php echo !empty($original_call_history_id) ? $original_call_history_id : ""; ?>">
                <input type="hidden" id="original_plivo_comm_id" name="original_plivo_comm_id"
                       value="<?php echo !empty($original_plivo_comm_id) ? $original_plivo_comm_id : ""; ?>">
                <input type="hidden" id="is_add_page" name="is_add_page"
                       value="<?php echo $isAddPage; ?>">
                <input type="hidden" id="eg_campaign_id" name="eg_campaign_id"
                       value="<?php echo isset($contactCallDetail->eg_campaign_id) ? $contactCallDetail->eg_campaign_id : set_value('eg_campaign_id'); ?>">
                <input type="hidden" id="campaign_type" name="campaign_type"
                       value="<?php echo isset($contactCallDetail->campaign_type) ? $contactCallDetail->campaign_type : set_value('campaign_type'); ?>">
                <input type="hidden" id="member_id" name="member_id"
                       value="<?php echo isset($contactCallDetail->member_id) ? $contactCallDetail->member_id : set_value('member_id'); ?>">
                <input type="hidden" id="campaign_contact_id" name="campaign_contact_id"
                       value="<?php echo isset($contactCallDetail->campaign_contact_id) ? $contactCallDetail->campaign_contact_id : set_value('campaign_contact_id'); ?>">
                <input type="hidden" id="lead_id" name="lead_id"
                       value="<?php echo isset($contactCallDetail->lead_id) ? $contactCallDetail->lead_id : set_value('lead_id'); ?>">
                <input type="hidden" id="source" name="source"
                       value="<?php echo isset($contactCallDetail->source) ? $contactCallDetail->source : set_value('source'); ?>">
                <input type="hidden" id="Qaing" name="Qaing"
                       value="<?php echo $Qaing; ?>">
                <input type="hidden" id="contact_campaign_id" name="contact_campaign_id"
                       value="<?php echo isset($contactCallDetail->campaign_id) ? $contactCallDetail->campaign_id : set_value('campaign_id'); ?>">
                <input type="hidden" id="distinct_leads" name="distinct_leads"
                       value="<?php echo isset($egCampaign->distinct_leads) ? $egCampaign->distinct_leads : set_value('distinct_leads'); ?>">
                <input type="hidden" id="questions" name="questions"
                       value="<?php echo isset($egCampaign->questions) ? $egCampaign->questions : set_value('questions'); ?>">
                <input type="hidden" id="intent_questions" name="intent_questions"
                       value="<?php echo isset($egCampaign->intent_questions) ? $egCampaign->intent_questions : set_value('intent_questions'); ?>">
                <input type="hidden" id="parent_id" name="parent_id"
                       value="<?php echo isset($egCampaign->parent_id) ? $egCampaign->parent_id : set_value('parent_id'); ?>">
                <input type="hidden" id="first_qa_date" name="first_qa_date"
                       value="<?php echo !empty($contactCallDetail->first_qa_date) ? $contactCallDetail->first_qa_date : set_value('first_qa_date'); ?>">
                <input type="hidden" id="client_id" name="client_id"
                       value="<?php echo $clientId; ?>">
                <input value="<?php if(!empty($Qaing) && $Qaing == '1'){echo $Qaing;} ;?>" type="hidden" id="action_qa" name="action_qa">
                <input value="<?php echo ($gdprRequired) ? 1 : 0; ?>" type="hidden" id="gdprRequired" name="gdprRequired">
                <input value="<?php echo (!empty($gdprClientNo) && $gdprClientNo) ? 1 : 0; ?>" type="hidden" id="gdprClientNo" name="gdprClientNo">
                <input type="hidden" id="is_manual_create" name="is_manual_create"
                       value="false">
                <input type="hidden" id="isLifted" name="isLifted"
                       value="<?php echo $contactCallDetail->lifted; ?>">
               <input type="hidden" id="callLimitReached" name="callLimitReached"
                       value="<?php echo (!empty($callLimitReached) && $callLimitReached) ? 1 : 0; ?>">
            </div>
        </form>

    </div>
     <?php if(!empty($contactCallDetail->source) && in_array($contactCallDetail->source,array('add_diff','form')) && !empty($contactCallDetail->contact_created_by)){
                            echo "<div style='font-size:12px;margin-top:-30px;margin-right:10px;float:right'>Created By: {$contactCallDetail->contact_created_by}</div>";
                    } ?>
<div class="clearfix"></div>
</section>

<script type="text/javascript">
    var logged_username = "<?php echo $this->session->userdata('user_fname');?>";
    var contact_id = <?php echo isset($contactCallDetail->id) ? $contactCallDetail->id:''; ?>;
    var list_id = <?php echo isset($contactCallDetail->list_id) ? $contactCallDetail->list_id:''; ?>;
    var logged_user_type = '<?php echo $contactCallDetail->logged_user_type;?>';
    var IsAddPage = '<?php echo $isAddPage;?>';
    var IsViewPage = '<?php echo $isViewPage;?>';
    var CallStatus = '<?php if(!empty($status)){echo $status;} else { "";} ;?>';
    var IsCallbackSelected = '<?php if(!empty($contactCallDetail->call_disposition_id)){ echo $contactCallDetail->call_disposition_id; }else{'';};?>';
    var action_qa = '<?php if(!empty($Qaing) && $Qaing == '1'){echo $Qaing;} ;?>';
    var qa_accepted_by_user = '<?php if(!empty($qa_accepted_by_user) && ($qa_accepted_by_user == '1' || $qa_accepted_by_user == true)){echo $qa_accepted_by_user;} ;?>';
    var isTodayExceedCallDial = '<?php echo (empty($isTodayExceedCallDial) ? false : $isTodayExceedCallDial);?>';
    var surveyQuestions = <?php echo '['.(isset($egCampaign->questions) ? $egCampaign->questions : set_value('questions') ). ']'; ?>;
    var isManualCreate = false;
    var gdprRequired = <?php echo ($gdprRequired) ? 'true' : 'false'; ?>;
    var dispoValues =  {<?php echo !empty($dispoValues) ? implode(",", $dispoValues) : ''; ?>};
    var dispoOptions = ["<?php echo !empty($dispoOptions) ? implode(",", $dispoOptions) : '';?>"];
</script>

<link rel="stylesheet" href="<?=$this->config->item('static_url')?>/css/jquery.datetimepicker.css<?=$this->cache_buster?>"/>
<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/jquery.datetimepicker.full.min.js<?=$this->cache_buster?>"></script>
<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/pagejs/dialer/calls/contactcalldetail.js<?=$this->cache_buster?>"></script>
<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/jquery.easytabs.min.js<?=$this->cache_buster?>" ></script>
<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/pagejs/dialer/calls/viewallcallhistory.js<?=$this->cache_buster?>"></script>
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