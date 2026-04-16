<?php
include 'database.php';

$obj = new Database();
//$obj->insert('student', ['student_name' => 'Ali Ahmad', 'age'=>23, 'city'=>'karachi']);

//echo "Insert result is: ";
//print_r($obj->getResult());

$obj->update('student', ['student_name' => 'Ali', 'age'=>23, 'city'=>'karachi'], 'id="3"');

echo "Update result is: ";
print_r($obj->getResult());