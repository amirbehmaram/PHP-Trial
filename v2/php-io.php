<?php
/* This is a script to solve the php trials problem at:
 * https://www.notion.so/Code-Learnings-c1f8893c908647cd91a49504192127d3
 */
 require "./classes/UserManager.php";

 use NobleStudios\php_trials\classes\UserManager;

 $user_mngr = new UserManager();

 $user_mngr->StartManager("data.csv");
 $user_mngr->outputJSON();
 $user_mngr->getUser('zjohann5b@tamu.edu');
 $user_mngr->getAllUsers();
 $user_mngr->deleteUser('zjohann5b@tamu.edu');
 $user_mngr->addUser('Amir', 'Behmaram', 'amir@test.com', '775-700-7000', '34.54', '23.5343');
