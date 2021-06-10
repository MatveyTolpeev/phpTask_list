<?php

class DBConnector {
    static private $database;
	private $dbNS;
    static private $databaseHost = 'localhost';
    static private $databaseUser = 'root';
    static private $databasePassword = 'root';
    static private $databaseName = 'phptask_list';
    private function __construct($__databaseHost, $__databaseUser, $__databasePassword, $__databaseName) {
        self::$databaseHost = $__databaseHost;
        self::$databaseUser = $__databaseUser;
        self::$databasePassword = $__databasePassword;
        self::$databaseName = $__databaseName;
        self::$database = mysqli_connect($__databaseHost, $__databaseUser, $__databasePassword, $__databaseName);
        self::$database->set_charset("utf8mb4");
		$this->dbNS = self::$database;
    }
    private static function getDatabase() {
        if (isset(self::$database)) {
            return self::$database;
        }
        else {
            new DBConnector(self::$databaseHost, self::$databaseUser, self::$databasePassword, self::$databaseName);
            return self::$database;
        }
    }
    public static function setDatabaseParameters($__databaseHost, $__databaseUser, $__databasePassword, $__databaseName) {
        if (isset(self::$database)) {
            mysqli_close(self::$database);
        }
        new DBConnector($__databaseHost, $__databaseUser, $__databasePassword, $__databaseName);
    }
    
    public static function getUser($__login, $__password) {
        self::getDatabase();
        $query = self::$database->prepare("SELECT id, password FROM users WHERE login=?");
        $query->bind_param("s", $__login);
        $query->execute();
        $result = $query->get_result();
        $resultArr = $result->fetch_assoc();
        if ($resultArr['password'] == sha1($__password)) {
            return $resultArr['id'];
        }
        else {
            return false;
        }
    }
	
	public static function getUserNameById($__userId) {
		self::getDatabase();
        $query = self::$database->prepare("SELECT login FROM users WHERE id=?");
        $query->bind_param("i", $__userId);
        $query->execute();
        $result = $query->get_result();
        $resultArr = $result->fetch_assoc();
		return $resultArr['login'];
	}


	public static function getRegister($__login, $__password) {
        self::getDatabase();
		if (DBConnector::getUser($__login, $__password)) {
			return false;
		}
        $query = self::$database->prepare("INSERT INTO users (login, password) VALUES (?, SHA1(?))");
        $query->bind_param("ss", $__login, $__password);
        $query->execute();
		$query = self::$database->prepare("SELECT LAST_INSERT_ID() as last_id");
		$query->execute();
		$result = $query->get_result();
		return $result->fetch_assoc()['last_id'];
    }
	
	public static function getUsers() {
		self::getDatabase();
		$query = self::$database->prepare("SELECT id, login from users");
		$query->execute();
        $result = $query->get_result();
		$usersArr = array();
        while ($user = $result->fetch_assoc()) {
			$usersArr[] = $user;
        }
        return $usersArr;
	}

	public static function addTask($__name, $__description, $__date, $__status, $__cost_estimation, $__author, $__executor, $__observer) {
		self::getDatabase();
		$query = self::$database->prepare("INSERT INTO tasks (name, description, date_creation, status, cost_estimation, author_id, executor_id, observer_id, is_deleted) "
										. "VALUES (?, ?, '".$__date."', ?, ?, ?, ?, ?, 0)");
		$query->bind_param("ssiisss", $__name, $__description,  $__status, $__cost_estimation, $__author, $__executor, $__observer);
		$query->execute();
	}
	
	public static function getTasks() {
		self::getDatabase();
		$query = self::$database->prepare("SELECT * from tasks WHERE is_deleted = 0");
		$query->execute();
        $result = $query->get_result();
		$tasksArr = array();
        while ($task = $result->fetch_assoc()) {
			$tasksArr[] = $task;
        }
        return $tasksArr;
	}
	
	public static function deleteTask($__taskId) {
		self::getDatabase();
        $query = self::$database->prepare("UPDATE `tasks` SET `is_deleted` = '1' WHERE `id` = ?");
		$query->bind_param("i", $__taskId);
		$query->execute();
	}
	
	public static function addComment($__text, $__author, $__taskId) {
		self::getDatabase();
        $query = self::$database->prepare("INSERT INTO comments (comment, author_id, task_id) VALUES (?, ?, ?)");
        $query->bind_param("sii", $__text, $__author, $__taskId);
        $query->execute();
		return json_encode(['text' => $__text, 'author' => DBConnector::getUserNameById($__author), 'task_id' => $__taskId]);
	}

	public static function getTaskComments($__taskId) {
		self::getDatabase();
        $query = self::$database->prepare("SELECT * FROM comments WHERE task_id=?");
        $query->bind_param("i", $__taskId);
        $query->execute();
        $result = $query->get_result();
		$commentsArr = array();
        while ($comment = $result->fetch_assoc()) {
			$commentsArr[] = $comment;
        }
        return $commentsArr;
	}
	
	public static function addCheckPoint($__text, $__author, $__taskId, $__checkPoint) {
		self::getDatabase();
		if ($__checkPoint == DBConnector::getTaskCost($__taskId)) {
			DBConnector::completeTask($__taskId);
		}
		
        $query = self::$database->prepare("INSERT INTO progress (text, check_point, author_id, task_id) VALUES (?, ?, ?, ?)");
        $query->bind_param("siii", $__text, $__checkPoint, $__author, $__taskId);
        $query->execute();
		return json_encode(['text' => $__text, 'author' => DBConnector::getUserNameById($__author), 'task_id' => $__taskId, 'check_point' => $__checkPoint]);
	}
	
	public static function completeTask($__taskId) {
		self::getDatabase();
		$query = self::$database->prepare("UPDATE tasks SET status = '2' WHERE id = ?");
		$query->bind_param("i", $__taskId);
		$query->execute();
	}


	public static function getTaskCost($__taskId) {
		self::getDatabase();
		$query = self::$database->prepare("SELECT cost_estimation FROM `tasks` WHERE id = ?");
        $query->bind_param("i", $__taskId);
		$query->execute();
        $result = $query->get_result();
		return $result->fetch_assoc()['cost_estimation'];
	}

	public static function getTaskCheckPoints($__taskId) {
		self::getDatabase();
        $query = self::$database->prepare("SELECT * FROM progress WHERE task_id=?");
        $query->bind_param("i", $__taskId);
        $query->execute();
        $result = $query->get_result();
		$checkPointArr = array();
        while ($checkPoint = $result->fetch_assoc()) {
			$checkPointArr[] = $checkPoint;
        }
        return $checkPointArr;
	}
	
	public static function getTaskLastCheckPoint($__taskId) {
		self::getDatabase();
        $query = self::$database->prepare("SELECT check_point FROM progress WHERE task_id=? ORDER by check_point DESC limit 1");
        $query->bind_param("i", $__taskId);
        $query->execute();
        $result = $query->get_result();
		$checkPointArr = array();
        while ($checkPoint = $result->fetch_assoc()) {
			$checkPointArr[] = $checkPoint;
        }
        return count($checkPointArr) ? $checkPointArr[0]['check_point'] : 0;
	}
}
