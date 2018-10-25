<?php
 if ( ! defined('BASEPATH')) exit('No direct script access allowed');
function full_url($s, $use_forwarded_host=false) {
    return url_origin($s, $use_forwarded_host) . $s['REQUEST_URI'];
}

function url_origin($s, $use_forwarded_host=false) {
    $ssl = (!empty($s['HTTPS']) && $s['HTTPS'] == 'on') ? true : false;

    $sp = strtolower($s['SERVER_PROTOCOL']);
    $protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
    $port = $s['SERVER_PORT'];
    $port = ((!$ssl && $port == '80') || ($ssl && $port == '443')) ? '' : ':' . $port;
    $host = ($use_forwarded_host && isset($s['HTTP_X_FORWARDED_HOST'])) ? $s['HTTP_X_FORWARDED_HOST'] : (isset($s['HTTP_HOST']) ? $s['HTTP_HOST'] : null);
    $host = isset($host) ? $host : $s['SERVER_NAME'] . $port;
    return $protocol . '://' . $host;
}

/**
 * Convert a comma separated file into an associated array.
 * The first row should contain the array keys.
 */
function csv_to_array($filename='', $delimiter=',')
{
    if(!file_exists($filename) || !is_readable($filename))
        return FALSE;
    
    $header = NULL;
    $data = array();
    if (($handle = fopen($filename, 'r')) !== FALSE)
    {
        while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE)
        {
            if(!$header){
                $header = $row;
                #$data[] = array_combine($header, $row);
            }
            else{
                $data[] = array_combine($header, $row);
            }
        }
        fclose($handle);
    }
    
    return $data;
}

function format_url($url) {
    if (strlen($url) > 8) {
        if (substr($url, 0, 7) == 'http://') {
            $u = $url;
        } elseif (substr($url, 0, 8) == 'https://') {
            $u = $url;
        } else {
            $u = 'http://' . $url;
        }
    } else {
        $u = 'http://' . $url;
    }

    return $u;
}


function get_states_array() {
    $states = array(
        //"" => "--SELECT--",
        "AL" => "AL",
        "AK" => "AK",
        "AZ" => "AZ",
        "AR" => "AR",
        "CA" => "CA",
        "CO" => "CO",
        "CT" => "CT",
        "DE" => "DE",
        "DC" => "DC",
        "FL" => "FL",
        "GA" => "GA",
        "HI" => "HI",
        "ID" => "ID",
        "IL" => "IL",
        "IN" => "IN",
        "IA" => "IA",
        "KS" => "KS",
        "KY" => "KY",
        "LA" => "LA",
        "MA" => "MA",
        "MD" => "MD",
        "ME" => "ME",
        "MI" => "MI",
        "MN" => "MN",
        "MS" => "MS",
        "MO" => "MO",
        "MT" => "MT",
        "NE" => "NE",
        "NV" => "NV",
        "NH" => "NH",
        "NJ" => "NJ",
        "NM" => "NM",
        "NY" => "NY",
        "NC" => "NC",
        "ND" => "ND",
        "OH" => "OH",
        "OK" => "OK",
        "OR" => "OR",
        "PA" => "PA",
        "RI" => "RI",
        "SC" => "SC",
        "SD" => "SD",
        "TN" => "TN",
        "TX" => "TX",
        "UT" => "UT",
        "VT" => "VT",
        "VA" => "VA",
        "WA" => "WA",
        "WV" => "WV",
        "WI" => "WI",
        "WY" => "WY",
        "AB" => "AB",
        "BC" => "BC",
        "MB" => "MB",
        "NB" => "NB",
        "NL" => "NL",
        "NT" => "NT",
        "NS" => "NS",
        "NU" => "NU",
        "ON" => "ON",
        "PE" => "PE",
        "QC" => "QC",
        "SK" => "SK",
        "YT" => "YT"
    );

    return $states;
}

