<?php

/**
 * Attempts to register a new user, given:
 * - user_handle: The User's intended handle
 * - password: The User's password
 * - name: The User's name
 * - timezone: The User's timezone string, like America/New_York
 *
 * If registering, must:
 * - Have the user handle's length less than USER_HANDLE_LENGTH_LIMIT
 * - Have the user's name's length less than USER_NAME_LENGTH_LIMIT
 * - Have the user's handle be alphanumeric only
 * - Have a unique user handle
 */

use Kelvinho\Notes\Singleton\Header;

$user_handle = $requestData->postCheck("user_handle");
$password = $requestData->postCheck("password");
$name = $requestData->postCheck("name");
$timezoneString = $requestData->postCheck("timezone");
if (!$timezone->hasTimezone($timezoneString)) Header::badRequest();

if (strlen($user_handle) > USER_HANDLE_LENGTH_LIMIT) Header::badRequest();
if (strlen($name) > USER_NAME_LENGTH_LIMIT) Header::badRequest();
if (preg_match('/[^A-Za-z0-9_]/', $user_handle)) Header::badRequest();
if ($userFactory->exists($user_handle)) Header::badRequest();

$userFactory->new($user_handle, $password, $name, $timezoneString);
