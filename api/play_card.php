<?php
require_once "db_connect.php";

// ---------------------------
// 1) Λήψη δεδομένων POST
// ---------------------------
$player_id = $_POST["player_id"];
$card_id   = $_POST["card_id"];   // Το φύλλο που παίζει ο παίκτης

// ---------------------------
// 2) Πάρε τα φύλλα στο τραπέζι
// ---------------------------
$lastCardQuery = $conn->query("
    SELECT * FROM table_cards 
    ORDER BY id DESC LIMIT 1
");

$lastCard = $lastCardQuery->fetch_assoc();  // Το τελευταίο φύλλο στο τραπέζι

// ---------------------------
// 3) Πάρε τα δεδομένα του φύλλου που ρίχνει ο παίκτης
// ---------------------------
$cardQuery = $conn->query("SELECT * FROM cards WHERE id = $card_id");
$playedCard = $cardQuery->fetch_assoc();

$rank_played = $playedCard["rank"];     // π.χ. 8
$rank_last   = $lastCard["rank"] ?? null; // Αν το τραπέζι είναι άδειο

// ---------------------------
// 4) Έλεγχος αν ο παίκτης μπορεί να πάρει φύλλα
// ---------------------------

$player_take_cards = false;
$is_xeri = false;
$is_bale_xeri = false;

if ($rank_last !== null) { 

    // Κανόνας 1: ίδιος αριθμός -> τα παίρνεις
    if ($rank_played == $rank_last) {
        $player_take_cards = true;

        // Αν το τραπέζι έχει μόνο 1 φύλλο → ΞΕΡΗ
        $tableCount = $conn->query("SELECT COUNT(*) AS c FROM table_cards")->fetch_assoc()["c"];
        if ($tableCount == 1) {
            $is_xeri = true;
        }
    }

    // Κανόνας 2: Βαλέ → τα παίρνεις όλα
    if ($rank_played == 11) {
        $player_take_cards = true;

        // Ξερή με βαλέ
        $tableCount = $conn->query("SELECT COUNT(*) AS c FROM table_cards")->fetch_assoc()["c"];
        if ($tableCount == 1) {
            $is_bale_xeri = true;
        }
    }
}

// ---------------------------
// 5) Εκτέλεση κινήσεων
// ---------------------------

// Αν παίρνει τα φύλλα
if ($player_take_cards) {

    // Μετακίνηση φύλλων στο χέρι του παίκτη
    $conn->query("UPDATE table_cards SET collected_by = $player_id WHERE collected_by IS NULL");
    
    // Περισσότερα αποτελέσματα για debugging (προαιρετικό)
    $result = [
        "status" => "take",
        "xeri" => $is_xeri,
        "bale_xeri" => $is_bale_xeri,
        "message" => "Ο παίκτης πήρε τα φύλλα."
    ];
    
} else {

    // Αν δεν παίρνει φύλλα → ρίχνουμε νέο στο τραπέζι
    $conn->query("
        INSERT INTO table_cards (card_id) 
        VALUES ($card_id)
    ");

    $result = [
        "status" => "drop",
        "message" => "Ο παίκτης έριξε φύλλο στο τραπέζι."
    ];
}

// ---------------------------
// 6) Σβήσε το φύλλο από το χέρι του παίκτη
// ---------------------------
$conn->query("
    DELETE FROM player_cards 
    WHERE card_id = $card_id AND player_id = $player_id
");

// ---------------------------
// 7) Return JSON
// ---------------------------
echo json_encode($result);
?>