function get_countries_array() {
    $countries = array(
        //"" => "--SELECT--",
        "AF" => "Afghanistan",
        "AL" => "Albania",
        "DZ" => "Algeria",
        "AS" => "American Samoa",
        "AD" => "Andorra",
        "AO" => "Angola",
        "AI" => "Anguilla",
        "AQ" => "Antarctica",
        "AG" => "Antigua and Barbuda",
        "AR" => "Argentina",
        "AM" => "Armenia",
        "AW" => "Aruba",
        "AU" => "Australia",
        "AT" => "Austria",
        "AZ" => "Azerbaijan",
        "BS" => "Bahamas",
        "BH" => "Bahrain",
        "BD" => "Bangladesh",
        "BB" => "Barbados",
        "BY" => "Belarus",
        "BE" => "Belgium",
        "BZ" => "Belize",
        "BJ" => "Benin",
        "BM" => "Bermuda",
        "BT" => "Bhutan",
        "BO" => "Bolivia",
        "BA" => "Bosnia and Herzegovina",
        "BW" => "Botswana",
        "BV" => "Bouvet Island",
        "BR" => "Brazil",
        "IO" => "British Indian Ocean Territory",
        "BN" => "Brunei",
        "BG" => "Bulgaria",
        "BF" => "Burkina Faso",
        "BI" => "Burundi",
        "KH" => "Cambodia",
        "CM" => "Cameroon",
        "CA" => "Canada",
        "CV" => "Cape Verde",
        "KY" => "Cayman Islands",
        "CF" => "Central African Republic",
        "TD" => "Chad",
        "CL" => "Chile",
        "CN" => "China",
        "CX" => "Christmas Island",
        "CC" => "Cocos (Keeling) Islands",
        "CO" => "Columbia",
        "KM" => "Comoros",
        "CG" => "Congo",
        "CK" => "Cook Islands",
        "CR" => "Costa Rica",
        "CI" => "Cote D'Ivoire (Ivory Coast)",
        "HR" => "Croatia (Hrvatska)",
        "CU" => "Cuba",
        "CY" => "Cyprus",
        "CZ" => "Czech Republic",
        "KP" => "D.P.R. Korea",
        "CD" => "Dem Rep of Congo (Zaire)",
        "DK" => "Denmark",
        "DJ" => "Djibouti",
        "DM" => "Dominica",
        "DO" => "Dominican Republic",
        "TP" => "East Timor",
        "EC" => "Ecuador",
        "EG" => "Egypt",
        "SV" => "El Salvador",
        "GQ" => "Equatorial Guinea",
        "ER" => "Eritrea",
        "EE" => "Estonia",
        "ET" => "Ethiopia",
        "FK" => "Falkland Islands (Malvinas)",
        "FO" => "Faroe Islands",
        "FJ" => "Fiji",
        "FI" => "Finland",
        "FR" => "France",
        "GF" => "French Guiana",
        "PF" => "French Polynesia",
        "TF" => "French Southern Territories",
        "GA" => "Gabon",
        "GM" => "Gambia",
        "GE" => "Georgia",
        "DE" => "Germany",
        "GH" => "Ghana",
        "GI" => "Gibraltar",
        "GR" => "Greece",
        "GL" => "Greenland",
        "GD" => "Grenada",
        "GP" => "Guadeloupe",
        "GU" => "Guam",
        "GT" => "Guatemala",
        "GN" => "Guinea",
        "GW" => "Guinea-Bissau",
        "GY" => "Guyana",
        "HT" => "Haiti",
        "HM" => "Heard and McDonald Islands",
        "HN" => "Honduras",
        "HK" => "Hong Kong SAR, PRC",
        "HU" => "Hungary",
        "IS" => "Iceland",
        "IN" => "India",
        "ID" => "Indonesia",
        "IR" => "Iran",
        "IQ" => "Iraq",
        "IE" => "Ireland",
        "IL" => "Israel",
        "IT" => "Italy",
        "JM" => "Jamaica",
        "JP" => "Japan",
        "JO" => "Jordan",
        "KZ" => "Kazakhstan",
        "KE" => "Kenya",
        "KI" => "Kiribati",
        "KR" => "Korea",
        "KW" => "Kuwait",
        "KG" => "Kyrgyzstan",
        "LA" => "Lao",
        "LV" => "Latvia",
        "LB" => "Lebanon",
        "LS" => "Lesotho",
        "LR" => "Liberia",
        "LY" => "Libya",
        "LI" => "Liechtenstein",
        "LT" => "Lithuania",
        "LU" => "Luxembourg",
        "MO" => "Macao",
        "MK" => "Macedonia",
        "MG" => "Madagascar",
        "MW" => "Malawi",
        "MY" => "Malaysia",
        "MV" => "Maldives",
        "ML" => "Mali",
        "MT" => "Malta",
        "MH" => "Marshall Islands",
        "MQ" => "Martinique",
        "MR" => "Mauritania",
        "MU" => "Mauritius",
        "YT" => "Mayotte",
        "MX" => "Mexico",
        "FM" => "Micronesia",
        "MD" => "Moldova",
        "MC" => "Monaco",
        "MN" => "Mongolia",
        "MS" => "Montserrat",
        "MA" => "Morocco",
        "MZ" => "Mozambique",
        "MM" => "Myanmar",
        "NA" => "Namibia",
        "NR" => "Nauru",
        "NP" => "Nepal",
        "NL" => "Netherlands",
        "AN" => "Netherlands Antilles",
        "NC" => "New Caledonia",
        "NZ" => "New Zealand",
        "NI" => "Nicaragua",
        "NE" => "Niger",
        "NG" => "Nigeria",
        "NU" => "Niue",
        "NF" => "Norfolk Island",
        "MP" => "Northern Mariana Islands",
        "NO" => "Norway",
        "OM" => "Oman",
        "PK" => "Pakistan",
        "PW" => "Palau",
        "PA" => "Panama",
        "PG" => "Papua new Guinea",
        "PY" => "Paraguay",
        "PE" => "Peru",
        "PH" => "Philippines",
        "PN" => "Pitcairn",
        "PL" => "Poland",
        "PT" => "Portugal",
        "PR" => "Puerto Rico",
        "QA" => "Qatar",
        "RE" => "Reunion",
        "RO" => "Romania",
        "RU" => "Russia",
        "RW" => "Rwanda",
        "KN" => "Saint Kitts and Nevis",
        "LC" => "Saint Lucia",
        "VC" => "Saint Vincent and Grenadines",
        "WS" => "Samoa",
        "SM" => "San Marino",
        "ST" => "Sao Tome and Principe",
        "SA" => "Saudi Arabia",
        "SN" => "Senegal",
        "SC" => "Seychelles",
        "SL" => "Sierra Leone",
        "SG" => "Singapore",
        "SK" => "Slovak Republic",
        "SI" => "Slovenia",
        "SB" => "Solomon Islands",
        "SO" => "Somalia",
        "ZA" => "South Africa",
        "GS" => "South Georgia",
        "ES" => "Spain",
        "LK" => "Sri Lanka",
        "SH" => "St Helena",
        "PM" => "St Pierre and Miquelon",
        "SD" => "Sudan",
        "SR" => "Suriname",
        "SJ" => "Svalbard and Jan Mayen Islands",
        "SZ" => "Swaziland",
        "SE" => "Sweden",
        "CH" => "Switzerland",
        "SY" => "Syria",
        "TW" => "Taiwan Region",
        "TJ" => "Tajikistan",
        "TZ" => "Tanzania",
        "TH" => "Thailand",
        "TG" => "Togo",
        "TK" => "Tokelau",
        "TO" => "Tonga",
        "TT" => "Trinidad and Tobago",
        "TN" => "Tunisia",
        "TR" => "Turkey",
        "TM" => "Turkmenistan",
        "TC" => "Turks and Caicos Islands",
        "TV" => "Tuvalu",
        "UG" => "Uganda",
        "UA" => "Ukraine",
        "AE" => "United Arab Emirates",
        "UK" => "United Kingdom",
        "US" => "United States",
        "UM" => "United States Minor Outlying Islands",
        "UY" => "Uruguay",
        "UZ" => "Uzbekistan",
        "VU" => "Vanuatu",
        "VA" => "Vatican City State (Holy See)",
        "VE" => "Venezuela",
        "VN" => "Vietnam",
        "VG" => "Virgin Islands (British)",
        "VI" => "Virgin Islands (US)",
        "WF" => "Wallis and Futuna Islands",
        "EH" => "Western Sahara",
        "YE" => "Yemen",
        "YU" => "Yugoslavia",
        "ZM" => "Zambia",
        "ZW" => "Zimbabwe"
    );

    return $countries;
}

