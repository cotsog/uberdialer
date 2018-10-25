<section class="section-content-main-area">

    <div class="content-main-area">
        <?php

        if ((isset($msg) && $msg != '') || $this->session->flashdata('msg') != '') {
            if ($this->session->flashdata('class') == 'good') $class = "class= 'error-msg good'"; else $class = "class='error-msg bad'";
            echo('<div style="z-index: 1; position: inherit; width: 100%;" id="divErrorMsg" ' . $class . '>');
            echo(' <p><span><i class="fa fa-times-circle"></i></span>');
            echo $this->session->flashdata('msg');
            echo('</div>');
        } ?>
        <div class="pad-15-b" >
            <div class="pad-15-t pad-15-l  call-row-title">
                <div class="column-header">
                    <p>List Management For  <?php echo $campaignData->name." - (".$campaignData->id ; ?>)<?php if($this->session->userdata('user_type') == 'admin'  ||  $this->session->userdata('user_type') == 'manager' || $this->session->userdata('user_type') == 'team_leader'){?> <a style="color:white; float: right;" href="/dialer/campaigns/createcontacts/<?php echo $this->uri->segment(4); ?>" class="add-icon"><b>Add List</b></a><?php }?></p>
                    
                </div>
                
            </div>

            <div class="pad-15-t pad-15-lr ">
                <table class="table table-bordered row vertical-tbl sort-th lead_table" style="width: 100%;table-layout: fixed;">
                    <thead>
                    <tr>
                        <th class="aligncenter" style="width:14%;">List Name</th>
                        <th class="aligncenter" style="width:10%;"  id="sort_column">[link to call queue]</th>
                        <?php if(in_array($this->session->userdata('user_type'), $upperManagement)  ||  $this->session->userdata('user_type') == 'manager' || $this->session->userdata('user_type') == 'team_leader'){?>
                        <th class="aligncenter" style="width:10%;"  id="sort_column">[link to contact list]</th>
                        <?php } ?>    
                        <th class="aligncenter" style="width:10%;"  id="sort_column"># contacts on list</th>
                        <th class="aligncenter" style="width:10%;"># workable contacts on list</th>
                        <th class="aligncenter" style="width:11%;"  id="sort_column">Date uploaded</th>
                        <th class="aligncenter" style="width:10%;"  id="sort_column">Status</th>
                        <?php if($this->session->userdata('user_type') == 'admin' ||  $this->session->userdata('user_type') == 'manager' || $this->session->userdata('user_type') == 'team_leader'){?>
                        <th class="aligncenter" style="width:10%;"  id="sort_column">Action</th>
                        <?php }?>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    if(!empty($get_all_list_by_campaign)){
                        foreach($get_all_list_by_campaign as $list_value){?>
                            <tr align="center" style="word-break: break-all">
                                <td><?php echo isset($list_value->list_name)?$list_value->list_name:""; ?></td>
                                <?php if($this->session->userdata('user_type') != 'qa'){?>
                                    <td><a href="/dialer/contacts/index/<?php echo $list_value->campaign_id.'/'.$list_value->list_id; ?>">[Call Queue]</a></td>
                                <?php } ?>
                                <?php if(in_array($this->session->userdata('user_type'), $upperManagement)  ||  $this->session->userdata('user_type') == 'manager'  ||  $this->session->userdata('user_type') == 'qa'){?>
                                    <td>
                                        <a href="/dialer/contacts/contactlist/<?php echo $list_value->campaign_id.'/'.$list_value->list_id; ?>">[Contact List]</a>
                                        <?php if($this->session->userdata('user_type') != 'qa'){?>
                                            &nbsp;&nbsp;
                                            <a href="/dialer/contacts/edit_campaign_contacts/<?php echo $list_value->campaign_id.'/'.$list_value->list_id; ?>">[Edit List]</a>
                                        <?php } ?>
                                    </td>
                                <?php } ?>
                                <?php if($this->session->userdata('user_type') == 'team_leader'){?>
                                <td><a href="/dialer/contacts/edit_campaign_contacts/<?php echo $list_value->campaign_id.'/'.$list_value->list_id; ?>">[Edit List]</a></td>
                                <?php } ?>
                                <td><?php echo isset($list_value->contact_list_count)?$list_value->contact_list_count:""; ?></td>
                                <td><?php echo isset($list_value->workable_count_list)?$list_value->workable_count_list:""; ?></td>
                                <td><?php echo isset($list_value->created_at)? php_datetimeformat($list_value->created_at):""; ?></td>
                                <td><?php echo isset($list_value->status)?$list_value->status:""; ?></td>
                                 <?php if($this->session->userdata('user_type') == 'admin' ||  $this->session->userdata('user_type') == 'manager' || $this->session->userdata('user_type') == 'team_leader'){?>
                                <td>
                                    <a href="/dialer/campaigns/createcontacts/<?php echo $list_value->campaign_id.'/'.$list_value->list_id; ?>" title="Edit"><i class="fa fa-pencil-square-o font-info-help"></i>
                                    <a href="#" id="<?php echo $list_value->campaign_id.'_'.$list_value->list_id; ?>" class="delete_list" title="Delete"><i class="fa fa-trash-o list-trash-font-help"></i>
                                    
                                </td>
                                <?php }?>
                                </tr>
                        <?php }  }
                    else{?>
                        <tr>
                            <td colspan= <?php if(!empty($get_all_list_by_campaign)){ echo "14"; }else{ echo "12";} ?>><div style='padding:6px;font-size: 14px; background:#D8D8D8'>No record(s) found</div></td>
                        </tr>
                    <?php }?>
                    </tbody>
                </table><br/><br/>
                <?php if(!empty($get_all_list_by_campaign) && !empty($this->pagination)){?>
               <div>
                    <div>
                        <div class="dataTables_info" id="DataTables_Table_0_info">Showing <?php echo $offset + 1; ?> to <?php echo $offset + count($get_all_list_by_campaign); ?> of <?php echo $num_recs; ?> entries</div>
                    </div>
                   <div class="pagination" style="margin: 40px 0;">
                        <?php echo $page_links; ?>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <div class="clearfix"></div>
</section>
<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/pagejs/dialer/lists/index.js"></script>