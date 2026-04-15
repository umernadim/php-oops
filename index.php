<?php
include 'database.php';

$obj = new Database();
$obj->insert('student', ['student_name' => 'Ali Ahmad', 'age'=>23, 'city'=>'karachi']);

echo "Insert result is: ";
print_r($obj->getResult());