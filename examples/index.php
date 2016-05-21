<?php

require 'config.php';

require '../vendor/autoload.php';

use Jump24\CubeSensors\CubeSensorsAuth;

$auth = new CubeSensorsAuth(CONSUMER_KEY, CONSUMER_SECRET, CALLBACK_URL);

$access_token = $auth->getAccessToken();

if ($access_token) {
    $_SESSION['token'] = $access_token->value['oauth_token'];

    $_SESSION['secret'] = $access_token->value['oauth_token_secret'];
    //
    //

    header('Location: demo.php');
    exit;
}
