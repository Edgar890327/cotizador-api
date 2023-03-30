<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

$router->get('/', function () use ($router) {
    //return project details as json
    return response()->json(
        [
            "ws" => "api_rest",
            "content" => [
                "name" => "Seguros Villagomez",
                "autocotizador" => "https://www.autocotizador.villagomezseguros.com",
                "version" => "1.0.0",
                "author" => "PROCEL TI",
            ],
            "access" => "Inauthorized",
        ],
        200
    );
});
