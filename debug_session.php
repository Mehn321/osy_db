<?php
/**
 * Session & CSRF Debug Tool
 * Visit this URL on the deployed site to diagnose session/CSRF issues.
 * DELETE THIS FILE after debugging is done.
 */
ini_set("session.cookie_httponly", 1);
ini_set("session.use_only_cookies", 1);
ini_set("session.cookie_samesite", "Lax");
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isHttps = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off")
    || (!empty($_SERVER["HTTP_X_FORWARDED_PROTO"]) && $_SERVER["HTTP_X_FORWARDED_PROTO"] === "https")
    || (!empty($_SERVER["HTTP_X_FORWARDED_SSL"]) && $_SERVER["HTTP_X_FORWARDED_SSL"] === "on")
    || (($_SERVER["SERVER_PORT"] ?? 80) == 443);

$_SESSION["debug_test"] = "written_at_" . time();

$debug = [
    "timestamp"              => date("Y-m-d H:i:s"),
    "session_id"             => session_id(),
    "session_status"         => session_status(),
    "session_save_path"      => session_save_path() ?: sys_get_temp_dir(),
    "session_save_writable"  => is_writable(session_save_path() ?: sys_get_temp_dir()),
    "is_https_detected"      => $isHttps,
    "SERVER_HTTPS"           => $_SERVER["HTTPS"] ?? "(not set)",
    "HTTP_X_FORWARDED_PROTO" => $_SERVER["HTTP_X_FORWARDED_PROTO"] ?? "(not set)",
    "HTTP_X_FORWARDED_SSL"   => $_SERVER["HTTP_X_FORWARDED_SSL"] ?? "(not set)",
    "SERVER_PORT"            => $_SERVER["SERVER_PORT"] ?? "(not set)",
    "PHP_SELF"               => $_SERVER["PHP_SELF"] ?? "(not set)",
    "REQUEST_URI"            => $_SERVER["REQUEST_URI"] ?? "(not set)",
    "cookie_secure_ini"      => ini_get("session.cookie_secure"),
    "cookie_samesite_ini"    => ini_get("session.cookie_samesite"),
    "cookie_httponly_ini"    => ini_get("session.cookie_httponly"),
    "session_data_keys"      => array_keys($_SESSION),
    "debug_test_written"     => $_SESSION["debug_test"] ?? "(MISSING)",
    "csrf_token_in_session"  => isset($_SESSION["csrf_token"]) ? substr($_SESSION["csrf_token"], 0, 8) . "..." : "(MISSING - SESSION BROKEN)",
    "form_nonce_pool_count"  => isset($_SESSION["form_nonce_pool"]) ? count($_SESSION["form_nonce_pool"]) : 0,
    "GET_params"             => $_GET,
    "POST_params_keys"       => array_keys($_POST),
];

header("Content-Type: application/json");
echo json_encode($debug, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
