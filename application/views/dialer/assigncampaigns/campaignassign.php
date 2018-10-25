<?php  if ((isset($msg) && $msg != '') || $this->session->flashdata('msg') != '') {
            if ($this->session->flashdata('class') == 'good') $class = "class= 'error-msg good'"; else $class = "class='error-msg bad'";
            echo('<div id="divErrorMsg" ' . $class . '>');
            echo(' <p><span><i class="fa fa-times-circle"></i></span>');
            echo $this->session->flashdata('msg');
            echo('</div>');
        }?>
<section class="section-content-main-area">
    <div class="content-main-area">
        
        <link rel="stylesheet" type="text/css" href="<?=$this->config->item('static_url')?>/css/bootstrap.css<?=$this->cache_buster?>">
        <link rel="stylesheet" type="text/css" href="<?=$this->config->item('static_url')?>/css/bootstrap-duallistbox.css<?=$this->cache_buster?>">
        <script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/pagejs/dialer/assigncampaigns/jquery.bootstrap-duallistbox.js<?=$this->cache_buster?>"></script>
        <?php

        if (validation_errors() != '') { ?>
            <div id="divErrorMsg" class="alert alert-warning server-validation-msg">
                <p><strong>Please fix the following input errors:</strong></p><?php echo validation_errors(); ?>
            </div>
        <?php } ?>
        <?php
        $attributes = array('class' => 'popup-form account-detail-dialog assign-form', 'id' => 'form', 'name' => 'form', 'autocomplete' => 'off', 'novalidate' => 'novalidate','enctype' => 'multipart/form-data');
        echo form_open('/dialer/assigncampaigns/', $attributes);
        ?>
        <div class="form-section-title">
            <p>Assign Campaign</p>
            <span></span>
        </div>
        <div class="form-row">
            <?php if($logedInUser){?>
                <input type="hidden" id="team_leader" required="required" name="team_leader" value="<?php echo $logedInUser; ?>"/>
            <?php } ?>  
                <!-- it will show when USer Type is  Manager  -->
            <div class="dialog-form managerdiv" style="display: none;">
                <label style="width:160px;"><span class="alert-required">*</span>Team Leader:</label>
               <div class="styled select-dropdown " >
                    <select  name="team_leader" id="team_leader" required="required">
                        <?php if(!$logedInUser){?>
                            <option role="option" value=""> ---SELECT ONE---</option>
                        <?php }
                        foreach ($teamleader as $tls) {
                            if($tls){
                             if ($tls->id == $logedInUser)
                                $selected = "selected";
                            else
                                $selected = "";
                            }else{
                                $selected = "";
                        }
                            echo '<option role="option" value="' . $tls->id . '" ' . $selected . '>' . $tls->member_name . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="dialog-form campaign_id_search" style="display: none;">
                <label style="width: 164px;"><span class="alert-required">*</span>Campaign ID:</label>
                <div class="form-input">
                    <input type="text" id="campaign_id" name="campaign_id" placeholder="Campaign ID" value="<?php echo $this->input->post('campaign_id'); ?>" onkeypress='return isNumberKey(event)'>
                </div>
                <div style="display:inline"><a href="javascript:void(0)" id="campaign_search" target="_blank"><i class="fa fa-search"></i></a></div>
            </div>   
           <div class="dialog-form managercampaigndiv" style="display: none;" >
               <label style="width: 165px;"><span class="alert-required">*</span>Campaign Name:</label>
                <div class="styled select-dropdown managercampaign"></div>
            </div>   
            <!-- it will show when user Type IS TL -->
            <div class="dialog-form tldiv " style="display: none;">
                <label style="width: 165px;"><span class="alert-required">*</span>Campaign Name:</label>
                <div class="styled select-dropdown">
                    <select name="campaign_name" id="campaign_name" required="required" onchange="assignagentlist(this);">
                        <option role="option" value=""> ---SELECT ONE---</option>
                        <?php
                        $campaignId = $this->input->post('campaign_name');
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
        </div>
        <div class="form-row">
            <div id="selectdiv" class="dialog-form alignleft"></div>
            <input type="hidden" id="oldselectedagents"  name ="oldselectedagents" values ="0">
            <input type="hidden" id="newselectedagents"  name ="newselectedagents" values ="0">
        </div>
        <div class="popup-btn-group">
            <ul style="text-align: left;">
                <li>
                    <button type="submit" class="general-btn" id="campaign_btnSave">Save</button>
                </li>
                <li>
                    <button  type="button" class="general-btn" id="btnCancel"  onclick="window.location.href='/dialer/campaigns/'">Cancel</button>
                </li>
            </ul>
        </div>
        <div class="clearfix"></div>
        </form>
    </div>
</section>
<script>
    var logedInUser = <?php echo $logedInUser;?>
</script>
<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/pagejs/dialer/assigncampaigns/campaignassign.js<?=$this->cache_buster?>"></script>
