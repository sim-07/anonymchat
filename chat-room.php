<?php

require_once("config.php");

session_start();


$csrf_token_chatphp = $_SESSION['csrf_token_chat'];
$csrf_token_chatjs = $_POST['csrf_token_chat'];

if (hash_equals($csrf_token_chatphp, $csrf_token_chatjs) == false) {
	echo"Error "/* . "PHP: " . $csrf_token_chatphp . " JS: " . $csrf_token_chatjs*/;
    session_regenerate_id();
    $connessione -> close();
    return;
}

if (!isset($_SESSION['username'])) {
    echo "Error. ";
    return;
}

$_SESSION['csrf_token_send_message'] = bin2hex(random_bytes(32)); // Genera un token CSRF univoco
$_SESSION['csrf_token_delete_message'] = bin2hex(random_bytes(32));

//$_SESSION['loadAllMessage'] = true;


$id = mysqli_real_escape_string($connessione, $_POST["username_id"]);
$code = mysqli_real_escape_string($connessione, $_POST["code"]);

$query = "SELECT username FROM webchat_users WHERE ID = ?";

if ($stmt = mysqli_prepare($connessione, $query)) {
    mysqli_stmt_bind_param($stmt, "s", $id); 
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $username);
    if (mysqli_stmt_fetch($stmt)) {
       // L'username è stato trovato
    } else {
        header("Location: dashboard.php");
        session_regenerate_id();
    	$connessione -> close();
    	return;
    }
 
    mysqli_stmt_close($stmt);
} //else {
    // die("Errore nella preparazione della query: " . mysqli_error($connessione));
//}


$queryid = "SELECT ID FROM chat_room WHERE ID = ?";
 
// Creazione di un prepared statement
if ($stmt = mysqli_prepare($connessione, $queryid)) {
    // Associazione del valore del parametro
    mysqli_stmt_bind_param($stmt, "s", $code);

    // Esecuzione della query
    mysqli_stmt_execute($stmt);
    
    // Conta le righe restituite dalla query
    mysqli_stmt_store_result($stmt);
    $num_rows = mysqli_stmt_num_rows($stmt);

    if ($num_rows === 0) {
        echo "Wrong code. ";
        session_regenerate_id();
    	$connessione -> close();
    	return;
    }

    // Chiusura del prepared statement
    mysqli_stmt_close($stmt);
} //else {
    // die("Errore nella preparazione della query: " . mysqli_error($connessione));
//}

$queryAuthor = "SELECT author, participants FROM chat_room WHERE ID = '$code'";
$resultAuthor = mysqli_query($connessione, $queryAuthor);

if ($resultAuthor && mysqli_num_rows($resultAuthor) > 0) {

    $rowAuthor = mysqli_fetch_assoc($resultAuthor);
    $author = $rowAuthor['author'];
    $participants = $rowAuthor['participants'];
    
    if ($author != $username) {
    		if (strpos($participants, $username) === false) {
        		$insertParticipantQuery = "UPDATE chat_room SET participants = CONCAT(participants, ', $username') WHERE ID = '$code'";
        		$resultInsert = mysqli_query($connessione, $insertParticipantQuery);

        		if (!$resultInsert) {
            		echo"Error. ";
        	} else {
                //$participants = $participants . ', ' . $username;
            }
        }
    }
}


$queryParticipants = "SELECT participants FROM chat_room WHERE ID = '$code'";
$resultParticipants = mysqli_query($connessione, $queryParticipants);

if ($resultParticipants && mysqli_num_rows($resultParticipants) > 0) {
    $rowParticipants = mysqli_fetch_assoc($resultParticipants);
    $participants = $rowParticipants['participants'];
} else {
    echo "Error. ";
}

/*if (!$result) {
    die("Errore nella query: " . mysqli_error($connessione));
}*/

$sender = $_SESSION["sender"];

if ($sender == "") {
	header("Location: index.html");
    exit;
} //else {
	//echo $sender;
