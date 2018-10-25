<section class="section-content-main-area">
    <?php 
        $page_num = (int)$this->uri->segment(3);
        if($page_num==0)$page_num=1;
        $order_seg = $this->uri->segment(5,'asc');
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
        <?php if (!empty($agentData)) {
            foreach ($agentData as $key => $agent_data_value) { ?>
            <div class="pad-15-t pad-15-l  call-row-title">
                <div class="column-header">
                        <p>Campaign : <b><?= $key;?></b></p>
                </div>
            </div>
            <div class="pad-15-t pad-15-lr ">
                <table class="table table-bordered row vertical-tbl sort-th userdashboard" style="width: 100%;">
                    <thead>
                        <tr>
                            <th class="aligncenter">Today</th>
                            <th class="aligncenter">My Dials</th>
                            <th class="aligncenter">Generated Leads</th>
                            <th class="aligncenter">Rejected Leads</th>
                            <th class="aligncenter">Approved Leads</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                         $sheduleArray = array('0','1','2','3','4','5','6','7','8');
                         $i=0;
                            foreach($agent_data_value as $Dkey =>$row){
                                if(in_array($Dkey,$sheduleArray)){?>
                            <tr>
                                <th><?= $row['time'];?></th>
                                <td><?= $row['tDials'];?></td>
                                <td><?= $row['tLeads'];?></td>
                                <td><?= $row['tRejLeads'];?></td>
                                <td><?= $row['tApLeads'];?></td>
                            </tr>
                            
                        <?php }else{?> 
                        <tr class="" style="background-color: #93c47d; font-weight: bold">
                            <th style="background-color: #93c47d; font-weight: bold">Total</th>
                                <td style="color:black; font-weight: bold"><?= $agent_data_value['Total']['TotalDials'];?></td>
                                <td style="color:black; font-weight: bold"><?= $agent_data_value['Total']['TotalLeads'];?></td>
                                <td style="color:black; font-weight: bold"><?= $agent_data_value['Total']['TotalRejleads'];?></td>
                                <td style="color:black; font-weight: bold"><?= $agent_data_value['Total']['TotalAprleads'];?></td>
                        </tr>              
                        <?php }}?> 
                         
                    <th colspan="5" style="color:green;font-weight: bold; background-color: #f2d7d5 !important;">Total Follow-up Leads : <?= $agent_data_value['Total']['totalFollowup'];?></th>
                    </tbody>                    
                </table><br/><br/>
                </div>
                    
            <?php
            }            
        }
        else { ?>
            <div class="pad-15-t pad-15-l row-left-pad call-row-title pad-15-b" style="margin-top: 10px;">
                <div class="dashboard_no_records">No record(s) found</div>
            </div>
        <?php } ?>
        </div>    
    </div>
    <div class="clearfix"></div>
</section>

   <!--ss-->   
   
            <script type="text/javascript">
    $("#dialer_dashboard_item").addClass("active");
            </script>