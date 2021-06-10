<?php

use Illuminate\Database\Migrations\Migrations;

require_once 'class.php';



class CreateCommentsTable extends Migrations
{
    
    private $database = DBConnector::getDatabase;

    public function up()
    {
        $createUsers = "CREATE TABLE `users` (
      `id` int(5) NOT NULL,
      `login` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
      `password` text COLLATE utf8mb4_unicode_ci NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $alterUsers1 = "ALTER TABLE `users`
      ADD PRIMARY KEY (`id`);";
        $alterUsers2 = "ALTER TABLE `users`
      MODIFY `id` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;";
        $alterUsers3 = "ALTER TABLE `comments`
        ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`),
        ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`);";
        $database->query($createUsers);
        $database->query($alterUsers1);
        $database->query($alterUsers2);
        $database->query($alterUsers3);
        $database->query("COMMIT;");
    }
    
    public function down()
    {
        $deleteUsers = "DROP TABLE 'users';";
        $database->query($deleteUsers);
        $database->query("COMMIT;");
    }
    
    
   
    /* 
     * To change this license header, choose License Headers in Project Properties.
     * To change this template file, choose Tools | Templates
     * and open the template in the editor.
     */
}

