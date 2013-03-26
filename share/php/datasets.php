<?php

$GLOBALS['PGSQL_LIKE_MAP'] = array(
    '=' => 'ILIKE',
    '!=' => 'NOT ILIKE'
);

$GLOBALS['IS_MAP'] = array(
    '=' => 'IS',
    '!=' => 'IS NOT'
);

$GLOBALS['NULL_MAP'] = array(
    'null' => 'NULL',
    'NULL' => 'NULL',
    '0' => 'NULL',
    'false' => 'NULL'
);

if (!defined('FILTER_REG')) define('FILTER_REG','/^(.*?)\s*(>=|<=|>|<|!=|=)\s*(.*?)$/');

$GLOBALS['REGISTRY_FIELDS'] = array(
    "r.registry_id",
    "r.data_server_type",
    "r.url_data",
    "r.url_metadata",
    "r.data_source_pull",
    "r.doi",
    "r.username",
    "r.password",
    "r.availability_date",
    "r.access_status",
    "r.access_period",
    "r.access_period_start",
    "r.access_period_weekdays",
    "r.dataset_poc_name",
    "r.dataset_poc_email",
    "r.submittimestamp",
    "r.userid",
    "r.authentication",
    "r.generatedoi",
    "r.dataset_download_start_datetime",
    "r.dataset_download_size",
    "r.dataset_download_end_datetime",
    "r.dataset_filename",
    "r.dataset_uuid",
    "r.dataset_metadata metadata_filename",
    "r.dataset_download_error_log",
    "r.dataset_download_status",
    "r.dataset_originator",
    "to_char(r.submittimestamp,'YYYY') as year"
);

$GLOBALS['DIF_FIELDS'] = array(
    "d.dataset_uid",
    "d.task_uid",
    "d.project_id",
    "d.dataset_type",
    "d.dataset_for",
    "d.size",
    "d.observation",
    "d.approach",
    "d.start_date",
    "d.end_date",
    "d.geo_location",
    "d.historic_links",
    "d.meta_editor",
    "d.meta_standards",
    "d.point",
    "d.national",
    "d.ethical",
    "d.remarks",
    "d.primary_poc",
    "d.secondary_poc",
    "d.logname",
    "d.status",
    "d.datafor"
);

$GLOBALS['REGISTRY_OVERRIDE_FIELDS'] = array(
    "CASE WHEN r.dataset_title IS NULL THEN title ELSE r.dataset_title END AS title",
    "CASE WHEN r.dataset_abstract IS NULL THEN abstract ELSE r.dataset_abstract END AS abstract"
);

$GLOBALS['IDENTIFIED_FROM'] = 'FROM datasets d
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

$GLOBALS['REGISTERED_FROM'] = 'FROM registry r
                               INNER JOIN (
                                   SELECT MAX(registry_id) AS MaxID
                                   FROM registry
                                   GROUP BY substr(registry_id,1,16)
                               ) m
                               ON r.registry_id = m.MaxID
                               LEFT JOIN datasets d
                               ON d.dataset_udi = r.dataset_udi';

$GLOBALS['IDENTIFIED_SEARCH_RANK'] = "
JOIN
(
    SELECT d2.dataset_uid, COUNT(search_word) AS search_rank
    FROM
    (
        SELECT
            dataset_uid,
                d3.dataset_udi || ' ' ||
                title || ' ' ||
                abstract || ' ' ||
                CASE WHEN dataset_originator IS NULL THEN '' ELSE dataset_originator END AS search_field
        FROM datasets d3
        LEFT JOIN (
            registry r4
            INNER JOIN (
                SELECT MAX(registry_id) AS MaxID
                FROM registry
                GROUP BY dataset_udi
            ) m
        ON r4.registry_id = m.MaxID
        ) r3
        ON r3.dataset_udi = d3.dataset_udi
    ) AS d2

    JOIN search_temp ON d2.search_field ~* ('\\y' || search_temp.search_word || '\\y')
    GROUP BY d2.dataset_uid

) ranked ON ranked.dataset_uid = d.dataset_uid";