function getTimezoneByAreacodes(){
    return array('201'=>'EST',
    '202'=>'EST',
    '203'=>'EST',
    '205'=>'CST',
    '206'=>'PST',
    '207'=>'EST',
    '208'=>'MST',
    '209'=>'PST',
    '210'=>'CST',
    '212'=>'EST',
    '213'=>'PST',
    '214'=>'CST',
    '215'=>'EST',
    '216'=>'EST',
    '217'=>'CST',
    '218'=>'CST',
    '219'=>'CST',
    '224'=>'CST',
    '225'=>'CST',
    '228'=>'CST',
    '229'=>'EST',
    '231'=>'EST',
    '234'=>'EST',
    '239'=>'EST',
    '240'=>'EST',
    '248'=>'EST',
    '251'=>'CST',
    '252'=>'EST',
    '253'=>'PST',
    '254'=>'CST',
    '256'=>'CST',
    '260'=>'EST',
    '262'=>'CST',
    '267'=>'EST',
    '269'=>'EST',
    '270'=>'CST',
    '272'=>'EST',
    '276'=>'EST',
    '281'=>'CST',
    '301'=>'EST',
    '302'=>'EST',
    '303'=>'MST',
    '304'=>'EST',
    '305'=>'EST',
    '307'=>'MST',
    '308'=>'CST',
    '309'=>'CST',
    '310'=>'PST',
    '312'=>'CST',
    '313'=>'EST',
    '314'=>'CST',
    '315'=>'EST',
    '316'=>'CST',
    '317'=>'EST',
    '318'=>'CST',
    '319'=>'CST',
    '320'=>'CST',
    '321'=>'EST',
    '323'=>'PST',
    '325'=>'CST',
    '330'=>'EST',
    '331'=>'CST',
    '334'=>'CST',
    '336'=>'EST',
    '337'=>'CST',
    '339'=>'EST',
    '346'=>'CST',
    '347'=>'EST',
    '351'=>'EST',
    '352'=>'EST',
    '360'=>'PST',
    '361'=>'CST',
    '364'=>'CST',
    '385'=>'MST',
    '386'=>'EST',
    '401'=>'EST',
    '402'=>'CST',
    '404'=>'EST',
    '405'=>'CST',
    '406'=>'MST',
    '407'=>'EST',
    '408'=>'PST',
    '409'=>'CST',
    '410'=>'EST',
    '412'=>'EST',
    '413'=>'EST',
    '414'=>'CST',
    '415'=>'PST',
    '417'=>'CST',
    '419'=>'EST',
    '423'=>'EST',
    '424'=>'PST',
    '425'=>'PST',
    '430'=>'CST',
    '432'=>'CST',
    '434'=>'EST',
    '435'=>'MST',
    '440'=>'EST',
    '442'=>'PST',
    '443'=>'EST',
    '456'=>'',
    '458'=>'PST',
    '469'=>'CST',
    '470'=>'EST',
    '475'=>'EST',
    '478'=>'EST',
    '479'=>'CST',
    '480'=>'MST',
    '484'=>'EST',
    '500'=>'',
    '501'=>'CST',
    '502'=>'EST',
    '503'=>'PST',
    '504'=>'CST',
    '505'=>'MST',
    '507'=>'CST',
    '508'=>'EST',
    '509'=>'PST',
    '510'=>'PST',
    '512'=>'CST',
    '513'=>'EST',
    '515'=>'CST',
    '516'=>'EST',
    '517'=>'EST',
    '518'=>'EST',
    '520'=>'MST',
    '530'=>'PST',
    '531'=>'CST',
    '533'=>'',
    '534'=>'CST',
    '539'=>'CST',
    '540'=>'EST',
    '541'=>'PST',
    '544'=>'',
    '551'=>'EST',
    '559'=>'PST',
    '561'=>'EST',
    '562'=>'PST',
    '563'=>'CST',
    '566'=>'',
    '567'=>'EST',
    '570'=>'EST',
    '571'=>'EST',
    '573'=>'CST',
    '574'=>'EST',
    '575'=>'MST',
    '580'=>'CST',
    '585'=>'EST',
    '586'=>'EST',
    '601'=>'CST',
    '602'=>'MST',
    '603'=>'EST',
    '605'=>'CST',
    '606'=>'EST',
    '607'=>'EST',
    '608'=>'CST',
    '609'=>'EST',
    '610'=>'EST',
    '611'=>'',
    '612'=>'CST',
    '614'=>'EST',
    '615'=>'CST',
    '616'=>'EST',
    '617'=>'EST',
    '618'=>'CST',
    '619'=>'PST',
    '620'=>'CST',
    '623'=>'MST',
    '626'=>'PST',
    '630'=>'CST',
    '631'=>'EST',
    '636'=>'CST',
    '641'=>'CST',
    '646'=>'EST',
    '650'=>'PST',
    '651'=>'CST',
    '657'=>'PST',
    '660'=>'CST',
    '661'=>'PST',
    '662'=>'CST',
    '667'=>'EST',
    '669'=>'PST',
    '678'=>'EST',
    '681'=>'EST',
    '682'=>'CST',
    '700'=>'',
    '701'=>'CST',
    '702'=>'PST',
    '703'=>'EST',
    '704'=>'EST',
    '706'=>'EST',
    '707'=>'PST',
    '708'=>'CST',
    '710'=>'',
    '712'=>'CST',
    '713'=>'CST',
    '714'=>'PST',
    '715'=>'CST',
    '716'=>'EST',
    '717'=>'EST',
    '718'=>'EST',
    '719'=>'MST',
    '720'=>'MST',
    '724'=>'EST',
    '725'=>'PST',
    '727'=>'EST',
    '731'=>'CST',
    '732'=>'EST',
    '734'=>'EST',
    '737'=>'CST',
    '740'=>'EST',
    '747'=>'PST',
    '754'=>'EST',
    '757'=>'EST',
    '760'=>'PST',
    '762'=>'EST',
    '763'=>'CST',
    '765'=>'EST',
    '769'=>'CST',
    '770'=>'EST',
    '772'=>'EST',
    '773'=>'CST',
    '774'=>'EST',
    '775'=>'PST',
    '779'=>'CST',
    '781'=>'EST',
    '785'=>'CST',
    '786'=>'EST',
    '800'=>'',
    '801'=>'MST',
    '802'=>'EST',
    '803'=>'EST',
    '804'=>'EST',
    '805'=>'PST',
    '806'=>'CST',
    '808'=>'HAST',
    '809'=>'AST',
    '810'=>'EST',
    '812'=>'EST',
    '813'=>'EST',
    '814'=>'EST',
    '815'=>'CST',
    '816'=>'CST',
    '817'=>'CST',
    '818'=>'PST',
    '828'=>'EST',
    '830'=>'CST',
    '831'=>'PST',
    '832'=>'CST',
    '843'=>'EST',
    '844'=>'',
    '845'=>'EST',
    '847'=>'CST',
    '848'=>'EST',
    '850'=>'CST',
    '855'=>'',
    '856'=>'EST',
    '857'=>'EST',
    '858'=>'PST',
    '859'=>'EST',
    '860'=>'EST',
    '862'=>'EST',
    '863'=>'EST',
    '864'=>'EST',
    '865'=>'EST',
    '866'=>'',
    '870'=>'CST',
    '872'=>'CST',
    '877'=>'',
    '878'=>'EST',
    '880'=>'',
    '881'=>'',
    '888'=>'',
    '900'=>'',
    '901'=>'CST',
    '903'=>'CST',
    '904'=>'EST',
    '906'=>'EST',
    '907'=>'AKST',
    '908'=>'EST',
    '909'=>'PST',
    '910'=>'EST',
    '912'=>'EST',
    '913'=>'CST',
    '914'=>'EST',
    '915'=>'MST',
    '916'=>'PST',
    '917'=>'EST',
    '918'=>'CST',
    '919'=>'EST',
    '920'=>'CST',
    '925'=>'PST',
    '928'=>'MST',
    '929'=>'EST',
    '931'=>'CST',
    '936'=>'CST',
    '937'=>'EST',
    '938'=>'CST',
    '940'=>'CST',
    '941'=>'EST',
    '947'=>'EST',
    '949'=>'PST',
    '951'=>'PST',
    '952'=>'CST',
    '954'=>'EST',
    '956'=>'CST',
    '959'=>'EST',
    '970'=>'MST',
    '971'=>'PST',
    '972'=>'CST',
    '973'=>'EST',
    '978'=>'EST',
    '979'=>'CST',
    '980'=>'EST',
    '984'=>'EST',
    '985'=>'CST',
    '989'=>'EST');
}



