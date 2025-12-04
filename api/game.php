<?php
// Αρχικοποίηση Παιχνιδιού (Reset)
function reset_game($mysqli) {
    $mysqli->query("TRUNCATE TABLE board");
    $mysqli->query("UPDATE game_status SET status='started', p_turn='P1', result=null");

    $suits = ['H', 'D', 'S', 'C'];
    $ranks = ['2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K', 'A'];
    
    $sql = "INSERT INTO board (suit, rank, location) VALUES (?, ?, 'deck')";
    $st = $mysqli->prepare($sql);
    foreach ($suits as $s) {
        foreach ($ranks as $r) {
            $st->bind_param('ss', $s, $r);
            $st->execute();
        }
    }
    
    // Μοίρασμα
    $mysqli->query("UPDATE board SET location='P1' ORDER BY RAND() LIMIT 6");
    $mysqli->query("UPDATE board SET location='P2' WHERE location='deck' ORDER BY RAND() LIMIT 6");
    $mysqli->query("UPDATE board SET location='pile', position=id WHERE location='deck' ORDER BY RAND() LIMIT 4");
}

// Εκτέλεση Κίνησης
function make_move($mysqli, $card_id) {
    // Βρες ποιος παίζει
    $current_turn = $mysqli->query("SELECT p_turn FROM game_status")->fetch_assoc()['p_turn'];

    // Βρες το φύλλο που παίζεται
    $st = $mysqli->prepare("SELECT * FROM board WHERE id=?");
    $st->bind_param('i', $card_id);
    $st->execute();
    $card = $st->get_result()->fetch_assoc();

    if(!$card || $card['location'] != $current_turn) {
        header("HTTP/1.1 400 Bad Request");
        echo json_encode(["error" => "Invalid move"]);
        exit;
    }

    // Βρες το πάνω φύλλο της στοίβας
    $pile_card = $mysqli->query("SELECT * FROM board WHERE location='pile' ORDER BY position DESC LIMIT 1")->fetch_assoc();

    // --- LOGIC ΞΕΡΗΣ ---
    if ($pile_card && ($card['rank'] == $pile_card['rank'] || $card['rank'] == 'J')) {
        $dest = $current_turn . '_won'; 
        // Μαζεύω το φύλλο μου + όλη τη στοίβα
        $mysqli->query("UPDATE board SET location='$dest', position=0 WHERE id=$card_id OR location='pile'");
    } else {
        // Το ρίχνω κάτω
        $new_pos = ($pile_card) ? $pile_card['position'] + 1 : 1;
        $mysqli->query("UPDATE board SET location='pile', position=$new_pos WHERE id=$card_id");
    }

    // Αλλαγή Σειράς
    $next = ($current_turn == 'P1') ? 'P2' : 'P1';
    $mysqli->query("UPDATE game_status SET p_turn='$next', last_change=NOW()");

    check_redealing($mysqli);
}

// Έλεγχος για νέο μοίρασμα
function check_redealing($mysqli) {
    $c = $mysqli->query("SELECT count(*) as c FROM board WHERE location IN ('P1','P2')")->fetch_assoc()['c'];
    if ($c == 0) {
        $deck_c = $mysqli->query("SELECT count(*) as c FROM board WHERE location='deck'")->fetch_assoc()['c'];
        if ($deck_c > 0) {
            $mysqli->query("UPDATE board SET location='P1' WHERE location='deck' ORDER BY RAND() LIMIT 6");
            $mysqli->query("UPDATE board SET location='P2' WHERE location='deck' ORDER BY RAND() LIMIT 6");
        } else {
            $mysqli->query("UPDATE game_status SET status='ended'");
        }
    }
}
?>