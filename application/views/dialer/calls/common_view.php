<!-- Call History Email History-->

				<div class="pad-15-t pad-15-l row-left-pad">
				<div id="history-tab-container" class='tab-container'>
                        <ul class='etabs'>
                            <li class='tab'><a href="#call_history_tab">Call History</a></li>
                            <li class='tab'><a href="#email_history_tab">Email History</a></li>
                        </ul>
                        
                            <div id="call_history_tab">
                            <?php if (!empty($contactCallHistoryList)) { ?>
                            <table id="call-history-tbl" class="table table-bordered row call-history-tbl mar-b-0">
                        <thead>
                            <tr>
                                <th>Last call made</th>
                                <th>campaign</th>
                                <th>Result/ Status</th>
                                <th>Agent</th>
                                <th>Rec. link</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                                <?php foreach ($contactCallHistoryList as $key => $contactCallHistory) {
                                if ($key < 5) {
                                    echo '<tr>';
                                $content_notes = limit_words($contactCallHistory->notes, 10);
                                ?>
                                <td><?php echo php_datetimeformat($contactCallHistory->created_at); ?></td>
                                <td><?php echo $contactCallHistory->campaignName; ?></td>
                                <td><?php echo $contactCallHistory->result_Status; ?></td>
                                <td><?php echo $contactCallHistory->agent_tl_first_name; ?></td>
                                <td>
                                                <?php if (!empty($contactCallHistory->recording_url)) { ?>
                                                    <a href="<?php echo $contactCallHistory->recording_url; ?>"
                                                       target="_blank">Rec</a>
                                                <?php } ?>
                                </td>
                                <td class="break-all-word"><p><?php echo $content_notes['start']; ?></p>
                                    <?php if ($content_notes['end'] != ""): ?>
                                    <a href="javascript:void(0)" id="example<?php echo $key; ?>-show"
                                       class="showLink"
                                           onclick="showHide('example<?php echo $key; ?>');return false;">See
                                            more.</a>
                                    <div id="example<?php echo $key; ?>" class="more">
                                        <p><?php echo $content_notes['end']; ?><?php /*echo substr($contactCallHistory->notes,11); */ ?></p>

                                                        <p><a href="javascript:void(0)" id="example-hide"
                                                              class="hideLink"
                                              onclick="showHide('example<?php echo $key; ?>');return false;">Hide
                                                this content.</a></p>
                                    </div>
                                <?php endif; ?>
                            </td>
                                <?php
                                    echo '</tr>';
                                }}?>

                        </tbody>
                    </table>
                            <?php if (count($contactCallHistoryList) > 1) {
                                echo '<div class="view_all_btn"><a class="general-btn" href="/calls/view_all_call_history/' . $contactCallDetail->id . '" target="_blank">View All</a></div>';
								}
                            
                            } else {
                            echo "<div id ='call_history_no_record_div' class='no_record_found'>No record found</div>";
                            }?>
                            </div>
                            <div id="email_history_tab">
                               <table class="table table-bordered row mar-b-0">
                        <thead>
                        <?php
                        if (!empty($contact_email_history_list)) { ?>
                            <tr>
                                <th>User Name</th>
                                <th>Resource Name</th>
								<th>Timestamp</th>
                            </tr>
                        <?php } else {
                            echo "<div class='no_record_found'>No record found</div>";
                        } ?>

                        </thead>
                        <tbody>
                        <?php if (!empty($contact_email_history_list)) {

                            foreach ($contact_email_history_list as $key => $email_history_list) {
							
                                if ($key < 5) {
                                    echo '<tr>';
                                    ?>
                                    <td><?php echo $email_history_list->agent_name; ?></td>
                                    <td><?php echo $email_history_list->resource_name; ?></td>
									<td><?php echo php_datetimeformat($email_history_list->created_at); ?></td>
                                    <?php
                                    echo '</tr>';
                                }
                            }
                        } ?>

                        </tbody>
                    </table>
                            <?php if (count($contact_email_history_list) > 5) {
                                echo '<div class="view_all_btn"><a class="general-btn" href="/calls/email_history/' . $contactCallDetail->campaign_contact_id . '" target="_blank">View All</a></div>';
								}
								?>
                            </div>
                        
                    </div>
				</div>

               <!-- Call History Email History-->
            </div>

            <div class="col-lg-7 pad-15-b">
                <!-- Write Notes -->
                <div class="pad-15-lr pad-15-t  call-row-title">
                    <div class="column-header">
                        <p>Notes</p>
                    </div>
                </div>

                <div class="pad-15-lr row-with-pad pad-15-t">
                    <?php
                    if (isset($notes)) {
                        foreach ($notes as $note) { ?>
                            <p class="pre-populated-note" style="word-break: break-all">
                                <?php if (isset($note->note)) {
                                    echo $note->note;
                                } ?>
                                <span class="user_detail"> - <?php if (isset($note->first_name)) {
                                        echo $note->first_name;
                                    }
                                    if (isset($note->last_name)) {
                                        echo $note->last_name;
                                    } ?>
                                    <span class="date_format"><?php if (isset($note->created_at)) {
                                            echo $note->created_at;
                                        } ?></span>
                                    </span>
                            </p>
                        <?php }
                    }
                    ?>
                    <?php if(!$isViewPage){ ?>
                    <p class="character-count-text">
                <textarea rows="5" cols="10" placeholder="Notes..."
                          id="notes" name="notes"
                          class="row textarea-border box-sizing-border mar-b-0"
                    ><?php echo set_value('notes'); ?></textarea>
                    <?php }?>
                </div>
                <!-- Write Notes -->

                <!-- display Script -->

                <div class="pad-15-lr pad-15-t  call-row-title">
                    <div id="tab-container" class='tab-container'>
                        <ul class='etabs'>
                            <li class='tab'><a href="#tabs1-script">Script 1</a></li>
                            <li class='tab'><a href="#tabs2-script">Script 2</a></li>
                        </ul>
                        <div class='panel-container'>
                            <div id="tabs1-script">
                                <textarea class="campaignscript-textarea" style="" readonly placeholder="Script Main..."
                                          id="script_main"
                                          name="script_main"><?php if (!empty($contactCallDetail->script_main)) {
                                        echo $contactCallDetail->script_main;
                                    } ?></textarea>
                            </div>
                            <div id="tabs2-script">
                                <textarea class="campaignscript-textarea" readonly placeholder="Script Alter..."
                                          id="script_alt"
                                          name="script_alt"><?php if (!empty($contactCallDetail->script_alt)) {
                                        echo $contactCallDetail->script_alt;
                                    } ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- display Script -->

                <div class="pad-15-lr custom_questions">
                    <?php if (!empty($contactCallDetail->question_element_html)) {
                        echo $contactCallDetail->question_element_html;
                    } ?>
                </div>

                <div class="pad-15-lr row-with-pad pad-15-t bottom-btns">
                    <div class="dialog-form">
                        <?php if((!$isViewPage) || !empty($contactCallDetail->reference_link)){ ?>
                        <label>Reference Link:</label>
                        <?php } ?>

                        <div class="form-input">
                            <?php if($isViewPage){ ?>
                                <?php if (!empty($contactCallDetail->reference_link)) {
                                    echo $contactCallDetail->reference_link;
                                } ?>
                            <?php }else { ?>
                            <input type="text" id="reference_link" name="reference_link"
                                   value="<?php echo isset($contactCallDetail->reference_link) ? $contactCallDetail->reference_link : ""; ?>"
                                   placeholder="Reference Link"/>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <?php if (!$isViewPage && !empty($resources)) {?>
                    <div class="pad-15-lr row-with-pad pad-15-t bottom-btns">
                        <div class="select-dropdown styled pad-15-r  btn">
                            <select name="resource_id" id="resource_id_select">
                                <option value=""> --- SELECT Resource ---</option>
                                <?php
                                foreach ($resources as $resource) {
                                    if ($resource->id == $contactCallDetail->resource_id)
                                        $selected = "selected";
                                    else
                                        $selected = "";

                                    echo '<option value="' . $resource->id . '" ' . $selected . '>' . $resource->name . '</option>';
                                }
                                ?>
                            </select>
                            <input type="hidden" name="resource_name" id="resource_name"
                                   value="<?php echo isset($contactCallDetail->resource_name) ? $contactCallDetail->resource_name : ""; ?>">
                        </div>
						<div class="pad-15-r btn btn">
                            <button type="button" id="email_resource" class="general-btn">Email Resource
                            </button>
                        </div>
					</div>
                <?php } ?>

                <div class="pad-15-lr row-with-pad pad-15-t bottom-btns">
                    <?php if (!$isViewPage && ($contactCallDetail->logged_user_type != 'qa') && (!$Qaing)) { ?>
                        <div class="select-dropdown styled pad-15-r  btn">
                            <select name="call_disposition" id="call_disposition"
                                    onchange="CallDispositionCallBack(this)">
                                <option value=""> --- Call Disposition ---</option>
                                <?php
                                if (!empty($callDispositionList)) {
                                    foreach ($callDispositionList as $callDisposition) {
                                        if ($callDisposition->id == $contactCallDetail->call_disposition_id)
                                            $selected = "selected";
                                        else
                                            $selected = "";

                                        echo '<option value="' . $callDisposition->id . '" ' . $selected . '>' . $callDisposition->calldisposition_name . '</option>';
                                    }
                                }
                                ?>
                            </select>

                        </div>
                    <?php } ?>

                    <input type="hidden" id="hidden_Campaign_ID" name="campaign_id"
                           value="<?php echo $contactCallDetail->campaign_id; ?>"/>

                    <div class=" date-picker pad-15-r  btn" style="display: none;" id="call_disposition_datepicker">
                        <input type="text" id="call_disposition_update_date" name="call_disposition_update_date"
                               readonly
                               value="<?php
                               if (!empty($contactCallDetail->call_disposition_update_date)) {
                                   echo $contactCallDetail->call_disposition_update_date;
                               } else {
                                   echo $this->input->post('call_disposition_update_date');
                               }
                               ?>"/>
                    </div>

                    <?php if (!$isViewPage && isset($status) && $status != 'Follow-up' && ($contactCallDetail->logged_user_type != 'qa' && (!$Qaing))) { ?>
                        <div class="row pad-15-t">
                            <div class="pad-15-r btn alignleft btn">
                               <!-- <button type="submit" class="general-btn" id="call_history_btnSave">Submit
                                </button>-->
                                <input type="submit" class="general-btn" value="Submit" name="decision" id="call_history_btnSave">
                            </div>
                            <div class="pad-15-r btn alignleft btn">
                                <input type="submit" class="general-btn" value="Submit and go to next Contact" name="decision" id="submit_go_next_btnSave">
                                <!-- <button type="submit" class="general-btn" id="submit_go_next_btnSave">Submit and go to
                                    next Contact
                                </button>-->
                            </div>

                            <?php if(!$isAddPage){ ?>
                                <div class="pad-15-r  btn btn-long alignright">
                                    <input type="submit" class="general-btn" value="Add as a different person" name="decision" id="add_as_diff_person_btnSave">
                                    <!-- <button type="submit" id="add_as_diff_person_btnSave" class="general-btn">
                                        Add as a different person
                                </button> -->
                                </div>
                            <?php } ?>
                            
                        </div>
                    <?php } ?>

                    <?php if (!$isViewPage && !$isAddPage && $qa) {
                        $is_agent = $this->session->userdata('user_type') == 'agent' ? true : false;
                        ?>
                        <?php if ($this->session->userdata('user_type') != 'agent' && ($Qaing)) { ?>

                            <!-- Call Flow Findings -->
                            <div class="pad-15-t pad-15-b call-row-title">
                                <div class="column-header">
                                    <p>Call Flow Findings</p>
                                </div>
                            </div>
                            <div class="row" id="main_call_flow_findings_region">

                                <div class="pad-15-lr pad-15-t">
                                    <label class="bold_text">A. Opening â€“ Greeting and Identification:</label>

                                    <div class="sub_call_flow_label">
                                        <label>Proper Greeting</label>

                                        <div class="form-input popup-radio-group">
                                            <ul>
                                                <li>
                                                    <input type="radio" id="proper_greeting_yes" value="1"
                                                        <?php if (isset($callFlowFindingsData[0]->call_flow_value) && $callFlowFindingsData[0]->call_flow_value == 1): ?> checked="checked" <?php endif; ?>
                                                           name="proper_greeting"/><label
                                                        for="proper_greeting_yes">Yes</label>
                                                    <input type="radio" id="proper_greeting_no" value="0"
                                                        <?php if ((isset($callFlowFindingsData[0]->call_flow_value) && $callFlowFindingsData[0]->call_flow_value == 0) || !isset($callFlowFindingsData[0]->call_flow_value)): ?> checked="checked" <?php endif; ?>
                                                           name="proper_greeting"/><label
                                                        for="proper_greeting_no">No</label>

                                                </li>
                                            </ul>
                                        </div>

                                    </div>
                                    <div class="sub_call_flow_label">
                                        <label>Right Decision Maker Identified</label>

                                        <div class="form-input popup-radio-group">
                                            <ul>
                                                <li>
                                                    <input type="radio" id="right_decision_maker_identified_yes"
                                                           value="1" <?php if (isset($callFlowFindingsData[1]->call_flow_value) && $callFlowFindingsData[1]->call_flow_value == 1): ?> checked="checked" <?php endif; ?>
                                                           name="right_decision_maker_identified"/><label
                                                        for="right_decision_maker_identified_yes">Yes</label>
                                                    <input type="radio" id="right_decision_maker_identified_no"
                                                           value="0" <?php if ((isset($callFlowFindingsData[1]->call_flow_value) && $callFlowFindingsData[1]->call_flow_value == 0) || !isset($callFlowFindingsData[1]->call_flow_value)): ?> checked="checked" <?php endif; ?>
                                                           name="right_decision_maker_identified"/><label
                                                        for="right_decision_maker_identified_no">No</label>

                                                </li>
                                            </ul>
                                        </div>

                                    </div>
                                </div>
                                <div class="pad-15-lr pad-15-t">
                                    <label class="bold_text">B. Statement, Product Knowledge and Interest:</label>

                                    <div class="sub_call_flow_label">
                                        <label>Proper Branding</label>

                                        <div class="form-input popup-radio-group">
                                            <ul>
                                                <li>
                                                    <input type="radio" id="proper_branding_yes" value="1"
                                                        <?php if (isset($callFlowFindingsData[2]->call_flow_value) && $callFlowFindingsData[2]->call_flow_value == 1): ?> checked="checked" <?php endif; ?>
                                                           name="proper_branding"/><label
                                                        for="proper_branding_yes">Yes</label>
                                                    <input type="radio" id="proper_branding_no" value="0"
                                                        <?php if ((isset($callFlowFindingsData[2]->call_flow_value) && $callFlowFindingsData[2]->call_flow_value == 0) || !isset($callFlowFindingsData[2]->call_flow_value)): ?> checked="checked" <?php endif; ?>
                                                           name="proper_branding"/><label
                                                        for="proper_branding_no">No</label>

                                                </li>
                                            </ul>
                                        </div>

                                    </div>
                                    <div class="sub_call_flow_label">
                                        <label>Clearly Stated the Purpose of the Call</label>

                                        <div class="form-input popup-radio-group">
                                            <ul>
                                                <li>
                                                    <input type="radio" id="clearly_stated_the_purpose_of_the_call_yes"
                                                           value="1" <?php if (isset($callFlowFindingsData[3]->call_flow_value) && $callFlowFindingsData[3]->call_flow_value == 1): ?> checked="checked" <?php endif; ?>
                                                           name="clearly_stated_the_purpose_of_the_call"/><label
                                                        for="clearly_stated_the_purpose_of_the_call_yes">Yes</label>
                                                    <input type="radio" id="clearly_stated_the_purpose_of_the_call_no"
                                                           value="0" <?php if ((isset($callFlowFindingsData[3]->call_flow_value) && $callFlowFindingsData[3]->call_flow_value == 0) || !isset($callFlowFindingsData[3]->call_flow_value)): ?> checked="checked" <?php endif; ?>
                                                           name="clearly_stated_the_purpose_of_the_call"/><label
                                                        for="clearly_stated_the_purpose_of_the_call_no">No</label>

                                                </li>
                                            </ul>
                                        </div>

                                    </div>
                                    <div class="sub_call_flow_label">
                                        <label>Provided a clear Overview of the Content of the Asset</label>

                                        <div class="form-input popup-radio-group">
                                            <ul>
                                                <li>
                                                    <input type="radio"
                                                           id="provided_a_clear_overview_of_the_content_of_the_asset_yes"
                                                           value="1" <?php if (isset($callFlowFindingsData[4]->call_flow_value) && $callFlowFindingsData[4]->call_flow_value == 1): ?> checked="checked" <?php endif; ?>
                                                           name="provided_a_clear_overview_of_the_content_of_the_asset"/><label
                                                        for="provided_a_clear_overview_of_the_content_of_the_asset_yes">Yes</label>
                                                    <input type="radio"
                                                           id="provided_a_clear_overview_of_the_content_of_the_asset_no"
                                                           value="0" <?php if ((isset($callFlowFindingsData[4]->call_flow_value) && $callFlowFindingsData[4]->call_flow_value == 0) || !isset($callFlowFindingsData[4]->call_flow_value)): ?> checked="checked" <?php endif; ?>
                                                           name="provided_a_clear_overview_of_the_content_of_the_asset"
                                                           /><label
                                                        for="provided_a_clear_overview_of_the_content_of_the_asset_no">No</label>

                                                </li>
                                            </ul>
                                        </div>

                                    </div>
                                    <div class="sub_call_flow_label">
                                        <label>Prospect agreed to receiving a copy of the Asset</label>

                                        <div class="form-input popup-radio-group">
                                            <ul>
                                                <li>
                                                    <input type="radio"
                                                           id="prospect_agreed_to_receiving_a_copy_of_the_asset_yes"
                                                           value="1" <?php if (isset($callFlowFindingsData[5]->call_flow_value) && $callFlowFindingsData[5]->call_flow_value == 1): ?> checked="checked" <?php endif; ?>
                                                           name="prospect_agreed_to_receiving_a_copy_of_the_asset"/><label
                                                        for="prospect_agreed_to_receiving_a_copy_of_the_asset_yes">Yes</label>
                                                    <input type="radio"
                                                           id="prospect_agreed_to_receiving_a_copy_of_the_asset_no"
                                                           value="0" <?php if ((isset($callFlowFindingsData[5]->call_flow_value) && $callFlowFindingsData[5]->call_flow_value == 0) || !isset($callFlowFindingsData[5]->call_flow_value)): ?> checked="checked" <?php endif; ?>
                                                           name="prospect_agreed_to_receiving_a_copy_of_the_asset"
                                                           /><label
                                                        for="prospect_agreed_to_receiving_a_copy_of_the_asset_no">No</label>

                                                </li>
                                            </ul>
                                        </div>

                                    </div>
                                    <div class="sub_call_flow_label">
                                        <label>Accurate and Effective Rebuttals</label>

                                        <div class="form-input popup-radio-group">
                                            <ul>
                                                <li>
                                                    <input type="radio" id="accurate_and_effective_rebuttals_yes"
                                                           value="1" <?php if (isset($callFlowFindingsData[6]->call_flow_value) && $callFlowFindingsData[6]->call_flow_value == 1): ?> checked="checked" <?php endif; ?>
                                                           name="accurate_and_effective_rebuttals"/><label
                                                        for="accurate_and_effective_rebuttals_yes">Yes</label>
                                                    <input type="radio" id="accurate_and_effective_rebuttals_no"
                                                           value="0" <?php if ((isset($callFlowFindingsData[6]->call_flow_value) && $callFlowFindingsData[6]->call_flow_value == 0) || !isset($callFlowFindingsData[6]->call_flow_value)): ?> checked="checked" <?php endif; ?>
                                                           name="accurate_and_effective_rebuttals"/><label
                                                        for="accurate_and_effective_rebuttals_no">No</label>
                                                    <input type="radio"
                                                           id="accurate_and_effective_rebuttals_notapplicable" value="2"
                                                        <?php if (isset($callFlowFindingsData[6]->call_flow_value) && $callFlowFindingsData[6]->call_flow_value == 2): ?> checked="checked" <?php endif; ?>
                                                           name="accurate_and_effective_rebuttals"/><label
                                                        for="accurate_and_effective_rebuttals_notapplicable">N/A</label>
                                                </li>
                                            </ul>
                                        </div>

                                        </div>
                                    </div>
                                <div class="pad-15-lr pad-15-t">
                                    <label class="bold_text">C. Probing and Custom Questions:</label>

                                    <div class="sub_call_flow_label">

                                        <label>Agent was able to Probe</label>

                                        <div class="form-input popup-radio-group">
                                            <ul>
                                                <li>
                                                    <input type="radio" id="agent_was_able_to_probe_yes" value="1"
                                                        <?php if (isset($callFlowFindingsData[7]->call_flow_value) && $callFlowFindingsData[7]->call_flow_value == 1): ?> checked="checked" <?php endif; ?>
                                                           name="agent_was_able_to_probe"/><label
                                                        for="agent_was_able_to_probe_yes">Yes</label>
                                                    <input type="radio" id="agent_was_able_to_probe_no" value="0"
                                                        <?php if ((isset($callFlowFindingsData[7]->call_flow_value) && $callFlowFindingsData[7]->call_flow_value == 0) || !isset($callFlowFindingsData[7]->call_flow_value)): ?> checked="checked" <?php endif; ?>
                                                           name="agent_was_able_to_probe"/><label
                                                        for="agent_was_able_to_probe_no">No</label>
                                                    <input type="radio" id="agent_was_able_to_probe_notapplicable"
                                                           value="2"
                                                        <?php if (isset($callFlowFindingsData[7]->call_flow_value) && $callFlowFindingsData[7]->call_flow_value == 2): ?> checked="checked" <?php endif; ?>
                                                           name="agent_was_able_to_probe"/><label
                                                        for="agent_was_able_to_probe_notapplicable">N/A</label>
                                                </li>
                                            </ul>
                                        </div>

                                    </div>
                                    <div class="sub_call_flow_label">
                                        <label>Clear Delivery of the Questions</label>

                                        <div class="form-input popup-radio-group">
                                            <ul>
                                                <li>
                                                    <input type="radio" id="clear_delivery_of_the_questions_yes"
                                                           value="1" <?php if (isset($callFlowFindingsData[8]->call_flow_value) && $callFlowFindingsData[8]->call_flow_value == 1): ?> checked="checked" <?php endif; ?>
                                                           name="clear_delivery_of_the_questions"/><label
                                                        for="clear_delivery_of_the_questions_yes">Yes</label>
                                                    <input type="radio" id="clear_delivery_of_the_questions_no"
                                                           value="0" <?php if ((isset($callFlowFindingsData[8]->call_flow_value) && $callFlowFindingsData[8]->call_flow_value == 0) || !isset($callFlowFindingsData[8]->call_flow_value)): ?> checked="checked" <?php endif; ?>
                                                           name="clear_delivery_of_the_questions"/><label
                                                        for="clear_delivery_of_the_questions_no">No</label>
                                                    <input type="radio"
                                                           id="clear_delivery_of_the_questions_notapplicable" value="2"
                                                        <?php if (isset($callFlowFindingsData[8]->call_flow_value) && $callFlowFindingsData[8]->call_flow_value == 2): ?> checked="checked" <?php endif; ?>
                                                           name="clear_delivery_of_the_questions"/><label
                                                        for="clear_delivery_of_the_questions_notapplicable">N/A</label>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="sub_call_flow_label">
                                        <label>Custom Questions were all Answered </label>

                                        <div class="form-input popup-radio-group">
                                            <ul>
                                                <li>
                                                    <input type="radio" id="custom_questions_were_all_answered_yes"
                                                           value="1" <?php if (isset($callFlowFindingsData[9]->call_flow_value) && $callFlowFindingsData[9]->call_flow_value == 1): ?> checked="checked" <?php endif; ?>
                                                           name="custom_questions_were_all_answered"/><label
                                                        for="custom_questions_were_all_answered_yes">Yes</label>
                                                    <input type="radio" id="custom_questions_were_all_answered_no"
                                                           value="0" <?php if ((isset($callFlowFindingsData[9]->call_flow_value) && $callFlowFindingsData[9]->call_flow_value == 0) || !isset($callFlowFindingsData[9]->call_flow_value)): ?> checked="checked" <?php endif; ?>
                                                           name="custom_questions_were_all_answered"/><label
                                                        for="custom_questions_were_all_answered_no">No</label>
                                                    <input type="radio"
                                                           id="custom_questions_were_all_answered_notapplicable"
                                                           value="2" <?php if (isset($callFlowFindingsData[9]->call_flow_value) && $callFlowFindingsData[9]->call_flow_value == 2): ?> checked="checked" <?php endif; ?>
                                                           name="custom_questions_were_all_answered"/><label
                                                        for="custom_questions_were_all_answered_notapplicable">N/A</label>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="pad-15-lr pad-15-t">
                                    <label class="bold_text">D. Verification and Closing:</label>

                                    <div class="sub_call_flow_label">
                                        <label>All Pertinent Information Verified</label>

                                        <div class="form-input popup-radio-group">
                                            <ul>
                                                <li>
                                                    <input type="radio" id="all_pertinent_information_verified_yes"
                                                           value="1" <?php if (isset($callFlowFindingsData[10]->call_flow_value) && $callFlowFindingsData[10]->call_flow_value == 1): ?> checked="checked" <?php endif; ?>
                                                           name="all_pertinent_information_verified"/><label
                                                        for="all_pertinent_information_verified_yes">Yes</label>
                                                    <input type="radio" id="all_pertinent_information_verified_no"
                                                           value="0" <?php if ((isset($callFlowFindingsData[10]->call_flow_value) && $callFlowFindingsData[10]->call_flow_value == 0) || !isset($callFlowFindingsData[10]->call_flow_value)): ?> checked="checked" <?php endif; ?>
                                                           name="all_pertinent_information_verified"/><label
                                                        for="all_pertinent_information_verified_no">No</label>

                                                </li>
                                            </ul>
                                        </div>

                                    </div>
                                    <div class="sub_call_flow_label">
                                        <label>Proper Closing</label>

                                        <div class="form-input popup-radio-group">
                                            <ul>
                                                <li>
                                                    <input type="radio" id="proper_closing_yes" value="1"
                                                        <?php if (isset($callFlowFindingsData[11]->call_flow_value) && $callFlowFindingsData[11]->call_flow_value == 1): ?> checked="checked" <?php endif; ?>
                                                           name="proper_closing"/><label
                                                        for="proper_closing_yes">Yes</label>
                                                    <input type="radio" id="proper_closing_no" value="0"
                                                        <?php if ((isset($callFlowFindingsData[11]->call_flow_value) && $callFlowFindingsData[11]->call_flow_value == 0) || !isset($callFlowFindingsData[11]->call_flow_value)): ?> checked="checked" <?php endif; ?>
                                                           name="proper_closing"/><label
                                                        for="proper_closing_no">No</label>

                                                </li>
                                            </ul>
                                        </div>

                                    </div>
                                    <div class="sub_call_flow_label">
                                        <label>Right Expectations were set</label>

                                        <div class="form-input popup-radio-group">
                                            <ul>
                                                <li>
                                                    <input type="radio" id="right_expectations_were_set_yes" value="1"
                                                        <?php if (isset($callFlowFindingsData[12]->call_flow_value) && $callFlowFindingsData[12]->call_flow_value == 1): ?> checked="checked" <?php endif; ?>
                                                           name="right_expectations_were_set"/><label
                                                        for="right_expectations_were_set_yes">Yes</label>
                                                    <input type="radio" id="right_expectations_were_set_no" value="0"
                                                        <?php if ((isset($callFlowFindingsData[12]->call_flow_value) && $callFlowFindingsData[12]->call_flow_value == 0) || !isset($callFlowFindingsData[12]->call_flow_value)): ?> checked="checked" <?php endif; ?>
                                                           name="right_expectations_were_set"/><label
                                                        for="right_expectations_were_set_no">No</label>

                                                </li>
                                            </ul>
                                        </div>

                                    </div>
                                </div>


                            </div>
                        <?php } ?>

                        <?php if (!$is_agent && (isset($action) && $action == "qa" || (isset($status) && $status == 'Follow-up' && $qa_accepted_by_user))) { ?>
                            <div class="row pad-15-t">

                            <div class="pad-15-r btn alignleft btn">
                               <!-- <button type="submit" id="approve" class="general-btn" disabled>Approve</button>-->
                                <input type="submit" class="general-btn" value="Approve" name="decision" id="approve">
                            </div>
                            <div class="pad-15-r btn alignleft btn">
                                <!--<button type="submit" id="follow-up" disabled="true" class="general-btn btn-is-disabled"
                                        disabled>Follow Up
                                </button>-->
                                <input type="submit" class="general-btn" value="Follow Up" name="decision" id="follow-up">
                            </div>
                            <div class="pad-15-r  btn btn-long alignleft">
                                <!--<button type="submit" id="reject" disabled="true" class="general-btn btn-is-disabled"
                                        disabled>Reject
                                </button>-->
                                <input type="submit" class="general-btn" value="Reject" name="decision" id="reject">
                            </div>
                        <?php } ?>

                        <?php if (isset($status) && $status == 'Follow-up' && (($user_is_agent && isset($action) && $action != "qa") || (!$is_agent && $qa_accepted_by_user))) { ?>
                            <div class="pad-15-r btn alignleft btn">
                                <!--<button type="submit" id="update_and_submit" class="general-btn" disabled>Update and
                                    Submit
                                </button>-->
                                <input type="submit" class="general-btn" value="Update and Submit" name="decision" id="update_and_submit">
                            </div>
                            </div>
                        <?php } ?>
                        <?php if ($this->session->userdata('user_type') != 'agent' && isset($status) && ($status != '' && $status != 'In Progress') && ($Qaing)) { ?>
                            <!-- FollowUP reason -->
                            <div class="row pad-15-t" id="qa_follow_up_selector">
                                <div class="follow_up_input_fields_wrap" id="follow_up_input_fields_wrap">
                                    <label class="add_field_button" id="follow_up_add_field_button">Add another
                                        Follow-up
                                        Reason
                                    </label>

                                    <div class="followUpClonedInput">
                                        <div class="styled select-dropdown table-dropdown">
                                            <select name="follow_up_reason[]" id="follow_up_reason_1"
                                                    class="main_follow_up_reason_combo"
                                                    onchange="FollowUpReason(this)">
                                                <option value="">--Select Follow-up Reason--</option>
                                                <?php
                                                if (!empty($followupReason)) {
                                                    foreach ($followupReason as $follow_up_reason) {
                                                        echo '<option role="option" value="' . $follow_up_reason . '" >' . $follow_up_reason . '</option>';
                                                    }
                                                }
                                                ?>
                                            </select>

                                            <div class="styled select-dropdown table-dropdown sub_combo"
                                                 id="sub_follow_up_combo_1"></div>
                                            <div class="dialog-form others_label_input_text" style="display: none;"
                                                 id="follow_up_text_div_1">

                                                <div class="form-input">
                                                    <input type="text" id="follow_up_text_1" name="follow_up_text[]"
                                                           maxlength="100"
                                                           placeholder="Follow-Up Reason"/>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <input type="hidden" name="follow-up-status" id="follow-up-status">
                            </div>
                            <!-- Reject reason -->
                            <div class="row pad-15-t" id="qa_status_selector">
                                <div class="input_fields_wrap" id="input_fields_wrap">
                                    <label class="add_field_button" id="add_field_button">Add another Rejection Reason
                                    </label>

                                    <div class="clonedInput">
                                        <div class="styled select-dropdown table-dropdown">
                                            <select name="reject_reason[]" id="reject_reason_1"
                                                    class="main_reason_combo"
                                                    onchange="RejectedReason(this)">
                                                <option value="">--Select Rejection Reason--</option>
                                                <?php
                                                if (!empty($rejectedReason)) {
                                                    foreach ($rejectedReason as $reason) {
                                                        echo '<option role="option" value="' . $reason . '" >' . $reason . '</option>';
                                                    }
                                                }
                                                ?>
                                            </select>

                                            <div class="styled select-dropdown table-dropdown sub_combo"
                                                 id="sub_combo_1"></div>
                                            <div class="dialog-form others_label_input_text" style="display: none;"
                                                 id="reason_text_div_1">
                                                <!-- <label>Rejection Reason:</label>-->

                                                <div class="form-input">
                                                    <input type="text" id="reason_text_1" name="reason_text[]"
                                                           maxlength="100"
                                                           placeholder="Rejection Reason"/>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <input type="hidden" name="reject-status" id="reject-status">
                            </div>

                        <?php } ?>
                    <?php } ?>

                </div>
                <div class="pad-15-lr row-with-pad pad-15-t bottom-btns">
                    <div class="pad-15-r btn alignleft btn">
                        <?php if ($save_contact_visible) { ?>
                            <input type="submit" class="general-btn" value="Save Contact" name="decision" id="save_contact">
                            <!-- <button type="submit" class="general-btn" id="save_contact">Save Contact</button>-->
                        <?php } ?>
                    </div>
                </div>
                <div class="pad-15-lr row-with-pad pad-15-t bottom-btns">
                    <div class="pad-15-r btn alignleft btn">
                        <?php if (!empty($referrerURL)) {
                            if ($this->session->contactdata('ContactFilter')) {
                                if ((strpos($referrerURL, 'contacts')) && (!strpos($referrerURL, 'contactsort'))) {
                                    $referrerURL .= "/contactsort";
                            }                            
                            }
                            ?>
                            <a class="general-btn" id="btnBack" href="<?php echo $referrerURL; ?>">Back
                            </a>
                        <?php } else { ?>
                            <a class="general-btn btn-is-disabled" id="btnBack" href="<?php echo $referrerURL; ?>">Back
                            </a> <?php } ?>
                    </div>
                </div>

                <input type="hidden" id="dial_code" name="dial_code"
                       value="<?php echo isset($contactCallDetail->dial_code) ? $contactCallDetail->dial_code : ""; ?>">

                <input type="hidden" id="calling_status" name="calling_status"
                       value="">

                <input type="hidden" id="campaign_site" name="campaign_site"
                       value="<?php echo isset($contactCallDetail->site_id) ? $contactCallDetail->site_id : ""; ?>">
                <!--<input type="hidden" id="email_resource_sent" name="email_resource_sent"
                       value="<?php /*echo isset($contactCallDetail->email_resource_sent) ? $contactCallDetail->email_resource_sent : ""; */?>">-->
                <!--<input type="hidden" id="statusValue" name="statusValue"
                       value="">-->
                <input type="hidden" id="LeadEditBy" name="LeadEditBy"
                       value="<?php echo isset($contactCallDetail->qa) ? $contactCallDetail->qa : ""; ?>">
                <input type="hidden" id="lead_status" name="lead_status"
                       value="<?php echo isset($status) ? $status : ""; ?>">
                <input type="hidden" id="previous_agent_id" name="previous_agent_id"
                       value="<?php echo isset($contactCallDetail->agent_id) ? $contactCallDetail->agent_id : ""; ?>">
                <input type="hidden" id="previous_call_disposition_id" name="previous_call_disposition_id"
                       value="<?php echo isset($contactCallDetail->call_disposition_id) ? $contactCallDetail->call_disposition_id : ""; ?>">
                <input type="hidden" id="call_start_datetime" name="call_start_datetime"
                       value="<?php echo set_value('call_start_datetime'); ?>">
                <input type="hidden" id="call_end_datetime" name="call_end_datetime"
                       value="<?php echo set_value('call_end_datetime'); ?>">
                <input type="hidden" id="post_last_call_history_id" name="last_call_history_id"
                       value="<?php echo set_value('last_call_history_id'); ?>">
                <input type="hidden" id="resource_id" name="email_resource_id"
                       value="<?php echo isset($resource_id) ? $resource_id : set_value('email_resource_id'); ?>">
                <input type="hidden" id="is_add_page" name="is_add_page"
                       value="<?php echo $isAddPage; ?>">
                <input type="hidden" id="eg_campaign_id" name="eg_campaign_id"
                       value="<?php echo isset($contactCallDetail->eg_campaign_id) ? $contactCallDetail->eg_campaign_id : set_value('eg_campaign_id'); ?>">
                <input type="hidden" id="campaign_type" name="campaign_type"
                       value="<?php echo isset($contactCallDetail->campaign_type) ? $contactCallDetail->campaign_type : set_value('campaign_type'); ?>">
                <input type="hidden" id="member_id" name="member_id"
                       value="<?php echo isset($contactCallDetail->member_id) ? $contactCallDetail->member_id : set_value('member_id'); ?>">
                <input type="hidden" id="campaign_contact_id" name="campaign_contact_id"
                       value="<?php echo isset($contactCallDetail->campaign_contact_id) ? $contactCallDetail->campaign_contact_id : set_value('campaign_contact_id'); ?>">
                <input type="hidden" id="lead_id" name="lead_id"
                       value="<?php echo isset($contactCallDetail->lead_id) ? $contactCallDetail->lead_id : set_value('lead_id'); ?>">
                <input type="hidden" id="source" name="source"
                       value="<?php echo isset($contactCallDetail->source) ? $contactCallDetail->source : set_value('source'); ?>">
                <input type="hidden" id="Qaing" name="Qaing"
                       value="<?php echo $Qaing; ?>">
                <input type="hidden" id="contact_campaign_id" name="contact_campaign_id"
                       value="<?php echo isset($contactCallDetail->campaign_id) ? $contactCallDetail->campaign_id : set_value('campaign_id'); ?>">
            </div>