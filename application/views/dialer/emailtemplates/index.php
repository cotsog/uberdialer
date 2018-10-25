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
        <div id="divErrorMsg" class="error-msg good hiddendiv" style="display:none;" >
            <p><span><i class="fa fa-times-circle"></i></span> Template(s) deleted successfully!
        </div>
        <div id="ajax-content-container"></div>
        <div class="column-header query-list">
            <div class="alignleft">
                <span class="column-title">MANAGE TEMPLATES</span>
            </div>
            <div class="icons">
                <div class="search-area listing">
                    <input id="globalSearchText" type="text" placeholder="Search...">
                    <i class="fa fa-search"></i>
                </div>
                <a href="#" id="del_c"><i class="fa fa-trash-o list-trash-font"></i></a>
                <a href="/dialer/emailtemplates/create" class="add-icon"><i class="fa"></i></a>
            </div>
        </div>
        <div id="dvGqgrid" style="width: 100%" class="jqglabel">
            <table id="list" class="jqglabel">
            </table>
            <div id="pager" class="jqgrid-footer"></div>
        </div>
    </div>
</section>

<!--Add/Edit Dialog Form-->
<div id="dialog-form" title="Email Tempate" class="account-detail-dialog" style="display: none">
    <div class="alphabetic-search-area-horizontal"></div>
</div>
<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/pagejs/dialer/emailtemplates/index.js"></script>
<script type="text/javascript">
    var logged_user_id = '<?php echo $this->session->userdata['uid'];?>';
</script>