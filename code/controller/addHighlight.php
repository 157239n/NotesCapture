<?php

use Kelvinho\Notes\Singleton\Header;

$strings = $requestData->postCheck("strings");
$website = $websiteFactory->get($requestData->postCheck("websiteId"));

if (!$authenticator->websiteAuthenticated($website)) Header::forbidden();
echo $highlightFactory->new($website, "", json_decode($strings, true))->getHighlightId();