//}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="https://www.anonymchat.altervista.org/logo_chat2.png" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <title>AnonymChat | <?php echo $sender ?></title>

    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            overflow: hidden;
        }
        
        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: gray;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgb(67, 67, 67);
        }

        .barra {
            width: 100%;
            height: 80px;
            background-color: rgb(0 133 73);
            margin-top: 0px;
            position: fixed;
            z-index: 1;
        }

        .chat-container {
            position: fixed;
            bottom: 0;
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
        }

        .message {
            display: flex;
            align-items: center;
            /*--------------------
            bottom: 0;
    		position: relative;*/
        }

        input {
            flex: 1;
            border-radius: 5px;
            border: none;
            height: 40px;
            padding: 6px 11px 6px 60px;
            font-size: 16px;
            box-shadow: 1px 3px 9px -2px gray;
        }

        button {
            margin-left: 10px;
            width: 80px;
            height: 52px;
            border-radius: 5px;
            border: none;
            background-color: white;
            box-shadow: 2px 5px 11px -2px gray;
            letter-spacing: 1px;
            cursor: pointer;
        }

        button:hover {
            background-color: #efefef;
        }

        button:active {
            background-color: #e5e5e5;
        }
        
        #receiver {
        	color: white;
    		text-align: center;
    		margin-top: 27px;
            position: fixed;
            width: 100%;
        }
        
        .messageContainer {
        	position: fixed;
    		bottom: 0;
    		width: 100vw;
    		padding: 10px;
    		box-sizing: border-box;
    		height: 75%;
    		overflow-y: auto;
            top: 120px;
        }
        
        @media only screen and (max-width: 660px) {
            .messageContainer {
            	height: 70%;
        	}
        }

        /*@media only screen and (max-width: 420px) {
            #chatInput {
                font-size: 15px;
                padding: 6px 3px 6px 15px;
                width: 20%;
    			min-width: 150px;
            }
        }*/
        
        @media only screen and (max-width: 400px) {
        	#barra-laterale {
            	height: 48px !important;
            }
            
            #chat-code {
            	margin-top: 117px !important;
    			margin-left: 7px !important;
            }
            
            .messageContainer {
    			top: 140px;
			}
        }
        
        #barra-laterale {
        	margin-top: 80px;
    		padding: 12px 0px 10px 7px;
    		position: absolute;
    		width: 100%;
    		background-color: lightgray;
            height: 18px;
            color: #353535;
            z-index: 1;
        }
        
        #chat-code {
        	    position: relative;
    			margin-top: 90px;
    			margin-left: 250px;
    			z-index: 1;
        }
        
        @media only screen and (max-height: 665px) {
            .messageContainer {
        		height: 470px;
        	}
        }
        
        @media only screen and (max-height: 650px) {
            .messageContainer {
        		height: 450px;
        	}
        }
        
        @media only screen and (max-height: 630px) {
            .messageContainer {
        		height: 430px;
        	}
        }
        
        @media only screen and (max-height: 607px) {
            .messageContainer {
        		height: 410px;
        	}
        }
        
        @media only screen and (max-height: 587px) {
            .messageContainer {
        		height: 380px;
        	}
        }
        
        @media only screen and (max-height: 560px) {
            .messageContainer {
        		height: 360px;
        	}
        }
        
        @media only screen and (max-height: 540px) {
            .messageContainer {
        		height: 330px;
        	}
        }
        
        p {
        	background-color: green;
    		border-radius: 5px;
    		color: white;
    		min-width: 170px;
            max-width: 280px;
    		padding: 23px 5px 5px 10px;
        }
        
        .authorContainer h5 {
    		margin-top: 20px;
		}
        
        .message-container {
    		display: flex;
    		align-items: flex-start;
            overflow: hidden;
            word-wrap: break-word;
            /*justify-content: flex-end;
            margin-right: 20px;*/
        }
        
        
        .message-container h5 {
    		margin-left: 10px;
            position: absolute;
		}
        
        .message-container span {
    		color: #dfdfdf;
    		font-size: 10px;
    		position: absolute;
    		margin-top: 17px;
    		margin-left: 150px;
		}
        
        .delete-icon {
    		color: red;
    		cursor: pointer;
		}

		.red-icon {
    		color: red;
            cursor: pointer;
            margin-top: 10px;
            margin-right: 5px;
		}
        
        @keyframes slideFromRight {
  			from {
    			transform: translateX(-100%);
    			opacity: 0;
  			}
  			to {
    			transform: translateX(0);
    			opacity: 1;
  			}
		}

		.message-container.last-message {
  			animation: slideFromRight 0.3s ease;
		}
        
        p.dateOfMessages {
        	background-color: #1b431b;
    		width: 90px;
    		min-width: 90px;
    		padding: 5px 1px 5px 9px;
    		left: 50%;
    		position: relative;
    		transform: translateX(-50%);
        }
        
        .upload {
            width: 40px;
    		height: 40px;
    		position: absolute;
    		top: 16px;
    		left: 21px;
    		scale: 0.8;
        }
        
        .hide {
        	display: none;
        }
        
        #submitUpload {
        	padding: 11px;
    		top: -40px;
    		position: absolute;
    		background-color: transparent;
    		cursor: pointer;
    		box-shadow: none;
    		border: 1px solid black;
            width: 200px;
        }
        
        .progressUpload {
        	width: 0px;
    		border-radius: 6px;
    		background-color: lime;
    		border: none;
    		z-index: -1;
    		position: absolute;
    		top: -40px;
    		height: 40px;
        }
        
        
        .cancel {
        	position: absolute;
    		top: -41px;
    		left: 204px;
    		scale: 0.65;
    		cursor: pointer;
    		width: 43px;
    		height: 41px;
        }
        
        .accept {
        	left: 234px;
    		position: absolute;
    		top: -45px;
    		scale: 0.5;
    		width: 50px;
    		height: 50px;
    		cursor: pointer;
        }
        
        .files {
        	width: 41px;
    		height: 54px;
    		position: absolute;
    		top: 16px;
    		left: 19px;
    		scale: 0.5;
            cursor: pointer;
        }
 
    </style>
