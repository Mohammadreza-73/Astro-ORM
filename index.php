<?php

use App\Helpers\JsonHandler;
use App\Database\PDOQueryBuilder;

require_once "./vendor/autoload.php";

$request = JsonHandler::request();

switch ($_SERVER['REQUEST_METHOD'])
{
    case 'POST':
        PDOQueryBuilder::table('users')->create($request);
        JsonHandler::response(null, 201); // 201: created
    break;

    case 'PUT':
        PDOQueryBuilder::table('users')
        ->where('id', $request['id'])
        ->update($request);
    
        JsonHandler::response(null, 200);
    break;

    case 'GET':
        $user = PDOQueryBuilder::table('users')
        ->find($request['id']);

        JsonHandler::response($user, 200);
    break;

    case 'DELETE':
        PDOQueryBuilder::table('users')
        ->where('id', $request['id'])
        ->delete();

        JsonHandler::response(null, 204); //204: No Content
    break;
}
