<?php

require_once "config.php";
require_once "keymessage.php";
session_start();

if (!isset($_SESSION['username'])) {
    echo "Error. ";
    return;
}

if (isset($_FILES['file']) && $_FILES["file"]["error"] == 0) {
    $fileName = $_FILES["file"]["name"];
    $fileData = file_get_contents($_FILES["file"]["tmp_name"]);
    $author = $_POST['author'];
    $chatcode = $_POST['chatcode'];
    $univocoFile = $_POST['univocoFile'];
    
    $randomBytes = random_bytes(8);
	$randomHex = bin2hex($randomBytes);
	$randomId = substr($randomHex, 0, 16);

    $query = "INSERT INTO files (filename, file_data, date, author, chatcode, ID) VALUES (?, ?, NOW(), ?, ?, ?)";

    $stmt = $connessione->prepare($query);
    if (!$stmt) {
        die("Errore nella preparazione dell'istruzione SQL: " . $connessione->error);
    }
    
    $stmt->bind_param("sbsss", $fileName, $fileData, $author, $chatcode, $randomId);
    $stmt->send_long_data(1, $fileData);
    
    if ($stmt->execute()) {
        echo "File caricato correttamente nel database";
    } else {
        echo "Errore durante il caricamento del file nel database: " . $stmt->error;
    }
    
	$encryptedFilename = openssl_encrypt($fileName, "aes-256-cbc", $keymessage, 0, $keymessage);
    
    $currentDate = date('Y-m-d H:i:s');
    $futureDate = date('Y-m-d H:i:s', strtotime('+3 months', strtotime($currentDate)));
    $queryMessage = "INSERT INTO chat_message (chatcode, author, text, time, deadline, identifier) VALUES (?, ?, ?, NOW(), ?, ?)";
    $stmtM = $connessione->prepare($queryMessage);
    if (!$stmtM) {
        die("Errore nella preparazione dell'istruzione SQL: " . $connessione->error);
    }
    
    $stmtM->bind_param("sssss", $chatcode, $author, $encryptedFilename, $futureDate, $randomId);
    
    
    if ($stmtM->execute()) {
        echo "Messaggio caricato correttamente nel database";
    } else {
        echo "Errore: " . $stmtM->error;
    }

    $stmtM->close();
} else {
    echo "Errore durante il caricamento del file";
}

$connessione->close();
?>