function form_dropdown_from_db($name = '', $sql, $selected = array(), $extra = '') {
    $CI = & get_instance();
    #echo ">>".$selected;exit;
    if (!is_array($selected)) {
        $selected = array($selected);
    }

    // If no selected state was submitted we will attempt to set it automatically
    if (count($selected) === 0) {
        // If the form name appears in the $_POST array we have a winner!
        if (isset($_POST[$name])) {
            $selected = array($_POST[$name]);
        }
    }

    if ($extra != '')
        $extra = ' ' . $extra;

    $multiple = (count($selected) > 1 && strpos($extra, 'multiple') === FALSE) ? ' multiple="multiple"' : '';
    #print_r($selected);exit;
    $form = '<select name="' . $name . '"  id="' . $name . '" ' . $extra . $multiple . ">\n";
    $query = $CI->db->query($sql);
    if ($query->num_rows() > 0) {
        //echo '<pre>';print_r($query->result_array());exit;
        foreach ($query->result_array() as $row) {
            $values = array_values($row);
            if (count($values) === 2) {
                $key = (string) $values[0];
                $val = (string) $values[1];
                //$this->option($values[0], $values[1]);
            }

            $sel = (in_array($key, $selected)) ? ' selected' : '';

            $form .= '<option ' . $sel . ' value="' . $key . '">' . $val . "</option>\n";
        }
    }
    $form .= '</select>';
    return $form;
}