</head>

<body>
    <div class="barra">
    	<h2 id="receiver"></h2>

        <div id="barra-laterale">Share this code to invite to chat: </div>
        <h4 id="chat-code"><?php echo $code; ?></h4>
        
        <!--svg xmlns="http://www.w3.org/2000/svg" class="files">
			<path
       			style="fill:white;stroke-width:0.277137"
       			d="M 4.84,52.92 C 2.93,52.33 1.48,51.12 0.51,49.30 0.02,48.38 0,47.27 0,26.57 0,5.88 0.02,4.77 0.51,3.85 1.23,2.49 2.30,1.41 3.60,0.71 4.64,0.14 5.14,0.10 12.27,0.02 c 6.35,-0.07 7.81,-0.01 9.14,0.36 1.52,0.43 1.86,0.73 9.51,8.39 7.15,7.16 7.97,8.08 8.39,9.36 0.40,1.25 0.44,3.04 0.37,15.79 -0.07,13.41 -0.11,14.44 -0.59,15.35 -0.72,1.35 -1.78,2.44 -3.08,3.14 -1.09,0.58 -1.32,0.59 -15.79,0.64 -8.07,0.02 -15.00,-0.04 -15.38,-0.16 z M 34.76,49.40 c 1.71,-1.01 1.67,-0.65 1.67,-15.71 v -13.72 l -5.88,-0.08 c -5.10,-0.07 -6.01,-0.15 -6.85,-0.60 -1.35,-0.72 -2.44,-1.78 -3.14,-3.08 C 20.00,15.16 19.94,14.59 19.86,9.18 l -0.08,-5.88 h -7.07 c -7.74,0 -8.11,0.06 -9.06,1.67 -0.40,0.69 -0.45,2.81 -0.45,21.60 0,18.72 0.04,20.91 0.44,21.59 1.01,1.71 0.52,1.66 16.13,1.67 12.68,0.00 14.31,-0.04 14.99,-0.44 z M 28.68,11.19 23.27,5.78 23.19,9.87 c -0.13,6.41 0.15,6.70 6.59,6.71 l 4.29,0.00 z"
       		/>
		<svg-->
    </div>
    
    <div id="container">
    	<div id="messageContainer" class="messageContainer"></div>
    	<div id="authorContainer" class="authorContainer"></div>
        
    	<div class="chat-container" id="chat-container">
        
        <form id="uploadForm" enctype="multipart/form-data">
        	<label for="up" name="file">
                <svg xmlns="http://www.w3.org/2000/svg" class="upload">
                    <path style="fill:rgb(0, 0, 0);stroke-width:0.186916;"
                        d="M 1.22,36.59 C 0.08,35.97 0,35.50 0,30.02 0,25.99 0.06,24.89 0.31,24.40 1.05,22.98 2.68,22.68 3.87,23.74 l 0.69,0.61 0.06,3.94 0.06,3.94 H 18.48 32.27 v -3.51 c 0,-3.78 0.17,-4.63 1.08,-5.27 0.27,-0.19 0.86,-0.34 1.31,-0.34 0.65,0 0.97,0.14 1.55,0.73 l 0.73,0.73 -0.06,5.52 -0.06,5.52 -0.58,0.58 -0.58,0.58 -16.93,0.04 C 4.05,36.90 1.72,36.87 1.22,36.59 Z M 17.24,27.25 C 16.15,26.48 16.13,26.35 16.13,16.83 V 7.97 l -2.95,2.90 c -2.70,2.66 -3.01,2.90 -3.72,2.90 -1.94,0 -3.02,-1.69 -2.15,-3.35 C 7.82,9.44 17.24,0.23 17.89,0.07 c 1.20,-0.29 1.66,0.05 6.90,5.28 5.12,5.11 5.14,5.14 5.14,6.05 0,1.13 -0.26,1.63 -1.05,2.04 -0.73,0.37 -1.55,0.41 -2.19,0.08 -0.25,-0.12 -1.68,-1.44 -3.17,-2.91 L 20.81,7.94 v 9.09 9.09 l -0.72,0.72 c -0.57,0.57 -0.89,0.72 -1.54,0.72 -0.45,0 -1.03,-0.14 -1.29,-0.33 z">
                    </path>
                </svg>
            </label>
            <input id="up" name="file" type="file" onchange="uploadShow()" style="display: none;">
            <div id="uploadContainer" class="hide">
            	<input type="submit" value="Upload" id="submitUpload">
                
                <div class="progressUpload" id="progressBar"></div> 		<!--		PROGRESS UPLOAD		-->
                
            	<svg xmlns="http://www.w3.org/2000/svg" class="cancel" onclick="cancelUpload()">
                 	<path style="fill:#ff0000;stroke:#ffffff;stroke-width:3.8025;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1"
       					d="M 29.243842,13.112228 13.11223,29.243842 m 0,-16.131614 16.131612,16.131614 M 40.454821,21.178035 A 19.276785,19.276785 0 0 1 21.178036,40.45482 19.276785,19.276785 0 0 1 1.90125,21.178035 19.276785,19.276785 0 0 1 21.178036,1.90125 19.276785,19.276785 0 0 1 40.454821,21.178035 Z">
                 	</path>
             	</svg>
                
                <label id="acceptUpload">
                	<svg xmlns="http://www.w3.org/2000/svg" class="accept" onclick="uploadFile()">
                 		<path style="color:#000000; fill:#00ff00"
       						d="m 37.272754,13.075733 c -0.560753,0.02106 -1.09428,0.247169 -1.499409,0.635445 L 18.297607,30.431197 8.9102617,21.396654 c -0.911201,-0.877745 -2.3613697,-0.850825 -3.2393666,0.06013 -0.8790854,0.911445 -0.8521519,2.36316 0.060137,3.24137 L 18.287585,36.783637 38.942552,17.020699 c 0.913805,-0.875194 0.945214,-2.325427 0.07016,-3.239366 -0.452567,-0.472946 -1.085807,-0.729744 -1.739957,-0.705605 z M 23.437295,0 c 12.965125,0 23.437293,10.472168 23.437293,23.437294 0,12.965126 -10.472168,23.437294 -23.437293,23.437294 C 10.472168,46.874588 9.9999998e-8,36.40242 0,23.437294 2e-7,10.472168 10.472168,-9.9999999e-8 23.437295,0 Z">
                 		</path>
             		</svg>
                </label>
            </div>
          </form>
          
        	<div class="message">           		
                <input type="text" placeholder="Type a message..." id="chatInput" autocomplete="off" maxlenght="200">
            	<button type="button" id="SendMessage" onclick="sendMessage()">Send</button>
        	</div>
    	</div>
    </div>
    <noscript>
   		<div style="font-size: 2em; color: red; top: 150px; position: absolute;">
            Enable JavaScript on your browser.
        </div>
	</noscript>
