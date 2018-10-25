<?php if (isset($msg)) {
    echo '<h2 class="warning">' . $msg . '</h2>';
}
?>
<section class="section-content-main-area">
    <div class="content-main-area">
        <?php
        if ((isset($msg) && $msg != '') || $this->session->flashdata('msg') != '') {
            if ($this->session->flashdata('class') == 'good')
                $class = "class= 'error-msg good'";
            else
                $class = "class='error-msg  bad'";
            echo('<div id="divErrorMsg" ' . $class . '>');
            echo (' <p><span><i class="fa fa-times-circle"></i></span>');
            echo $this->session->flashdata('msg');
            echo('</div>');
        }
        ?>
        <div id="ajax-content-container"></div>
        <div class="column-header query-list">
            <div class="alignleft" style="width: 790px">
                <div class="alignleft" >
                    <span class="column-title">
                        <strong>CAMPAIGN  #<?php echo $campaignData->eg_campaign_id." (".$campaignData->name .")" ?> Contact List
                        </strong>
                    </span>
                </div>
            </div>
        </div>
        <div id="dvGqgrid" style="width: 100%" class="jqglabel">
            <table id="list" class="jqglabel">
            </table>
            <div id="pager" class="jqgrid-footer"></div>
        </div>
    </div>
</section>

<script type="text/javascript">
var campaign_id = '<?php echo $campaignData->id?>';
    var listid = '<?php echo $listId?>';
    var contactsdata = <?php echo $contacts;?>;
</script>
<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/pagejs/dialer/contacts/list.js<?=$this->cache_buster?>"></script>
<style>
    .ui-jqgrid tr.jqgrow td {
        white-space: normal !important;
        height:auto;
        vertical-align:text-top;
    }
    .ui-jqgrid .ui-jqgrid-htable th div {
        white-space:normal !important;
        height:auto;
        vertical-align:text-top;
        position:relative;
        overflow:unset;
    }
</style>
