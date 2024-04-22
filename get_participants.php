<?php
require_once("config.php");

session_start();

if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

$code = mysqli_real_escape_string($connessione, $_POST["code"]);

//$queryParticipants = "SELECT participants FROM chat_room WHERE ID = '$code'";
//$resultParticipants = mysqli_query($connessione, $queryParticipants);

$queryParticipants = "SELECT participants FROM chat_room WHERE ID = ?";
$stmt = $connessione->prepare($queryParticipants);
$stmt->bind_param("s", $code);
$stmt->execute();
$resultParticipants = $stmt->get_result();


if ($resultParticipants && mysqli_num_rows($resultParticipants) > 0) {
    $rowParticipants = mysqli_fetch_assoc($resultParticipants);
    $participants = $rowParticipants['participants'];
    $response = array("participants" => $participants);
    echo json_encode($response);
} else {
    $response = array("error" => "Error retrieving participants");
    echo json_encode($response);
}

} else {
	header('Location: index.php');
    exit();
}

?>

