<?php

use Laravel\Lumen\Http\Request;

$router->group(['prefix' => 'user'], function () use ($router) {
    $router->post('register', 'UserAuthController@register');
    $router->get('register/verification/{token}', 'UserAuthController@verifyEmail');

    $router->post('logout', 'UserAuthController@logout');
    $router->post('login', 'UserAuthController@login');

    $router->post('/password/forgot', 'UserPasswordController@forgot');
    $router->post('/password/reset', 'UserPasswordController@reset');
});

$router->group(['middleware' => 'jwt-auth'], function ($router) {
    // Protected route...
    $router->get('test', function (Request $request) use ($router) {
        return "Authenticated data - Hungtest.";
    });
});
