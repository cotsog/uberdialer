l<aside class="left-panel">
    <?php $session_module = $this->session->userdata('module');
        if(in_array('tm',$session_module) || $this->session->userdata('user_type') == 'admin' ) {?>
   <p class="dialer dashboard has-submenu" id="dialer"><i class="fa fa-phone"></i><span class="nav-label">TELEMARKETING</span></p>

            <!--<span class="dialer">Dialer</span>-->
            <nav class="navigation" id="dialer_nav" style="display: none;"> 
        <?php 
        $managerUpperManagement = array_merge($this->config->item('upper_management_types'), array('manager'));
        if(in_array($this->session->userdata('user_type'), $managerUpperManagement)) { ?>
<!--            <a href="/dialer/dashboards/">
                <p class="dashboard has-submenu" id="dashboard" style="padding: 15px 0 15px 32px;"><i class="fa fa-fw fa-dashboard"></i><span class="nav-label dashboard-margin"  >DASHBOARD</span></p>
            </a>-->
            <ul class="list-unstyled accordian-nav">
                <!--<li class="active">
                    <a href="/dialer/dashboards/">
                        <p class="has-submenu" id="dashboard"><span class="nav-label">DASHBOARD</span></p>
                    </a>
                </li>-->
                <li class="has-submenu" id="dialer_dashboard_item">
                    <a href="javascript:void(0)">
                        <p class="reporting"></p>
                        <span class="nav-label">
                            <span>DASHBOARD</span>
                        </span>
                    </a>
                    <ul class=" list-unstyled-small ">
                        <li id="dialer_dashboard"><a href="/dialer/dashboards"><i class="fa fa-caret-right"></i>Dashboard</a></li>
                        <li id="dialer_real_time"><a href="/dialer/dashboards/realtimemonitoring"><i class="fa fa-caret-right"></i>Real-time Monitoring</a></li>
                    </ul>
                </li>
                <li class="has-submenu" id="campaign_item">
                    <a href="javascript:void(0)">
                        <p class="reporting"></p>
                        <span class="nav-label">
                            <span>CAMPAIGNS</span>
                        </span>
                    </a>
                    <ul class=" list-unstyled-small ">
                        <li id="campaign_lists"><a href="/dialer/campaigns"><i class="fa fa-caret-right"></i>Campaigns</a></li>
                        <li id="campaign_assign"><a href="/dialer/assigncampaigns/"><i class="fa fa-caret-right"></i>Assign Campaign</a></li>
                        <li id="lists"><a href="/dialer/campaigns/createcontacts"><i class="fa fa-caret-right"></i>Add Contacts/Lists</a></li>
                    </ul>
                </li>
                <?php if(in_array($this->session->userdata('user_type'), array('admin','manager'))){ ?>
                <li class="has-submenu " id="template_lists">
                    <a href="javascript:void(0)">
                        <p class="reporting admin"></p>
                        <span class="nav-label">
                            <span>Email Templates</span>
                        </span>
                    </a>
                    <ul class=" list-unstyled-small">
                        <li id="templates"><a href="/dialer/emailtemplates/"><i class="fa fa-caret-right"></i>Manage Templates</a></li>
                        <li id="template_create"><a href="/dialer/emailtemplates/create"><i class="fa fa-caret-right"></i>Create New</a></li>
                    </ul>
                </li>
                <?php } ?>
                <li class="has-submenu" id="qa_item">
                    <a href="javascript:void(0)">
                        <p class="reporting"></p>
                        <span class="nav-label">
                            <span>QA</span>
                        </span>
                    </a>
                    <ul class=" list-unstyled-small">
                        <li id="leads"><a href="/dialer/leads"><i class="fa fa-caret-right"></i>Lead Status</a></li>
                        <li id="call_history"><a href="/dialer/calls/call_history"><i class="fa fa-caret-right"></i>Call History List</a></li>
                    </ul>
                </li>
                <li class="has-submenu " id="report_item">
                    <a href="javascript:void(0)">
                        <p class="reporting"></p>
                        <span class="nav-label">
                            <span>Reports</span>
                        </span>
                    </a>
                    <ul class="list-unstyled-small" style="margin: 0 -12px;">
                        <li id="rejected_lead_summary"><a href="/dialer/reports/rejected_lead_summary"><i class="fa fa-caret-right"></i>Rejected Lead Summary</a></li>
                        <li id="agent_status"><a href="/dialer/reports/agent_status"><i class="fa fa-caret-right"></i>Agent Status</a></li>
                        <li id="qa_summary"><a href="/dialer/reports/qa_product_summary"><i class="fa fa-caret-right"></i>QA Production Summary</a></li>
                        <li id="disposition_history"><a href="/dialer/reports/disposition_report"><i class="fa fa-caret-right"></i>Disposition History</a></li>
                        <li id="realtime_monitoring_report"><a href="/dialer/reports/realtime_monitoring_report"><i class="fa fa-caret-right"></i>Real-time Monitoring</a></li>
                        <li id="call_file_status"><a href="/dialer/reports/call_file_status"><i class="fa fa-caret-right"></i>Call File Status</a></li>
                        <?php if($this->session->userdata('user_type') == 'admin'){ ?><li id="export_logs"><a href="/dialer/reports/export_logs"><i class="fa fa-caret-right"></i>Export Logs</a></li><?php } ?>
                    </ul>
                </li>
            </ul>
        <?php }
        else if($this->session->userdata('user_type') == 'team_leader'){?>
<!--            <a href="/dialer/dashboards/">
                <p class="dashboard has-submenu" id="dashboard"><span class="nav-label dashboard-margin" >DASHBOARD</span></p>
            </a>-->
            <ul class="list-unstyled accordian-nav">
                <!--<li>
                    <a href="/dialer/dashboards/">
                        <p class="has-submenu" id="dashboard"><span class="nav-label">DASHBOARD</span></p>
                    </a>
                </li>-->
                <li class="has-submenu" id="dialer_dashboard_item">
                    <a href="javascript:void(0)">
                        <p class="reporting"></p>
                        <span class="nav-label">
                            <span>DASHBOARD</span>
                        </span>
                    </a>
                    <ul class=" list-unstyled-small ">
                        <li id="dialer_dashboard"><a href="/dialer/dashboards"><i class="fa fa-caret-right"></i>Dashboard</a></li>
                        <li id="dialer_real_time"><a href="/dialer/dashboards/realtimemonitoring"><i class="fa fa-caret-right"></i>Real-time Monitoring</a></li>
                    </ul>
                </li>
                <li class="has-submenu" id="campaign_item">
                    <a href="javascript:void(0)">
                        <p class="reporting"></p>
                        <span class="nav-label">
                            <span>CAMPAIGNS</span>
                        </span>
                    </a>
                    <ul class=" list-unstyled-small ">
                        <li id="campaign_lists"><a href="/dialer/campaigns"><i class="fa fa-caret-right"></i>Campaigns</a></li>
                        <li id="campaign_assign"><a href="/dialer/assigncampaigns/"><i class="fa fa-caret-right"></i>Assign Campaign</a></li>
                        <li id="lists"><a href="/dialer/campaigns/createcontacts"><i class="fa fa-caret-right"></i>Add Contacts/Lists</a></li>
                    </ul>
                </li>
                <!--<li class="has-submenu " id="user_lists">
                    <a href="javascript:void(0)">
                        <p class="reporting admin"></p>
                        <span class="nav-label">
                            <span>Users</span>
                        </span>
                    </a>
                    <ul class=" list-unstyled-small">
                        <li id="user"><a href="/dialer/users/"><i class="fa fa-caret-right"></i>Manage Users</a></li>
                        <li id="changePass"><a href="/dialer/users/changepassword"><i class="fa fa-caret-right"></i>Change Password</a>
                        </li>
                    </ul>
                </li>-->
                <li class="has-submenu" id="qa_item">
                    <a href="javascript:void(0)">
                        <p class="reporting"></p>
                        <span class="nav-label">
                            <span>QA</span>
                        </span>
                    </a>
                    <ul class=" list-unstyled-small">
                        <li id="leads"><a href="/dialer/leads"><i class="fa fa-caret-right"></i>Lead Status</a></li>
                        <li id="call_history"><a href="/dialer/calls/call_history"><i class="fa fa-caret-right"></i>Call History List</a></li>
                    </ul>
                </li>
                <li class="has-submenu " id="report_item">
                    <a href="javascript:void(0)">
                        <p class="reporting"></p>
                        <span class="nav-label">
                            <span>Reports</span>
                        </span>
                    </a>
                    <ul class="list-unstyled-small" style="margin: 0 -12px;">
                        <li id="rejected_lead_summary"><a href="/dialer/reports/rejected_lead_summary"><i class="fa fa-caret-right"></i>Rejected Lead Summary</a></li>
                        <li id="agent_status"><a href="/dialer/reports/agent_status"><i class="fa fa-caret-right"></i>Agent Status</a></li>
                        <li id="qa_summary"><a href="/dialer/reports/qa_product_summary"><i class="fa fa-caret-right"></i>QA Production Summary</a></li>
                        <li id="disposition_history"><a href="/dialer/reports/disposition_report"><i class="fa fa-caret-right"></i>Disposition History</a></li>
                        <li id="realtime_monitoring_report"><a href="/dialer/reports/realtime_monitoring_report"><i class="fa fa-caret-right"></i>Real-time Monitoring</a></li>
                        <li id="call_file_status"><a href="/dialer/reports/call_file_status"><i class="fa fa-caret-right"></i>Call File Status</a></li>
                        <li id="upload_summay"><a href="/dialer/reports/upload_summary_report"><i class="fa fa-caret-right"></i>Upload Summary</a></li>
                    </ul>
                </li>
            </ul>
        <?php }
        else if($this->session->userdata('user_type') == 'agent'){?>
           <ul class="list-unstyled accordian-nav">          
                <!--<li class="has-submenu no-content" id="dialer_dashboard_item">
                <a href="/dialer/dashboards/">
                    <p class="dashboard" id="dialer_agent_dashboard"><span class="nav-label dashboard-margin" >DASHBOARD</span></p>
                </a>
            </li>-->
                <li class="has-submenu no-content" id="dialer_dashboard_item">
                    <a href="/dialer/dashboards/">
                        <p class="reporting"></p>
                        <span class="nav-label">
                            <span>DASHBOARD</span>
                        </span>
            </a>
                </li>
                <li class="has-submenu no-content" id="campaign_item">
                    <a href="/dialer/campaigns">
                        <p class="reporting"></p>
                        <span class="nav-label">
                            <span>CAMPAIGNS</span>
                        </span>
                    </a>
                </li>
                <li class="has-submenu no-content" id="user_lists">
                    <a href="/users/changepassword">
                        <p class="reporting admin"></p>
                        <span class="nav-label">
                            <span>Change Password</span>
                        </span>
                    </a>
                </li>
                <li class="has-submenu no-content" id="qa_item">
                    <a href="/dialer/leads/">
                        <p class="reporting"></p>
                        <span class="nav-label">
                            <span>QA</span>
                        </span>
                    </a>
                </li>
                <li class="has-submenu no-content" id="report_item">
                    <a href="/dialer/reports/agent_status">
                        <p class="reporting"></p>
                        <span class="nav-label">
                            <span>Agent Status</span>
                        </span>
                    </a>
                </li>
            </ul>
        <?php }
        else if($this->session->userdata('user_type') == 'dataresearch_user'){ ?>

            <ul class="list-unstyled accordian-nav">
                <li class="has-submenu no-content" id="user_lists">
                    <a href="/users/changepassword">
                        <p class="reporting admin"></p>
                        <span class="nav-label">
                            <span>Change Password</span>
                        </span>
                    </a>
                </li>
                <li class="has-submenu no-content" id="data_research_team">
                    <a href="/dialer/datateam">
                        <p class="reporting admin"></p>
                        <span class="nav-label">
                            <span>Data Research</span>
                        </span>
                    </a>
                </li>
            </ul>
        <?php }
        else if($this->session->userdata('user_type') == 'qa'){?>
            <ul class="list-unstyled accordian-nav">
                <li class="has-submenu no-content" id="campaign_item campaign_lists">
                    <a href="/dialer/campaigns">
                        <p class="reporting"></p>
                        <span class="nav-label">
                            <span>CAMPAIGNS</span>
                        </span>
                    </a>
                </li>
                <li class="has-submenu no-content" id="user_lists changePass">
                    <a href="/users/changepassword">
                        <p class="reporting admin"></p>
                        <span class="nav-label">
                            <span>Change Password</span>
                        </span>
                    </a>
                </li>
                <li class="has-submenu" id="qa_item">
                    <a href="javascript:void(0)">
                        <p class="reporting"></p>
                        <span class="nav-label">
                            <span>QA</span>
                        </span>
                    </a>
                    <ul class=" list-unstyled-small">
                        <li id="leads"><a href="/dialer/leads"><i class="fa fa-caret-right"></i>Lead Status</a></li>
                        <li id="call_history"><a href="/dialer/calls/call_history"><i class="fa fa-caret-right"></i>Call History List</a></li>
                    </ul>
                </li>
                <li class="has-submenu " id="report_item">
                    <a href="javascript:void(0)">
                        <p class="reporting"></p>
                        <span class="nav-label">
                            <span>Reports</span>
                        </span>
                    </a>
                    <ul class="list-unstyled-small" style="margin: 0 -12px;">
                        <li id="qa_escalation"><a href="/dialer/reports/qa_escalation"><i class="fa fa-caret-right"></i>QA Escalation</a></li>
                        <li id="rejected_lead_summary"><a href="/dialer/reports/rejected_lead_summary"><i class="fa fa-caret-right"></i>Rejected Lead Summary</a></li>
                        <li id="qa_summary"><a href="/dialer/reports/qa_product_summary/"><i class="fa fa-caret-right"></i>QA Production Summary</a></li>
                    </ul>
                </li>
            </ul>
        <?php }?>
    </nav>
   <?php } ?>       

    <?php  if($this->session->userdata('user_type') == 'admin'){ ?>
        <p class="dialer dashboard has-submenu" id="data_research_team">
            <a href="/dialer/datateam" class="menu-data-research"><i class="fa fa-search"></i><span class="nav-label">Data Research</span></a>
        </p>
    <?php } ?>
    <?php  

        $managerTLUpperManagement = array_merge($this->config->item('upper_management_types'), array('manager','team_leader'));
        if(in_array($this->session->userdata('user_type'), $managerTLUpperManagement)) { ?>
        <p class="dialer dashboard has-submenu" id="user_lists">
            <i class="fa fa-users"></i><span class="nav-label">User Management</span>
        </p>

        <nav class="navigation" id="user_nav"  style="display: none;">
            <ul class="list-unstyled accordian-nav">
                <li id="user"><a href="/users/">Manage Users</a></li>
                <?php if(!$this->config->item('sso_is_enabled') && $this->session->userdata('user_type') == 'admin') { ?><li id="user_create"><a href="/users/create">Create New</a></li><?php } ?>
                <?php if(in_array($this->session->userdata('user_type'), $managerUpperManagement)) { ?>
                    <li id="bulkupdate"><a href="/users/bulkupdate">Bulk Edit</a></li>
                <?php } ?>
                <li id="changePass"><a href="/users/changepassword">Change Password</a></li>
            </ul>
       </nav>
    <?php } ?>
    <?php
        $nonAgentTypes = array_merge($this->config->item('upper_management_types'), array('manager','team_leader','qa'));
        if(in_array($this->session->userdata('user_type'), $nonAgentTypes)){ ?>
        <p class="dialer dashboard has-submenu" id="utilities_lists">
            <i class="fa fa-users"></i><span class="nav-label">Utilities</span>
        </p>
        <nav class="navigation" id="utilities_nav"  style="display: none;"><ul class="list-unstyled accordian-nav">
        <?php  if(in_array($this->session->userdata('user_type'), array('admin'))){ ?>
            <li id="user"><a href="/utilities/sites">Manage Sites</a></li>
        <?php }
        if(in_array($this->session->userdata('user_type'), $nonAgentTypes)){ ?>
                <li id="user"><a href="/utilities/retrieve_recording">Retrieve Recording</a></li>
        <?php } 
        if(in_array($this->session->userdata('user_type'), array_merge($nonAgentTypes, array('agent')))){ ?>
                <li id="user"><a href="/utilities/emailChangeLookup">Email has changed lookup</a></li>
        <?php } ?></ul></nav>
    <?php } ?>
</aside>
