<?php
// notice_logic.php

function getNoticeLevel($client_id, $mysqli) {
    $stmt = $mysqli->prepare("
        SELECT COUNT(*) 
        FROM notices 
        WHERE client_id = ?
    ");
    $stmt->bind_param("i", $client_id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count == 0) return 'FIRST';
    if ($count == 1) return 'SECOND';
    return 'FINAL';
}

function numberToWords($amount) {
    $fmt = new NumberFormatter("en", NumberFormatter::SPELLOUT);
    $peso = floor($amount);
    $cent = round(($amount - $peso) * 100);

    $words = ucfirst($fmt->format($peso)) . " pesos";
    if ($cent > 0) {
        $words .= " and " . $fmt->format($cent) . " centavos";
    }
    return $words . " only";
}