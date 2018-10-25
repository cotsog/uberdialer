<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Normalize
{

    /**
     * Takes an array of "normalizing" rules from the DB for various member demographics.
     * Steps through each rule doing a keyword compare. All rules for a particular question id
     * have to be evaluated. If nothing matches then we assign Other by default
     * This function returns a value for Job Function (alias is Silo) which is question id 225 in the DB.
     **/
    function silo($job_title, $rules)
    {
        #job_level question_id = 16
        $silo = 'Business'; //default
        $current_step = 1;
        $we_have_a_match = false;
        $job_title = trim($job_title);

        foreach ($rules as $rule) {
            //only process SILO type rules. Question id = 225
            if ($rule->question_id == 225) {
                $current_action = trim($rule->action);
                $keyword = trim($rule->keyword);
                if ($rule->processing_step != $current_step) {
                    //reset
                    $we_have_a_match = false;
                    $current_step = $rule->processing_step;
                }

                if ($current_action == 'like') {
                    if ($we_have_a_match == false) {//keep trying for a find a match
                        //Wrap Keyword in regex delimiters. i at the end sets case-insenstive
                        $keyword = '/' . $keyword . '/i';
                        $is_match = preg_match($keyword, $job_title);
                        if ($is_match) {
                            $we_have_a_match = true;
                            //check if the keyword_not_like field for this keyword. make sure job title has no matches on that
                            if (trim($rule->keyword_not_like) != '') {
                                $words = explode(',', trim($rule->keyword_not_like));
                                foreach ($words as $word) {
                                    $pos = stripos($job_title, trim($word));
                                    if ($pos !== false) {
                                        //match found. So this is a disqualifier. Reset $match var back to false
                                        $we_have_a_match = false;
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }

                if ($we_have_a_match) {
                    $silo = $rule->value;
                }
            }
        }

        return $silo;
    }

    /**
     * Takes an array of "normalizing" rules from the DB for various member demographics.
     * Steps through each rule doing a keyword compare. All rules for a particular question id
     * have to be evaluated. If nothing matches then we assign Other by default.
     * Each Job level is evaluated as a whole (processing step). We only set the new job level
     * at the end of processing a particular job level (processing step or 'value' column).
     * This function returns a value for Job Level which is question id 16 in the DB.
     **/
    function job_level($job_title, $rules)
    {
        #job_level question_id = 16
        $job_level = 'Other'; //default
        $new_job_level = 'Other';
        $current_step = 1;
        $we_have_a_match = false;
        $job_title = trim($job_title);

        foreach ($rules as $rule) {
            //only process Job Level type rules. Question id = 16
            if ($rule->question_id == 16) {
                $current_action = trim($rule->action);
                $keyword = trim($rule->keyword);

                if ($rule->processing_step !== $current_step) {
                    //Processing is over for the current step. If a match was found then
                    //assign the new match value as the updated job level.
                    if ($we_have_a_match) {
                        $job_level = $new_job_level;
                    }
                    //reset
                    $we_have_a_match = false;
                    $current_step = $rule->processing_step;
                }

                if ($current_action == 'equals') {
                    if (strtolower(trim($job_title)) == strtolower(trim($rule->keyword))) {
                        $job_level = trim($rule->value);
                        break; //break from loop and return $job_level
                    }
                }

                if ($current_action == 'like') {
                    if ($we_have_a_match == false) {//keep trying for a find a match

                        //Wrap Keyword in regex delimiters. i at the end sets case-insenstive.
                        $keyword = '/' . $keyword . '/i';
                        $is_match = preg_match($keyword, $job_title);
                        if ($is_match) {
                            $we_have_a_match = true;
                            $new_job_level = trim($rule->value);
                            //check if the keyword_not_like field for this keyword. make sure job title has no matches on that
                            if (trim($rule->keyword_not_like) != '') {
                                $words = explode(',', trim($rule->keyword_not_like));
                                foreach ($words as $word) {
                                    $pos = stripos($job_title, trim($word));
                                    if ($pos !== false) {
                                        //match found. So this is a disqualifier. Reset $match var back to false
                                        $we_have_a_match = false;
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }

                if ($current_action == 'not like') {
                    //only need to do this check if we have found a match on this particular processing step
                    if ($we_have_a_match) {
                        $pos = stripos(trim($job_title), trim($rule->keyword));
                        if ($pos !== false) {
                            //match found here. So this is a disqualifier. Reset $match var back to false
                            $we_have_a_match = false;
                        }
                    }
                }
            }
        }

        //final check
        if ($we_have_a_match) {
            $job_level = $new_job_level;
        }

        return $job_level;
    }

    function ml_title($silo,$job_level){
        $ml_title = '';

        switch(strtoupper($silo)){
            case 'HR':
                if ($job_level == 'C-Level') { $ml_title = 'Executive Management (Chairman/CEO/CFO)';}
                else if ($job_level == 'VP Level') { $ml_title = 'VP of Human Resources';}
                else if ($job_level == 'Director Level') { $ml_title = 'HR Director';}
                else if ($job_level == 'Manager Level') { $ml_title = 'HR Manager';}
                else if ($job_level == 'Other') { $ml_title = 'Other HR';}
                break;

            case 'FINANCE':
                if ($job_level == 'C-Level') { $ml_title = 'Executive Management (Chairman/CEO/CFO)';}
                else if ($job_level == 'VP Level') { $ml_title = 'VP of Finance';}
                else if ($job_level == 'Director Level') { $ml_title = 'Treasurer, Finance Manager, Controller, Finance Director';}
                else if ($job_level == 'Manager Level') { $ml_title = 'Treasurer, Finance Manager, Controller, Finance Director';}
                #else if ($job_level == 'Other') { $ml_title = '';}
                break;

            case 'BUSINESS':
                if ($job_level == 'C-Level') { $ml_title = 'Executive Management (Chairman/CEO/CFO)';}
                else if ($job_level == 'VP Level') { $ml_title = 'Senior Management (SVP/GM/Director)';}
                else if ($job_level == 'Director Level') { $ml_title = 'Senior Management (SVP/GM/Director)';}
                else if ($job_level == 'Manager Level') { $ml_title = 'Senior Management (SVP/GM/Director)';}
                #else if ($job_level == 'Other') { $ml_title = '';}
                break;

            case 'IT':
                if ($job_level == 'C-Level') { $ml_title = 'CIO/CTO/CSO';}
                else if ($job_level == 'VP Level') { $ml_title = 'Senior Management (SVP/GM/Director)';}
                else if ($job_level == 'Director Level') { $ml_title = 'Technology Director';}
                else if ($job_level == 'Manager Level') { $ml_title = 'IT Department Manager';}
                else if ($job_level == 'Other') { $ml_title = 'Other IT';}
                break;

            case 'MARKETING':
                if ($job_level == 'C-Level') { $ml_title = 'Chief Marketing Officer';}
                else if ($job_level == 'VP Level') { $ml_title = 'Marketing Vice President';}
                else if ($job_level == 'Director Level') { $ml_title = 'Marketing Director';}
                else if ($job_level == 'Manager Level') { $ml_title = 'Marketing Manager/Professional';}
                else if ($job_level == 'Other') { $ml_title = 'Other Marketing';}
                break;
}

        return $ml_title;
    }


    function generate_password($app = 'eg',$length = 8) {

        // start with a blank password
        $password = "";

        // define possible characters - any character in this string can be
        // picked for use in the password, so if you want to put vowels back in
        // or add special characters such as exclamation marks, this is where
        // you should do it
        $possible = "2346789bcdfghjkmnpqrtvwxyzBCDFGHJKLMNPQRTVWXYZ";

        // we refer to the length of $possible a few times, so let's grab it now
        $maxlength = strlen($possible);

        // check for length overflow and truncate if necessary
        if ($length > $maxlength) {
            $length = $maxlength;
        }

        // set up a counter for how many characters are in the password so far
        $i = 0;

        // add random characters to $password until $length is reached
        while ($i < $length) {

            // pick a random character from the possible ones
            $char = substr($possible, mt_rand(0, $maxlength - 1), 1);

            // have we already used this character in $password?
            if (!strstr($password, $char)) {
                // no, so it's OK to add it onto the end of whatever we've already got...
                $password .= $char;
                // ... and increase the counter by one
                $i++;
            }
        }
        if($app == 'mpg'){
            return $password;
        }else{
            return password_hash($password, CRYPT_BLOWFISH);
        }
        
    }
}