</body>

<script>
	var username = '<?= $username ?>';
    var messageContainer = document.getElementById("messageContainer");
    
    function uploadShow() {
        document.getElementById('uploadContainer').classList.remove('hide');
        document.getElementById('submitUpload').value = document.getElementById('up').files[0].name;
    }
    
    function cancelUpload() {
    	var uploadContainer = document.getElementById('uploadContainer');
    	var xhr = uploadContainer.xhr;
        var acceptUpload = document.getElementById('acceptUpload');
        var fileInput = document.getElementById('up');
        
    	if (xhr) {
        	xhr.abort();
    	}
        progressBar.style.width = '0px';
        fileInput.value = '';
    	uploadContainer.classList.add('hide');
        acceptUpload.classList.remove("hide");
	}
    
    function uploadFile() {
    	var fileInput = document.getElementById('up');
    	var file = fileInput.files[0];
        var form = document.getElementById('uploadForm');
    	var formData = new FormData(form);
        var progressBar = document.getElementById('progressBar');
        var acceptUpload = document.getElementById('acceptUpload');
        var identifierGenerate = generateIdentifier();
        
        if (!file || file.size === 0) {
        	alert('Nessun file selezionato.');
            return;
    	}
        
		acceptUpload.classList.add("hide");
    	var xhr = new XMLHttpRequest();
    	xhr.open('POST', 'save_message.php', true);
    	xhr.onreadystatechange = function() {
        	if (xhr.readyState === XMLHttpRequest.DONE) {
            	if (xhr.status === 200) {
                	//console.log(xhr.responseText);
                    //displayMessage(document.getElementById('up').files[0].name, '<?php //echo $_SESSION['username']; ?>', "", new Date());
                    cancelUpload();
                    progressBar.style.width = '0px';
                    acceptUpload.classList.remove("hide");
            	} else {
                	console.error('Upload failed');
            	}
        	}
    	};
        
        xhr.upload.onprogress = function(event) {
    		if (event.lengthComputable) {
        		var percentComplete = (event.loaded / event.total) * 100;
                var progressWidth = (percentComplete/100)*200;
        		progressBar.style.width = progressWidth + 'px';
    		}
		};
		formData.append('code', '<?php echo $code; ?>');
    	formData.append('message', document.getElementById('up').files[0].name);
    	formData.append('sender', '<?php echo $sender; ?>');
    	formData.append('csrf_token_send_message_js', '<?php echo $_SESSION['csrf_token_send_message']; ?>');
    	formData.append('identifier', identifierGenerate);
        formData.append('file', file);
    
    	xhr.send(formData);
        document.getElementById('uploadContainer').xhr = xhr;
	}

    
    /*document.addEventListener('contextmenu', function (e) {
        e.preventDefault(); // Impedisce l'apertura del menu contestuale
    });
    
    document.addEventListener('keydown', function(e) {
    	// Disabilita Ctrl + Shift + I
    	if ((e.ctrlKey || e.metaKey) && e.shiftKey && (e.key === 'I' || e.keyCode === 73)) {
      		e.preventDefault();
    	}
  	});*/

    
