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
            <div class="pad-15-t pad-15-l  call-row-title">
                <div class="column-header">
                    <p>Manually Added Contacts Report</p>
                </div>
                <div style="float:right">
                        <form method='POST' action='/dialer/reports/export_manually_added_contacts'>
                            <input type="image" name="xls" value="xls" src="/images/file-extension-xls-biff-icon.png" width="32" height="32" alt="Submit"/>
                            <input type="image" name="csv" value="csv" src="/images/file-extension-csv-icon.png" width="32" height="32" alt="Submit"/>
                        </form>
                </div>
            </div>

            <div class="pad-15-t pad-15-lr ">
                <table id="manually_added_contacts" class="table table-bordered row vertical-tbl sort-th" style="width: 100%;table-layout: fixed;">
                    <thead>
                    <tr>
                        <th class="aligncenter" id="sort_column">Campaign ID</th>
                        <th class="aligncenter" id="sort_column">Campaign Name</th>
                        <th class="aligncenter" id="sort_column">Total numbers of contact/s added manually</th>
                        <th class="aligncenter" id="sort_column">Total numbers of contact/s added as a different person</th>
                        <th class="aligncenter" id="sort_column">Link</th>
                        
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    if(!empty($report)){

                        foreach($report as $data){

                            ?>
                            <tr class="text_display_area" align="center" style="word-break: break-all">
                                <td><?php echo $data['Campaign ID']; ?></td>
                                <td><?php echo $data['Campaign Name']; ?></td>
                                <td><?php echo $data['Total numbers of contact/s added manually']; ?></td>
                                <td><?php echo $data['Total numbers of contact/s added as a different person']; ?></td>
                                <td><a target="_blank" href="/dialer/reports/manually_added_contacts_campaign/<?=$data['Campaign ID']?>">Link</a></td>
                                
                            </tr>
                        <?php }  }
                    else{?>
                        <tr>
                            <td colspan="10"><div style='padding:6px;font-size: 14px; background:#D8D8D8'>No record(s) found</div></td>
                        </tr>
                    <?php }?>
                    </tbody>
                </table><br/><br/>

            </div>
        </div>
    </div>
    <div id="dialog-form" title="REJECTION REASONS" class="account-detail-dialog" style="display:none;"></div>
    <!--    <div id="dialog-form dialog-notes" title="NOTES" class="account-detail-dialog" style="display:none;"></div>-->
    <div class="clearfix"></div>
</section>
<script type="text/javascript">
$("#report_item").addClass("active open");
$("#manually_added_contacts").addClass("active");

</script>>