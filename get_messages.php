<?php

require_once "config.php";
require_once "keymessage.php";


session_start();

/*error_reporting(E_ALL);
ini_set('display_errors', 'On');*/


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['code']) && isset($_SESSION['username'])) {


    $code = mysqli_real_escape_string($connessione, $_POST['code']);
    
    $lastUnivocoClient = mysqli_real_escape_string($connessione, $_POST['lastUnivocoClient']);
    
	//$loadMessages = isset($_POST['loadAllMessages']) ? ($_POST['loadAllMessages'] === 'true') : true;
    
    $loadAllMessages = mysqli_real_escape_string($connessione, $_POST['loadAllMessages']);
    
    /*
    // Query per recuperare il massimo "univoco"
    $maxUnivocoQuery = "SELECT MAX(univoco) AS max_univoco FROM chat_message WHERE ID = ?";
    $stmtMaxUnivoco = $connessione->prepare($maxUnivocoQuery);
    $stmtMaxUnivoco->bind_param("s", $code);
    $stmtMaxUnivoco->execute();
    $resultMaxUnivoco = $stmtMaxUnivoco->get_result();
    
    $maxUnivoco = 0;
    
    if ($rowMaxUnivoco = mysqli_fetch_assoc($resultMaxUnivoco)) {
        $maxUnivoco = $rowMaxUnivoco['max_univoco'];
    }*/
    
    

    if ($loadAllMessages === 'true') {
    	$query = "SELECT text, univoco, author, identifier, time FROM chat_message WHERE chatcode = ?";
		$stmt = $connessione->prepare($query);
		$stmt->bind_param("s", $code);
		$stmt->execute();
		$result = $stmt->get_result();
        //$loadAllMessages = false;
    } elseif ($loadAllMessages === 'false'/* || isset($loadAllMessages)*/) {
    	$query = "SELECT text, univoco, author, identifier, time FROM chat_message WHERE chatcode = ? AND univoco > ?";
		$stmt = $connessione->prepare($query);
		$stmt->bind_param("ss", $code, $lastUnivocoClient);
		$stmt->execute();
		$result = $stmt->get_result();
    }
    
    error_log('loadAllMessages: ' . $loadAllMessages);
    
    
//----------------------
    
    
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
    //http_response_code(401); // Accesso non autorizzato
    //echo json_encode(array('error' => 'Impossibile accedere alla chiave.'));
}

curl_close($ch);*/

//---------------------
       
       

    if ($result) {
        $messages = array();
        
        $identifiers = array();
        
        while ($row = mysqli_fetch_assoc($result)) {
        
        	$univoco = $row['univoco'];
        	$encryptedText = $row['text'];
            $author = $row['author'];
            $identifier = $row['identifier'];
            $time = $row['time'];
            
            $identifiers[] = $identifier;
            
            $decryptedText = openssl_decrypt($encryptedText, "aes-256-cbc", $keymessage, 0, $keymessage);
            
        	if ($decryptedText !== false) {
            	// Aggiungi il messaggio decriptato all'array dei messaggi
            	$messages[] = array(
                	'text' => $decryptedText,
                	'author' => $author,
                    'univoco' => $univoco,
                    'identifier' => $identifier,
                    'time' => $time
            	);
        	} else {
            	// Gestisci l'errore nella decrittazione
            	// Ad esempio, puoi ignorare il messaggio o gestirlo in base alle tue esigenze
        	}
        	$univoco = $row['univoco'];
    	}   

        // Invia i messaggi e univoco come risposta JSON al client
        header('Content-Type: application/json');
        echo json_encode(array('messages' => $messages, 'univoco' => $univoco, 'identifier' => $identifiers, 'time' => $time));
        
        
    } else {
        // Si è verificato un errore durante l'esecuzione della query
        http_response_code(500);
        echo json_encode(array('error' => 'Errore durante il recupero dei messaggi dal database.'));
    }
} else {
    // Parametro 'code' non presente nella query string
    //http_response_code(400);
    //echo json_encode(array('error' => 'Parametro "code" mancante.'));
    //echo "Error. ";
    header("Location: dashboard.php");
}

$stmt->close();
$connessione->close();
?>