function getParticipants() {
    var xhrParticipants = new XMLHttpRequest();
    xhrParticipants.onreadystatechange = function() {
        if (xhrParticipants.readyState === XMLHttpRequest.DONE) {
            if (xhrParticipants.status === 200) {
                var response = JSON.parse(xhrParticipants.responseText);
                var participants = response.participants;
                document.getElementById("receiver").innerText = participants;
            } else {
                console.error("Errore durante il recupero dei partecipanti dal server.");
                console.error(xhrParticipants.responseText);
            }
        }
    };
    xhrParticipants.open('POST', 'get_participants.php', true);
	xhrParticipants.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	xhrParticipants.send('code=' + encodeURIComponent('<?php echo $code; ?>'));
}

getParticipants();
    
setInterval( function() {
	getParticipants();
}, 4000);

    
    document.addEventListener('DOMContentLoaded', function() {
    
        displayMessageFromServer();
        
        setInterval(function() {
        	displayMessageFromServer();
    	}, 1000);
        
        //var messageContainer = document.getElementById("messageContainer");        
        //messageContainer.scrollTop = messageContainer.scrollHeight;
    });
    

    function generateIdentifier() {
    	const array = new Uint8Array(8); // Usiamo Uint8Array per 16 caratteri
    	crypto.getRandomValues(array);
    	const identifier = Array.from(array)
        	.map(byte => byte.toString(16).padStart(2, '0')) // Ogni byte viene convertito in 2 caratteri esadecimali
        	.join('');
    	return identifier;
	}
    
    function sendMessage() {
  		var input = document.getElementById('chatInput');
  		var userMessage = input.value;
        var identifierGenerate = generateIdentifier();
        //var tempUnivoco = "tempUnivoco";
        
        if (userMessage.length > 250) {
        	alert("Maximum length 250 characters");
            return;
        }
        
        if (userMessage.trim() === "") {
        	return; // Il messaggio è vuoto, esce dalla funzione senza inviare nulla
    	}
        
        var now = new Date();
        
        displayMessage(userMessage, '<?php echo $sender; ?>', identifierGenerate, now); //Fa subito vedere il messaggio per evitare ritardi
        
  		var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    // Messaggio salvato con successo nel database
                    //console.log('Messaggio inviato e salvato nel database:', userMessage);
                    //displayMessage(userMessage, '<?php //echo $sender; ?>', identifierGenerate);
                    
                    messageContainer.scrollTop = messageContainer.scrollHeight;
                    
                } else {
                    // Si è verificato un errore durante la richiesta al server
                    //console.error("Errore durante il salvataggio del messaggio nel database.");
                    //console.error(xhr.responseText);
                }
            }
        };
        xhr.open('POST', 'save_message.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        
        xhr.send('code=' + encodeURIComponent('<?php echo $code; ?>') + '&message=' + encodeURIComponent(userMessage) + '&sender=' + encodeURIComponent('<?php echo $sender; ?>') + '&csrf_token_send_message_js=' + encodeURIComponent('<?php echo $_SESSION['csrf_token_send_message']; ?>') + '&identifier=' + encodeURIComponent(identifierGenerate));
        
        input.value = ''; // Per svuotare l'input dopo l'invio
        
        //messageContainer.scrollTop = messageContainer.scrollHeight;
		}

		document.getElementById('chatInput').addEventListener('keydown', function(event) {
  		if (event.key === 'Enter') {
    		sendMessage();
  		}
		});

			function deleteMessage() {
            	var clickedIcon = event.target;
    			var identifier = clickedIcon.getAttribute("data-univoco");
                
                if (!/^[0-9a-fA-F]{16}$/.test(identifier)) {
    				return;
				}
                
                var messageElement = document.querySelector('[data-univoco="' + identifier + '"]');
                
            	var xhr = new XMLHttpRequest();
    			xhr.onreadystatechange = function() {
        			if (xhr.readyState === XMLHttpRequest.DONE) {
                    	if (xhr.responseText.length > 0) {
            				if (xhr.status === 200) {
                				var response = JSON.parse(xhr.responseText);
                            	//console.log(response);
                				if (response.success === true) {
                            		messageElement.remove();
                    				//console.log("Messaggio eliminato con successo.");
                				} else {
                    				//console.error("Errore durante l'eliminazione del messaggio:", response.message);
                				}
            				} else {
                				console.error("Error.");
                				console.error(xhr.responseText);
                            }
            			} else {
                        	//console.error("Vuoto.");
                        }
        			}
    			};
    			xhr.open('POST', 'delete_message.php', true);
				xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
				xhr.send('univoco=' + encodeURIComponent(identifier) + '&token_csrf_delete_message=<?php echo $_SESSION["csrf_token_delete_message"]; ?>');
			}
    
    
    

