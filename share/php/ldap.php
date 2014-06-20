<?php

require_once('/usr/local/share/GRIIDC/php/drupal.php');
$GLOBALS['config'] = parse_ini_file('/etc/griidc.ini',true);
$GLOBALS['ldap'] = parse_ini_file($GLOBALS['config']['paths']['conf'].'/ldap.ini',true);

function connectLDAP($ldaphost) {
    $ldapconnect = ldap_connect("ldap://$ldaphost");
    
    ldap_set_option($ldapconnect, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($ldapconnect, LDAP_OPT_REFERRALS, 0);
    
    if (!ldap_bind($ldapconnect)) {
        $dMessage = "Could not connect to LDAP. Please contact the administrator <a href=\"mailto:griidc@gomri.org?subject=DOI Error\">griidc@gomri.org</a>.";
        drupal_set_message($dMessage,'error',false);
    }
    return $ldapconnect;
}

function getDNs($ldap,$basedn,$search) {
    $attributes = array('dn');
    $result = ldap_search($ldap, $basedn, $search, $attributes);
    if ($result === false) { return array(); }
    $entries = ldap_get_entries($ldap, $result);
    if ($entries['count']>0) { 
        return $entries;
    }
    else { 
        return array(); 
    }
}

function isMember($ldap,$userDN,$groupDN) {
    $attributes = array('member');
    $result = ldap_read($ldap, "$groupDN", "(member=$userDN)", $attributes);
    if ($result === false) { 
        return false; 
    }
    else {
        $entries = ldap_get_entries($ldap, $result);
        if ($entries['count'] > 0) { return TRUE; }else{ return false; };
    }
}

function memberHasApplicationRole($username,$applicationName,$applicationRole) {
    $allowAccess=false;
    $ldap=connectLDAP('triton.tamucc.edu');
    // check for group membership
    $attributes = array('dn');
    $groupDN = "cn=$applicationRole,ou=$applicationName,ou=applications,dc=griidc,dc=org";
    $result = ldap_read($ldap, "$groupDN", "(member=uid=$username,ou=members,ou=people,dc=griidc,dc=org)", $attributes);
    if ($result === false) {
        return false; 
    }
    else {
        $entries = ldap_get_entries($ldap, $result);
        if ($entries['count'] > 0) { $allowAccess=true; }
    }
    // check for admin group membership
    $attributes = array('dn');
    $groupDN = "cn=administrators,ou=applications,dc=griidc,dc=org";
    $result = ldap_read($ldap, "$groupDN", "(member=uid=$username,ou=members,ou=people,dc=griidc,dc=org)", $attributes);
    if ($result === false) {
        return false; 
    }
    else {
        $entries = ldap_get_entries($ldap, $result);
        if ($entries['count'] > 0) { $allowAccess=true; }
    }
    ldap_unbind($ldap);
    return $allowAccess;
}

function getAttributes($ldap,$dn,$attributes) {
    $result = ldap_read($ldap,$dn,'(objectClass=*)',$attributes);
    if ($result === false) { 
        return array();
    }
    else {
        $entry = ldap_first_entry($ldap, $result);
        $attrs = ldap_get_attributes($ldap, $entry);
        if ($attrs['count'] > 0) {
            return $attrs;
        }
        else {
            return array();
        }
    }
}

function getHomedir($user) {
    $server = $GLOBALS['ldap']['ldap']['server'];
    $ldap = connectLDAP($server);
    $dn_ary = getDNs($ldap,"ou=members,ou=people,dc=griidc,dc=org","uid=$user");
    $attributes = getAttributes($ldap,$dn_ary[0]['dn'],array("homeDirectory"));
    return $attributes["homeDirectory"][0];
}


?>
