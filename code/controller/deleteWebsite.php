<?php

use Kelvinho\Notes\Singleton\Header;

$website = $websiteFactory->get($requestData->postCheck("website_id"));
if (!$authenticator->websiteAuthenticated($website)) Header::forbidden();
$website->delete();
