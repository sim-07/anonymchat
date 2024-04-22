<?php

session_start();

require_once "config.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {


    $username = mysqli_real_escape_string($connessione, $_POST["username"]);
    $password = mysqli_real_escape_string($connessione, $_POST["password"]);
    
    if (preg_match('/\s/', $username)) {
    	$_SESSION['error_message_signin'] = "The username cannot contain spaces.";
        header("Location: signin.php");
        return;
    } elseif (preg_match('/[[:cntrl:]]/', $username)) {
        $_SESSION['error_message_signin'] = "Invalid username.";
        header("Location: signin.php");
        return;
    } elseif (preg_match('/[&<>"\']/', $username)) {
    	$_SESSION['error_message_signin'] = "Invalid username.";
        header("Location: signin.php");
        return;
	}



    if (strlen($username) > 20) {
        $_SESSION['error_message_signin'] = "The username cannot exceed 20 characters. ";
        header("Location: signin.php");
        return;
    }
    
    if (strlen($username) > 50) {
        $_SESSION['error_message_signin'] = "The password cannot exceed 50 characters. ";
        header("Location: signin.php");
        return;
    }


    $_SESSION['username'] = $username;


    $sqlquery = "SELECT * FROM webchat_users WHERE username = ?";
    $stmt = mysqli_prepare($connessione, $sqlquery);

    if ($stmt) {
        // Associa il parametro alla query
        mysqli_stmt_bind_param($stmt, "s", $username);

        // Esegui la query
        mysqli_stmt_execute($stmt);

        // Ottieni il risultato
        $result = mysqli_stmt_get_result($stmt);

        // Ottieni il numero di righe
        $numRighe = mysqli_num_rows($result);

        // Chiudi lo statement preparato
        mysqli_stmt_close($stmt);
    }


    $passwordh = password_hash($password, PASSWORD_BCRYPT);

    if ($numRighe == 0) {
        if (!empty($username)) {
            if (!empty($password)) {
                $randomBytes = random_bytes(16); // genera 16 byte di dati casuali
                $randomString = bin2hex($randomBytes); // converte i byte in una stringa esadecimale

                $stmt2 = mysqli_prepare($connessione, "INSERT INTO webchat_users (ID, username, password) VALUES (?, ?, ?)");
                mysqli_stmt_bind_param($stmt2, "sss", $randomString, $username, $passwordh);

                if (mysqli_stmt_execute($stmt2)) {
                    header('Location: dashboard.php');
                    session_regenerate_id();
                } else {
                    echo "Error: ";
                }

                mysqli_stmt_close($stmt2);
            } else {
                header("Location: signin.php");
            }
        } else {
            header("Location: signin.php");
        }
    } else {
        $_SESSION['error_message_signin'] = "Account already exists. ";
        header("Location: signin.php");
    }
} else {
	header("Location: signin.php");
}
?>