<?php
$username = 'root';
$password = '123';
$servername = "localhost";
try {
	$connect = new PDO("mysql:host=$servername;dbname=chat", $username, $password);
	$connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
catch(PDOException $e){
    echo "Connection failed: " . $e->getMessage();
}

function fetch_user_last_activity($user_id, $connect){
	$query = "SELECT * FROM login_details WHERE user_id = '$user_id' ORDER BY last_activity DESC LIMIT 1";
	$statement = $connect->prepare($query);
	$statement->execute();
	$result = $statement->fetchAll();
	foreach ($result as $row) {
		return $row['last_activity'];
	}
}
function fetch_user_chat_history($from_user_id, $to_user_id, $connect){
	$query ="
	SELECT * FROM chat_message
    WHERE (from_user_id = '".$from_user_id."' AND to_user_id = '".$to_user_id."')
    OR (from_user_id = '".$to_user_id."' AND to_user_id = '".$from_user_id."')
    ORDER BY timestamp
    ";
    $statement = $connect->prepare($query);
    $statement->execute();
    $result = $statement->fetchAll();
    $output = '<ul class="list-unstyled">';
    foreach ($result as $row) {
    	$user_name = '';
    	if ($row['from_user_id'] == $from_user_id) {
    		$user_name = '<b class="text-success">You</b>';
    	}
    	else{
    		$user_name = '<b class="text-danger">'.get_user_name($row['from_user_id'], $connect).'</b>';
    	}
    	$output .= '
    	<li style="border-bottom:1px dotted #ccc">
    		<p>'.$user_name.' - '.$row["chat_message"].'<p>
    			<div align="right">
    				<small><em>'.currentTimeStamp($row['timestamp']).'<em></small>
    			</div>
    	</li>';
    }
    $output .= '</ul>';
    $query = "
    UPDATE chat_message
    SET status = '0'
    WHERE from_user_id = '".$to_user_id."'
    AND to_user_id = '".$from_user_id."'
    AND status = '1'
    ";
    $statement = $connect->prepare($query);
    $statement->execute();
    return $output;
}
function get_user_name($user_id , $connect){
	$query = "SELECT user_name FROM login WHERE user_id = '$user_id'";
	$statement = $connect->prepare($query);
    $statement->execute();
    $result = $statement->fetchAll();
    foreach ($result as $row) {
    	return $row['user_name'];
    }
}
function count_unseen_message($from_user_id, $to_user_id, $connect){
    $query = "SELECT * FROM chat_message
              where from_user_id = '$from_user_id'
              AND to_user_id = '$to_user_id'
              AND status = '1' 
              ";
    $statement = $connect->prepare($query);
    $statement->execute();
    $count = $statement->rowCount();
    $output = '';  
    if ($count) {
        $output = '<span class="label label-success">'.$count.'</span>';        
    }
    return $output;       
}
function fetch_is_type_status($user_id, $connect){
    $query = "
    SELECT is_type FROM login_details
    WHERE user_id = '".$user_id."'
    ORDER BY last_activity DESC LIMIT 1
    ";
    $statement = $connect->prepare($query);
    $statement->execute();
    $result = $statement->fetchAll();
    $output = '';
    foreach ($result as $row) {
         if ($row['is_type'] == 'yes') {
            $output = ' - <small><em><span class="text-muted">Typing...</span></em></small>';
         }
    }
    return $output;
}

function currentTimeStamp($datetime, $full = false){
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
  }
?>