var loadAllMessages = true;
var univoco = null;
var sentUnivocos = [];

function displayMessageFromServer() {
    var authorContainer = document.getElementById("authorContainer");
    var previousScrollTop = messageContainer.scrollTop;

    // Carica i messaggi dal server
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);
                var messages = response.messages;
                var newUnivoco = response.univoco;
                var identifier = response.identifier;
                //var time = response.time;
                
                
                //console.log(identifier);
                

                //console.log("UnivocoA: ", newUnivoco);
                //console.log("Messages: ", messages);

                if (loadAllMessages || newUnivoco > univoco) {
                    var previousContainerHeight = messageContainer.scrollHeight;

                    
                    messages.sort(function(a, b) {
    					return a.univoco - b.univoco;
					});


                    messages.forEach(function (message) {
                        if (!sentUnivocos.includes(message.univoco)) {
                            
                            var lastMessage = document.querySelector('.message-container.last-message');
      						if (lastMessage) {
        						lastMessage.classList.remove('last-message');
      						}
                            
                            // Verifica se l'identifier del messaggio è già stato visualizzato
                            if (!document.querySelector('[data-univoco="' + message.identifier + '"]')) {
                                displayMessage(message.text, message.author, message.identifier, message.time);
                                sentUnivocos.push(message.univoco);
                            }
                        }
                    });

                    var containerHeightDifference = messageContainer.scrollHeight - previousContainerHeight;
                    messageContainer.scrollTop = previousScrollTop + containerHeightDifference;

                    // Aggiorno univoco solo se ho ricevuto nuovi messaggi
                    if (newUnivoco > univoco) {
                        univoco = newUnivoco;
                    }

                    loadAllMessages = false;
                }
            } else {
                //console.error("Errore durante il recupero dei messaggi dal server.");
                //console.error(xhr.responseText); // Visualizza il messaggio di errore del server nella console
            }
        }
    };

    //console.log(loadAllMessages);
    //console.log("univocoB:", univoco);
    xhr.open('POST', 'get_messages.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.send('code=' + encodeURIComponent('<?php echo $code; ?>') + '&lastUnivocoClient=' + encodeURIComponent(univoco) + '&loadAllMessages=' + encodeURIComponent(loadAllMessages));
}