if (!function_exists('get_country_name')) {

    function get_country_name($key=NULL) {
        $country_arr = get_countries_array();
        $value = "";
        if (array_key_exists($key, $country_arr)) {
            $value = $country_arr[$key];
        }
        return $value;
    }

}

if (!function_exists('get_when_used')) {

    function get_when_used($key=NULL) {
        $when_array = array("C" => "Current", "P" => "Past", "N" => "Null");
        $value = "";
        if (array_key_exists($key, $when_array)) {
            $value = $when_array[$key];
        }
        return $value;
    }

}
if (!function_exists('get_where_used')) {

    function get_where_used($key=NULL) {
        $where_array = array("P" => "Personal", "PR" => "Private", "C" => "At a Company", "N" => "Null");
        $value = "";
        if (array_key_exists($key, $where_array)) {
            $value = $where_array[$key];
        }
        return $value;
    }

}


if (!function_exists('get_yesno')) {

    function get_yesno() {
        $yesno = array("" => "--SELECT--",
            "1" => "Yes",
            "0" => "No"
        );
        return $yesno;
    }

}


if (!function_exists('dropdown_from_array')) {

    function dropdown_from_array($name = '', $options = array(), $selected = array(), $extra = '') {
        if (!is_array($selected)) {
            $selected = array($selected);
        }

        // If no selected state was submitted we will attempt to set it automatically
        if (count($selected) === 0) {
            // If the form name appears in the $_POST array we have a winner!
            if (isset($_POST[$name])) {
                $selected = array($_POST[$name]);
            }
        }

        if ($extra != '')
            $extra = ' ' . $extra;

        $multiple = (count($selected) > 1 && strpos($extra, 'multiple') === FALSE) ? ' multiple="multiple"' : '';

        $form = '<select name="' . $name . '"' . $extra . $multiple . ">\n";
        $form .= '<option value="">--SELECT--</option>\n';
        foreach ($options as $key => $val) {
            $key = (string) $key;

            if (is_array($val) && !empty($val)) {
                $form .= '<optgroup label="' . $key . '">' . "\n";

                foreach ($val as $optgroup_key => $optgroup_val) {
                    $sel = (in_array($optgroup_key, $selected)) ? ' selected="selected"' : '';

                    $form .= '<option value="' . $optgroup_key . '"' . $sel . '>' . (string) $optgroup_val . "</option>\n";
                }

                $form .= '</optgroup>' . "\n";
            } else {
                $sel = (in_array($key, $selected)) ? ' selected="selected"' : '';

                $form .= '<option value="' . $key . '"' . $sel . '>' . (string) $val . "</option>\n";
            }
        }

        $form .= '</select>';

        return $form;
    }

}
function MYSQLConnectOFDefaultDB(){
    $db = (array)get_instance()->db;
    return mysqli_connect($db['hostname'], $db['username'], $db['password'], $db['database']);
}

