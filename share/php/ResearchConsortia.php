<?php

if (!function_exists('getRCFromUDI')) {
    function getRCFromUDI($udi)
    {
        # Precondition: Every registration has exactly 1 entry in the datasets (DIF) table.

        require_once 'DBUtils.php';
        $dbh = openDB("GOMRI_RW");
        $sql = "SELECT project_id from datasets WHERE dataset_udi = :udi";
        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(":udi", $udi);
        $stmt->execute();
        $project_id = null;

        # Currently an UDI cannot be in more than one RC,
        # so the following will get a single value.
        if ($row = $stmt->fetch()) {
            $project_id = $row[0];
        }

        $dbms = null;
        unset($dbms);

        return $project_id;
    }
}

if (!function_exists('getRCsFromUser')) {
    function getRCsFromUser($userId)
    {
        require_once 'RIS.php';
        require_once 'DBUtils.php';
        require_once 'ldap.php';
        #consult LDAP for $userId -> $RIS_user_ID
        $risUserId = getEmployeeNumberFromUID($userId);
        # open a database connetion to RIS
        $RIS_DBH = openDB('RIS_RO');
        $project_ids = getRCsFromRISUser($RIS_DBH, $risUserId);
        # close database connection
        $RIS_DBH = null;
        return $project_ids;
    }
}
