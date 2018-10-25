<style>
    .email {
        width: 300px !important;
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
            <div class="pad-15-t pad-15-l  call-row-title">
               <div class="column-header">
                    <p><?=$title;?></p>
                </div>
            </div>

            <div class="pad-15-t pad-15-lr ">
                <?php
                    $campaign_id = "";
                    if(empty($_POST['campaign_id']) && !empty($_REQUEST['campaign_id'])){
                        $campaign_id = $_REQUEST['campaign_id'];
                    }else if(!empty($_POST['campaign_id'])){
                        $campaign_id = $_POST['campaign_id'];
                    }
                    
                    $email = "";
                    if(empty($_POST['email']) && !empty($_REQUEST['email'])){
                        $email = $_REQUEST['email'];
                    }else if(!empty($_POST['email'])){
                        $email = $_POST['email'];
                    }

                ?>
                <div class="pad-15-l row-left-pad call-row-title">
                    <form method="post" name="search_form" id="search_form" class="search_form" style="float: left;">

                        <div class="dialog-form ">
                            <label style="width: 60px;"> Email:</label>

                            <div class="form-input">
                                <input id="globalSearchText" class ="email" type="text" name='email' placeholder="Email..." value="<?php echo $email; ?>">
                            </div>
                        </div>
                        <div class="dialog-form ">
                            <label style="width: 140px;"> Campaign ID:</label>

                            <div class="form-input">
                                <input id="globalSearchText" type="text" name='campaign_id' placeholder="Campaign ID..." value="<?php echo $campaign_id; ?>">
                            </div>
                        </div>
                        <input type="hidden" name="filter_status" id="filter_status" value="">

                        <div class="dialog-form ">
                            <button type="submit" class="general-btn" id="leads_btnSave">Search</button>
                        </div>
                    </form>
                </div>


                <table id="staffing_attrition_report" class="table table-bordered row vertical-tbl sort-th" style="width: 100%;table-layout: fixed;">
                    <thead>
                    <tr style="background: #f4f4f4;">
                        <th class="aligncenter">Full Name</th>
                        <th class="aligncenter">Email</th>
                        <th class="aligncenter">Contact Form</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    if (!empty($searchResult)) {
                       foreach($searchResult as $ctr => $data){
                            echo "<tr>";
                            $list_id = $data['list_id'];
                            $campaignContactId = $data['campaignContactId'];
                           // $campaign_id = $data['campaign_id'];
                            foreach($data as $field => $record){
                                if(!in_array($field, array('list_id','campaignContactId',))){
                                    echo "<td>{$record}</td>";
                                }
                            }
                            if(!empty($campaignContactId)){
                            ?>
                                <td><a  href="/dialer/calls/index/<?php echo $campaignContactId . '/' . $list_id ?>" target="_blank">Open</a>
                                </td>
                            <?php
                            }else{
                                echo "<td></td>";
                            }

                            echo "</tr>";
                        }  
                    } else { ?>
                        <tr>
                            <td colspan="3"><div style='padding:6px;font-size: 14px; background:#D8D8D8'>No record(s) found</div></td>
                        </tr>
                    <?php }?>
                    </tbody>
                </table>
                <br/><br/>
            </div>
        </div>
    <div class="clearfix"></div>
</section>