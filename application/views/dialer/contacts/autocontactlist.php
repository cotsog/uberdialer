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
                        <strong>Call Queue for Campaign Id <?php echo $campaignData->eg_campaign_id." - (".$campaignData->name .")" ?></strong>
                    </span>
                </div>
            </div>
            <div class="icons">
                <!--<a href="#" id="edit_c"><i class="fa fa-edit list-edit-font"></i></a>-->
                <?php   $user_type = $this->session->userdata('user_type');
                if(in_array($user_type, array('admin','team_leader','manager','agent'))){
                    ?>
                <a href="/utilities/emailChangeLookup?campaign_id=<?=$campaignData->eg_campaign_id?>" target="_blank"><i class="fa fa-search"></i></a>
                    <?php
                }
                if ($user_type == 'team_leader' || $user_type == 'manager' || $user_type == 'admin' || $user_type == 'agent') {
                    if($user_type == 'manager' || $user_type == 'admin'){?>
                    <a href="/dialer/contacts/contactlist/<?=$campaignData->id;?>/<?=$listId?>"><i class="fa fa-list-alt"></i></a>
                    <?php }
                        }
                     ?>
            </div>
            
        </div>
        <div id="dvGqgrid" style="width: 100%" class="jqglabel">
            <!-- Workable grid view -->
            <table id="list" class="jqglabel">
            </table>

            <div id="pager" class="jqgrid-footer"></div>
        </div>
    </div>
</section>

<!--Add/Edit Dialog Form-->
<div id="dialog-form" title="VIEW INDIVIDUAL LIST DETAILS" class="account-detail-dialog" style="display: none">
    <div class="alphabetic-search-area-horizontal"></div>
</div>


<script type="text/javascript">
    var campaign_id = '<?php echo $campaign_id?>';
    var listid = '<?php echo $listId?>';
    var jsonDispo = '<?php echo trim($CallDispositionValues);?>';
    var leadStatusDataJsonData = '<?php echo $LeadStatusValues;?>';
    var logged_user_type = '<?php echo $this->session->userdata('user_type');?>';
    var logged_user_id = '<?php echo $this->session->userdata('uid');?>';
	var contactsdata = <?php echo $contacts;?>;
    var filterRules = $.parseJSON('<?php echo str_replace("\'", "'", $filter_rules); ?>');
</script>
<script type="text/javascript" src="/js/pagejs/dialer/contacts/autocalllist.js?<?=$this->cache_buster?>"></script>
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
