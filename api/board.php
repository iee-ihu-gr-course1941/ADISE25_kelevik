<?php
function show_board($mysqli) {
    // 1. Τράβα τα φύλλα
    $sql = 'SELECT * FROM board';
    $res = $mysqli->query($sql);
    $board = $res->fetch_all(MYSQLI_ASSOC);
    
    // 2. Τράβα το Status
    $sql_status = 'SELECT * FROM game_status';
    $res_status = $mysqli->query($sql_status);
    $status = $res_status->fetch_assoc();

    // 3. Τράβα τους Παίκτες
    $sql_players = 'SELECT username, token, last_action FROM players';
    $res_players = $mysqli->query($sql_players);
    $players = $res_players->fetch_all(MYSQLI_ASSOC);

    // Επιστροφή JSON
    echo json_encode([
        'board' => $board, 
        'game_status' => $status,
        'players' => $players
    ], JSON_PRETTY_PRINT);
}
?>