<section class="section-content-main-area">
    <div class="content-main-area">
        <div style="display:none;" >
            <?php //echo "<pre>"; print_r($_SESSION); echo "</pre>";?>
        </div> 
        <?php

        if ((isset($msg) && $msg != '') || $this->session->flashdata('msg') != '') {
            if ($this->session->flashdata('class') == 'good') $class = "class= 'error-msg good'"; else $class = "class='error-msg bad'";
            echo('<div id="divErrorMsg" ' . $class . '>');
            echo(' <p><span><i class="fa fa-times-circle"></i></span>');
            echo $this->session->flashdata('msg');
            echo('</div>');
        } ?>
        <div id="divErrorMsg" class="error-msg good hiddendiv" style="display:none;" >
            <p><span><i class="fa fa-times-circle"></i></span> Campaign deleted successfully!
        </div>   
        <div id="ajax-content-container"></div>
        <div class="column-header query-list">
            <div class="alignleft">
                <span class="column-title">Campaigns</span>
            </div>
            <div class="icons">
                <?php if($logged_user_type == 'admin' || $logged_user_type == 'manager'){ ?>
                    <a href="#" id="edit_c"><i class="fa fa-edit list-edit-font"></i></a>
                    <a href="#" id="del_c"><i id="delete_campaign" class="fa fa-trash-o list-trash-font"></i></a>
                    <?php if($logged_user_type == 'admin'){?>
                        <a href="/dialer/campaigns/create" class="add-icon"><i class="fa add-tooltip"></i></a>
                <?php } ?>
                <?php } ?>
            </div>
            <?php if($logged_user_type != 'team_leader' && $logged_user_type != 'agent'){?>
            <div class="form-row clear">
                <div class="filter-type-menu jq-tab-container">
                    <ul>
                        <li class="all_type_search active" id="main_type_search">MAIN</li>
                        <li class="all_type_search" id="completed_type_search">COMPLETED</li>
                       
                    </ul>
                </div>
            </div>
             <?php }?>
        </div>
           
        <div id="dvGqgrid" style="width: 100%" class="jqglabel">
            <table id="list" class="jqglabel">
            </table>
			
            <?php if($logged_user_type != 'team_leader' && $logged_user_type != 'agent'){ ?>
			 <table id="grid2" class="jqglabel">
            </table>            
            <?php }?>
            <div id="pager" class="jqgrid-footer"></div>
        </div>
            
    </div>
</section>

<!--Add/Edit Dialog Form-->
<div id="dialog-form" title="CAMPAIGN DETAILS" class="account-detail-dialog" style="display:none;">
    <div class="alphabetic-search-area-horizontal"></div>
</div>
<script type="text/javascript">
    var campaign_type_list = '<?php echo $campaignTypeList;?>';
    var logged_user_type = '<?php echo $logged_user_type;?>';
    var tm_offices = '<?php echo $tm_offices;?>';
	var campaigndata = <?php echo $campaigns;?>;
	 <?php if($logged_user_type != 'team_leader' && $logged_user_type != 'agent'){?>
	var completedcampaigndata = <?php echo $completed_campaigns;?>;
	<?php }?>
	var totalCampaignRecord = <?php echo $totalCampaignRecord;?>;
</script>
<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/pagejs/dialer/campaigns/dashboard.js<?=$this->cache_buster?>"></script>


