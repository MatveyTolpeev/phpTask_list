<?php
use Illuminate\Database\Migrations\Migrations;

require_once 'class.php';



class CreateProgressTable extends Migrations
{
    
    private $database = DBConnector::getDatabase;
    
    public function up()
    {
        $createProgress = "CREATE TABLE `progress` (
      `id` int(10) NOT NULL,
      `text` text COLLATE utf8mb4_unicode_ci NOT NULL,
      `check_point` int(5) NOT NULL,
      `author_id` int(5) NOT NULL,
      `task_id` int(10) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $alterProgress1 = "ALTER TABLE `progress`
      ADD PRIMARY KEY (`id`),
      ADD KEY `author_id` (`author_id`),
      ADD KEY `task_id` (`task_id`);";
        $alterProgress2 = " ALTER TABLE `progress`
      MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;";
        $alterProgress3 = "ALTER TABLE `progress`
      ADD CONSTRAINT `progress_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`),
      ADD CONSTRAINT `progress_ibfk_2` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`);";
        $database->query($createProgress);
        $database->query($alterProgress1);
        $database->query($alterProgress2);
        $database->query($alterProgress3);
        $database->query("COMMIT;");
    }
    
    public function down()
    {
        $deleteProgress = "DROP TABLE 'progress';";
        $database->query($deleteProgress);
        $database->query("COMMIT;");
    }
}
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

