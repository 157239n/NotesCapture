<?php

/**
 * Attempts to log the user in, given:
 * - user_handle: The User's handle
 * - password: The User's password
 */

use Kelvinho\Notes\Singleton\Header;

$user_handle = $requestData->postCheck("user_handle");
$password = $requestData->postCheck("password");

$authenticator->authenticate($user_handle, $password);
$authenticator->authenticated() ? Header::ok() : Header::forbidden();
