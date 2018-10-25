<style>
    .incentive-label {
        width: 100% !important;
    }
    #survey_questions_id input{
        background: white !important;
    }
    #survey_error, #gdpr_error {
        color: #FD0000;
    }
    #newemail_error {
        color: #FD0000;
    }
    #resource_error {
        display: none;
    }
    
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

<section class="section-content-main-area">
    <div class="content-main-area">
        <?php
        if ((isset($msg) && $msg != '') || $this->session->flashdata('msg') != '') {
            if ($this->session->flashdata('class') == 'good')
                $class = "class= 'error-msg good'";
            else
                $class = "class='error-msg  bad'";
            echo('<div id="divErrorMsg" ' . $class . '>');
            echo (' <p><span><i class="fa fa-check-circle"></i></span>');
            echo $this->session->flashdata('msg');
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
        
        <div style='color:red;font-size:12px;margin-top:50px;margin-left:10px'>*Manually Added Contact</div>
        
        <form method="post" role="form" id="call_detail_form" class="call-history-form" name="form" autocomplete="off"
              novalidate="novalidate" action="/dialer/calls/save/<?php echo $contactCallDetail->list_id; ?>">
            <div class="col-lg-5  pad-15-b">
                <div class="pad-15-t pad-15-l row-left-pad call-row-title">
                    <div class="column-header alignleft contact-detail-header">
                        <p class="alignleft">Contact Details</p>
                    </div>
                </div>
                <div class="pad-15-t pad-15-l row-left-pad">
                    <table class="table table-bordered row vertical-tbl  contact-tbl" id="editableContact">
                        <tbody>
                        <tr>
                            <th>Email: <span class="alert-required">*</span></th>
                            <td>
                                <input type="text" id="email" name="email" maxlength="100"
                                       placeholder="Email" value="<?php if (!empty($contactCallDetail->email)) {
                                           echo $contactCallDetail->email;
                                       } ?>"/><span id='email_loader'></span>
                            </td>
                        </tr>
                        
                        <tr>
                            <th>First Name: <span class="alert-required">*</span></th>

                            <td>
                                <input type="text" id="first_name" name="first_name" maxlength="100" placeholder="First Name" value="<?php if (!empty($contactCallDetail->first_name)) { echo $contactCallDetail->first_name;} ?>"/>
                            </td>
                        </tr>
                        
                         <tr>
                            <th>Last Name: <span class="alert-required">*</span></th>
                            <td>
                                <input type="text" id="last_name" name="last_name"
                                                                 maxlength="100"
                                       placeholder="Last Name"
                                                                 value="<?php if (!empty($contactCallDetail->last_name)) {
                                                                     echo $contactCallDetail->last_name;
                                                                 } ?>"/>
                            </td>
                        </tr>
                        
                        <tr>
                            <th> Company: <span class="alert-required">*</span></th>
                            <td>
                                <input type="text" id="company" name="company" maxlength="100"
                                       placeholder="Company"
                                                                 value="<?php if (!empty($contactCallDetail->company)) {
                                                                     echo $contactCallDetail->company;
                                                                 } ?>"/>
                            </td>
                        </tr>
                        
                        <tr>
                            <th>Job Title: <span class="alert-required">*</span></th>
                            <td>
                                <input type="text" id="job_title" name="job_title"
                                                                 maxlength="255"
                                       placeholder="Job Title"
                                                                 value="<?php if (!empty($contactCallDetail->job_title)) {
                                                                     echo $contactCallDetail->job_title;
                                                                 } ?>"/>
                            </td>
                        </tr>
                        
                        <tr>
                            <th>Phone No: <span class="alert-required">*</span></th>
                            <td>
                                <input type="text" id="phone-number" name="phone" maxlength="20"
                                       placeholder="Phone" class="width_30"
                                       value="<?php if (!empty($contactCallDetail->phone)) {
                                           if (isset($contactCallDetail->dial_code)) {
                                               echo $contactCallDetail->dial_code;
                                           }
                                           echo $contactCallDetail->phone;
                                       } ?>"/>
                                <?php if($this->isConference && (empty($this->isConferenceOffices) || in_array($this->session->userdata('telemarketing_offices'), $this->isConferenceOffices) || $this->session->userdata('user_type') == 'admin')){?>
                                <a href="javascript:void(0)"  class="btn general-btn disable-btn-make-call"
                                     id="internalConfMakeCallBtn"> Direct <i class="fa fa-phone"></i></a>
                                    <?php }else{ ?>
                                    <a href="javascript:void(0)"  class="btn general-btn disable-btn-make-call"
                                                                       id="editContactCallAccess"
                                                                        > Direct <i class="fa fa-phone"></i></a>
                                    <?php } ?>
                            </td>
                        </tr>
                        
                        <tr>
                            <th>Phone Extension: </th>
                            <td>
                                <input type="text" id="ext" name="ext" maxlength="20" placeholder="Ext" class="width_30" value="<?=!empty($contactCallDetail->ext) ? $contactCallDetail->ext: ''?>"/>
                            </td>
                        </tr>
                        
                        <tr>
                            <th>Alternate No:</th>
                            <td>
                                <input type="text" id="alternate_no" name="alternate_no" maxlength="20"
                                       placeholder="Alternate No" class="width_30"
                                       value="<?php if (!empty($contactCallDetail->alternate_no)) {
                                           if (isset($contactCallDetail->dial_code)) {
                                               echo $contactCallDetail->dial_code;
                                           }
                                           echo $contactCallDetail->alternate_no;
                                       } ?>"/>
                                    <a href="javascript:void(0)"  class="btn general-btn disable-btn-make-call"
                                     id="editAltContactCallAccess"> Direct <i class="fa fa-phone"> </i></a>
                            </td>
                        </tr>
                        
                        <tr>
                            <th>Address: <span class="alert-required">*</span></th>
                            <td>
                                <input type="text" id="address" name="address" maxlength="100"
                                       placeholder="Address" value="<?php if (!empty($contactCallDetail->address)) {
                                           echo $contactCallDetail->address;
                                       } ?>"/>
                            </td>
                        </tr>
                        
                        <tr>
                            <th>City: <span class="alert-required">*</span></th>
                            <td>
                                <input type="text" id="city" name="city" maxlength="25"
                                       placeholder="City"
                                       value="<?php if (!empty($contactCallDetail->city)) {
                                           echo $contactCallDetail->city;
                                       } ?>"/>
                            </td>
                        </tr>
                        
                        <tr>
                            <th>State:</th>
                            <td>
                                <input type="text" id="state" name="state" maxlength="25"
                                       placeholder="State" value="<?php if (!empty($contactCallDetail->state)) {
                                           echo $contactCallDetail->state;
                                           } ?>"/>
                            </td>
                        </tr>
                        
                        <tr>
                            <th>Postal Code:</th>
                            <td>
                                <input type="text" id="zip" name="zip" maxlength="20"
                                       placeholder="Postal Code" value="<?php if (!empty($contactCallDetail->zip)) {
                                           echo $contactCallDetail->zip;
                                           } ?>"/>
                            </td>
                        </tr>
                        
                        <tr>
                            <th>Country:  <span class="alert-required">*</span></th>
                            <td>
                                <div class="styled select-dropdown table-dropdown" id="country_validation">
                                    <select name="country" id="country">
                                        <option selected="selected" value="">--SELECT--</option>
                                        <?php if (!empty($countries)) {
                                                foreach ($countries as $country) {
                                                    $countryCode = trim($country->country_code);
                                                    $country = trim($country->country);?>
                                                    <option role="option" value="<?= $countryCode;?>"><?= $country;?></option>
                                        <?php   }
                                            }
                                        ?>
                                    </select>
                                </div>
                            </td>
                        </tr>
                        <?php if (!empty($contactCallDetail->time_zone)) { ?>
                        <tr>
                            <th>Time Zone:</th>
                            <td>
                                <input type="text" id="time_zone" name="time_zone" maxlength="100"
                                       placeholder="Time Zone" value="<?php if (!empty($contactCallDetail->time_zone)) {
                                           echo $contactCallDetail->time_zone;
                                       } ?>"/>
                            </td>
                        </tr><?php } ?>
                        
                        <tr>
                            <th>Industry: <span class="alert-required">*</span></th>
                            <td>
                                <?php if (!empty($industriesValues)) {
                                    $contactCallDetail->industry = !empty($contactCallDetail->industry) ? $contactCallDetail->industry : "";?>
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
                                    } ?>
                            </td>
                        </tr>
                        
                        <tr>
                            <th>Employee Size: <span class="alert-required">*</span></th>
                            <td>
                                    <?php
                                    if (!empty($companySizeValues)) {
                                        if ($contactCallDetail->company_size == "1-9") {
                                            $contactCallDetail->company_size = '1 to 9';
                                        } else if ($contactCallDetail->company_size == "10-24") {
                                            $contactCallDetail->company_size = '10 to 24';
                                        }
                                        
                                        $contactCallDetail->company_size = !empty($contactCallDetail->company_size) ? $contactCallDetail->company_size : "";
                                        
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
                                    }
                                    ?>
                                </div>
                            </td>
                        </tr>
                        
                        <?php if (!empty($companyRevenueForm)) {
                            $companyRevenueForm = explode('|', $companyRevenueForm);
                            ?>
                        <tr>
                            <th>Company Revenue:</th>
                            <td>
                                <?php
                                $contactCallDetail->company_revenue = !empty($contactCallDetail->company_revenue) ? $contactCallDetail->company_revenue : "";
                                ?>
                                <div class="styled select-dropdown table-dropdown">
                                    <select name="company_revenue" id="company_revenue">
                                        <option selected="selected" value="">--SELECT--</option>
                                        <?php
                                        foreach ($companyRevenueForm as $companyRevenue) {
                                            $companyRevenue = trim($companyRevenue);?>
                                            <option role="option" value="<?= $companyRevenue;?>" <?= $contactCallDetail->company_revenue == $companyRevenue ? 'selected="selected"' : '' ?>><?= $companyRevenue;?></option>
                                        <?php } ?>
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
                
                <!-- Call/Email History -->
                <div class="pad-15-t pad-15-l row-left-pad">
                    <div id="history-tab-container" class='tab-container'>
                        <ul class='etabs'>
                            <li class='tab'><a href="#call_history_tab">Call History</a></li>
                            <li class='tab'><a href="#email_history_tab">Email History</a></li>
                        </ul>
                        <div id="call_history_tab">
                            <div id ='call_history_no_record_div' class='no_record_found'>No record found</div>
                        </div>
                        <div id="email_history_tab">
                            <table class="table table-bordered row mar-b-0">
                                <thead>
                                    <div class='no_record_found'>No record found</div>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Call History Email History-->                
            <!-- Right Pane -->
            <div class="col-lg-7 pad-15-b">
                <!-- Write Notes -->
                <div class="pad-15-lr pad-15-t  call-row-title">
                    <div class="column-header">
                        <p>Notes</p>
                    </div>
                </div>

                <div class="pad-15-lr row-with-pad pad-15-t">
                    <textarea rows="5" cols="10" placeholder="Notes..."
                          id="notes" name="notes"
                          class="row textarea-border box-sizing-border mar-b-0"
                    ><?php echo set_value('notes'); ?></textarea>
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
                
                <div class="pad-15-lr custom_questions">
                    <?php if (!empty($contactCallDetail->question_element_html)) {
                        echo $contactCallDetail->question_element_html;
                    } ?>
                </div>

                <?php if (!empty($contactCallDetail->intent_question_element_html)) { ?>
                <h3 class="intent_question_header">Intent Questions:</h3>
                <div class="pad-15-lr intent_custom_questions">
                    <?php echo $contactCallDetail->intent_question_element_html; ?>
                </div>
                <?php }
                if(!empty($pureB2bConsentLabel) && !empty($clientConsentLabel)){
                    $consentAnswers = array("yes","no");?>
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
                        <label>Reference Link:</label>
                        <div class="form-input">
                            <input type="text" id="reference_link" name="reference_link"
                                   value=""
                                   placeholder="Reference Link"/>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($resources)) {?>
                    <div class="pad-15-lr row-with-pad pad-15-t bottom-btns">
                        <div class="select-dropdown styled pad-15-r  btn">
                            <select name="resource_id" id="resource_id_select">
                                <option value=""> --- SELECT Resource ---</option>
                                <?php
                                $contactCallDetail->resource_id = !empty($contactCallDetail->resource_id) ? $contactCallDetail->resource_id : '';
                                foreach ($resources as $resource) {
                                    if ($resource->id == $contactCallDetail->resource_id)
                                        $selected = "selected";
                                    else
                                        $selected = "";

                                    echo '<option value="' . $resource->id . '" ' . $selected . '>' . $resource->name . '</option>';
                                }
                                ?>
                            </select>
                            <?php 
                                $errorMsg = "resource required if LFQ";
                             ?>
                            <span class="alert-required" style="font-size:10px" id="resource_error">* <i><?php echo $errorMsg; ?></i></span>    
                            <input type="hidden" name="resource_name" id="resource_name"
                                   value="<?php echo isset($contactCallDetail->resource_name) ? $contactCallDetail->resource_name : ""; ?>">
                        </div>
                        <div class="pad-15-r btn btn">
                            <button type="button" id="email_resource" class="general-btn">Email Resource
                            </button>
                        </div>
                    </div>
                <?php } ?>

                <div class="pad-15-lr row-with-pad pad-15-t bottom-btns">
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
                                    if (isset($contactCallDetail->call_disposition_id) && $callDisposition->id == $contactCallDetail->call_disposition_id) {
                                        $selected = "selected";
                                    } else {
                                        $selected = "";
                                    }
                                    
                                    if($selected == "selected" || $callDisposition->is_active) {
                                        echo '<option value="' . $callDisposition->id . '" ' . $selected . '>' . $callDisposition->calldisposition_name . '</option>';
                                        $dispoOptions[] = "<option value='{$callDisposition->id}'>{$callDisposition->calldisposition_name}</option>";
                                    }
                                }
                            }
                            ?>
                        </select>
                    </div>
                <input type="hidden" id="hidden_Campaign_ID" name="campaign_id"
                       value="<?php echo $contactCallDetail->campaign_id; ?>"/>
                <input type="hidden" id="hidden_list_id" name="hidden_list_id"
                       value="<?php echo isset($contactCallDetail->list_id) ? $contactCallDetail->list_id : "";?>"/>

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
                <div class="row pad-15-t">
                    <div class="pad-15-r btn alignleft btn">
                        <input type="submit" class="general-btn" value="Submit" name="decision" id="call_history_btnSave">
                    </div>
                </div>
            </div>
            <input type="hidden" id="dial_code" name="dial_code"
                       value="<?php echo isset($contactCallDetail->dial_code) ? $contactCallDetail->dial_code : ""; ?>">

                <input type="hidden" id="calling_status" name="calling_status"
                       value="">
                <input type="hidden" id="is_add_page" name="is_add_page"
                       value="<?php echo $isAddPage; ?>">
                <input type="hidden" id="Qaing" name="Qaing"
                       value="<?php echo $Qaing; ?>">
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
                <input type="hidden" id="is_manual_create" name="is_manual_create"
                       value="true">
                <input type="hidden" id="client_id" name="client_id" value="<?php echo $clientId; ?>">
                <input value="" type="hidden" id="action_qa" name="action_qa">
                <input value="<?php echo ($gdprRequired) ? 1 : 0; ?>" type="hidden" id="gdprRequired" name="gdprRequired">
            </div>
                       <!-- here -->
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
    var CallStatus = '<?php if(!empty($status)){echo $status;} else { "";} ;?>';
    var IsCallbackSelected = '<?php if(!empty($contactCallDetail->call_disposition_id)){ echo $contactCallDetail->call_disposition_id; }else{'';};?>';
    var action_qa =false;
    var qa_accepted_by_user = '<?php if(!empty($qa_accepted_by_user) && ($qa_accepted_by_user == '1' || $qa_accepted_by_user == true)){echo $qa_accepted_by_user;} ;?>';
    var surveyQuestions = <?php echo '['.(isset($egCampaign->questions) ? $egCampaign->questions : set_value('questions') ). ']'; ?>;
    var isManualCreate = true;
    var IsAddPage = '<?php echo $isAddPage;?>';
    var gdprRequired = <?php echo ($gdprRequired) ? 'true' : 'false'; ?>;
    var dispoValues =  {<?php echo !empty($dispoValues) ? implode(",", $dispoValues) : ''; ?>};
    var dispoOptions = ["<?php echo !empty($dispoOptions) ? implode(",", $dispoOptions) : '';?>"];
</script>

<link rel="stylesheet" href="<?=$this->config->item('static_url')?>/css/jquery.datetimepicker.css<?=$this->cache_buster?>"/>
<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/jquery.datetimepicker.full.min.js<?=$this->cache_buster?>"></script>
<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/pagejs/dialer/calls/contactcalldetail.js?<?=$this->cache_buster?>"></script>
<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/jquery.easytabs.min.js<?=$this->cache_buster?>" ></script>
<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/pagejs/dialer/calls/viewallcallhistory.js<?=$this->cache_buster?>"></script>

<script type="text/javascript">
    $(document).ready(function () {
        $('#tab-container').easytabs();
        $('#history-tab-container').easytabs();
    });
</script>