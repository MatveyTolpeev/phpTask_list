<?php
use Illuminate\Database\Migrations\Migrations;

require_once 'class.php';



class CreateCommentsTable extends Migrations
{
    
    private $database = DBConnector::getDatabase;
    
    public function up()
    {
        $createComments = "CREATE TABLE `comments` (
        `id` int(10) NOT NULL,
        `comment` text COLLATE utf8mb4_unicode_ci NOT NULL,
        `author_id` int(5) NOT NULL,
        `task_id` int(10) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $alterComments1 = "ALTER TABLE `comments`
        ADD PRIMARY KEY (`id`),
        ADD KEY `author_id` (`author_id`),
        ADD KEY `task_id` (`task_id`);";
        $alterComments2 = "ALTER TABLE `comments`
        MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;";
        $alterComments3 = "ALTER TABLE `comments`
        ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`),
        ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`);";
        $database->query($createComments);
        $database->query($alterComments1);
        $database->query($alterComments2);
        $database->query($alterComments3);
        $database->query("COMMIT;");
    }
    
    public function down()
    {
        $deleteComments = "DROP TABLE 'comments';";
        $database->query($deleteComments);
        $database->query("COMMIT;");
    }
}
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

