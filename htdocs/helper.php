<?php
require_once('../db/connection.php');

function check_request_method($methods)
{
    if (!in_array($_SERVER['REQUEST_METHOD'], $methods)) {
        $error =  'method not allowed';
        http_response_code(405);
        echo json_encode(['status' => 'error', 'error' => $error]);
        return false;
    }
    return true;
};
function get_request_body()
{
    $arrContextOptions = array(
        "ssl" => array(
            "verify_peer" => false,
            "verify_peer_name" => false,
        ),
    );
    return json_decode(file_get_contents('php://input', false, stream_context_create($arrContextOptions)));
}

function get_request_headers()
{
    $headers = array();

    require('config.php');
    if ($config['use_apache']) {
        foreach (apache_request_headers() as $key => $value) {
            $headers[$key] = $value;
        }
    } else {
        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) <> 'HTTP_') {
                continue;
            }
            $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
            $headers[$header] = $value;
        }
    }

    return $headers;
}

function set_CORS_response()
{
    header('Access-Control-Allow-Origin:*');
    header('Access-Control-Allow-Methods:POST, GET, PUT, PATCH, DELETE');
    header('Access-Control-Allow-Headers:x-requested-with,content-type,custom-authorization');
}
