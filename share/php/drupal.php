<?php

if (!function_exists('drupal_set_message')) {
    function drupal_set_message($message = NULL, $type = 'status', $repeat = TRUE) {
        echo "<p><strong>[$type]: $message</strong></p>";
    }
}

function getDrupalUserName() {
    global $user;
    if (isset($user) and is_object($user) and property_exists($user,'name')) return $user->name;
    else return NULL;
}

function fixEnvironment() {
    $orig_env = array();
    # save original script name
    $orig_env['SCRIPT_NAME'] = $_SERVER['SCRIPT_NAME'];
    # fix up the script name for Slim
    $_SERVER['SCRIPT_NAME'] = preg_replace('/^(\/[^\/]+).*$/','$1',$_SERVER['REQUEST_URI']);
    # save original query string
    $orig_env['QUERY_STRING'] = $_SERVER['QUERY_STRING'];
    # fix up query string for Slim
    $_SERVER['QUERY_STRING'] = preg_replace('/^q=[^&]+&?/','',$_SERVER['QUERY_STRING']);
    # run Slim application
    return $orig_env;
}

function restoreEnvironment($orig_env) {
    # restore all environment variables back to their saved values
    foreach ($orig_env as $key => $val) {
        $_SERVER[$key] = $val;
    }
}

?>
