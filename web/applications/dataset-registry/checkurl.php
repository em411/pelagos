<?php

//require 'build_url.php';

if (isset($_GET['url'])) {
    echo checkURL($_GET['url']);
    exit;
};

function checkURL($url)
{
    $urlScheme = parse_url($url,PHP_URL_SCHEME);
        
    $validhost = filter_var(gethostbyname(parse_url($url,PHP_URL_HOST)), FILTER_VALIDATE_IP);
    if($validhost)
    {
        switch ($urlScheme)
        {
            case "http":
                return checkHTTPURL($url);
                break;
            case "ftp":
                return checkFTPURL($url);
                break;
            case "https":
                return checkHTTPURL($url);
                break;
        }
    }
    else
    {
        return "false";
    }
}

function checkFTPURL($url)
{
    $urlHost = parse_url($url,PHP_URL_HOST);
    $ftpConn = ftp_connect($urlHost);
    $timeout = ftp_get_option($ftpConn, FTP_TIMEOUT_SEC);
    if ($ftpConn <> false)
    {
        return "FTP Connection Succesfull! [200]($ftpConn)";
    }
    else
    {
        return "Failed to connect! [404]($ftpConn)";
    }
}

function checkHTTPURL($url)
{
    $headers = array();
    $headers = get_headers($url, 1);
    
    $httpCodeP = substr($headers[0], 9, 1);
    $httpCode = substr($headers[0], 9, 3);
    if (array_key_exists('Location',$headers))
    {
        $altLocation = $headers["Location"];
    }
    
    switch ($httpCodeP)
    {
        case "2":
            return "true";
            break;
        case "3":
            return "true";
        break;
            case "4":
            return "false";
            break;
        case "5":
            return "true";
            break;
    }
}

?>
