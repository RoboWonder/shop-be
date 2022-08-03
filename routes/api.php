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

$router->group(['middleware' => 'jwt-auth'], function () use ($router) {
    $router->post('products', 'ProductController@create');
    $router->get('products', 'ProductController@list');
    $router->get('products/{id}', 'ProductController@view');
    $router->put('products/{id}', 'ProductController@update');
    $router->delete('products/{id}', 'ProductController@delete');

    $router->post('product/groups', 'ProductGroupController@create');
    $router->get('product/groups', 'ProductGroupController@list');
    $router->put('product/groups/{id}', 'ProductGroupController@update');
    $router->delete('product/groups/{id}', 'ProductGroupController@delete');
});

$router->group(['middleware' => 'jwt-auth'], function ($router) {
    // Protected route...
    $router->get('protected', function (Request $request) use ($router) {
        return "Authenticated";
    });
});
