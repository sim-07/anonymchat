<?php

require_once("config.php");

session_start();

if (!isset($_SESSION['username'])) {
    echo "Error. ";
    return;
}

$username = mysqli_real_escape_string($connessione, $_SESSION['username']);
$password = mysqli_real_escape_string($connessione, $_POST['password']);
$token_js = mysqli_real_escape_string($connessione, $_POST['csrf_token']);
$token_php = mysqli_real_escape_string($connessione, $_SESSION['csrf_token_delete_php']);


if($token_php === $token_js) {

	$query = "SELECT password FROM webchat_users WHERE username = ?";
    $stmt = $connessione->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // Verifica se esiste un record con l'username fornito
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $passworddb = $row['password'];

        // Verifica se la password fornita coincide con quella nel database
        if (password_verify($password, $passworddb)) {
            // Prepara la query parametrica per eliminare l'utente
            $deleteQuery = "DELETE FROM webchat_users WHERE username = ?";
            $stmtDelete = $connessione->prepare($deleteQuery);
            $stmtDelete->bind_param("s", $username);
            
            
            if ($stmtDelete->execute()) {
                // Controlla se l'eliminazione ha avuto successo
                if ($stmtDelete->affected_rows > 0) {
                    // Account eliminato con successo
                    $stmtDelete->close();
            		$stmt->close();
            		$connessione->close();
            		session_destroy();
                    header("Location: index.php");
                } else {
                    // Non è stato possibile eliminare l'account
                    echo "Error deleting your account ."/* . $stmtDelete->error*/;
                    /*echo " Affected rows: " . $connessione -> affected_rows;
                    echo " Passworddb: " . $passworddb;
                    echo " Password: " . $password;*/
                }
            } else {
                // Visualizza messaggio di errore per la query di eliminazione
                echo "Error deleting your account: "/* . $stmtDelete->error*/;
            }

            // Chiudi la connessione al database
            /*$stmtDelete->close();
            $stmt->close();
            $connessione->close();
            session_regenerate_id();*/

            // Reindirizza l'utente
            //header('Location: index.html');
            exit;
        } else {
            echo "Wrong password.";
        }
    
} else {
	echo"Error. User not found";
}

} else {
	echo"Error. ";
}

?>