<?php

use App\Database\PDOQueryBuilder;

require_once "./vendor/autoload.php";

function json_response($data = null, int $statusCode = 200)
{
    header_remove();
    header('Content-Type: application/json');
    http_response_code($statusCode);
    echo json_encode($data);
    exit();
}

function request()
{
    return json_decode(file_get_contents('php://input'), true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    PDOQueryBuilder::table('users')->create(request());

    json_response(null, 201); // 201: created
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    PDOQueryBuilder::table('users')
    ->where('id', request()['id'])
    ->update(request());

    json_response(null, 200);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $user = PDOQueryBuilder::table('users')
        ->find(request()['id']);

    json_response($user, 200);
}
