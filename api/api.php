<?php
header("Content-Type: application/json");
require "db_connect.php";

function generateDeck() {
    $suits = ["hearts", "clubs", "diamonds", "spades"];
    $values = ["A","2","3","4","5","6","7","8","9","10","J","Q","K"];

    $deck = [];

    foreach ($suits as $suit) {
        foreach ($values as $value) {
            $deck[] = ["value" => $value, "suit" => $suit];
        }
    }

    shuffle($deck);
    return $deck;
}

$player_name = $_GET["name"] ?? "Player";


// 1) Δημιουργία παίκτη
$stmt = $conn->prepare("INSERT INTO players (name, session_token) VALUES (?, NULL)");
$stmt->bind_param("s", $player_name);
$stmt->execute();
$player_id = $stmt->insert_id;


// 2) Νέο παιχνίδι
$deck = generateDeck();
$table_cards = array_splice($deck, 0, 4);   // 4 ανοιχτά στο τραπέζι


$stmt = $conn->prepare("INSERT INTO games (current_player, deck, table_cards, status)
                        VALUES (?, ?, ?, 'waiting')");
$null_player = $player_id; 
$deck_json = json_encode($deck);
$table_json = json_encode($table_cards);

$stmt->bind_param("iss", $null_player, $deck_json, $table_json);
$stmt->execute();
$game_id = $stmt->insert_id;


// 3) Κάρτες παίκτη
$hand = array_splice($deck, 0, 6);
$hand_json = json_encode($hand);

$stmt = $conn->prepare("INSERT INTO game_players (game_id, player_id, hand)
                        VALUES (?, ?, ?)");
$stmt->bind_param("iis", $game_id, $player_id, $hand_json);
$stmt->execute();


echo json_encode([
    "status" => "success",
    "game_id" => $game_id,
    "player_id" => $player_id,
    "table" => $table_cards,
    "hand" => $hand
]);
?>
