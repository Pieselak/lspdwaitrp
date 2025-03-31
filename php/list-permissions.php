<?php 
    return [
        // Role ID => ["Permission1", "Permission2"...]
        1 => [],
        2 => [
            "staff-applications" => []
        ],
        3 => [
            "staff-applications" => [], 
            "staff-academies" => [],
        ],
        4 => [
            "staff-applications" => [],
            "staff-academies" => [],
            "staff-suspensions" => [],
            "staff-warnings" => []
            ],
        5 => [
            "staff-applications" => [],
            "staff-academies" => [], 
            "staff-suspensions" => [], 
            "staff-warnings" => [], 
            "staff-users" => []
        ],
        6 => [
            "staff-applications" => [], 
            "staff-academies" => [], 
            "staff-suspensions" => [], 
            "staff-warnings" => [], 
            "staff-users" => [], 
            "staff-logs" => []
        ],
        7 => [
            "staff-admin" => [], 
            "staff-applications" => ["view-applications"], 
            "staff-academies" => [], 
            "staff-suspensions" => [], 
            "staff-warnings" => [], 
            "staff-users" => [], 
            "staff-logs" => []
        ],
    ]
?>