function formatTimeToHHMM(dateTimeStr) {
    const dateTime = new Date(dateTimeStr); // Crea un oggetto Data dalla stringa dateTime
    const hours = dateTime.getHours().toString().padStart(2, '0'); // Ottieni le ore (formato 00)
    const minutes = dateTime.getMinutes().toString().padStart(2, '0'); // Ottieni i minuti (formato 00)
    
    return `${hours}:${minutes}`;
}

function formatTimeToDate(dateTimeStr) {
    const dateTime = new Date(dateTimeStr);
    const year = dateTime.getFullYear().toString();
    const month = (dateTime.getMonth() + 1).toString().padStart(2, '0');
    const day = dateTime.getDate().toString().padStart(2, '0');
    
    return `${year}-${month}-${day}`;
}


//------------------------------------------------------------

        var dates = [];
        var dayContainers = {};
        var dateOfMessagesCreated = {};
    	function displayMessage(message, author, identifier, time) {
        
            //console.log(identifier);
            //console.log(univoco);
            
            
            //scroll
            var messageContainerScroll = messageContainer.scrollTop;
    		var messageContainerHeight = messageContainer.scrollHeight
            //-----------------------------------------------------------
            
            
    		var newMessage = document.createElement("div");
			var messageAuthor = document.createElement("h5");
            var timeCreate = document.createElement("span");
            
            
            //timeCreate.textContent = time;
            timeCreate.textContent = formatTimeToHHMM(time);
            var date = formatTimeToDate(time);
            
             if (!dates.includes(date)) {
        		//console.log(date);
        		if (!dayContainers[date]) {
            		dayContainers[date] = document.createElement("div");
            		dayContainers[date].id = date;
        		}
                
                if (!dateOfMessagesCreated[date]) {
            		var dateOfMessages = document.createElement("p");
            		dateOfMessages.textContent = date;
                    dateOfMessages.classList.add("dateOfMessages");
            		dayContainers[date].appendChild(dateOfMessages);
            		dateOfMessagesCreated[date] = true;
        		}
        
        		dates.push(date);
    		}
            
            
			newMessage.classList.add("message-container");

			var messageText = document.createElement("p");
			messageText.textContent = message;
			messageText.style.margin = "10px 0";
			messageText.style.textAlign = "left";
            
            messageAuthor.textContent = author;
            
            messageAuthor.style.margin = "15px 0px 0px 10px";
    		messageAuthor.style.color = "white";
            
            
			if (author === username) {
    			// Il messaggio è stato inviato dall'utente corrente
    			newMessage.style.marginRight = "20px";
    			newMessage.style.justifyContent = "flex-end";
    			newMessage.setAttribute("data-univoco", identifier);

    			messageAuthor.style.display = "inline-flex";
    			messageAuthor.style.marginRight = "10px";
    			messageAuthor.style.margin = "15px 0px 0px 15px !important";
                
                //timeCreate.style.right = "114px";
                timeCreate.style.float = "right";
                timeCreate.style.right = "180px";

    			var deleteIcon = document.createElement("i");
    			deleteIcon.classList.add("fas");
    			deleteIcon.classList.add("fa-trash");
    			deleteIcon.classList.add("red-icon");
    			deleteIcon.id = "deleteIcon";
    			deleteIcon.setAttribute("data-univoco", identifier);
                
                if (!loadAllMessages) {
                	newMessage.classList.add("last-message");
               }


    			newMessage.appendChild(deleteIcon);

    			deleteIcon.addEventListener("click", function() {
        			deleteMessage();
    			});
			}

			newMessage.appendChild(messageAuthor);
			newMessage.appendChild(messageText);
            newMessage.appendChild(timeCreate);
            
            
            //messageContainer.appendChild(newMessage);
            
            dayContainers[date].appendChild(newMessage);
            
            if (!dayContainers[date].parentNode) {
        		messageContainer.appendChild(dayContainers[date]);
    		}
            
        }


