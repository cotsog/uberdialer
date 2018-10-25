<style type="text/css">
    .icons{
        float: left!important;
        margin-top: 1%;
        margin-left: 1%;
        display: block;
    }

    th {
        white-space: nowrap;
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
                <span class="column-title"><?=$title;?></span>
            </div>
            
        </div>
        <form method="post" name="search" id="search_form">
        <div class="icons">
                <div class="search-area listing">
                    <input id="globalSearchText" type="text" name='email' placeholder="Email..." <?php echo !empty($_POST['email']) ? "value='{$_POST['email']}'": ""; ?>>
                    <i class="fa fa-search"></i>
                </div>

        </div>
        <div class="icons">
                <div class="search-area listing">
                    <input id="globalSearchText" type="text" name='campaign_contact_id' placeholder="Campaign Contact ID..." <?php echo !empty($_POST['campaign_contact_id']) ? "value='{$_POST['campaign_contact_id']}'": ""; ?>>
                    <i class="fa fa-search"></i>
                </div>

        </div>
        <div class="icons">
                <div class="search-area listing">
                    <input id="globalSearchText" type="text" name='phone' placeholder="Phone..." <?php echo !empty($_POST['phone']) ? "value='{$_POST['phone']}'": ""; ?>>
                    <i class="fa fa-search"></i>
                </div>

        </div>
        <div class="dialog-form ">
            <button type="submit" class="general-btn" id="leads_btnSave">Search</button>
        </div>
        </form>
        <div>
        <?php if(!empty($call_history_records)){   ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                <?php 
                    foreach($call_history_records[0] as $header => $val){
                        if($header != 'id'){
                            echo "<th>{$header}</th>";
                        }
                    }
                ?>
                </tr>
            </thead>
            <?php 
                foreach($call_history_records as $ctr => $data){
                    echo "<tr>";
                    $id = $data['id'];
                    $sid = $data['sid'];
                    $confSid =  $data['conf_sid'];
                    foreach($data as $field => $record){
                        if($field != 'id'){
                            if($field == 'recording_url' && empty($data['recording_url']) && !empty($sid) && !empty($id)){
                                ?>
                                <td><a id="retrieve_<?php echo $record; ?>"  href="javascript:" onclick="retrieve_recording('<?php echo $sid; ?>','<?php echo $id; ?>','<?php echo $confSid; ?>')">Retrieve</a>
                                <div id="message_<?php echo $id; ?>"></div></td>
                                <?php
                            }else{
                                echo "<td>{$record}</td>";
                            }
                        }
                        
                    }
                    echo "</tr>";
                }
            ?>
        </table>
        <?php } ?>
        </div>
        <div id="dvGqgrid" style="width: 100%" class="jqglabel">
            <table id="list" class="jqglabel">
            </table>
            <div id="pager" class="jqgrid-footer"></div>
        </div>
    </div>
</section>

<!--Add/Edit Dialog Form-->
<div id="dialog-form" title="USER DETAILS" class="account-detail-dialog" style="display: none">
    <div class="alphabetic-search-area-horizontal"></div>
</div>

<script type="text/javascript">


function retrieve_recording(call_uuid, plivo_id, confSid){
    $.ajax({ url: "/utilities/retrieve_call_recording/"+call_uuid+"/"+plivo_id+"/"+confSid,
                data: {},
                dataType: "json",
                type: "POST",
                success: function(data)
                {
                    console.log(" == "+ call_uuid +" == "+ plivo_id);
                    if(data.recording)
                    {
                        $( "#search_form" ).submit();
                    }
                    else
                    {
                        $("#message_"+plivo_id).html('No recording found.');
                    }
                    
                },
                error: function(error){
                    console.log(error);
                },
                beforeSend:function()
                {
                    $("#message_"+plivo_id).html('<img src="https://s3.amazonaws.com/enterprise-guide/images/ajax-loader.gif" alt="Processing.." />');
                }
            });
}
</script>
