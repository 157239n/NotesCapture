<?php

/**
 * Updates a User, given:
 * - name: The User's name
 * - timezone: The User's timezone, like America/New_York
 *
 * Must:
 * - Be logged in
 */

use Kelvinho\Notes\Singleton\Header;

if (!$authenticator->authenticated()) Header::forbidden();
$user = $userFactory->currentUser();
$user->setName($requestData->postCheck("name"));
$timezoneString = $requestData->postCheck("timezone");
if (!$timezone->hasTimezone($timezoneString)) Header::badRequest();
$user->setTimezone($timezoneString);
$user->saveState();
