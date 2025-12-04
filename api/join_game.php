<?php
header("Content-Type: application/json");
require "db_connect.php";

$game_id = $_GET["game_id"] ?? null;
$name = $_GET["name"] ?? "Player 2";

if (!$game_id) {
    echo json_encode(["status" => "error", "message" => "No game_id provided"]);
    exit;
}

// Δημιουργία παίκτη
$stmt = $conn->prepare("INSERT INTO players (name) VALUES (?)");
$stmt->bind_param("s", $name);
$stmt->execute();
$player_id = $stmt->insert_id;

// Φέρε το deck
$g = $conn->query("SELECT deck FROM games WHERE game_id=$game_id")->fetch_assoc();
$deck = json_decode($g["deck"], true);

// Μοίρασμα 6 φύλλων
$hand = array_splice($deck, 0, 6);

// Save χέρι
$hand_json = json_encode($hand);

$stmt = $conn->prepare("INSERT INTO game_players (game_id, player_id, hand) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $game_id, $player_id, $hand_json);
$stmt->execute();

// Update deck
$deck_json = json_encode($deck);
$conn->query("UPDATE games SET deck='$deck_json', status='active' WHERE game_id=$game_id");


echo json_encode([
    "status" => "success",
    "message" => "Player joined",
    "player_id" => $player_id,
    "hand" => $hand
]);
?>
