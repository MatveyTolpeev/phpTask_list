<?php
require_once 'class.php';
setlocale(LC_ALL, '');
session_start();

//блок обработки параметров
if (isset($_REQUEST['action'])) {
	$action = $_REQUEST['action'];

	//обрабатываем вход/регистрацию
	if (isset($_REQUEST['login']) && isset($_REQUEST['password'])) {
		$login = $_REQUEST['login'];
		$password = $_REQUEST['password'];
		if ($action == 'Зарегистрироваться') {
			$_SESSION['user'] = DBConnector::getRegister($_REQUEST['login'], $_REQUEST['password']);
		}

		if ($action == 'Войти') {
			$_SESSION['user'] = DBConnector::getUser($login, $password);
		}
	}

	//создание задачи
	if ($action == 'Создать') {
		DBConnector::addTask($_REQUEST['task_name'], $_REQUEST['description'], $_REQUEST['date'], $_REQUEST['status'], $_REQUEST['cost_estimation'], $_SESSION['user'], $_REQUEST['executor'], $_REQUEST['observer']);
	}
	
	//комментарий (на ajax)
	if ($action == 'Комментировать') {
		print_r(DBConnector::addComment($_REQUEST['text'], $_SESSION['user'], $_REQUEST['task_id']));
		return;
	}
	
	//ход работы
	if ($action == 'Отчет') {
		print_r(DBConnector::addCheckPoint($_REQUEST['text'], $_SESSION['user'], $_REQUEST['task_id'], $_REQUEST['check_point']));
		return;
	}
	
	//выход
	if ($action == "Выйти") {
		session_destroy();
		$_SESSION = array();
	}
	
	//редирект на эту же страницу со сбросом параметров (т.е. просто для отображения html-кода)
	header('Location: http://'.$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME']);
}

//обработка удаления отдельно, тк нужно передать номер удаляемого задания
if (isset($_REQUEST['delete'])) {
	DBConnector::deleteTask($_REQUEST['delete']);
	header('Location: http://'.$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME']);
}

//обработка завершения отдельно, тк нужно передать номер завершаемого задания
if (isset($_REQUEST['complete_task'])) {
	DBConnector::completeTask($_REQUEST['complete_task']);
	header('Location: http://'.$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME']);
}


//собираем html-код страницы по частям
if (isset($_SESSION['user'])) {
	$html = file_get_contents("templates/head.html");
	
	//блок создания задачи
	$html .= file_get_contents("templates/task_create.html");
	$html .= '		<div class="input-group mb-3 col-sm">
						<span class="input-group-text">Автор</span>
						<input name="author" type="text" class="form-control" value="'.DBConnector::getUserNameById($_SESSION['user']).'" readonly>
					</div>';
	
	//исполнитель
	$html .= '		<div class="input-group mb-3 col-sm">
						<label class="input-group-text" for="inputGroupSelect03">Исполнитель</label>';
	
	$html .= '					<select name="executor" class="form-select" id="inputGroupSelect03">';
	$users = DBConnector::getUsers();
	foreach ($users as $user) {
		$html .= '<option value="'.($user['id'] == $_SESSION['user'] ? $_SESSION['user'].'" selected' : $user['id'].'"').'>'.$user['login'].'</option>';
	}
	$html .=					'</select>
					</div>';
	
	//наблюдатель (аналогично исполнителю)
	$html .= '		<div class="input-group mb-3 col-sm">
						<label class="input-group-text" for="inputGroupSelect04">Наблюдатель</label>';

	$html .= '					<select name="observer" class="form-select" id="inputGroupSelect04">';

	foreach ($users as $user) {
		$html .= '<option value="'.($user['id'] == $_SESSION['user'] ? $_SESSION['user'].'" selected' : $user['id'].'"').'>'.$user['login'].'</option>';
	}
	$html .=					'</select>
					</div>
				</div>
			
				<div class=" text-center">
					<button type="submit" name="action" class="task_create btn btn-primary btn-xs" value="Создать" disabled>Создать задачу</button>
				</div>
			</form>
		</div>';

	//поле фильтрации
	$html .= file_get_contents("templates/task_filter.html");
	
	//список задач с комментариями к ним
	$html .= '<ul class="list list-group">';
	$tasks = DBConnector::getTasks();
	foreach ($tasks as $task) {
		$lastCheckPoint = DBConnector::getTaskLastCheckPoint($task['id']);
		$html .= '<li class="list-group-item">
					<div class="card">
						<div class="card-header">
							<h3 class="name">'.$task['name'].'</h3>
							<form class="delete" action="" method="POST">
								<button type="submit" name="delete" value="'.$task['id'].'" class="btn btn-outline-danger btn-sm "">Х</button>
							</form>
							<p class="creation_date">'.$task['date_creation'].'</p>
						</div>
						<div class="card-body">
							<blockquote class="blockquote mb-0">
								<p class="description">'.$task['description'].'</p>
								<p class="'.($task['status'] == 1 ? 'in_progress">(в процессе)' : 'completed">Завершено').'</p>
								<p class="cost_estimation">Трудозатраты: <mark class="estimation">'.$lastCheckPoint.'/'.$task['cost_estimation'].'</mark>ч</p>
								<p class="authors">Автор: '.DBConnector::getUserNameById($task['author_id']).', исполнитель: '.DBConnector::getUserNameById($task['executor_id']).', наблюдатель: '.DBConnector::getUserNameById($task['observer_id']).'</p>
							</blockquote>
							<button type="button" class="btn btn-light" onclick="dropdown(\'comment'.$task['id'].'\')">Комментарии</button>
							<button type="button" class="btn btn-light" onclick="dropdown(\'work'.$task['id'].'\')">Ход работы</button>
							<blockquote id="comment'.$task['id'].'" class="blockquote mb-0" style="display: none">';
		
		$comments = DBConnector::getTaskComments($task['id']);
		foreach ($comments as $comment) {
			$html .= '			<p class="comment">'.DBConnector::getUserNameById($comment['author_id']).': '.$comment['comment'].'</p>';
		}
		$html .=				'<div class="comment_input input-group mb-3">
									<span class="input-group-text" id="inputGroup-sizing-default">Ваш комментарий</span>
									<input type="text" name="comment_text" class="form-control" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-default">
									<button class="btn btn-outline-secondary" onclick=comment(\'comment'.$task['id'].'\') type="button">Комментировать</button>
								</div>
							</blockquote>
							<blockquote id="work'.$task['id'].'" class="blockquote mb-0" style="display: none">';
		
		$checkPoints = DBConnector::getTaskCheckPoints($task['id']);
		foreach ($checkPoints as $checkPoint) {
			$html .= '			<p class="comment">'.$checkPoint['check_point'].'ч '.DBConnector::getUserNameById($checkPoint['author_id']).': '.$checkPoint['text'].'</p>';
		}

		$html .=				'<div class="input-group mb-3">
									<label for="customRange2" class="form-label input-group">Временная метка</label>
									<input name="check_point" type="range" class="form-range" value="'.$lastCheckPoint.'" onChange=rangeValue(\'work'.$task['id'].'\') min="'.$lastCheckPoint.'" max="'.$task['cost_estimation'].'" id="customRange2">
									<span class="range_value input-group">'.$lastCheckPoint.'ч</span>
								</div>
								<div class="input-group mb-3">
									<span class="input-group-text" id="inputGroup-sizing-default">Ваш комментарий</span>
									<input type="text" name="check_point_text" class="form-control" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-default">
									<button class="btn btn-outline-secondary" onclick=addWork(\'work'.$task['id'].'\') type="button">Добавить отчет</button>
								</div>
								<div class=" text-center">
									<form action="" method="POST">
										<button type="submit" name="complete_task" class="btn btn-success btn-xs" value="'.$task['id'].'">Завершить задачу</button>
									</form>
								</div>
							</blockquote>
						</div>
				   </div>
				</li>';
	}
	$html .= "</ul>

		</div>
		<script>
			var options = {
			  valueNames: [ 'name', 'description', 'creation_date', 'estimation', 'creation_date' ]
			};

			var userList = new List('task_list', options);
		</script>";
	

	//кнопка выхода
	$html .= '	<div class="exitForm">
					<form action="" method="POST">
						<button type="submit" class="exit btn btn-danger" name="action" value="Выйти">Выйти</button>
					</form>
				</div>';
}
else {
	
	//форма авторизации
	$html = file_get_contents("templates/authorization.html");
}

//вывод верстки
print($html);
