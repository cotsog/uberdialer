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
            <div class="pad-15-t pad-15-l  call-row-title">
               <div class="column-header">
                    <p>Calls in Progress</p>
                </div>
            </div>

            <div class="pad-15-t pad-15-lr ">

                <div class="pad-15-l row-left-pad call-row-title">

                </div>

                <div class="span12 pad-15-t" style="margin:0 0 10px 0;">

                </div>

                <table id="staffing_attrition_report" class="table table-bordered row vertical-tbl sort-th" style="width: 100%;table-layout: fixed;">
                    <thead>
                    <tr style="background: #f4f4f4;">

                        <th class="aligncenter">Contact Phone</th>
                        <th class="aligncenter">Contact Name</th>
                        <th class="aligncenter">Agent Name</th>
                        <th class="aligncenter">Listen In.</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    if(!empty($activeCalls)){
                        foreach($activeCalls as $activeCall){?>
                            <tr class="text_display_area" align="center" style="word-break: break-all">
                                <td class="text_align_left"><?php echo $activeCall->target; ?></td>
                                <td class="text_align_left"><?php echo $activeCall->target_name; ?></td>
                                <td><?php echo $activeCall->first_name; ?></td>
                                <td><a target="_blank" href="/dialer/calls/tap/<?php echo $activeCall->id; ?>">Tap</a></td>
                            </tr>
                        <?php }  }
                    else{?>
                        <tr>
                            <td colspan="4"><div style='padding:6px;font-size: 14px; background:#D8D8D8'>No record(s) found</div></td>
                        </tr>
                    <?php }?>
                    </tbody>
                </table><br/><br/>

            </div>

        </div>

    <div class="clearfix"></div>
</section>
<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/pagejs/dialer/calls/plivo_active_calls.js"></script>
<script type="text/javascript"> var loggedInUserType = '<?php echo $this->session->userdata('user_type'); ?>'; </script>
<style>
    td {
        word-break: break-word;
        word-wrap: break-word;
    }
</style>