<?php
session_start();
include('database_connection.php');
$message = '';
if ($_SESSION['user_id']) {
	header('location:index.php');
}
if (isset($_POST['login'])) {
	$query = "SELECT * FROM login WHERE user_name = :user_name";
	$statement = $connect->prepare($query);
	$statement->execute(
		array(':user_name' => $_POST["user_name"])
	);
	$count = $statement->rowCount();
	if ($count) {
		$result = $statement->fetchAll();
		foreach ($result as $row) {
			if ($_POST['password'] === $row['password']) {
				$_SESSION['user_id']   = $row['user_id'];
				$_SESSION['user_name'] = $row['user_name'];
				$sub_query = "INSERT INTO login_details (user_id) VALUES ('".$row['user_id']."')";
				$statement = $connect->prepare($sub_query);
				$statement->execute();
				$_SESSION['login_details_id'] = $connect->lastInsertId();
				header('location:index.php');
			}
			else{
				$message = '<label>Wrong Password</label>';		
			}
		}
	}
	else{
		$message = '<label>Wrong Username</label>';
	}
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Chat Appliction</title>
	<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
	<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
</head>
<body>
<div class="container">
	<br/>
	<h3 align="center">Chat Application</h3>
	<br/>
	<div class="panel panel-default">
		<div class="panel-heading">Chat Application Login</div>
		<div class="panel-body">
			<p class="text-danger"><?php echo $message; ?></p>
			<form method="post">
				<div class="form-group">
					<label>Enter Username</label>
					<input class="form-control" type="text" name="user_name" required>
				</div>
				<div class="form-group">
					<label>Enter Password</label>
					<input class="form-control" type="password" name="password" required>
				</div>
				<div class="form-group">
					<input class="btn btn-info" type="submit" name="login" value="Login">
				</div>
			</form>
		</div>
	</div>
</div>
</body>
</html>