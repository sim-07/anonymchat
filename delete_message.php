<?php

require_once "config.php";

session_start();

if (!isset($_SESSION['username'])) {
	header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $univoco = mysqli_real_escape_string($connessione, $_POST['univoco']);
    $token_csrf_php = $_SESSION['csrf_token_delete_message'];
    $token_csrf_js = mysqli_real_escape_string($connessione, $_POST['token_csrf_delete_message']);
    
    if ($token_csrf_php !== $token_csrf_js) {
        http_response_code(401); // Accesso non autorizzato
        echo json_encode(array('success' => false));
        exit;
    }

    // Esegui una query per ottenere l'autore del messaggio
    $stmt = $connessione->prepare("SELECT author FROM chat_message WHERE identifier = ?");
    $stmt->bind_param("s", $univoco);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row) {
            $author = $row['author'];

            if ($author == $_SESSION['username']) {
                // L'utente è autorizzato a eliminare il messaggio
                $stmt = $connessione->prepare("DELETE FROM chat_message WHERE identifier = ?");
                $stmt->bind_param("s", $univoco);

                if ($stmt->execute()) {
                    echo json_encode(array('success' => true));
                } else {
                    http_response_code(500); // Errore del server
                    echo json_encode(array('success' => false));
                }
            } else {
                http_response_code(401); // Accesso non autorizzato
                echo json_encode(array('success' => false));
            }
        } else {
            http_response_code(404); // Non trovato
            echo json_encode(array('success' => false));
        }
    } else {
        http_response_code(500); // Errore del server
        echo json_encode(array('success' => false));
    }
} else {
    http_response_code(400); // Richiesta non valida
    echo json_encode(array('success' => false));
}

?>
