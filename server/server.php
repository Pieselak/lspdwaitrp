<?php
require_once ("functions.php");

$requestMethod = $_SERVER["REQUEST_METHOD"];
$requestData = [];

switch ($requestMethod) {
    case "POST": // for creating new data
        $contentType = $_SERVER["CONTENT_TYPE"] ?? '';

        if (strpos($contentType, 'application/json') !== false) {
            $jsonData = file_get_contents('php://input');
            $requestData = json_decode($jsonData, true) ?: [];
        } else {
            $requestData = $_POST;
        }
        break;
    case "GET": // for retrieving data
        $requestData = $_GET;
        break;
    case "PUT": // for updating data
        $jsonData = file_get_contents("php://input");
        $requestData = json_decode($jsonData, true) ?: [];
        break;

    case "DELETE": // for deleting data
        $jsonData = file_get_contents("php://input");
        $jsonRequestData = json_decode($jsonData, true) ?: [];
        $requestData = array_merge($_GET, $jsonRequestData);
        break;
    default:
        http_response_code(405);
        header("Allow: GET, POST, PUT, DELETE");
        echo json_encode([
            "success" => false,
            "message" => "Method {$requestMethod} is not allowed",
            "error_code" => "METHOD_NOT_ALLOWED"
        ]);
        exit;
}

$endpoint = $requestData["endpoint"] ?? null;
$action = $requestData["action"] ?? null;

if (!$endpoint) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Endpoint is required",
        "error_code" => "ENDPOINT_REQUIRED"
    ]);
    exit;
}

const INCLUDED_FROM_SERVER = true;

$endpointFile = "endpoints/{$endpoint}.php";
if (file_exists($endpointFile)) {
    require_once($endpointFile);
} else {
    http_response_code(404);
    echo json_encode([
        "success" => false,
        "message" => "Endpoint not found",
        "error_code" => "ENDPOINT_NOT_FOUND"
    ]);
    exit;
}