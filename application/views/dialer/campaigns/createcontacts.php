<style>
    #userfile_error {
        color: #FD0000;
    }
    #userfile_error {
        display: none;
    }
    </style>
<?php $list_id_segment =$this->uri->segment('5'); if ($this->session->flashdata('msg') != '') { ?>
<div id="divErrorMsg" class="error-msg bad" style="padding-left:5px; margin-top:0px !important;">
        <p><span><i class="fa fa-times-circle"></i></span>  <?php echo $this->session->flashdata('msg'); ?></p>
    </div>
<?php } ?>

<section class="section-content-main-area">
    <div class="content-main-area">
        <?php
            $page_data = $this->session->flashdata('page_data');     //echo"<pre>";  print_r($page_data); echo"</pre>";
            $_POST['list_name'] = $page_data['list_name'];
            $_POST['status'] = $page_data['status'];
        if (validation_errors() != '') { ?>
            <div id="divErrorMsg" class="alert alert-warning server-validation-msg">
                <p><strong>Please fix the following input errors:</strong></p><?php echo validation_errors(); ?>
            </div>
        <?php } ?>
        <?php
        $attributes = array('class' => 'popup-form account-detail-dialog campaign-form', 'id' => 'create_contatsform', 'name' => 'form', 'autocomplete' => 'off', 'novalidate' => 'novalidate','enctype' => 'multipart/form-data');
        echo form_open(str_replace('index.php/', '', $_SERVER['PHP_SELF']), $attributes);
        ?>
        <input type="hidden" id="edit_mode" name="edit_mode" value=""> 
<!--        <form class="popup-form account-detail-dialog campaign-form" id="form" name="form" method="post" action="/lists/create" enctype="multipart/form-data">-->

            <!--<div class="form-section-title">
                <p>MANAGE LISTS </p>
                <span></span>
            </div>-->
            

            <div class="form-section-title">
                <p>List</p>
                <span></span>
            </div>
            <div class="form-row mar-b-0 radio-alignment-popup">
                <div class="dialog-form popup-radio-group alignleft">
                    <ul>
                        <li>
                            <input tabindex="8" type="radio" id="newlist" name="list_type" <?php echo empty($list_id_segment)?'checked':""; ?> />
                            <label for="newlist">Create New</label>
                        </li>

                        <li>
                            <input tabindex="9" type="radio" id="existinglist" name="list_type" <?php echo !empty($list_id_segment)?'checked':""; ?>/>
                            <label for="existinglist">Add to existing</label>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="form-row">
                <div class="dialog-form">
                    <label><span class="alert-required">*</span>Campaign Name:</label>
                    <div class="styled select-dropdown">
                        <select name="campaign_name" id="campaign_name" required="required" onchange="get_selected_campaign_value($(this).val());">
                            <option role="option" value=""> ---SELECT ONE---</option>
                            <?php
                           // $campaignId = $this->input->post('campaign_name');
                            foreach ($campaign as $campaigns) {
                                if($campaignId){
                                 if ($campaigns->id == $campaignId)
                                    $selected = "selected";
                                else
                                    $selected = "";
                                }else{
                                    $selected = "";
                            }
                                echo '<option role="option" value="' . $campaigns->id . '" ' . $selected . '>' . $campaigns->name . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="dialog-form alignleft" id="selectlist" style="display: none;">
                    <label>
                        <span class="alert-required">*</span>
                        List Name:</label>
                    <div class="styled select-dropdown">
                        <select name="select_list_name" id="select_list_name" required="required">

                        </select>
            </div>
                </div>
                <div class="dialog-form alignleft" id="addlist">
                    <label><span class="alert-required">*</span>List Name:</label>
                    <div class="form-input"><input type="text" id="list_name" name="list_name"  maxlength="100" placehoder="List Name" required="required"
                                                   value="<?php echo set_value('list_name'); ?>"/></div>
                </div>

                <div class="dialog-form">
                    <label class="alignleft"><span class="alert-required">*</span>Status:</label>
                    <div class="styled select-dropdown">
                        <select name="status" id="status">
                            <option role="option" value="Active">Active</option>
                            <option role="option" value="InActive">InActive</option>
                        </select>
                    </div>
                </div>

                <div class="dialog-form radio-alignment-popup">
                    <label class="alignleft"><span class="alert-required">*</span>For Revision:</label>
                    <div class="styled select-dropdown">
                        <select name="for_revision" id="for_revision">
                            <option role="option" value="No">No</option>
                            <option role="option" value="Yes">Yes</option>
                        </select>
                    </div>                   
                </div>

            </div>

            <?php if(!empty($list_id_segment)){ ?>
                <input type="hidden" id="hidden_campaign_name" name="campaign_name" value="<?php echo $campaignId; ?>"/>
                <input type="hidden" id="hidden_list_name" name="select_list_name" value="<?php echo isset($hidden_list_id)?$hidden_list_id:""; ?>"/>
            <?php } ?>

            <div class="form-row">
                <div class="dialog-form alignleft">
                    <label><span class="alert-required" id="file_mand">*</span>Upload File:</label>
                    <div class="form-input"><input type="file" name="userfile"  required="required" size="20" id="userfile" accept=".zip"/>
                    <label id="userfile_error"></label>
                    </div>
                    <a  href="#" onclick='window.open("/dialer/campaigns/helpcontactfile","","toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, copyhistory=yes,top=10,left=300, width=1040, height=780") '><i class="fa fa-info-circle font-info-help" aria-hidden="true"></i></a>
                    
                </div>
                
                <p class="char_alert">Note: Please do not append country code in phone number while uploading ZIP.</p>
            </div>
            <div class="popup-btn-group">
                <ul>
                    <li>
                        <button type="submit" class="general-btn" id="campaign_btnSave">Save</button>
                    </li>
                    <li>
                        <!--<button class="general-btn" id="btnCancel" onclick="history.back();return false;">Cancel</button>-->
                        <button class="general-btn" id="btnCancel" name="btnCancel" value="cancel" onclick="window.location = '/dialer/dashboards';">Cancel</button>
                        
                    </li>
                </ul>
            </div>
        </form>
    </div>
    <div class="clearfix"></div>
</section>
<script type="text/javascript">
    var list_id_segment = '<?php echo $this->uri->segment('5')?>';
    var campaign_id_segment = '<?php echo $this->uri->segment('4')?>';
</script>

<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/pagejs/dialer/campaigns/contacts.js<?=$this->cache_buster?>"></script>