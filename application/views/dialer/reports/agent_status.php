<section class="section-content-main-area">
    <div class="content-main-area">
        <?php
        if ((isset($msg) && $msg != '') || $this->session->flashdata('msg') != '') {
            if ($this->session->flashdata('class') == 'good') $class = "class= 'error-msg good'"; else $class = "class='error-msg bad'";
            echo('<div id="divErrorMsg" ' . $class . '>');
            echo(' <p><span><i class="fa fa-times-circle"></i></span>');
            echo $this->session->flashdata('msg');
            echo('</div>');
        } ?>
        <div class="pad-15-b">
            <div class="pad-15-t pad-15-l  call-row-title">
               <div class="column-header">
                    <p>Agent Status</p>
                </div>
            </div>
           
            <div class="pad-15-t pad-15-l row-left-pad call-row-title">
                <form method="post" name="leadstatus_searchform" id="leadstatus_searchform" class="dashboard_filter">
                    <?php if($this->session->userdata('user_type') != 'agent'){?>
                    <div class="dialog-form">
                        <label style="width: 100px;">User:</label>
                        <div class="styled select-dropdown">                          
                            <select class="team_leader_combo" name="team_leader_id" id="team_leader_id">                    
                                <?php                               
                                if (!empty($users)) {
                                    foreach ($users as $teamMember) {
                                        if ($teamMember['id'] == $this->input->post('team_leader_id'))
                                            $selected = "selected";
                                        else
                                            $selected = "";
                                        echo '<option role="option" value="' . $teamMember['id'] . '" ' . $selected . '>' . $teamMember['member_name'] . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <input type="hidden" id ="username" name="username"/>
                    </div>
                    <?php } ?>

                    <div class="dialog-form ">
                        <label style="width: 70px;"> From Date:</label>

                        <div class="form-input date-picker">
                            <input type="text" id="from_date" name="from_date" placeholder="From date" readonly
                                   maxlength="10"
                                   value="<?php echo $this->input->post('from_date'); ?>"/>
                        </div>
                    </div>
                    <div class="dialog-form ">
                        <label style="width: 60px;"> To Date:</label>

                        <div class="form-input date-picker">
                            <input type="text" id="to_date" name="to_date" placeholder="To date" readonly
                                   maxlength="10"
                                   value="<?php echo $this->input->post('to_date'); ?>"/>
                        </div>
                    </div>
                    <input type="hidden" name="filter_status" id="filter_status" value="">

                    <div class="dialog-form ">
                        <button type="submit" class="general-btn" id="leads_btnSave">Filter</button>
                    </div>
                </form>                
                    <?php if(count($reportData)>0){?>
                     <div class="span12" style="margin:10px 10px;float: right">
                         <form action="/dialer/reports/agent_status" method="post"  onsubmit='return true;'>
                             <input type="hidden" name="status_export_type" value="excel" />
                            <input type="hidden" name="from_date" value="<?php echo $this->input->post('from_date'); ?>" />
                             <input type="hidden" name="to_date" value="<?php echo $this->input->post('to_date'); ?>" />
                             <input type="hidden" name="team_leader_id" value="<?php echo $this->input->post('team_leader_id'); ?>" />
                             <input style="top: 140px;position: absolute;right: 90px;" type="image" name="submit" src="/images/file-extension-xls-biff-icon.png" width="32" height="32" alt="Submit"/>
                        </form>                        

                         <form action="/dialer/reports/agent_status" method="post"  onsubmit='return true;'>
                             <input type="hidden" name="status_export_type" value="csv" />
                             <input type="hidden" name="from_date" value="<?php echo $this->input->post('from_date'); ?>" />
                             <input type="hidden" name="to_date" value="<?php echo $this->input->post('to_date'); ?>" />
                             <input type="hidden" name="team_leader_id" value="<?php echo $this->input->post('team_leader_id'); ?>" />
                             <input style="top: 140px;position: absolute;right: 46px;" type="image" name="submit" src="/images/file-extension-csv-icon.png" width="32" height="32" alt="Submit"/>
                         </form>
                    </div>
                    <?php }?>
            </div>
            
            <div class="pad-15-t pad-15-lr ">
                <table id="call_file_analysis_report" class="call_file_analysis_report  table table-bordered row vertical-tbl sort-th"
                   style="width: 100%;">
                    <thead>
                        <tr style="background: #f4f4f4;">
                            <th class="text_align_left blue_label">Call Disposition </th>
                            <th class="blue_label">Total Count</th>
                            <th class="blue_label">Total Spent Time</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $total = 0;
                    foreach($reportData as $row){
                        $total += (int) $row['TotalDials'];
                        ?>
                        <tr class="text_display_area" align="center" style="word-break: break-all">
                            <td class="text_align_left"><?php echo $row['calldisposition_name']; ?></td>
                            <td><?php echo $row['TotalDials']; ?></td>
                            <td class=""><?php echo $row['TotalTime']; ?></td>                          
                        </tr>
                    <?php }  ?>
                        <tr class="text_display_area" align="center" style="word-break: break-all">
                            <td class="text_align_left"><b>Total Count:</b></td>
                            <td><b><?php echo $total; ?></b></td>
                            <td class=""></td>                          
                        </tr>
                    </tbody>
                </table>    
            </div>
            <?php if($this->session->userdata('user_type') != 'manager' && !in_array($this->session->userdata('user_type'), $upperManagement)){?>
            <div class="pad-15-t pad-15-l  call-row-title">
               <div class="column-header">
                    <p>Sign in/out records</p>
                </div>
            </div>
           
            <?php if (!empty($logInOutData)) { ?>
             <div class="span12" style="margin:10px 10px;float: right">
                 <form action="/dialer/reports/agent_status" method="post"  onsubmit='return true;'>
                     <input type="hidden" name="login_export_type" value="excel" />
                    <input type="hidden" name="from_date" value="<?php echo $this->input->post('from_date'); ?>" />
                     <input type="hidden" name="to_date" value="<?php echo $this->input->post('to_date'); ?>" />
                     <input type="hidden" name="team_leader_id" value="<?php echo $this->input->post('team_leader_id'); ?>" />
                     <input style="position: absolute;right: 90px;" type="image" name="submit" src="/images/file-extension-xls-biff-icon.png" width="32" height="32" alt="Submit"/>
                </form>                        

                 <form action="/dialer/reports/agent_status" method="post"  onsubmit='return true;'>
                     <input type="hidden" name="login_export_type" value="csv" />
                     <input type="hidden" name="from_date" value="<?php echo $this->input->post('from_date'); ?>" />
                     <input type="hidden" name="to_date" value="<?php echo $this->input->post('to_date'); ?>" />
                     <input type="hidden" name="team_leader_id" value="<?php echo $this->input->post('team_leader_id'); ?>" />
                     <input style="position: absolute;right: 46px;" type="image" name="submit" src="/images/file-extension-csv-icon.png" width="32" height="32" alt="Submit"/>
                 </form>
            </div>
            <div  class="pad-15-t pad-15-l  call-row-title"></div>
            
            <div class="pad-15-t pad-15-lr ">
                <table id="call_file_andalysis_report" class="call_file_analysis_report  table table-bordered row vertical-tbl sort-th"
                   style="width: 100%;margin-top: 15px;">
                    <thead>
                        <tr style="background: #f4f4f4;">
                            <th class="text_align_left blue_label">Date </th>
                            <th class="blue_label text_align_center">Campaign Name</th>
                            <th class="blue_label text_align_center">LogIn</th>
                            <th class="blue_label text_align_center">LogOut</th>
                            <th class="blue_label text_align_center">Total Spent Time</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php  foreach($logInOutData as $row1){?>
                        <tr class="text_display_area" align="center">
                            <td class="text_align_left"><?php echo $row1['Date']; ?></td>
                            <td class="text_align_left"><?php echo $row1['Campaign Name']; ?></td>
                            <td><?php echo $row1['LogIn']; ?></td>
                            <td><?php echo $row1['LogOut']; ?></td>
                            <td class=""><?php echo $row1['Total Spent Time']; ?></td>                          
                        </tr>
                    <?php }  ?>
                    </tbody>
                </table>  
            </div>
            <?php } else { ?>
            <div class="pad-15-t pad-15-l row-left-pad call-row-title pad-15-b" style="margin-top: 10px;">
                <div class="dashboard_no_records">No record(s) found</div>
            </div>
            <?php } }?>
        </div>
        <br/><br/>        
    </div>
    <div class="clearfix"></div>
</section>
<script>
var logedInUser =  '<?php echo $this->session->userdata('user_fname')." ".$this->session->userdata('user_lname')?>';
var logedInUserType  = '<?php echo $this->session->userdata('user_type')?>';  
</script>
<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/pagejs/dialer/reports/agent_status.js<?=$this->cache_buster?>"></script>
