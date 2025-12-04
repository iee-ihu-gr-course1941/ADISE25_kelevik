<?php
// Εδώ κάνουμε Import τα άλλα αρχεία
require_once "dbconnect.php";
require_once "board.php";
require_once "game.php";

$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'],'/'));
$input = json_decode(file_get_contents('php://input'),true);

header('Content-Type: application/json');

switch ($method) {
    case 'GET':
        // Κλήση από board.php
        show_board($mysqli); 
        break;
        
    case 'POST':
        handle_post($mysqli, $request, $input);
        break;
        
    default:
        header("HTTP/1.1 404 Not Found");
        exit;
}

function handle_post($mysqli, $request, $input) {
    if(!isset($request[0])) { header("HTTP/1.1 400 Bad Request"); exit;}
    
    $action = $request[0];

    if($action == 'reset') {
        // Κλήση από game.php
        reset_game($mysqli);
        show_board($mysqli);
    } 
    elseif ($action == 'move') {
        // Κλήση από game.php
        make_move($mysqli, $input['card_id']);
        show_board($mysqli);
    }
    elseif ($action == 'login') {
         // Εδώ θα έβαζες κώδικα για login
         echo json_encode(["status" => "logged_in"]);
    }
    else {
        header("HTTP/1.1 400 Bad Request");
        echo json_encode(["error" => "Unknown action"]);
    }
}
?>