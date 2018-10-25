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
                    <p>QA Production Summary</p>
                </div>
            </div>
            <div class="pad-15-t pad-15-l row-left-pad call-row-title">
                <form method="post" name="leadstatus_searchform" id="leadstatus_searchform" class="dashboard_filter">
                    <div class="dialog-form ">
                        <label style="width: 70px;">Date:</label>
                        <div class="form-input date-picker">
                            <input type="text" id="from_date" name="from_date" placeholder="From date" readonly maxlength="10"
                                   value="<?php echo $this->input->post('from_date'); ?>"/>
                        </div>
                    </div>
                    <input type="hidden" name="filter_status" id="filter_status" value="">
                    <div class="dialog-form ">
                        <button type="submit" class="general-btn" id="leads_btnSave">Filter</button>
                    </div>
                </form>
            </div>
            <?php  if(!empty($Qa)){?>
            <div class="pad-15-t pad-15-lr ">    
             <?php ob_start(); ?>
                <table border="1px" id="call_file_analysis_report" class="call_file_analysis_report  table table-bordered row vertical-tbl sort-th"
                   style="width: 100%;">
                    <thead>
                        <tr class='text_display_area' align='center' style="background: #f4f4f4;">
                            <th class="blue_label"> </th>
                            <?php $thcount = count($Qa);?>
                            <?php foreach($Qa  as $Qarow){?> 
                            <th style="text-align:left;" class="blue_label break-word"><?=$Qarow;?></th>
                            <?php }?>
                            <th class="blue_label break-word">QA Team Total</th>
                        </tr>
                    </thead>
                    <tbody>  
                        <?php  
                            foreach($QA_Leads_Data as $key=> $leadValue)
                            {
                                echo "<tr class='text_display_area' align='center'>";
                                echo "<th style='text-align:left;' class='text_align_left break-word'>".$key."</th>";
                                foreach($Qa  as $Qarow){ 
                                    echo "<td>".$leadValue[$Qarow]."</td>";
                                }
                                echo "<td>".$leadValue['QA_Team_Total']."</td>";
                                echo "</tr>";
                        }?>
                        <tr class='text_display_area'>
                            <th style="text-align:left;" colspan="<?php echo ($thcount+1)?>" class="text_align_left break-word">Total Processed Leads</th>
                            <th><?=$totalLeads;?></th>
                        </tr>
                        <tr><td colspan="<?php echo ($thcount+2)?>">&nbsp;</td></tr>
                        <tr>
                            <th style="text-align:left;" colspan="<?php echo ($thcount+1)?>" class="text_align_left break-word">Campaign wise Processed Leads</th>
                            <th>Total</th>
                        </tr>
                        <?php  
                        $campaign_Total = 0;
                        $typeFlage=0;
                        $type="";
                        $type_old="";
                        foreach($Campaign_Data as $key=> $campaignValue)
                        {    
                            if(empty($typeFlage)){     
                               $typeFlage = 1;
                                $type =  $campaignValue['type'];                            
                            }else{
                        
                            }
                            if($type == $campaignValue['type'] && $typeFlage =1 & $type_old !=$type){
                                echo "<tr class='text_display_area' align='center'>";
                                echo "<td style='text-align:left !important;' colspan=".($thcount+2)." class='campaign_type_format_position'>".ucwords($type)."</td>";
                                echo "</tr>"; 
                                $type_old=$campaignValue['type'];
                                $typeFlage=0;
                            } 
                            echo "<tr class='text_display_area' align='center'>";
                            echo "<th style='text-align:left; class='text_align_left break-word'>".$key."</th>";
                            foreach($Qa  as $Qarow){ 
                              $totalLeadByQA[$Qarow] = array_reduce($Campaign_Data,
                                function($runningTotal, $record) use($Qarow) {
                                 if(isset($record[$Qarow]))
                                    $runningTotal += $record[$Qarow];

                                 return $runningTotal;
                                }, 0);
                               
                              if(isset($campaignValue[$Qarow])){                                  
                                  echo "<td>".$campaignValue[$Qarow]."</td>";
                                  $campaign_Total += $campaignValue[$Qarow];
                              }else{
                                  echo "<td>0</td>";
                                }                             
                            }
                            echo "<td>".$campaign_Total."</td>";                            
                            echo "</tr>";
                            $campaign_Total =0;
                            }?>
                        <tr class='text_display_area' align='center'>
                            <th style='text-align:left !important;' class='text_align_left blue_label break-word'>Total Leads:</th>
                            <?php foreach($Qa  as $Qarow){ 
                                if(isset($totalLeadByQA[$Qarow])){
                                    echo "<th class='blue_label'>".$totalLeadByQA[$Qarow]."</th>"; 
                                }  
                                else{
                                    echo "<th class='blue_label'>0</th>"; 
                                }
                            }?>
                            <th class='blue_label'><?=$totalLeads;?></th>
                        </tr>
                    </tbody>
                </table>
                <?php                
                    $HtmlCode = ob_get_contents();                    
                    ob_end_flush();                  
                ?>
                <form action="/dialer/reports/export_qa" method="post"  onsubmit='redirect();return false;'>
                    <input type="hidden" name="from_date" value="<?php echo $this->input->post('from_date'); ?>"/>
                    <input type="hidden" name="data" value="<?=htmlspecialchars($HtmlCode);?>" /> 
                    <input style="top: 120px;position: absolute;right: 46px;" type="image" name="submit" src="/images/file-extension-xls-biff-icon.png" width="32" height="32" alt="Submit"/>
                </form>                   
            </div>
            <?php }else{?>
            <div class="pad-15-t pad-15-l row-left-pad call-row-title pad-15-b" style="margin-top: 10px;">
                <div class="dashboard_no_records">No record(s) found</div>
            </div>
            <?php }?>
        </div>
    </div>
    <div class="clearfix"></div>
</section>
<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/pagejs/dialer/reports/qa_product_summary.js<?=$this->cache_buster?>"></script>
