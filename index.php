<?php

session_start();

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="description" content="Free anonymous chat">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="https://www.anonymchat.altervista.org/logo_chat2.png" />
    <title>Anonymchat | Login</title>

    <style>
        body {
            font-family: Arial;
        }

        label {
            color: #6c6c6c;
            display: block;
            margin-bottom: 20px;
            margin-left: -12px;
        }

        input {
            border-radius: 5px;
            border: none;
            height: 30px;
            width: 100%;
            margin-bottom: 20px;
            padding: 6px 11px;
            box-shadow: 0px 2px 5px -1px gray;
            margin-left: -12px;
            font-size: 16px;
        }

        button {
            margin-top: 45px;
            width: 100%;
            height: 40px;
            border-radius: 5px;
            border: none;
            background-color: white;
            box-shadow: 0px 3px 11px -3px gray;
            letter-spacing: 1px;
            cursor: pointer;
        }

        button:hover {
            background-color: #efefef;
        }

        button:active {
            background-color: #e5e5e5;
        }

        a {
            color: #6c6c6c;
            text-decoration: none;
        }

        .container {
            text-align: left;
            margin-top: 50px;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
            padding: 40px;
            border: 1px solid lightgray;
            border-radius: 15px;
            height: 500px;
            position: relative;
        }

        h2 {
            color: #6c6c6c;
            margin-left: -10px;
            margin-top: 6px;
            margin-bottom: 50px;
            white-space: nowrap;
        }
        
        #showPassword {
        	position: absolute;
    		top: 291px;
    		right: 45px;
    		cursor: pointer;
    	}
        
        @media only screen and (max-width: 310px) {
        	.container {
            	height: 520px;
            }
        }
        
    </style>

</head>

<body>
	
    

    <form class="container" action="login.php" method="post">
        <h2>LOGIN</h2>
        <label>Username:</label>
        <input id="username" name="username" style="margin-bottom: 40px;" maxlength="20"><br>
        <label for="password">Password:</label>
        <input id="password" name="password" type="password" maxlength="50">
        <span id="showPassword" onclick="togglePasswordVisibility()">&#128065;</span>
        <button type="submit">Log in</button>
        <p style="color: #6c6c6c; margin-top: 60px; margin-left: -12px;">Don't have an account? <a href="signin.php">Sign in</a></p>
    
    	
    	<div class="error-message <?php echo isset($_SESSION['error_message']) ? 'show' : ''; ?>">
    		<p style="color: red; margin-left: -12px;"><?php
            	if (isset($_SESSION['error_message'])) {
        			echo $_SESSION['error_message'];
        			unset($_SESSION['error_message']); // Rimuovi il messaggio di errore dalla sessione
    			} ?>
            </p>
    	</div>
        
    </form>
</body>

<script>
	function togglePasswordVisibility() {
    	var passwordInput = document.getElementById('password');
    	var showPasswordIcon = document.getElementById('showPassword');

    	if (passwordInput.type === 'password') {
    	    passwordInput.type = 'text';
    	} else {
    	    passwordInput.type = 'password';
    	}
	}
</script>

</html>