<style>
.styled select{ width: 300px;}
.styled select {max-width: 300px !important;
    width: 290px;}
.pagination { margin-bottom: 5% !important;}
</style>
<section class="section-content-main-area">
    <?php
        $page_num = (int)$this->uri->segment(6);
        if($page_num==0)$page_num=1;
        $order_seg = $this->uri->segment(8,'asc');
        if($order_seg=='asc')$order = 'desc';else $order = 'asc';
    ?>
    <div class="content-main-area">
        <?php

        if ((isset($msg) && $msg != '') || $this->session->flashdata('msg') != '') {
            if ($this->session->flashdata('class') == 'good') $class = "class= 'error-msg good'"; else $class = "class='error-msg bad'";
            echo('<div id="divErrorMsg" ' . $class . '>');
            echo(' <p><span><i class="fa fa-times-circle"></i></span>');
            echo $this->session->flashdata('msg');
            echo('</div>');
        } ?>
        <div class="pad-15-b" >
            <div class="pad-15-t pad-15-l  call-row-title">
                <div class="column-header">
                    <p><span class="column-title"><strong>CAMPAIGN  #<?php echo $campaignData->eg_campaign_id." (".$campaignData->name .")" ?> CALL CONTACTS</strong></span></p>
                </div>
            </div>
            <div class="pad-15-t pad-15-l  call-row-title">
                <fieldset style="display: block; border-width: 5px; border-style: groove; border-color: threedface; border-image: initial;">
                <legend><p><span class="column-title"><strong>Summary</strong></span></p></legend>
                <div style="margin-top:7px;"><label style="font-weight: bold; font-size: 14px;">Job Function :</label><label style="font-size: 14px; margin-left: 10px;"><?php echo !empty($_POST['job_function']) ? implode(", ", $_POST['job_function']) : ""; ?></label><br></div>
                <div style="margin-top:7px;"><label style="font-weight: bold; font-size: 14px;">Job Level :</label><label style="font-size: 14px; margin-left: 10px;"><?php echo !empty($_POST['job_level']) ? implode(", ", $_POST['job_level']) : ""; ?></label><br></div>
                <div style="margin-top:7px;"><label style="font-weight: bold; font-size: 14px;">Company Size :</label><label style="font-size: 14px; margin-left: 10px;"><?php echo !empty($_POST['company_size']) ? implode(", ", $_POST['company_size']) : ""; ?></label><br></div>
                <div style="margin-top:7px;"><label style="font-weight: bold; font-size: 14px;">Industries :</label><label style="font-size: 14px; margin-left: 10px;"><?php echo !empty($_POST['industry']) ? implode(", ", $_POST['industry']) : ""; ?></label><br></div>
                <div style="margin:7px 0px 7px 0px;"><label style="font-weight: bold; font-size: 14px;">Country :</label><label style="font-size: 14px; margin-left: 10px;"><?php echo !empty($srh_country) ? $srh_country : ""; ?></label><br></div>
               </fieldset>
                
                
            </div>
                    </div>
            <div class="pad-15-t pad-15-l row-left-pad call-row-title">  
                <form method="post" name="callcontacts_searchform" id="callcontacts_searchform" action="/dialer/contacts/edit_campaign_contacts/<?=$campaignData->id?>/<?=$list_id?>/filter">
                    <div class="dialog-form ">
                        <input type="hidden" name="campaign_id" value="<?=$campaignData->id;?>" />
                        <input type="hidden" name="list_id" value="<?=$list_id;?>" />
                        <label>Job Function:</label>
                        <div class="styled select-dropdown">
                             <select name="job_function[]" id="job_function" multiple="multiple" size="3">
							<!--<option selected="selected" value="">--SELECT--</option>-->
							<?php
                           $job_function_post_value =  $this->input->post('job_function');

							if (!empty($jobFunctionValues)) {
								foreach ($jobFunctionValues as $jobFunction) { 
                                    if(!empty($job_function_post_value)){
                                        if (in_array($jobFunction,$this->input->post('job_function'))){
                                            $selected = "selected";
                                        }
                                        else{
                                            $selected = "";
                                        }
                                    }else{
                                        $selected = "";
                                    }
                                        ?>
									<option role="option" value="<?= $jobFunction;?>" <?=$selected?>><?= $jobFunction;?></option>
								<?php }
							}
							?>
						</select>
                        </div>
                    </div>
                    <div class="dialog-form ">
                        <label> Job Level:</label>

                        <div class="styled select-dropdown">
                           <select name="job_level[]" id="job_level" multiple="multiple" size="3">
                        <?php
                        $job_level_post_value =  $this->input->post('job_level');
                        if (!empty($jobLevelValues)) {
                            foreach ($jobLevelValues as $jobLevel) { 
                                if(!empty($job_level_post_value)) {
                                    if (in_array($jobLevel, $job_level_post_value))
                                            $selected = "selected";
                                        else
                                        $selected = "";
                                } else
                                    $selected = "";
                                ?>
                                <option role="option" value="<?= $jobLevel;?>" <?=$selected?>><?= $jobLevel;?></option>
                            <?php }
                        }
                        ?>
                    </select>
                        </div>
                    </div>
                     <div class="dialog-form ">
                        <label> Company Size:</label>

                        <div class="styled select-dropdown">
                            <select name="company_size[]" id="company_size" multiple="multiple" size="3">
                        <?php
                        $company_size_post_value =  $this->input->post('company_size');
                        if (!empty($companySizeValues)) {
                            foreach ($companySizeValues as $companySize) {
                                if($companySize == '1-9'){
                                    $companySize = '1 to 9';
                                }
                                if($companySize == '10-24'){
                                    $companySize = '10 to 24';
                                }
                                if(!empty($company_size_post_value)) {
                                    if (in_array($companySize, $company_size_post_value))
                                            $selected = "selected";
                                        else
                                        $selected = "";
                                    } else
                                            $selected = "";

                              ?>
                                <option role="option" value="<?= $companySize;?>" <?=$selected?>><?= $companySize;?></option>
                            <?php }
                        }
                        ?>
                    </select>
                        </div>
                    </div>
                    <div class="dialog-form ">
                        <label>Industries:</label>

                        <div class="styled select-dropdown">
                            <select name="industry[]" id="industry" multiple="multiple" size="3">
                        <?php
                        $industry_post_value =  $this->input->post('industry');
                        if (!empty($industriesValues)) {
                            foreach ($industriesValues as $industries) { 
                                if(!empty($industry_post_value)) {
                                    if (in_array($industries,$industry_post_value))
                                            $selected = "selected";
                                        else
                                        $selected = "";
                                }else
                                            $selected = "";?>
                                <option role="option"
                                        value="<?= $industries; ?>" <?=$selected?>><?= $industries; ?></option>
                            <?php }
                        }
                        ?>
                    </select>
                        </div>
                    </div>
                    <div class="dialog-form ">
                        <label>Country:</label>

                       <div class="styled select-dropdown"><select name="country[]" id="country" multiple="multiple" size="3">
                        <?php
                        $country_post_value =  $this->input->post('country');
                        if (!empty($countryValues)) {
                            foreach ($countryValues as $key=>$countries) { 
                                if($country_post_value){
							if (in_array($key,$this->input->post('country')))
                                            $selected = "selected";
                                        else
                                        $selected = "";
                                }else
                                    $selected = "";
							    ?>
                                <option role="option"
                                        value="<?= $key; ?>" <?=$selected?>><?= $countries; ?></option>
                            <?php }
                        }
                        ?>
                    </select></div>
                    </div>
                    <div class="dialog-form ">
                        <button type="submit" class="general-btn" id="leads_btnSave">Filter</button>
                    </div>
                    <div class="dialog-form ">
                        <button type="button" class="general-btn" id="leads_btnClear" onclick="clear_filter(<?php echo $campaignData->id; ?>,<?php echo $list_id; ?>) ">Clear</button>
                    </div>
                    <?php if(!empty($contactsdata)){?>
                    <div class="dialog-form ">
                        <button type="button" class="general-btn" id="delete_contact">Delete Contact</button>
                    </div>
                    <div class="dialog-form ">
                        <input type="hidden" value="" name="save_filter" id="save_filter">
                        <button type="button" class="general-btn" id="save_list_contact"><?php if(!empty($contact_filter)){ echo "Update Filter";}else{echo "Save"; } ?></button>
                        
                    </div>
                    <?php }?>
                    <div class="span12" style="margin:0 0 10px 0;float: right">
                    <span class="tm-filter-msg"></span>
                       
                </div>
                </form>
            </div>
            <div class="pad-15-t pad-15-lr ">
			
                <table class="table table-bordered row vertical-tbl sort-th lead_table" style="width: 100%;table-layout: fixed;">
                    <thead>
                        <tr>
                            
                            <th class="aligncenter" style="width:3%;">
                                <?php if(!empty($contactsdata)){?>
                                <input type="checkbox" onchange="checkAll()" name="checkArray" class="cbox css-checkbox" id="jqg_list_29" role="checkbox"  value="0">
                                <label class="css-label checkbox-label radGroup1 cst-export-lbl" for="jqg_list_29"></label>
                                <?php }?>
                            </th>
                            <th class="aligncenter" style="width:12%;">Full Name</th>
                            <th class="aligncenter" style="width:8%;" id="sort_column"><a href="<?= $base_url?>/1/phone/<?=$order?>">Phone Number</a></th>
                            <th class="aligncenter" style="width:19%;" id="sort_column"><a href="<?= $base_url?>/1/company/<?=$order?>">Company Name</a></th>
                            
                            <th class="aligncenter" style="width:10%;" id="sort_column"><a href="<?= $base_url?>/1/job_function/<?=$order?>">Job Function</a></th>
                           
                            <th class="aligncenter" style="width:10%;"><a href="<?= $base_url?>/1/job_level/<?=$order?>">Job Level</a></th>
                            <th class="aligncenter" style="width:10%;"><a href="<?= $base_url?>/1/company_size/<?=$order?>">Company Size</a></th>
                            <th class="aligncenter" style="width:15%;"><a href="<?= $base_url?>/1/industry/<?=$order?>">Industries</a></th>
                            <th class="aligncenter" style="width:6%;"><a href="<?= $base_url?>/1/country/<?=$order?>">Country</a></th>
                            <th class="aligncenter" style="width:6%;">Time Zone</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if(!empty($contactsdata)){
                           
                            foreach($contactsdata as $contacts){
                                  ?>
                            <tr align="center">
                                <td>
                                    <input type="checkbox" name="jqg_list_29" class="cbox css-checkbox" id="jqg_list_<?=$contacts->campaign_contact_id;?>" role="checkbox" value="0">
                                    <label class="css-label checkbox-label radGroup1 cst-export-lbl" for="jqg_list_<?=$contacts->campaign_contact_id;?>"></label>
                                </td>
                                <td><?php echo $contacts->first_name." ".$contacts->last_name; ?></td>
                                <td><?php echo $contacts->phone; ?></td>
                                <td><?php echo $contacts->company; ?></td>
                                <td><?php echo $contacts->job_function; ?></td>
                                <td><?php echo $contacts->job_level; ?></td>
                                <td><?php
                                    if( $contacts->company_size == '1-9'){
                                        $contacts->company_size = '1 to 9';
                                    }
                                    if( $contacts->company_size == '10-24'){
                                        $contacts->company_size = '10 to 24';
                                    }

                                    echo  $contacts->company_size; ?></td>
                                <td><?php echo $contacts->industry; ?></td>
				                <td><?php echo $contacts->country; ?></td>
                                <td><?php echo $contacts->time_zone; ?></td>
                                
                            </tr>
                            <?php }  }
                            else{?>
                            <tr>
                                <td colspan="14"><div style='padding:6px;font-size: 14px; background:#D8D8D8'>No record(s) found</div></td>
                            </tr>
                            <?php }?>
                    </tbody>
                </table><br/><br/>
                <?php if(!empty($contactsdata)){ if (isset($this->pagination)) {?>
               <div>
                    <div>
                        <div class="dataTables_info" id="DataTables_Table_0_info">Showing <?php echo $offset + 1; ?> to <?php echo $offset + count($contactsdata); ?>  of <?php echo $num_recs; ?> entries</div>
                    </div>
                    <div class="pagination">
                        <?php echo $page_links; ?>
                    </div>
                </div>
                <?php } } ?>
            </div>
        </div>
    </div>
    <div id="dialog-form" title="FOLLOWUP/REJECTION REASONS" class="account-detail-dialog" style="display:none;"></div>
    <div class="clearfix"></div>
</section>
<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/pagejs/dialer/contacts/filter_campaign_contacts.js<?=$this->cache_buster?>"></script>

<script type="text/javascript"> 

</script>
<style>
   td {
    word-break: break-word;
    word-wrap: break-word;
}
</style>