$GLOBALS['REGISTRY_SEARCH_RANK'] = "
JOIN
(
    SELECT r2.registry_id, COUNT(search_word) AS search_rank
    FROM
    (
        SELECT
            registry_id,
                CASE WHEN dataset_udi IS NULL THEN substr(registry_id,1,16) ELSE dataset_udi END  || ' ' ||
                dataset_title || ' ' ||
                dataset_abstract || ' ' ||
                dataset_originator || ' ' ||
                to_char(submittimestamp,'YYYY')
            AS search_field
        FROM registry r3
        INNER JOIN
        (
            SELECT MAX(registry_id) AS MaxID
            FROM registry
            GROUP BY substr(registry_id,1,16)
        ) m
        ON r3.registry_id = m.MaxID

    ) AS r2

    JOIN search_temp ON r2.search_field ~* ('\\y' || search_temp.search_word || '\\y')
    GROUP BY r2.registry_id

) ranked ON ranked.registry_id = r.registry_id";

function count_identified_datasets($dbh, $filters = array(), $search = '') {
    create_search_temp($dbh,$search);

    $SELECT = 'SELECT COUNT(d.dataset_udi)';

    $WHERE = build_where($filters);

    $stmt = $dbh->prepare("$SELECT $GLOBALS[IDENTIFIED_FROM] $GLOBALS[IDENTIFIED_SEARCH_RANK] $WHERE;");

    if (!$stmt->execute()) {
        $arr = $stmt->errorInfo();
        print_r($arr);
    }

    $retval = $stmt->fetchColumn();

    drop_search_temp($dbh);

    return $retval;
}

function count_registered_datasets($dbh, $filters = array(), $search = '') {
    create_search_temp($dbh,$search);

    $SELECT = 'SELECT COUNT(r.registry_id)';

    $WHERE = build_where($filters,true);

    $stmt = $dbh->prepare("$SELECT $GLOBALS[REGISTERED_FROM] $GLOBALS[REGISTRY_SEARCH_RANK] $WHERE;");

    if (!$stmt->execute()) {
        $arr = $stmt->errorInfo();
        print_r($arr);
    }

    $retval = $stmt->fetchColumn();

    drop_search_temp($dbh);

    return $retval;
}

function get_identified_datasets($dbh, $filters = array(), $search = '', $order_by = 'udi') {
    create_search_temp($dbh,$search);

    $SELECT = 'SELECT ' . 
              implode(',',$GLOBALS['DIF_FIELDS']) . ',' .
              implode(',',$GLOBALS['REGISTRY_FIELDS']) . ',' .
              implode(',',$GLOBALS['REGISTRY_OVERRIDE_FIELDS']) . ',' .
              'd.dataset_udi udi';

    $WHERE = build_where($filters);

    $stmt = $dbh->prepare("$SELECT $GLOBALS[IDENTIFIED_FROM] $GLOBALS[IDENTIFIED_SEARCH_RANK] $WHERE ORDER BY search_rank DESC, $order_by;");

    if (!$stmt->execute()) {
        $arr = $stmt->errorInfo();
        print_r($arr);
    }

    $retval = $stmt->fetchAll();

    drop_search_temp($dbh);

    return $retval;
}

function get_registered_datasets($dbh, $filters = array(), $search = '', $order_by = 'registry_id') {
    create_search_temp($dbh,$search);

    $SELECT = 'SELECT ' . 
              implode(',',$GLOBALS['REGISTRY_FIELDS']) . ',' .
              implode(',',$GLOBALS['DIF_FIELDS']) . ',' .
              implode(',',$GLOBALS['REGISTRY_OVERRIDE_FIELDS']) . ',' .
              "CASE WHEN r.dataset_udi IS NULL THEN substr(r.registry_id,1,16) ELSE r.dataset_udi END AS udi";

    $WHERE = build_where($filters,true);

    $stmt = $dbh->prepare("$SELECT $GLOBALS[REGISTERED_FROM] $GLOBALS[REGISTRY_SEARCH_RANK] $WHERE ORDER BY search_rank DESC, $order_by;");

    if (!$stmt->execute()) {
        $arr = $stmt->errorInfo();
        print_r($arr);
    }

    $retval = $stmt->fetchAll();

    drop_search_temp($dbh);

    return $retval;
}

