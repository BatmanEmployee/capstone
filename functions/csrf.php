<?php
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES) . '">';
}

function csrf_verify() {
    $posted = $_POST['csrf_token'] ?? '';
    return !empty($posted)
        && !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $posted);
}

function csrf_abort($redirect) {
    header("Location: $redirect?error=Invalid+or+expired+request.+Please+try+again.");
    exit();
}
