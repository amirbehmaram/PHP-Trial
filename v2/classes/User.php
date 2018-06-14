<?php
/* User Class:
 * Each user should have first name, last name, email, phone, lat coords, and lon coords
 */

 namespace NobleStudios\php_trials\classes;

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
   public function createUser(array $data_array) {
     $new_user = new User();
     $iter = 0;

     // Pass $value in by reference so we ca actually edit it
     foreach ($new_user as $key => &$value) {
       $value = $data_array[$iter];
       $iter++;
     }

     return $new_user;
   }
 }