function build_where($filters,$registered = false) {
    $table = $registered ? 'r' : 'd';
    $WHERE = 'WHERE TRUE';

    foreach ($filters as $filter) {
        if (preg_match(FILTER_REG,$filter,$matches)) {
            switch (strtolower($matches[1])) {
                case 'registry_id':
                    $WHERE .= " AND r.registry_id " . $GLOBALS['PGSQL_LIKE_MAP'][$matches[2]] . " '$matches[3]'";
                    break;
                case 'registry_ids':
                    $registry_ids = preg_split('/,/',$matches[3]);
                    $registry_id_filters = array();
                    foreach ($registry_ids as $registry_id) {
                        $registry_id_filters[] = "r.registry_id " . $GLOBALS['PGSQL_LIKE_MAP'][$matches[2]] . " '$registry_id%'";
                    }
                    if ($matches[2] == '!=') { $glue = ' AND '; }
                    else { $glue = ' OR '; }
                    $WHERE .= " AND (" . implode($glue,$registry_id_filters) . ")";
                    break;
                case 'udi':
                    $WHERE .= " AND $table.dataset_udi " . $GLOBALS['PGSQL_LIKE_MAP'][$matches[2]] . " '$matches[3]'";
                    break;
                case 'udis':
                    $udis = preg_split('/,/',$matches[3]);
                    $udiFilters = array();
                    foreach ($udis as $udi) {
                        $udiFilters[] = "$table.dataset_udi " . $GLOBALS['PGSQL_LIKE_MAP'][$matches[2]] . " '$udi'";
                    }
                    if ($matches[2] == '!=') { $glue = ' AND '; }
                    else { $glue = ' OR '; }
                    $WHERE .= " AND (" . implode($glue,$udiFilters) . ")";
                    break;
                case 'taskid':
                    $WHERE .= " AND task_uid $matches[2] $matches[3]";
                    break;
                case 'projectid':
                    $WHERE .= " AND project_id $matches[2] $matches[3]";
                    break;
                case 'projectids':
                    $projectIds = preg_split('/,/',$matches[3]);
                    $projects = array();
                    foreach ($projectIds as $projectId) {
                        $projects[] = "project_id $matches[2] $projectId";
                    }
                    if ($matches[2] == '!=') { $glue = ' AND '; }
                    else { $glue = ' OR '; }
                    $WHERE .= " AND (" . implode($glue,$projects) . ")";
                    break;
                case 'title':
                    if ($registered) {
                        $WHERE .= " AND r.dataset_title " . $GLOBALS['PGSQL_LIKE_MAP'][$matches[2]] . " '$matches[3]'";
                    }
                    else {
                        $WHERE .= " AND title " . $GLOBALS['PGSQL_LIKE_MAP'][$matches[2]] . " '$matches[3]'";
                    }
                    break;
                case 'abstract':
                    if ($registered) {
                        $WHERE .= " AND r.dataset_abstract " . $GLOBALS['PGSQL_LIKE_MAP'][$matches[2]] . " '$matches[3]'";
                    }
                    else {
                        $WHERE .= " AND abstract " . $GLOBALS['PGSQL_LIKE_MAP'][$matches[2]] . " '$matches[3]'";
                    }
                    break;
                case 'status':
                    $WHERE .= " AND status $matches[2] $matches[3]";
                    break;
                case 'dataset_download_status':
                    if ($matches[2] == '!=') {
                        $WHERE .= " AND (r.dataset_download_status IS NULL OR r.dataset_download_status != '$matches[3]')";
                    }
                    else {
                        $WHERE .= " AND (r.dataset_download_status IS NOT NULL AND r.dataset_download_status = '$matches[3]')";
                    }
                    break;
                case 'registered':
                    $WHERE .= " AND r.registry_id " . $GLOBALS['IS_MAP'][$matches[2]] . ' ' . $GLOBALS['NULL_MAP'][$matches[3]];
                    break;
                case 'filter':
                    $WHERE .= " AND (";
                    if ($registered) {
                        $WHERE .= "dataset_title " . $GLOBALS['PGSQL_LIKE_MAP'][$matches[2]] . " '$matches[3]'";
                        $WHERE .= " OR dataset_abstract " . $GLOBALS['PGSQL_LIKE_MAP'][$matches[2]] . " '$matches[3]'";
                    }
                    else {
                        $WHERE .= "title " . $GLOBALS['PGSQL_LIKE_MAP'][$matches[2]] . " '$matches[3]'";
                        $WHERE .= " OR abstract " . $GLOBALS['PGSQL_LIKE_MAP'][$matches[2]] . " '$matches[3]'";
                    }
                    $WHERE .= ")";
                    break;
            }
        }
    }

    return $WHERE;
}

function create_search_temp($dbh,$search) {
    $stmt = $dbh->prepare("CREATE TEMP TABLE search_temp AS SELECT search_word FROM regexp_split_to_table('$search', '\\s+') AS search_word;");
    if (!$stmt->execute()) {
        $arr = $stmt->errorInfo();
        print_r($arr);
    }
}

function drop_search_temp($dbh) {
    $stmt = $dbh->prepare("DROP TABLE search_temp;");
    if (!$stmt->execute()) {
        $arr = $stmt->errorInfo();
        print_r($arr);
    }
}

?>
