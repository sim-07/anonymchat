<?php

require_once "config.php";

session_start();

/*if (!isset($_SESSION['failed_attempts'])) {
    $_SESSION['attempts'] = 0;
}*/

$ipUtente = $_SERVER['REMOTE_ADDR'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

	$queryBlacklistEx = "SELECT ip FROM blacklist WHERE ip = ?";
    $stmtBl = mysqli_prepare($connessione, $queryBlacklistEx);
    mysqli_stmt_bind_param($stmtBl, "s", $ipUtente);
    mysqli_stmt_execute($stmtBl);
    $resultBl = mysqli_stmt_get_result($stmtBl);
    $numRigheBl = mysqli_num_rows($resultBl);
    
    if ($numRigheBl >= 1) {
    	$_SESSION['error_message'] = "Too many failed attempts. You have been blocked.";
        header("Location: index.php");
        exit;
    }
    
    

	$username = mysqli_real_escape_string($connessione, $_POST['username']);
	$password = mysqli_real_escape_string($connessione, $_POST['password']);


	$query = "SELECT password FROM webchat_users WHERE username = ?";
    $stmt = mysqli_prepare($connessione, $query);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $numRighe = mysqli_num_rows($result);

	if (empty($username) || empty($password)) {
    	header('Location: index.php');
    	exit;
	}

	if ($numRighe != 0) {
    
    	$row = mysqli_fetch_assoc($result);
    	$passworddb = $row['password'];
        
    	if (password_verify($password, $passworddb)) {
        	$_SESSION['username'] = $username;
    		header('Location: dashboard.php');
        	//echo $count;
    		session_regenerate_id();
    		$connessione -> close();
            } else {
    			$_SESSION['error_message'] = "Wrong username or password";
    			header("Location: index.php");
			}
	} else {
    	//$_SESSION['error_message'] = "Wrong username or password";
        //header("Location: index.php");
        $_SESSION['attempts']++;
        
        if($_SESSION['attempts'] >= 10) {
        	$_SESSION['error_message'] = "Too many failed attempts.";
            
            $queryBlacklist = "INSERT INTO blacklist (ip, date) VALUES (?, NOW())";
        	$stmt = mysqli_prepare($connessione, $queryBlacklist);

        	if ($stmt) {
            	mysqli_stmt_bind_param($stmt, "s", $ipUtente);
            	mysqli_stmt_execute($stmt);
            	mysqli_stmt_close($stmt);
        	}
        
            header("Location: index.php");
        } else {
        	$_SESSION['error_message'] = "Wrong username or password";
            header("Location: index.php");
        }
        
	}
    
    mysqli_stmt_close($stmt);
} else {
	header("Location: index.php");
}

?>