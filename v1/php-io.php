<?php
/* This is a script to solve the php trials problem at:
 * https://www.notion.so/Code-Learnings-c1f8893c908647cd91a49504192127d3
 */

/* User Class:
 * Each user should have first name, last name, email, phone, lat coords, and lon coords
 */
 class User {
   public $first_name = '';
   public $last_name = '';
   public $email = '';
   public $phone = '';
   public $latitude = '';
   public $longitude = '';
   public $password = '';

   /* User Class Function that takes in an array of values ordered in the same manner
    * as the classes members and updates the member's values to the values
    * in the array
    */
   public function User(array $dataArray) {
     $iter = 0;

     // Pass $value in by reference so we ca actually edit it
     foreach ($this as $key => &$value) {
       $value = $dataArray[$iter];
       $iter++;
     }
   }
 }

 // Function to parse a file line and create a User from it
 function getUserData($fileLine) {
   $dataArr = explode(",", $fileLine);
   $newUser = new User($dataArr);

   return $newUser;
 }

 // Function to generate a strong 9 character password
 function generatePassword(int $length) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?";
    $password = substr( str_shuffle( $chars ), 0, $length );
    return $password;
 }

 // Comparision function to be used for the ordering of the user array
 function cmp($a, $b)
 {
   return strcmp($a->first_name, $b->first_name);
 }

 // Global Variables
 $userArray = array();
 $dataFile = fopen("data.csv", "r") or die("Can't open data.csv file.");

 // Get first line of file
 $columnNames = fgets($dataFile);

 // Get each line from the file, create a user, generate their password, push them on to the user array
 while(!feof($dataFile)) {
   $fileLine = fgets($dataFile);

   // To clean the line of any end line characters of carriage returns
   $fileLine = str_replace(array('.', ' ', "\n", "\t", "\r"), '', $fileLine);

   $newUser = getUserData($fileLine);
   $newPass = generatePassword(9);
   $newUser->password = $newPass;
   
   array_push($userArray, $newUser);
 }

 fclose($dataFile);

 // Order the user array alphabetically by first name
 usort($userArray, "cmp");

 // Export to JSON file
 $json = json_encode($userArray);
 $file = fopen('sorted-user-data.json','w+');
 fwrite($file, $json);
 fclose($file);

?>
