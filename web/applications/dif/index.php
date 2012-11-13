<?php

require_once '/usr/local/share/GRIIDC/php/ldap.php';
require_once '/usr/local/share/GRIIDC/php/drupal.php';
require_once 'lib/functions.php';

if (!file_exists('config.php')) {
    echo 'Error: config.php is missing. Please see config.php.example for an example config file.';
    exit;
}
require_once 'config.php';

$uid = getDrupalUserName();
if (!isset($uid)) {
    $currentpage = urlencode(preg_replace('/^\//','',$_SERVER['REQUEST_URI']));
    drupal_set_message("You must be logged in to access the Dataset Information Form.<p><a href='/cas?destination=$currentpage' style='font-weight:bold;'>Log In</a></p>",'error');
}
else {
?>

<table style="margin: 0; padding: 20; border: 5; outline: 0; font-size: 100%; vertical-align: top; width:100%;">
    <tr>
        <td style="vertical-align: top; width:50%" >
            <?php require_once 'dif.php'; ?>
        </td>
        <td style="vertical-align: top; width:50%; height:2500px;" >
            <?php require_once 'sidebar.php'; ?>
        </td>
    </tr>
</table>

<?php
}
?>
