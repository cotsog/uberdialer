<section class="section-content-main-area">
    <div class="content-main-area">
	 <?php

        if ((isset($msg) && $msg != '') || $this->session->flashdata('msg') != '') {
            if ($this->session->flashdata('class') == 'good') $class = "class= 'error-msg good'"; else $class = "class='error-msg bad'";
            echo('<div id="divErrorMsg" ' . $class . '>');
            echo(' <p><span><i class="fa fa-times-circle"></i></span>');
            echo $this->session->flashdata('msg');
            echo('</div>');
            } 
            $tm_office = str_replace(' ','',$campaign->telemarketing_offices);
            $extra_params = 'ext/'.$campaign->eg_campaign_id."/".str_replace(',','-', $tm_office).'/'.str_replace(' ', '%20', $campaign->name);
            $extra_params = '';
        ?>
        <form class="popup-form account-detail-dialog campaign-form">
            <div class="form-section-title">
                <p>CAMPAIGN VIEW</p>
                <span></span>
            </div>
            <div class="form-row">
                <div class="dialog-form ">
                    <label>Admin Campaign ID:</label>

                    <div class="form-view">
                        <label class="view-text-field"><?php if (!empty($campaign->eg_campaign_id)) {
                                echo $campaign->eg_campaign_id;
                            } ?></label>
                    </div>
                </div>
                <div class="dialog-form ">
                    <label>Campaign Name:</label>

                    <div class="form-view">
                        <label class="view-text-field"><?php if (!empty($campaign->name)) { echo htmlspecialchars($campaign->name); } ?></label></div>
                </div>

                <div class="dialog-form ">
                    <label>Status:</label>

                    <div class="form-view">
                        <label class="view-text-field"><?php if (!empty($campaign->status)) {
                                echo $campaign->status;
                            } ?></label></div>
                </div>
                <div class="dialog-form ">
                    <label> Type:</label>

                    <div class="form-view">
                        <label class="view-text-field"><?php if (!empty($campaign->type)) {
                                echo $campaign->type;
                            } ?></label></div>
                </div>
				 <?php if($logged_user_type == 'manager'){?>
                <div class="dialog-form ">
                    <label>Campaign CPL:</label>

                    <div class="form-view">
                        <label class="view-text-field"><?php if (!empty($campaign->cpl)) {
                                echo $campaign->cpl;
                            } ?></label></div>
                </div>
				<?php }?>
                <div class="dialog-form ">
                    <label>Lead Goal:</label>

                    <div class="form-view">
                        <label class="view-text-field"><?php if (!empty($campaign->lead_goal)) {
                                echo $campaign->lead_goal;
                            } ?></label></div>
                </div>
                <div class="dialog-form ">
                    <label> Start Date:</label>

                    <div class="form-view">
                        <label class="view-text-field"><?php if (!empty($campaign->start_date)) {
                                echo php_dateformat($campaign->start_date);
                            } ?></label>
                    </div>
                </div>
                <div class="dialog-form ">
                    <label>End Date:</label>

                    <div class="form-view">
                        <label class="view-text-field"><?php if (!empty($campaign->end_date)) {
                                echo php_dateformat($campaign->end_date);
                            } ?></label></div>
                </div>

                <div class="dialog-form ">
                    <label>TM Brand:</label>

                    <div class="form-view">
                        <label class="view-text-field"><?php if (!empty($campaign->site_name)) {
                                echo $campaign->site_name;
                            }else{echo ' - ';} ?></label></div>
                </div>
                <div class="dialog-form ">
                    <label> Custom Question/s:</label>

                    <div class="form-view">

                        <label class="view-text-field"><?php if (!empty($campaign->custom_questions)) {
                                echo $campaign->custom_questions;
                            } ?></label></div>

                </div>
                <?php if (isset($campaign->custom_questions) && ($campaign->custom_questions == 'Yes') or $campaign->custom_questions == 1) { ?>
                    <div class="dialog-form ">
                        <label class="vertical-top"> Question Text:</label>

                        <div class="form-view">

                            <label
                                class="view-text-field large_text_area"><?php if (!empty($campaign->custom_question_value)) {
                                    echo htmlspecialchars($campaign->custom_question_value);
                                } ?></label></div>

                    </div>
                <?php } ?>
                <div class="dialog-form ">
                    <label>Call File Request Date:</label>

                    <div class="form-view">
                        <label class="view-text-field"><?php if (!empty($campaign->call_filerequest_date)) {
                                echo php_dateformat($campaign->call_filerequest_date);
                            }else{echo ' - ';} ?></label></div>
                </div>
				<div class="dialog-form ">
                    <label>Materials Sent to TM Ops (Asset, CF,  TM Kick Off Email, etc):</label>

                    <div class="form-view">
                        <label class="view-text-field"><?php if (!empty($campaign->materials_sent_to_tm_Date)) {
                                echo php_dateformat($campaign->materials_sent_to_tm_Date);
                            }else{echo ' - ';} ?></label></div>
                </div>
                <div class="dialog-form ">
                    <label>Telemarketing Offices:</label>

                    <div class="form-view">
                        <label class="view-text-field"><?php
						if(!empty($campaign->telemarketing_offices))
						{
							echo $campaign->telemarketing_offices;
                            }else{echo ' - ';} ?></label></div>
                </div>
                <div class="dialog-form ">
                    <label>Contacts:</label>

                    <div class="form-view">
                        <label class="view-text-field">
                        <?php
                            echo '<a href="/dialer/lists/index/'.$campaign->id.'">[Call Lists]</a>';
                        ?>
                        </label>
                    </div>
                </div>
            </div>
            <div class="form-section-title">
                <p>JOB FILTERS</p>
                <span></span>
            </div>
            <div class="row">
                <div class="form-row mar-b-0">
                <?php if($this->app == 'mpg'){ ?>
                    <div class="dialog-form ">
                        <label class="vertical-top">Filters:</label>

                        <div class="form-view">
                            <label class="view-text-field large_text_area"><?php echo $campaign->filters; ?></label></div>
                    </div>
                <?php }else{?> 
                
                    <div class="dialog-form ">
                        <label>Job Function:</label>

                        <div class="form-view">
                            <label class="view-text-field"><?php
						if($campaign->job_function!="")
						{
							$job_function=str_replace("|","<br>",$campaign->job_function);
							echo $job_function;
                                }else{echo ' - ';} ?></label></div>
                    </div>
                    <div class="dialog-form ">
                        <label>Job Level:</label>

                        <div class="form-view">
                            <label class="view-text-field"><?php
						if($campaign->job_level!="")
						{
							$job_level=str_replace("|","<br>",$campaign->job_level);
							echo $job_level;
                                }else{echo ' - ';} ?></label></div>
                    </div>
             
                    <div class="dialog-form ">
                        <label>Company Size:</label>

                        <div class="form-view">
                            <label class="view-text-field"><?php
						if($campaign->company_size!="")
						{
							$company_size=str_replace("|","<br>",$campaign->company_size);
                            if($company_size == '1-9'){
                                $company_size = '1 to 9';
                            }
                            if($company_size == '10-24'){
                                $company_size = '10 to 24';
                            }

							echo $company_size;
                                }else{echo ' - ';} ?></label></div>
                    </div>

                    <div class="dialog-form ">
                        <label>Industries:</label>

                        <div class="form-view">
                            <label class="view-text-field"><?php
						if($campaign->industries!="")
						{
							$industries=str_replace("|","<br>",$campaign->industries);
							echo $industries;
                                }else{echo ' - ';} ?></label></div>
                    </div>
					<div class="dialog-form ">
                <label>Country:</label>
				 <div class="form-view">
                            <label class="view-text-field">
                            <?php if($campaign->country!=""){
							$country=str_replace("|","<br>",$campaign->country);
							echo $country;
                                }else{echo ' - ';} 
                            ?>
                            </label>                 
                </div>
            </div>
                
                <?php } ?>		
                </div>
                <div class="form-row mar-b-0">
                    <div class="dialog-form ">
                        <label class="vertical-top">Script Main:</label>

                        <div class="form-view">
                            <label class="view-text-field large_text_area"><?php if (!empty($campaign->script_main)) {
                                    echo $campaign->script_main;
                                }else{echo ' - ';} ?></label></div>
                    </div>

                    <div class="dialog-form ">
                        <label class="vertical-top">Script Alter:</label>

                        <div class="form-view">

                                <label class="view-text-field large_text_area"><?php if (!empty($campaign->script_alt)) {
                                        echo $campaign->script_alt;
                                    }else{echo ' - ';} ?></label></div>
                    </div>
                </div>
                <div class="form-row mar-b-0">
                    <div class="dialog-form ">
                        <label>Assign Team:</label>
                        <div class="form-view">
                            <label class="view-text-field">
                                <?php if (!empty($tlList)) {                                     
                                        foreach($tlList as $list){
											$userdata=explode("|",$list);
                                            if($this->session->userdata['user_type'] == 'qa' or $this->session->userdata['user_type'] == 'manager' || $this->session->userdata['user_type'] == 'admin'){?>
                                                <a href="/dialer/campaigns/campaignassign/<?php echo $userdata[1].'/'.$campaign->id?>"><?php echo $userdata[0]; ?></a><br/>
                                        <?php }else{
                                                echo $userdata[0]; echo "<br>";
                                            }                                        
                                        } ?>
                                <?php }else{ echo " - ";}?>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="clearfix"></div>
</section>

<script>
      var campaingid = '<?php echo $campaign->id;?>'; 
    $("#delete_contact").click(function () {      
        ShowConfirm('Do you want to delete all Contacts of this campaign?', function () {
             window.location="/dialer/contacts/delete_campaign_contacts/" + campaingid;
        }, function () {
            return false;
        },'Remove Contacts');
    });
    </script>
    
