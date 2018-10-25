<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function getEGCampaignQuestionsByID($id)
{
    $CI =& get_instance();
    $db2 = $CI->load->database('db2', TRUE);

    $sql = 'SELECT * FROM questions q WHERE id = ?';
    $query = $db2->query($sql, array($id));
    $array = $query->result();

    return ($array) ? $array[0] : 0;
}

function getIndustriesValues()
{
	$industriesValues=array();
    $industry_question = 'Aerospace and Aviation|Agriculture and Mining|Business Services|Computers and Electronics|Construction and Real Estate|Education|Energy, Raw Materials and Utilities|Finance|Food and Beverage|Government|Healthcare, Pharmaceuticals and Biotech|Insurance|Legal|Manufacturing|Marketing, Advertising and Public Relations|Media, Entertainment and Publishing|Non-Profit|Retail|Software, Internet and Technology|Telecommunications|Transportation|Travel, Hotel, Restaurant and Recreation|Wholesale and Distribution';
    if(!empty($industry_question)) {
    $industriesValues = explode('|', $industry_question);
    }
    return $industriesValues;
}

function getCompanySizeValues()
{
	$companySizeValues=array();
    $company_size_question ='1 to 9|10 to 24|25-49|50-99|100-249|250-499|500-999|1,000-4,999|5,000-9,999|10,000-49,999|50K-100K|>100K';
    if(!empty($company_size_question)) {
    $companySizeValues = explode('|', $company_size_question);
    return $companySizeValues;
}
}

function getJobFunctionValues()
{
	/*$job_functions=array();
    $job_function_question = getEGCampaignQuestionsByID(77);
    if(!empty($job_function_question)) {
    $job_functions = explode('|', $job_function_question->options);
    }
    return $job_functions;*/
    $job_functions = array(
        "Business" => "Business",
        "IT" => "IT",
        "HR" => "HR",
        "Marketing" => "Marketing",
        "Finance" => "Finance"
    );
    return $job_functions;

}
function getJobLevelValues()
{
	$job_levels=array();
    $job_level_question = 'C-Level|VP Level|Director Level|Manager Level|Other';
    if(!empty($job_level_question)) {
    $job_levels = explode('|', $job_level_question);
    }
    return $job_levels;
}

function getRejectReasonValues(){
    $rejectReasonValues = array(
        "Campaign Filter" => "Campaign Filter",
        "Unprofessionalism/Call handling" => "Unprofessionalism/Call handling",
        "Duplicate Lead in Admin" => "Duplicate Lead in Admin",
        "Prospect expressing signs of Sarcasm, being agitated, being Irate, and hanging-up without proper closing" => "Prospect expressing signs of Sarcasm, being agitated, being Irate, and hanging-up without proper closing",
        "Others" => "Others"

//        "Delayed Aggregator" => "Delayed Aggregator",
//        "Invalid Email and Phone " => "Invalid Email and Phone",
//        "Never sent inquiry" => "Never sent inquiry",
//        "No Opportunity" => "No Opportunity",
//        "Not Interested" => "Not Interested",
//        "Does not want to Product" => "Does not want to Product",
//        "Other" => "Other"
    );
    return $rejectReasonValues;
}

function getFollowUpReasonValues(){
    $followUpReasonValues = array(
        "Inaccurate Data Entry" => "Inaccurate Data Entry",
        "Failure to Ask or Verify Pertinent Information" => "Failure to Ask or Verify Pertinent Information",
        "Improper Branding" => "Improper Branding",
        "Failure to state the purpose of the call and what we are trying to promote" => "Failure to state the purpose of the call and what we are trying to promote",
        "Others" => "Others"
    );
    return $followUpReasonValues;
}


function getLeadStatusValues($app_module_type=null){
    $duplicate_lead = "Duplicate Lead";
    $leadStatusValues = array(
       // ""=> " ",
        "Pending" =>"Pending",
        "In Progress" => "In Progress",
        "QA in progress" => "QA in progress",
        "Approve" => "Approved",
        "Reject"  => "Rejected",
        "Follow-up" => "Follow-up",
        $duplicate_lead => $duplicate_lead
    );
    return $leadStatusValues;
}

function getCampaignTypeValuesEg(){
    $campaignTypeValues = array(
        "leadgen" => "Lead Gen",
        "cat_leads" => "Category Leads",
        "iq_center" => "IQ Center",
        "dual_cpl" => "Dual CPL",
        "hql" => "HQL",
        "partner" => "Partner",
        "blended" => "Blended",
        "telemarketing" => "Telemarketing",
        "mql" => "MQL",
    );
    return $campaignTypeValues;
}

function getModuleTypeValues(){
    $moduleTypeValues = array(
        "tm" => "Telemarketing"
    );
    return $moduleTypeValues;
}

function getCampaignTypeValuesMpg(){
    $campaignTypeValues = array(
        "leadgen" => "CPL",
        "iq_center" => "IQ/MRL",
        "cost_per_click" => "CPC",
        "mrl" => "MRL",
        "telemarketing" => "Telemarketing"
    );
    return $campaignTypeValues;
}

function getEGWebsitesList($id=0)
{
    /*$CI =& get_instance();
    $db2 = $CI->load->database('db2', TRUE);

    $db2->select('id,`name`,site_name,base_url');
    $db2->from('sites');
    if($id>0) {
        $db2->where('id', $id);
    }
    $query = $db2->get();
    $array = $query->result();
    if($id>0) {
        return ($array) ? $array[0] : 0;
    }
    return $array;*/
	
	$campaigntelemarketingoffices = array(
        "Davao" => "Davao TM",
        "Oceana" => "Oceana TM",
        "Virtual" => "Pampanga VTM",
        "Davao VTM" => "Davao VTM",
        "Cebu TM" => "Cebu TM",
        "Cebu VTM" => "Cebu VTM"
    );
    return $campaigntelemarketingoffices;
}

function getCompanyRevenue()
{
    $revenue_question = '< $1 Million|$1-9 Million|$10-49 Million|$50 - 99 Million|$100 - 249 Million|$250 - 499 Million|$500 M - 1 Billion|>$1 Billion';
    if(!empty($revenue_question)) {
        return $revenue_question;
    }
}