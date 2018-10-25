<style>
    .agents {
        float:left; margin-bottom:1%; margin-right: 4%;
        margin-left: 1%;
    }   
    .agent_label{
        font-size: 11px;
    }
    .agent_data{
        font-size: 20px;
        font-weight: bold;
    }
    .select-dropdown {
        margin-right: 5%;
    }
    #form_div {
        margin-bottom: 35px;
    }
    .select-dropdown select {
        width: 1800px!important;
        max-width: 180px!important;
    }
    .general-btn, #team_leader_error {
        display: none;
    }
</style>
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
        <div class="pad-15-b">
            <div class="pad-15-t pad-15-l  call-row-title">
               <div class="column-header">
                    <p>Real-time Monitoring</p>
                </div>
            </div>
            <?php if(in_array($this->session->userdata('user_type'), $upperManagement) || $this->session->userdata('user_type') == 'manager') { ?>
            <div class="pad-15-t pad-15-l row-left-pad call-row-title" id="form_div">
                <form method="post" name="monitoring_report" id="monitoring_report">
                    <?php if(in_array($this->session->userdata('user_type'), $upperManagement)) { ?>
                    <div class="select-dropdown styled" style="float: left;margin-top: 1.5%">
                        TM Site:
                        <select id="tm_site_selector" name="tm_site" onclick="select_tm_site($(this).val())">
                            <option value="">Select a TM Site</option>
                            <?php foreach($tm_offices as $tm_office) { 
                                     $selected = ( $selected_site == $tm_office['office'] ? 'selected="selected"' : '');
                            ?>
                            <option value="<?=$tm_office['office']?>" <?=$selected?>><?=$tm_office['office']?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="select-dropdown styled" style="float: left;">
                        Managers:
                        <select multiple="true" id="tm_manager_selector" name="tm_managers[]"></select>
                    </div>
                    <?php } ?>
                    <div class="select-dropdown styled" style="float: left;">
                        Team Leaders:
                        <select multiple="true" id="tm_team_leader_selector" name="tm_team_leaders[]">
                            <?php 
                            foreach( $tm_team_leaders as $tm_team_leader ){ 
                                $selected = ( in_array( $tm_team_leader->id, $selected_team_leaders ) ? 'selected="selected"' : '');
                            ?>
                                <option value="<?=$tm_team_leader->id?>" <?=$selected?>><?=$tm_team_leader->name?></option>        
                            <?php }?>
                        </select>
                        <span id="team_leader_error" class="error">You must select at least 1 Team Leader</span>
                    </div>
                    <div class="select-dropdown styled" style="float: left;">
                        <button type="submit" class="general-btn" id="filtering_submit">Filter</button>
                        <?php if ($user_type != 'manager') { ?>
                            <button class="general-btn" id="clear_form">Clear</button>
                        <?php } ?>
                    </div>
                </form>
            </div>
            <?php } ?>
            <div class="pad-15-t pad-15-l row-left-pad call-row-title">
                <?php
                $html = "";
                $incall = 0;
                $idle = 0;
                $total = 0;
                $officeList = !empty($selected_site) ? array($selected_site) : array();
                if($this->session->userdata('user_type') == 'manager' && empty($officeList)){
                    if(!empty($subOffices)){
                        $officeList = $this->session->userdata('sub_telemarketing_offices');
                    }else{
                        $officeList = array($this->session->userdata('telemarketing_offices'));
                    }
                }else if($this->session->userdata('user_type') == 'team_leader' && empty($officeList)){
                    $officeList = array($this->session->userdata('telemarketing_offices'));
                }
                $isOfficeForConference = count(array_intersect($officeList, $this->isConferenceOffices)) > 0;
                if(!empty($agents_calls_today)){
                    foreach ($agents_calls_today as $call_detail){
                        $separate_data = array();
                        if(!empty($call_detail->call_details)){
                            $separate_data = explode("|", $call_detail->call_details);
                        }
                        $html .= "<tr style='" . $call_detail->bg_color . "'>";
                        if(in_array($user_type, $upperManagement)){ $html .= "<td>{$call_detail->office}</td>";}
                        if($call_detail->agent_state =='Incall'){
                            $incall++;
                        } elseif($call_detail->agent_state =='Idle'){
                            $idle++;
                        }
                        $total++;
                        $html .="<td>{$call_detail->agent}";
                        if($call_detail->agent_state=="Incall"){                        
                            if($this->isConference && (empty($this->isConferenceOffices) || $isOfficeForConference)){
                                $html .="&nbsp;&nbsp;<a target='_blank' href='/dialer/calls/tap/{$call_detail->call_id}'>Listen</a>";
                            }
                        }
                        $html .= "</td>";
                        $html .="<td>{$call_detail->agent_state}</td>";
                        $html .="<td>{$call_detail->latest_call_duration}</td>";
                        $html .="<td>{$call_detail->campaign_id}</td>";
                        $html .="<td>{$call_detail->campaign_name}</td>";
                        $html .="<td>{$call_detail->count_calls_today}</td>";
                        $html .="<tr>";
                    }
                }
                $agents_time_in = '0';
                $campaign_sign_in = $total >0 ? $total:'0';
                $agents_in_call = $incall>0 ?$incall:'0';
                $agents_in_idle = $idle>0 ? $idle :'0';
                if(!empty($agents_counts)){
                    $agents_time_in = $agents_counts['agent_time_in'];
                    if($agents_in_call >0){
                        $agents_in_idle = $agents_time_in - $agents_in_call;
                    }
                } ?>
                <div class="agents"><span class="agent_data"><?php echo $agents_time_in; ?></span> <span class="agent_label">Agents Time In</span></div>
                <div class="agents"><span class="agent_data"><?php echo $campaign_sign_in; ?></span> <span class="agent_label">Agents Campaign Sign In</span></div>
                <div class="agents"><span class="agent_data"><?php echo $agents_in_call; ?></span> <span class="agent_label">Agents In Calls</span></div>
                <div class="agents"><span class="agent_data"><?php echo $agents_in_idle; ?></span> <span class="agent_label">Agents In Idle</span></div>
            </div>
            <div class="pad-15-t pad-15-lr ">
                <table id="call_file_analysis_report" class="call_file_analysis_report  table table-bordered row vertical-tbl sort-th" style="width: 100%;">
                    <thead>
                        <tr style="background: #f4f4f4;">
                            <?php if(in_array($user_type, $upperManagement)){?><th class="blue_label">Office </th><?php } ?>
                            <th class="blue_label">Agent Name</th>
                            <th class="blue_label">Statuses</th>
                            <th class="blue_label">MM:SS</th>
                            <th class="blue_label">Campaign ID</th>
                            <th class="blue_label">Campaign Name</th>
                            <th class="blue_label">Total number of Calls</th>
                        </tr>
                    </thead>
                    <tbody id="results_body">
                    <?php
                    if(!empty($agents_calls_today)){
                            echo $html;
                    } ?>
                    </tbody>
                </table>    
            </div>
            
        </div>
        <br/><br/>        
    </div>
    <div class="clearfix"></div>
</section>
<script type="text/javascript">
clear = false;
$(document).ready(function() {
    var selectedSite = '<?php if(isset($selected_site) && $selected_site != '') { echo $selected_site; } ?>';
    <?php if(!isset($tm_team_leaders)) { ?>
    select_tm_site(selectedSite);
    <?php } ?>
    var tmTeamLeaders = '<?=(isset($tm_team_leaders) ? 'true' : '')?>';
    if(tmTeamLeaders != '') {
        $('.general-btn').show();
    }
});
<?php
if(!isset($tm_team_leaders)) {  ?>
function select_tm_site(siteSelected) {
    if(siteSelected != '') {
        $.ajax({url: "get_mgr_tl",
            data: {site_name: siteSelected},
            dataType: "JSON",
            type: "POST"
        })
        .done(function(data) {
            $('#tm_manager_selector').html(data.manager);
            $('#tm_team_leader_selector').html(data.team_leader);
            if(clear == true){
                clear = false;
                $('#tm_manager_selector option').prop('selected', true);
                $('#tm_team_leader_selector option').prop('selected', true);
            }
            if(data.team_leader != '') {
                $('.general-btn').show();
            } else {
                $('.general-btn').hide();
            }
        });
    } else {
        $('.general-btn').hide();
        $('#tm_manager_selector').html('');
        $('#tm_team_leader_selector').html('');
    }
}
<?php } else { ?>
$('#filtering_submit').show();
<?php } ?>
$('#clear_form').click(function(ev) {
    clear = true;
    ev.preventDefault();
    $('#team_leader_error').hide();
    $('#tm_site_selector').val('');
    $('#tm_manager_selector').html('');
    $('#tm_team_leader_selector').html('');
});
$('#filtering_submit').click(function(ev) {
    ev.preventDefault();
    if($('#tm_team_leader_selector').val() == null) {
        $('#results_body').html('');
        $('#team_leader_error').show();
    } else {
        $('#team_leader_error').hide();
        $('#monitoring_report').submit();
    }
});
$("#dialer_dashboard_item").addClass("active open");
$("#dialer_real_time").addClass("active");
</script>