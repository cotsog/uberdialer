<style>
.user-edit-popup {
  position: relative;
  background: #FFF;
  padding: 25px;
  width: auto;
  max-width: 400px;
  margin: 20px auto;
  height: 280px
}
.user-edit-popup-border {
    margin: 10px;
    border: 1px solid #ccc;
    height: 260px;
}
.user-edit-header {
    padding: 5px;
    border-bottom: 3px solid #0193e8;
}
.user-edit-form {
    display: block;
    padding: 15px;
}
.user-edit-form span.move-selected-span {
    font-size: 15px;
    display: block;
    margin-top: 40px;
    margin-bottom: 10px;
}
.user-btn-group {
    display: inline-block;
    margin-top: 30px;
    text-align: right;
    width: 100%;
    margin-bottom: 20px;
}
</style>
<?php
//------------------------------>
// Error Message if any
//------------------------------>

if (isset($msg)) {
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
            <div class="alignleft">
                <?php
                $fullName = $this->session->userdata('user_fname')." ".$this->session->userdata('user_lname');
                if($this->session->userdata('user_type') == 'team_leader'){
                     $title ="AGENT ASSIGN TO ".$fullName;
                }else{
                    $title="MANAGE USERS";
                }?>
                <span class="column-title"><?=$title;?></span>
            </div>
            <div class="icons">
                <div class="search-area listing">
                    <input id="globalSearchText" type="text" placeholder="Search...">
                    <i class="fa fa-search"></i>
                </div>
                <?php  if(in_array($this->session->userdata('user_type'), $upperManagementTypes) || $this->session->userdata['user_type'] =='manager' || $this->session->userdata['user_type'] =='team_leader'){?>
                <a href="#user-edit-popup" id="" class="open-popup-link" style="display: none">click</i></a>                           
                <a href="#" id="edit-u"><i class="fa fa-edit list-edit-font"></i></a>                           
                <?php if(!$this->config->item('sso_is_enabled') && (in_array($this->session->userdata('user_type'), $upperManagementTypes) || $this->session->userdata['user_type'] =='team_leader')) { ?><a href="/users/create" class="add-icon"><i class="fa"></i></a><?php } ?>
                <?php }?>
            </div>
        </div>
        <div id="dvGqgrid" style="width: 100%" class="jqglabel">
            <table id="list" class="jqglabel">
            </table>
            <div id="pager" class="jqgrid-footer"></div>
        </div>
    </div>
</section>

<div id="user-edit-popup" class="user-edit-popup mfp-hide">
    <form method="post" role="form" id="user_bulk_update_form" class="user-bulk-update-form" name="form" autocomplete="off" action="/users/bulkupdate">
        <div class="user-edit-popup-border">
            <h3 class="user-edit-header form-section-title">Bulk Edit</h3>
            <div class="user-edit-form">
                <span class="move-selected-span">Move selected to...</span>

                <div class="dialog-form alignleft clear" id="tm_offices_section">
                    <label> <span class="alert-required">*</span>TM Office:</label>
                 
                    <div class="styled select-dropdown">
                        <select <?php if($this->session->userdata('user_type') == 'team_leader'){echo 'disabled'; } ?> name="telemarketing_offices" id="telemarketing_offices" required>
                            <option role="option" value=""> ---SELECT ONE---</option>
                            <?php
                            if (!empty($getEGWebsitesList)) {
                                foreach ($getEGWebsitesList as $key => $sitesList) {
                                    if($sitesList != 'All') {
                                        echo '<option value="' . $key . '" "">' . $sitesList . '</option>';
                                    }
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="dialog-form" id="teamleads_details">
                    <label><span class="alert-required">*</span>Team Leader:</label>

                    <div class="styled select-dropdown">
                        <select name="teamleads" id="teamleads" required="required">
                            <option role="option" value=""> ---SELECT ONE---</option>
                            ?>
                        </select>

                    </div>
                </div>
                
                <div class="user-btn-group">
                    <ul>
                        <li>
                            <button type="submit" tabindex="9" class="general-btn" id="btnSave">Save</button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <input type="hidden" name="user_ids" id="user_ids" value=""/>
    </form>
</div>



<!--Add/Edit Dialog Form-->
<div id="dialog-form" title="USER DETAILS" class="account-detail-dialog" style="display: none">
    <div class="alphabetic-search-area-horizontal"></div>
</div>
<script type="text/javascript">
    var logged_user_id = '<?php echo $this->session->userdata['uid'];?>';
    var logged_user_type = '<?php echo $this->session->userdata['user_type'];?>';
    var tm_offices = '<?php echo $tm_offices;?>';
    var usersdata = <?php echo $users;?>;
    var allUserTypes = '<?php echo $allUserTypes;?>';
    var upperManagementTypes = ["<?php echo implode('","', $upperManagementTypes); ?>"];
</script>
<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/pagejs/users/index.js<?=$this->cache_buster?>"></script>

