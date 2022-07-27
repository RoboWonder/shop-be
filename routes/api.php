<?php

use Laravel\Lumen\Http\Request;

$router->group(['prefix' => 'user'], function () use ($router) {
    $router->post('register', 'UserAuthController@register');
    $router->get('register/verification/{token}', 'UserAuthController@verifyEmail');

    $router->post('login', 'UserAuthController@login');
    $router->post('logout', 'UserAuthController@logout');

    $router->post('/password/forgot', 'UserPasswordController@forgot');
    $router->post('/password/reset', 'UserPasswordController@reset');

    $router->group(['middleware' => 'verify-email'], function ($router) {
        // some routes need verified email permission.
    });
});

$router->group(['middleware' => 'jwt-auth'], function ($router) {
    // Protected route...
    $router->get('protected', function (Request $request) use ($router) {
        return "Authenticated";
    });
});
