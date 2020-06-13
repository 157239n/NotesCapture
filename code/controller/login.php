<?php

use Kelvinho\Notes\Singleton\Header;

$user_handle = $requestData->postCheck("user_handle");
$password = $requestData->postCheck("password");

$authenticator->authenticate($user_handle, $password);
$authenticator->authenticated() ? Header::ok() : Header::forbidden();