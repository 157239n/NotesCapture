<?php

use Kelvinho\Notes\Singleton\Header;

if (!$authenticator->authenticated()) Header::forbidden();
$user = $userFactory->get($session->getCheck("user_handle"));
$user->setName($requestData->postCheck("name"));
$timezoneString = $requestData->postCheck("timezone");
if (!$timezone->hasTimezone($timezoneString)) Header::badRequest();
$user->setTimezone($timezoneString);
$user->saveState();
