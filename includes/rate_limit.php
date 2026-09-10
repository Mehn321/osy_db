<?php
function checkRateLimit(int $maxPerMinute = 30): bool {
    $now = time();
    if (!isset($_SESSION['rl_timestamp']) || $now - $_SESSION['rl_timestamp'] >= 60) {
        $_SESSION['rl_timestamp'] = $now;
        $_SESSION['rl_counter'] = 0;
    }
    $_SESSION['rl_counter']++;
    return $_SESSION['rl_counter'] <= $maxPerMinute;
}
?>