function SQLInjectionOFDefaultDB($string,$connection=null){
    if(empty($connection)){
        $connection = MYSQLConnectOFDefaultDB();
    }
        return mysqli_real_escape_string($connection,$string);
}

function SQLInjectionOFEGDB($string){
    $CI =& get_instance();
    $db2 = $CI->load->database('db2', TRUE);
    $eg_db = (array)$db2;

    $connection =  mysqli_connect($eg_db['hostname'], $eg_db['username'], $eg_db['password'], $eg_db['database']);
    return mysqli_real_escape_string($connection,$string);
}
function MYSQLConnectOFEGDB(){
    $CI =& get_instance();
    $db2 = $CI->load->database('db2', TRUE);
    $eg_db = (array)$db2;

    return  mysqli_connect($eg_db['hostname'], $eg_db['username'], $eg_db['password'], $eg_db['database']);
}
function php_dateformat($datestr){
        $date = date_create($datestr);
		return date_format($date, 'm/d/y');
}
function php_datetimeformat($datestr){
        $date = date_create($datestr);
		return date_format($date, 'm/d/y g:i A');
}
function limit_words($string, $limit)
	{
		$string = preg_replace('/\s/',' ', $string);

		$array = array_diff(explode(" ", $string), array( '' ));
		
	   if (count($array)<=$limit)
		{
			$returned_val['start'] =  $string;
			$returned_val['end'] =  "";
	   }
		else
		{
		   $end_content=array_splice($array, $limit);
		   $returned_val['start'] = implode(" ", $array);
		   $returned_val['end'] = implode(" ", $end_content);
		}
		return $returned_val;
	}
if ( ! function_exists('_decode_auth_cookie'))
{
    function _decode_auth_cookie($value) {
        $decoded_value = substr($value, 3);
        $decoded_value = substr($decoded_value, 0, -3);
        return base64_decode($decoded_value);
    }
}
function format_array($data, $key, $value)
{
    $new_array = array();

    foreach ($data as $record) {
        $new_array[$record[$key]] = $record[$value];
    }

    return $new_array;
}