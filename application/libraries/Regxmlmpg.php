<?php

class Regxmlmpg
{
    public $regXml;
    public $regRules;
    public $questions;
    public $campaign;
    public $jsRegRules;
    public function __construct()
    {
        //Get reference to CodeIgniter:
        //http://ellislab.com/codeigniter/user-guide/general/creating_libraries.html
        $this->CI = &get_instance();
    }

    function setInfo($campaign)
    {
        $this->campaign = $campaign;
        $this->regXml = simplexml_load_string($campaign->reg_data);
        $this->regRules = unserialize(trim($campaign->reg_rules));
        $this->questions = explode(',', $campaign->questions);
        if(($key = array_search(0, $this->questions)) !== false) {
            unset($this->questions[$key]);
        }
        $this->setRegRules();
        $this->setRegXml();
        $this->setJsRules();
    }

    function setRegXml(){
        if(!empty($this->questions)){
            $idx = 0;
            foreach ($this->regXml->fieldset->field as $field) {
                $include = 'true';
                if(in_array((string) $field->question_id, $this->questions)){
//                    unset($this->regXml->fieldset->field[$idx]);
                    $include = 'false';
                }
                $field->addChild('include', $include);
                $idx++;
            }
        }
    }
    
    function setRegRules(){
        $result = $this->regXml->xpath('//professions');
        if(!empty($result)){
            $profession_xml = $result[0]->saveXML();
            if(strpos($profession_xml, '242') !== false){
                unset($this->regRules['hidden_vals']['Healthcare Business and Administration']['qid_5'],
                $this->regRules['hidden_vals']['Healthcare Business and Administration']['qid_25'],
                $this->regRules['hidden_vals']['Healthcare IT Professional']['qid_5'],
                $this->regRules['hidden_vals']['Healthcare IT Professional']['qid_25']);
            }
        }
    }
    
    function setJsRules(){
        $this->jsRegRules = str_replace('$("#reg_form").validator();', "", $this->regRules['js_rules']);
    }
}

?>