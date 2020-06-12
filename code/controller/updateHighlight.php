<?php

use Kelvinho\Notes\Singleton\Header;

$highlight = $highlightFactory->get($requestData->postCheck("highlightId"));
if (!$authenticator->websiteAuthenticated($websiteFactory->get($highlight->getWebsiteId()))) Header::forbidden();
$highlight->setComment($requestData->postCheck("comment"))->saveState();
