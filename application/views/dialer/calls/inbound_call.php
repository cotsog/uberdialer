<section class="section-content-main-area">
    <div class="content-main-area">
        <div id="ajax-content-container"></div>
        <div class="column-header query-list">
            <div class="alignleft">
                <span class="column-title">INBOUND CALLS</span>
            </div>
            
        </div>
        <div id="dvGqgrid" style="width: 100%" class="jqglabel">
            <table id="list" class="jqglabel">
            </table>
            <div id="pager" class="jqgrid-footer"></div>
        </div>
    </div>
    <div class="clearfix"></div>
</section>
<style>
    /* #gbox_list .ui-th-column{
        padding-left: 15px;
    } */
    #list tr.jqgrow td {
        padding-left: 15px;
    }
</style>
<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/pagejs/dialer/calls/inbound_call.js"></script>
