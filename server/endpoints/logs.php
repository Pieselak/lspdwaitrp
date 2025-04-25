<?php
global $requestData, $endpoint, $action;

if (!defined('INCLUDED_FROM_SERVER') || !INCLUDED_FROM_SERVER) {
    http_response_code(403);
    echo json_encode([
        "success" => false,
        "message" => "Forbidden",
        "error_code" => "FORBIDDEN"
    ]);
    exit;
}

switch ($action) {
    case "getAll":
        if (!isUserLogged()) {
            http_response_code(401);
            echo json_encode([
                "success" => false,
                "message" => "Unauthorized - User not logged in",
                "error_code" => "UNAUTHORIZED"
            ]);
            exit;
        } elseif (!checkUserPermission("staff-logs", "view-logs")) {
            http_response_code(403);
            echo json_encode([
                "success" => false,
                "message" => "Forbidden - Missing view-logs permission",
                "error_code" => "FORBIDDEN"
            ]);
            exit;
        }

        $response = getLogs();
        echo json_encode($response);
        exit;
    case "delete":
        $logId = $requestData["id"] ?? null;
        if (!$logId) {
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "message" => "Log ID must be provided",
                "error_code" => "LOG_ID_REQUIRED"
            ]);
            exit;
        } elseif (!is_numeric($logId)) {
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "message" => "Log ID must be a number",
                "error_code" => "LOG_ID_INVALID"
            ]);
            exit;
        } elseif (!isUserLogged()) {
            http_response_code(401);
            echo json_encode([
               "success" => false,
               "message" => "Unauthorized - User not logged in",
                "error_code" => "UNAUTHORIZED"
            ]);
            exit;
        } elseif (!checkUserPermission("staff-logs", "delete-logs")) {
            http_response_code(403);
            echo json_encode([
                "success" => false,
                "message" => "Forbidden - Missing delete-logs permission",
                "error_code" => "FORBIDDEN"
            ]);
            exit;
        }

        $response = deleteLog($logId);
        echo json_encode($response);
        exit;
    default:
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Invalid action specified for endpoint {$endpoint}",
        "error_code" => "INVALID_ACTION"
    ]);
    exit;
}
