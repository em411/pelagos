<?php

function getTasksAndDatasets($projects)
{
    $enforceMetadataRule = 0;
    if (isset($GLOBALS['config']['system']['enforce_approved_metadata'])
        and $GLOBALS['config']['system']['enforce_approved_metadata'] == 1) {
        $enforceMetadataRule = 1;
    } else {
        $enforceMetadataRule = 0;
    }

    $SELECT = "SELECT DISTINCT status, dataset_uid, d.dataset_udi AS udi,
               CASE WHEN r.dataset_title IS NULL THEN title ELSE r.dataset_title END AS title,

               CASE WHEN status = 2 THEN 10
                    WHEN status = 1 THEN 1
                    ELSE 0
               END AS identified,

               CASE WHEN registry_id IS NULL THEN 0
                    WHEN url_data IS NULL OR url_data = '' THEN 1
                    ELSE 10
               END AS registered,

               CASE WHEN metadata_dl_status = 'Completed' THEN
                        CASE WHEN metadata_status = 'Accepted' THEN 10
                             WHEN metadata_status = 'InReview' THEN 2
                             ELSE 1
                        END
                    ELSE 0
               END AS metadata,

               CASE WHEN dataset_download_status = 'Completed' THEN
                        CASE WHEN (metadata_status <> 'Accepted' AND '$enforceMetadataRule' = '1') THEN 4
                             WHEN access_status = 'None' THEN 10
                             WHEN access_status = 'Approval' THEN 9
                             WHEN access_status = 'Restricted' THEN 8
                             ELSE 0
                        END
                    WHEN dataset_download_status = 'RemotelyHosted' THEN
                        CASE WHEN (metadata_status <> 'Accepted' AND '$enforceMetadataRule' = '1') THEN 4
                             WHEN access_status = 'None' THEN 7
                             WHEN access_status = 'Approval' THEN 6
                             WHEN access_status = 'Restricted' THEN 5
                             ELSE 0
                        END
                    ELSE 0
               END AS available";

    $FROM = 'FROM datasets d
             LEFT JOIN (
                 registry r2
                 INNER JOIN (
                     SELECT MAX(registry_id) AS MaxID
                     FROM registry
                     GROUP BY dataset_udi
                 ) m
             ON r2.registry_id = m.MaxID
             ) r
             ON r.dataset_udi = d.dataset_udi';
    $RIS_DBH = openDB('RIS_RO');
    $GOMRI_DBH = openDB('GOMRI_RO');
    for ($i=0; $i<count($projects); $i++) {
        $pi = getPeopleDetails($RIS_DBH, array('projectId='.$projects[$i]['ID'], 'roleId=1'));
        if ($pi) {
            $projects[$i]['PI'] = $pi[0];
        }
        $projects[$i]['Institutions'] = getInstitutionDetails($RIS_DBH, array('projectId='.$projects[$i]['ID']));
        $taskFilter = array('projectId='.$projects[$i]['ID']);
        if (isset($GLOBALS['config']['exclude']['tasks'])) {
            foreach ($GLOBALS['config']['exclude']['tasks'] as $exclude) {
                $taskFilter[] = "title!=$exclude";
            }
        }
        $tasks = getTaskDetails($RIS_DBH, $taskFilter);
        if (count($tasks) > 0) {
            for ($j=0; $j<count($tasks); $j++) {
                $stmt = $GOMRI_DBH->prepare(
                    "$SELECT $FROM WHERE task_uid=" . $tasks[$j]['ID'] . ' AND status>0 ORDER BY udi;'
                );
                $stmt->execute();
                $datasets = $stmt->fetchAll();
                if (is_array($datasets)) {
                    $tasks[$j]['datasets'] = $datasets;
                }
                $stmt = null;
            }
            $projects[$i]['tasks'] = $tasks;
        } else {
            $stmt = $GOMRI_DBH->prepare(
                "$SELECT $FROM WHERE project_id=".$projects[$i]['ID'].' AND status>0 ORDER BY udi;'
            );
            $stmt->execute();
            $datasets = $stmt->fetchAll();
            if (is_array($datasets)) {
                $projects[$i]['datasets'] = $datasets;
            }
            $stmt = null;
        }
    }
    $RIS_DBH = null;
    $GOMRI_DBH = null;
    return $projects;
}
