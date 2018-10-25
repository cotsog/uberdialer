<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Uber dialer - How to upload contacts </title>
</head>

<body>
<p><b>Accepted File Type:</b> CSV
    <br><br><b>INSTRUCTIONS:</b>
<ul>
    <li>Only CSV file compressed to a .ZIP file is accepted for upload </br>
        *Right-click on the CSV file. Hover your cursor over the "Send to" option. Then, select "compressed (zipped) folder" on the opened sub-menu
    </li>
    <li>When we upload a new file first row must have combination of following header column.</li>
    <li>Header columns are case insensitive.</li>
    <li>columns sequence should be as shown below:
        <ol>
       <?php
       foreach($headers as $header){
           if($header == 'Member ID'){
               echo "<li><b>{$header} :</b> The column should be included in the file but it is optional/blank.</li>";
           }else if($header == 'Email'){
               echo "<li><b>{$header} :</b> should be email id & unique</li>";
           }else if($header == 'Time Zone'){
               echo "<li><b>{$header} :</b> should be EST/CST/MST/PST</li>";
           }else if($header == 'Ext') {
               echo "<li><b>{$header} :</b> Phone Extension column should be included in the file, but it is optional and can be blank.</li>";
           }else{
               echo "<li><b>{$header}</b></li>";
           }
           
       }
       
       ?>
        </ol>   
    </li>
</ul>
</p>

<ul>
    <h4>
        <li>Create the file in Excel</li>
    </h4>
    <ol>
        <li>Create a list of codes in 21 different columns. <br><br>
                    <img src="https://s3.amazonaws.com/uberdialer/images/contact_file_format.png"><br><br></li>
        <li>Save the file as CSV (Comma delimited).<br><br>
            <img src="https://s3.amazonaws.com/uberdialer/images/save-csv.png"><br><br></li>
        
    </ol>
</ul>
</body>
</html>
