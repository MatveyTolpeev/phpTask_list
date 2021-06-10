<?php
use Illuminate\Database\Migrations\Migrations;

require_once 'class.php';



class CreateCommentsTable extends Migrations
{
    
    private $database = DBConnector::getDatabase;
    
    public function up()
    {
        $createTasks = "CREATE TABLE `tasks` (
      `id` int(10) NOT NULL,
      `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
      `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
      `date_creation` date NOT NULL,
      `status` int(5) NOT NULL,
      `cost_estimation` int(10) NOT NULL,
      `author_id` int(5) NOT NULL,
      `executor_id` int(5) NOT NULL,
      `observer_id` int(5) NOT NULL,
      `is_deleted` tinyint(1) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $alterTasks1 = "ALTER TABLE `tasks`
      ADD PRIMARY KEY (`id`),
      ADD KEY `author_id` (`author_id`),
      ADD KEY `executor_id` (`executor_id`),
      ADD KEY `observer_id` (`observer_id`);";
        $alterTasks2 = "ALTER TABLE `tasks`
      MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;";
        $alterTasks3 = "ALTER TABLE `tasks`
      ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`),
      ADD CONSTRAINT `tasks_ibfk_2` FOREIGN KEY (`executor_id`) REFERENCES `users` (`id`),
      ADD CONSTRAINT `tasks_ibfk_3` FOREIGN KEY (`observer_id`) REFERENCES `users` (`id`);";
        $database->query($createTasks);
        $database->query($alterTasks1);
        $database->query($alterTasks2);
        $database->query($alterTasks3);
        $database->query("COMMIT;");
    }
    
    public function down()
    {
        $deleteTasks = "DROP TABLE 'tasks';";
        $database->query($deleteTasks);
        $database->query("COMMIT;");
    }
}
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

