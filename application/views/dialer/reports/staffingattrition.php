<section class="section-content-main-area">
    <div class="content-main-area">
        <div class="pad-15-b" >
            <div class="pad-15-t pad-15-l  call-row-title">
               <div class="column-header">
                    <p>Staffing and Attrition</p>
                </div>
            </div>

            <div class="pad-15-t pad-15-lr ">
                <div class="span12 pad-15-t" style="margin:0 0 10px 0;">
                    <span class="tm-filter-msg"></span>
                    <div class="no_of_record_area"><?php if(isset($num_recs)){echo $num_recs;} ?> Record(s) found</div>

                            <form action="/dialer/reports/staff_attrition" method="post"  onsubmit='return true;'>
                                <input type="hidden" name="file_type" value="excel" />
                                <input style="top: 110px;position: absolute;right: 90px;" type="image" name="submit" src="/images/file-extension-xls-biff-icon.png" width="32" height="32" alt="Submit"/>
                            </form>

                            <form action="/dialer/reports/staff_attrition" method="post"  onsubmit='return true;'>
                                <input type="hidden" name="file_type" value="csv" />
                                <input style="top: 110px;position: absolute;right: 46px;" type="image" name="submit" src="/images/file-extension-csv-icon.png" width="32" height="32" alt="Submit"/>
                            </form>
                </div>
                <table id="staffing_attrition_report" class="table table-bordered row vertical-tbl sort-th" style="width: 100%;table-layout: fixed;">
                    <thead>
                    <tr style="background: #f4f4f4;">
                        <!--                            <th class="aligncenter">member</th>-->
                        <th class="aligncenter">Agents</th>
                        <th class="aligncenter">Tier</a></th>
                        <th class="aligncenter">TL</a></th>
                        <th class="aligncenter">Project</th>
                        <th class="aligncenter">Schedule</a></th>
                        <th class="aligncenter">Date Hired</th>
                        <th class="aligncenter">Status</a></th>
                        <th class="aligncenter">Date</a></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    if(!empty($staffing_attrition)){
                        foreach($staffing_attrition as $staffing_attrition_value){?>
                            <tr class="text_display_area" align="center" style="word-break: break-all">
                                <td class="text_align_left"><?php echo $staffing_attrition_value['Agents']; ?></td>
                                <td><?php echo $staffing_attrition_value['Tier']; ?></td>
                                <td class="text_align_left"><?php echo $staffing_attrition_value['TL']; ?></td>
                                <td><?php echo $staffing_attrition_value['Project']; ?></td>
                                <td><?php echo $staffing_attrition_value['Schedule']; ?></td>
                                <td><?php echo $staffing_attrition_value['Date Hired']; ?></td>
                                <td><?php echo $staffing_attrition_value['Status']; ?></td>
                                <td><?php echo $staffing_attrition_value['Date']; ?></td>
                            </tr>
                        <?php }  }
                    else{?>
                        <tr>
                            <td colspan="8"><div style='padding:6px;font-size: 14px; background:#D8D8D8'>No record(s) found</div></td>
                        </tr>
                    <?php }?>
                    </tbody>
                </table><br/><br/>

            </div>
        </div>
    </div>
    <div class="clearfix"></div>
</section>

<script type="text/javascript"> var loggedInUserType = '<?php echo $this->session->userdata('user_type'); ?>'; </script>
<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/pagejs/dialer/reports/index.js<?=$this->cache_buster?>"></script>
<style>
    td {
        word-break: break-word;
        word-wrap: break-word;
    }
</style>