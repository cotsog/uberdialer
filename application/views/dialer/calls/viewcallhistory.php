<section class="section-content-main-area">

    <div class="content-main-area">

        <div class="pad-15-b" >
            <div class="pad-15-t pad-15-l  call-row-title">
                <div class="column-header">
                    <p>View Call History</p>
                </div>
            </div>

            <div class="pad-15-t pad-15-lr ">
                <table class="table table-bordered row vertical-tbl sort-th" style="width: 100%;table-layout: fixed;">
                    <thead>
                        <tr>
                            <th class="aligncenter">Last call made</th>
                            <th class="aligncenter">campaign</th>
                            <th class="aligncenter">Result/ Status</th>
                            <th class="aligncenter">Agent</th>
                            <th class="aligncenter">Rec. link</th>
                            <th class="aligncenter">Sec</th>
                            <th class="aligncenter">TM Brand</th>
                            <th class="aligncenter">Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($view_all_call_history)) {
                             foreach ($view_all_call_history as $key => $contactCallHistory) {

                                echo '<tr>';
                                $content_notes = limit_words($contactCallHistory->notes, 10);
                                ?>
                                <td class="aligncenter"><?php echo php_datetimeformat($contactCallHistory->created_at); ?></td>
                                <td class="aligncenter"><?php echo $contactCallHistory->campaignName; ?></td>
                                <td class="aligncenter"><?php echo $contactCallHistory->result_Status; ?></td>
                                <td class="aligncenter"><?php echo $contactCallHistory->agent_tl_first_name; ?></td>
                                <td id="rec_link_<?php echo $contactCallHistory->plivo_id; ?>">
                                    <?php if(!empty($contactCallHistory->recording_url)){ ?>
                                        <a href="<?php echo $contactCallHistory->recording_url; ?>" target="_blank">Rec</a>
                                    <?php }else{ ?>
                                        <a id="retrieve_<?php echo $contactCallHistory->plivo_id; ?>"  href="javascript:" onclick="retrieve_recording('<?php echo $contactCallHistory->call_uuid; ?>','<?php echo $contactCallHistory->plivo_id; ?>')" style="color: #b10a11">Retrieve</a>
                                        <div id="message_<?php echo $contactCallHistory->plivo_id; ?>"></div>
                                    <?php } ?>
                                </td>
                                <td class="aligncenter"><?php echo $contactCallHistory->duration; ?></td>
                                <td class="aligncenter"><?php echo $contactCallHistory->site_name; ?></td>
                                <td class="break-all-word"><p><?php echo $content_notes['start']; ?></p>
                                    <?php if ($content_notes['end'] != ""): ?>
                                        <a href="javascript:void(0)" id="example<?php echo $key; ?>-show"
                                           class="showLink"
                                           onclick="showHide('example<?php echo $key; ?>');return false;">See
                                            more.</a>
                                        <div id="example<?php echo $key; ?>" class="more">
                                            <p><?php echo $content_notes['end']; ?><?php /*echo substr($contactCallHistory->notes,11); */ ?></p>

                                            <p><a href="javascript:void(0)" id="example-hide" class="hideLink"
                                                  onclick="showHide('example<?php echo $key; ?>');return false;">Hide
                                                    this content.</a></p>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <?php
                                echo '</tr>';
                            }
                    } ?>

                    </tbody>
                </table><br/><br/>

            </div>
        </div>
    </div>
<div class="clearfix"></div>
</section>

<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/pagejs/dialer/calls/viewallcallhistory.js<?=$this->cache_buster?>"></script>
<style>
   td {
    word-break: break-word;
    word-wrap: break-word;
}
</style>