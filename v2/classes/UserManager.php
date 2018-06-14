<?php
/* User Manager Class:
 *
 */
 namespace NobleStudios\php_trials\classes;

 require "./classes/User.php";
 require "./classes/DBConnect.php";

 use NobleStudios\php_trials\classes\User as User;
 use NobleStudios\php_trials\classes\DBConnect;

 class UserManager {

   /*   -------------   */
   /*   Class Members   */
   /*   -------------   */

   protected $mysqli;

   /*   --------------   */
   /*   Public classes   */
   /*   --------------   */

   public function StartManager($file_name) {

     // Connect to the mysql server
     $this->mysqli = DBConnect::init();

     // Check if the mysqli succeeded
     if (!$this->mysqli) {
       echo $this->mysqli->mysqli_connect_error() . PHP_EOL;
       die;
     }

     // Try to create DB PHP_TRIALS. If it fails, Die
     if(!$db_creation = $this->createDatabase()) {
       // If the db creation fails, die
       die();
     }

     // First select the db to use: PHP_TRIALS
     $use_db = "USE PHP_TRIALS";
     $this->mysqli->query($use_db);

     // Check if the table exists
     $table_exists_query = "SELECT 1 FROM Users LIMIT 1";
     if (!$table_result = $this->mysqli->query($table_exists_query)) {

       // Oh no! the query failed: so the table doesnt exist! Create the table
       if(!$table_creation = $this->createTable()) {
         // If the table creation fails, die
         die();
       }

       //Get the info from the data file
       $user_array = $this->extractUserArray($file_name);

       // Loop through the user array, build an insert query, then insert it
       $this->insertUsers($user_array);
     }
   }

   // Prints the user file out into a json file
   public function outputJSON() {
     //Get the info from the data file
     $user_array = $this->extractUserArray("./data.csv");

     // Export to JSON file
     $json = json_encode($user_array);
     $file = fopen('sorted-user-data.json','w+');
     fwrite($file, $json);
     fclose($file);
   }

   // Get the user info from the db based on the given email
   public function getUser($user_email) {
     echo "Getting user: " . $user_email . PHP_EOL;

     $user_query = "SELECT * FROM Users WHERE email = '$user_email' LIMIT 1";

     if (!$user_result = $this->mysqli->query($user_query)) {
       echo "Failed to find the user" . $user_result->error . PHP_EOL;
     }
     else {
       // Since we did a LIMIT 1, I won't loop through fetch_assoc()
       $user = $user_result->fetch_assoc();
       printf("Name: %s %s\n", $user["first_name"], $user["last_name"]);
       printf("Phone: %s\n", $user["phone"]);
       printf("Latitude & Longitude: %s | %s\n", $user["latitude"], $user["longitude"]);
     }
   }

   // Get all users and print them to the screen.
   public function getAllUsers() {
     echo "Getting all users: " . PHP_EOL;

     $user_query = "SELECT * FROM Users";

     if (!$user_result = $this->mysqli->query($user_query)) {
       echo "Failed to get users" . $user_result->error . PHP_EOL;
     }
     else {
       while($user = $user_result->fetch_assoc()) {
         printf("Name: %s %s\n", $user["first_name"], $user["last_name"]);
         printf("Phone: %s\n", $user["phone"]);
         printf("Latitude & Longitude: %s | %s\n", $user["latitude"], $user["longitude"]);
         print "-----------------------------------------------" . PHP_EOL;
       }
     }
   }

   // Delete a single user from the db based on the email given
   public function deleteUser($user_email) {
     echo "Deleting user: " . $user_email . "..." .PHP_EOL;

     $delete_query = "DELETE FROM Users WHERE email = '$user_email' LIMIT 1";

     if (!$delete_result = $this->mysqli->query($delete_query)) {
       echo "Failed to delete the user" . $delete_result->error . PHP_EOL;
     }
     else {
       echo "Success! User: " . $user_email . " was deleted." . PHP_EOL;
     }
   }

   // Need to be given a first/last name, phone, email, lon, & lat.
   // Generate the password before insertion
   public function addUser($first, $last, $email, $phone, $lat, $lon) {
     $new_pass = $this->generatePassword(9);

     $insert_query = "INSERT INTO Users (first_name, last_name, email, phone, latitude, longitude, password)
                      VALUES ('$first', '$last', '$email', '$phone', '$lat', '$lon', '$new_pass')";

     if (!$insert_result = $this->mysqli->query($insert_query)) {
       echo "Failed to insert the user" . $insert_result->error . \mysqli_error($this->mysqli) . PHP_EOL;
     }

     echo "Success! User " . $first . " " . $last . " was inserted!" . PHP_EOL;
   }

   /*   ---------------   */
   /*   Private classes   */
   /*   ---------------   */

   // Function to parse a file line and create a User from it
   private function parseUserData($file_line) {
     $data_arr = explode(",", $file_line);
     $new_user = new User();
     $new_user = $new_user->createUser($data_arr);

     return $new_user;
   }

   // Function to generate a strong 9 character password
   private function generatePassword(int $length) {
      $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?";
      $password = substr( str_shuffle( $chars ), 0, $length );
      return $password;
   }

   // Extract user information from the csv file, generate their passwords, and then push to the user array
   private function extractUserArray($file_name) {
     $user_array = array();

     $data_file = fopen($file_name, "r") or die("Can't open data.csv file.");

     // Get first line of file : Can be used for later, currently unused
     $column_names = fgets($data_file);

     // Get each line from the file, create a user, generate their password, push them on to the user array
     while(!feof($data_file)) {
       $file_line = fgets($data_file);

       // To clean the line of any end line characters of carriage returns
       $file_line = str_replace(array(' ', "\n", "\t", "\r"), '', $file_line);

       if(!(strlen($file_line) <= 1)) {
         $new_user = $this->parseUserData($file_line);
         $new_pass = $this->generatePassword(9);
         $new_user->password = $new_pass;

         array_push($user_array, $new_user);
       }
     }

     fclose($data_file);

     // Order the user array alphabetically by first name
     usort($user_array, function ($a, $b) {
       return strcmp($a->first_name, $b->first_name);
     });

     return $user_array;
   }

   // Try to create the db. If it fails return false, otherwise, return true
   private function createDatabase () {
     $db_exists_query = "CREATE DATABASE IF NOT EXISTS PHP_TRIALS";

     if (!$db_result = $this->mysqli->query($db_exists_query)) {
       echo "Database creation failed" . $db_result->error . PHP_EOL;
       return false;
     }

     return true;
   }

   // Try to create the table. If it fails return false, otherwise, return true
   private function createTable() {
     $create_table = "CREATE TABLE Users( id INT AUTO_INCREMENT, first_name VARCHAR(64),
                                 last_name VARCHAR(64), email VARCHAR(64),
                                 phone VARCHAR(24), latitude VARCHAR(128),
                                 longitude VARCHAR(128), password VARCHAR(256),
                                 primary key (id));";

     if (!$create_table_result = $this->mysqli->query($create_table)) {
       echo "Table creation failed" . " " . \mysqli_error($this->mysqli) . PHP_EOL;
       return false;
     }

     return true;
   }

   /* Go through the user array and try to insert them. If an error
    * occurs, log the error, keep going.
   */
   private function insertUsers($user_array) {
     foreach ($user_array as $user) {
       $first = $this->mysqli->real_escape_string($user->first_name);
       $last = $this->mysqli->real_escape_string($user->last_name);
       $email = $this->mysqli->real_escape_string($user->email);
       $phone = $this->mysqli->real_escape_string($user->phone);
       $lat = $this->mysqli->real_escape_string($user->latitude);
       $lon = $this->mysqli->real_escape_string($user->longitude);
       $pass = $this->mysqli->real_escape_string($user->password);

       $query ="INSERT INTO Users (first_name, last_name, email, phone,
                                   latitude, longitude, password)
                VALUES ('$first', '$last', '$email', '$phone', '$lat', '$lon', '$pass')";

       if (!$insert_result = $this->mysqli->query($query)) {
         echo "Inserting a user failed " . \mysqli_error($this->mysqli) . PHP_EOL;
       }
     }
   }
 }
