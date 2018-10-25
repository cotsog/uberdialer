<?php
ini_set("max_execution_time", 0);
ini_set("set_time_limit", 0);
ini_set('memory_limit', '-1');
class ExportExcel
{
	//variable of the class
	var $titles=array();
	var $all_values=array();
	var $filename;
	
	//functions of the class
	function ExportExcel($f_name) //constructor
	{
		$this->filename=$f_name;
	}
	function setHeadersAndValues($hdrs,$all_vals) //set headers and query
	{
		$this->titles=$hdrs;
		$this->all_values=$all_vals;
	}
	function GenerateExcelFile() //function to generate excel file
	{
		$header="";
                $data="";
		foreach ($this->titles as $title_val) 
 		{ 
 			$header .= $title_val."\t"; 
 		} 
 		for($i=0;$i<sizeof($this->all_values);$i++) 
 		{ 
 			$line = ''; 
 			foreach($this->all_values[$i] as $value) 
			{ 
 				if ((!isset($value)) OR ($value == "")) 
				{ 
 					$value = "\t"; 
 				} //end of if
				else 
				{ 
 					$value = str_replace('"', '""', $value); 
 					$value = '"' . $value . '"' . "\t"; 
 				} //end of else
 				$line .= $value; 
 			} //end of foreach
 			$data .= trim($line)."\n"; 
 		}//end of the while 
 		$data = str_replace("\r", "", $data); 
		if ($data == "") 
 		{ 
 			$data = "\n(0) Records Found!\n"; 
 		} 
		//echo $data;
		header("Content-type: application/vnd.ms-excel"); 
		header("Content-Disposition: attachment; filename=$this->filename"); 
		header("Pragma: no-cache"); 
		header("Expires: 0"); 
		print "$header\n$data";  
	
	
	}

}
class Excel_XML
{

    /**
     * Header of excel document (prepended to the rows)
     * 
     * Copied from the excel xml-specs.
     * 
     * @access private
     * @var string
     */
    private $header = "<?xml version=\"1.0\" encoding=\"UTF-8\"?\>
<Workbook xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\"
 xmlns:x=\"urn:schemas-microsoft-com:office:excel\"
 xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\"
 xmlns:html=\"http://www.w3.org/TR/REC-html40\">";

    /**
     * Footer of excel document (appended to the rows)
     * 
     * Copied from the excel xml-specs.
     * 
     * @access private
     * @var string
     */
    private $footer = "</Workbook>";

    /**
     * Document lines (rows in an array)
     * 
     * @access private
     * @var array
     */
    private $lines = array ();

    /**
     * Worksheet title
     *
     * Contains the title of a single worksheet
     *
     * @access private 
     * @var string
     */
    private $worksheet_title = "Table1";

    /**
     * Add a single row to the $document string
     * 
     * @access private
     * @param array 1-dimensional array
     * @todo Row-creation should be done by $this->addArray
     */
    private function addRow ($array)
    {

        // initialize all cells for this row
        $cells = "";

        // foreach key -> write value into cells
        foreach ($array as $k => $v):

            $cells .= "<Cell><Data ss:Type=\"String\">" . utf8_encode($v) . "</Data></Cell>\n"; 

        endforeach;

        // transform $cells content into one row
        $this->lines[] = "<Row>\n" . $cells . "</Row>\n";

    }

    /**
     * Add an array to the document
     * 
     * This should be the only method needed to generate an excel
     * document.
     * 
     * @access public
     * @param array 2-dimensional array
     * @todo Can be transfered to __construct() later on
     */
    public function addArray ($array)
    {

        // run through the array and add them into rows
        foreach ($array as $k => $v):
            $this->addRow ($v);
        endforeach;

    }

    /**
     * Set the worksheet title
     * 
     * Checks the string for not allowed characters (:\/?*),
     * cuts it to maximum 31 characters and set the title. Damn
     * why are not-allowed chars nowhere to be found? Windows
     * help's no help...
     *
     * @access public
     * @param string $title Designed title
     */
    public function setWorksheetTitle ($title)
    {

        // strip out special chars first
        $title = preg_replace ("/[\\\|:|\/|\?|\*|\[|\]]/", "", $title);

        // now cut it to the allowed length
        $title = substr ($title, 0, 31);

        // set title
        $this->worksheet_title = $title;

    }

    /**
     * Generate the excel file
     * 
     * Finally generates the excel file and uses the header() function
     * to deliver it to the browser.
     * 
     * @access public
     * @param string $filename Name of excel file to generate (...xls)
     */
    function generateXML ($filename)
    {

        // deliver header (as recommended in php manual)
        header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
        header("Content-Disposition: inline; filename=\"" . $filename . ".xls\"");

        // print out document to the browser
        // need to use stripslashes for the damn ">"
        echo stripslashes ($this->header);
        echo "\n<Worksheet ss:Name=\"" . $this->worksheet_title . "\">\n<Table>\n";
        echo "<Column ss:Index=\"1\" ss:AutoFitWidth=\"0\" ss:Width=\"110\"/>\n";
        echo implode ("\n", $this->lines);
        echo "</Table>\n</Worksheet>\n";
        echo $this->footer;

    }

}

class ExportCSV
{
	//variable of the class
	var $titles=array();
	var $all_values=array();
	var $filename;
	
	//functions of the class
	function ExportCSV($f_name) //constructor
	{
		$this->filename=$f_name;
	}
	function setHeadersAndValues($hdrs,$all_vals) //set headers and query
	{
		$this->titles=$hdrs;
		$this->all_values=$all_vals;
	}
	function GenerateCSVFile() //function to generate excel file
	{
		$header="";
                $data="";
		foreach ($this->titles as $title_val) 
 		{ 
 			$header .= $title_val.","; 
 		} 
		
 		for($i=0;$i<sizeof($this->all_values);$i++) 
 		{ 
 			$line = ''; 
 			foreach($this->all_values[$i] as $value) 
			{ 
 				if ((!isset($value)) OR ($value == "")) 
				{ 
 					$value = ","; 
 				} //end of if
				else 
				{ 
 					$value = str_replace('"', '""', $value); 
 					$value = '"' . $value . '"' . ","; 
 				} //end of else
 				$line .= $value; 
 			} //end of foreach
 			$data .= trim($line)."\n"; 
 		}//end of the while 
 		$data = str_replace("\r", "", $data); 
		if ($data == "") 
 		{ 
 			$data = "\n(0) Records Found!\n"; 
 		} 
		//echo $data;
		header('Content-type: application/csv');
		header("Content-Disposition: attachment; filename=$this->filename"); 
		header("Pragma: no-cache"); 
		header("Expires: 0"); 
		print "$header\n$data";  
	
	
	}

}
?>