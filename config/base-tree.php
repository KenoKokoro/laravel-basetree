<?php

return [
    /**
     * The model that is used for authorization
     */
    'auth-model' => \App\User::class,

    /**
     * If the authorization value is set to true, then each controller before doing the actual action will try to call
     * the policy for the given model, which means that if that is not set correctly, a forbidden exception will
     * be thrown
     *
     * Default: false
     */
    'authorization' => env('BASE_TREE_AUTHORIZATION', false),

    /**
     * If the log value is set to true, then each incoming request and response will be logged in the database
     * For that purpose you will need to publish the traffic_logs migration
     * NOTE: REQUIRES kenokokoro/laravel-basetree-logger package to be installed
     *
     * Default: false
     */
    'log' => env('BASE_TREE_LOG', false),
];
