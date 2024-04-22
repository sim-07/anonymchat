<?php

require_once "config.php";

$pass = "E%Uq*a%?Mf_6B!xq1QSzQ6*@4h8WlY";

$passkey = mysqli_real_escape_string($connessione, $_GET['pass']);

if ($passkey == $pass) {
	$deleteQuery = "DELETE FROM chat_message WHERE deadline <= NOW()";
    
    if (mysqli_query($connessione, $deleteQuery)) {
    	echo "Messaggi scaduti eliminati con successo.";
	} else {
    	echo "Errore nell'eliminazione dei messaggi scaduti: " . mysqli_error($connessione);
	}
} else {
	http_response_code(401);
    echo "Restricted access. " . $passkey;
    
}

?>