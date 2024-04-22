<?php

require_once "config.php";

session_start();

if (!isset($_SESSION['username'])) {
	header("Location: index.php");
	return;
}

$sender = $_SESSION['sender'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['code'])) {
    
        $randomCode = mysqli_real_escape_string($connessione, $_POST['code']);

        $query = "INSERT INTO chat_room (chatcode, author, participants, date) VALUES (?, ?, ?, NOW())";
		$stmt = $connessione->prepare($query);
		$stmt->bind_param("sss", $randomCode, $sender, $sender);
		$stmt->execute();
		$result = $stmt->get_result();

        // Controlla se l'inserimento è avvenuto con successo
        if ($result) {
            // Invia una risposta di successo al frontend (status 200 OK)
            http_response_code(200);
            //echo "Codice casuale memorizzato con successo nel database.";
        } else {
            // Invia una risposta di errore al frontend (status 500 Internal Server Error)
            http_response_code(500);
            
            //$error_message = "Errore durante il salvataggio del codice casuale nel database: " . mysqli_error($connessione);
            //echo $error_message;
            // Registra l'errore nel log degli errori
            //error_log($error_message);
        }
    } else {
        // Invia una risposta di errore al frontend se il parametro 'code' non è stato ricevuto (status 400 Bad Request)
        http_response_code(400);
    }
} else {
    // Invia una risposta di errore al frontend se la richiesta non è di tipo POST (status 405 Method Not Allowed)
    http_response_code(405);
    echo "Restricted access.";
}

?>