const chatContainer = document.getElementById('chat-container');
const message_container = document.querySelector('.messageContainer');
const barra_laterale = document.getElementById("barra-laterale");
const chatCode = document.getElementById("chat-code");
const totalPageHeight = document.documentElement.scrollHeight;


const container = document.getElementById("container");


if ('visualViewport' in window) {
  const VIEWPORT_VS_CLIENT_HEIGHT_RATIO = 0.75;
  window.visualViewport.addEventListener('resize', function (event) {
  
  	const viewportHeightRatio = (event.target.height * event.target.scale) / window.screen.height;
    const pageHeightChange = totalPageHeight * (1 - viewportHeightRatio);
    const pageChangePercentage = (pageHeightChange / totalPageHeight) * 100;
    //console.log("Percentuale di cambiamento in altezza: " + pageChangePercentage + "%");
    let pageChangePx = (pageChangePercentage / 100) * totalPageHeight;
    //console.log("Px di cambiamento in altezza: " + pageChangePx);
    
    let newHeightBody = totalPageHeight - pageChangePx;
    
    if (
      (event.target.height * event.target.scale) / window.screen.height <
      VIEWPORT_VS_CLIENT_HEIGHT_RATIO
    ) {
    
      let newHeight = window.screen.height - pageChangePx - 80 - 50 - 110;
      
      let newTop = pageChangePx + 85;
      
      var style = document.createElement("style");
	  style.innerHTML = ".keyboardUp{ top: 90px; height: " + newHeight + "px; }";
	  document.head.appendChild(style);
      
      
      var style = document.createElement("style");
	  style.innerHTML = ".keyboardUpInput{ top:" + newTop + "px; }";
	  document.head.appendChild(style);
      
      
		//alert(chatContainer.style.bottom);
        
      if (chatContainer.style.bottom == '0px') { //-----------------
	  	chatContainer.style.bottom = pageChangePx - 50 + 'px';        
      }
      
      
      //if (chatContainer.style.bottom == '0px') {
	  	 //chatContainer.classList.add("keyboardUpInput");
      //}
      
      //console.log("PAGE CHANGE PX: " + pageChangePx);
      message_container.classList.add("keyboardUp");
      //message_container.style.height = newHeight;
      barra_laterale.style.display = 'none';
      chatCode.style.display = 'none';
      message_container.scrollTop = message_container.scrollHeight;
     
      
    } else {
      //console.log('keyboard is hidden');
      
      chatContainer.style.bottom = '0px'; //--------------
      
      chatContainer.classList.remove("keyboardUpInput");
      
      message_container.classList.remove("keyboardUp");
      //message_container.style.height = '480px';
      barra_laterale.style.display = 'block';
      chatCode.style.display = 'block';
    }
  });
}

    
</script>

</html>