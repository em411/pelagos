<?php

if (!function_exists('getDMsFromUser')) {
    function getDMsFromUser($uid)
    {
        require_once 'ResearchConsortia.php';
        require_once 'RIS.php';
        $dms = array();
        foreach (getRCsFromUser($uid) as $rc) {
            $dms = array_merge($dms, getDMsFromRC($rc));
        }
        return $dms;
    }
}

if (!function_exists('getDMsFromUDI')) {
    function getDMsFromUDI($udi)
    {
        require_once 'ResearchConsortia.php';
        require_once 'RIS.php';
        return getDMsFromRC(getRCFromUDI($udi));
    }
}
