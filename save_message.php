<?php

require_once("config.php");
require_once("keymessage.php");

session_start();

if (!isset($_SESSION['username'])) {
    echo "Error. ";
    return;
}

$code = mysqli_real_escape_string($connessione, $_POST["code"]);
//$message = mysqli_real_escape_string($connessione, $_POST["message"]);
$message = $_POST["message"];
$sender = mysqli_real_escape_string($connessione, $_POST["sender"]);
$identifier = mysqli_real_escape_string($connessione, $_POST["identifier"]);
$csrf_token_send_message_js = mysqli_real_escape_string($connessione, $_POST["csrf_token_send_message_js"]);
$csrf_token_send_message_php = $_SESSION['csrf_token_send_message'];

if (empty($message)) {
    echo "Error. ";
    return;
}

if (strlen($message) > 250) {
	return;
}

if (empty($csrf_token_send_message_js) || empty($csrf_token_send_message_php)) {
    echo "Restricted access. ";
    return;
}

if ($csrf_token_send_message_js !== $csrf_token_send_message_php) {
    echo "Restricted access. ";
    return;
}

//error_log("Codice chat: " . $code);
//error_log("Messaggio: " . $message);

$currentDate = date('Y-m-d H:i:s'); // Formato "YYYY-MM-DD"
$futureDate = date('Y-m-d H:i:s', strtotime('+3 months', strtotime($currentDate)));


//---------------

/*$username = "GSsJqWuQ0ZjC";
$password = "ri4EmpfjHsvk";

$url = "https://anonymchat.altervista.org/private/keymessage.php";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($httpCode == 200) {
    // Puoi accedere alla chiave di crittografia
    $encryptionKey = $response;
} else {
    // Gestisci l'errore di autenticazione o il codice di risposta HTTP
    echo "Errore: Impossibile accedere alla chiave.";
}

curl_close($ch);*/

//---------------


$encryptedMessage = openssl_encrypt($message, "aes-256-cbc", $keymessage, 0, $keymessage);


$fileName = $_FILES["file"]["name"];
$fileData = file_get_contents($_FILES["file"]["tmp_name"]);

$query = "INSERT INTO chat_message (chatcode, author, text, deadline, time, identifier, file_data) VALUES (?, ?, ?, ?, NOW(), ?, ?)";
$stmt = $connessione->prepare($query);

if(empty($fileData) && $fileData !== NULL) {
    echo "Errore: il file non è stato caricato correttamente.";
}

$stmt->bind_param("sssssb", $code, $sender, $encryptedMessage, $futureDate, $identifier, $fileData);
if($fileData !== NULL) {
    $stmt->send_long_data(5, $fileData);
}
$stmt->execute();
$result = $stmt->get_result();

/*if ($result) {
    http_response_code(200);
    echo "Messaggio salvato con successo nel database.";
} else {
    //http_response_code(500);
    echo "Errore durante il salvataggio del messaggio nel database. " . mysqli_error($connessione);
}*/

$stmt->close();
$connessione->close();

?>
