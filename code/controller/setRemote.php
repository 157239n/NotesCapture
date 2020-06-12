<?php

use Kelvinho\Notes\Singleton\Header;

if (!$authenticator->authenticated()) Header::forbidden();
$website = $websiteFactory->get($requestData->postCheck("websiteId"));
$session->set("websiteId", $website->getWebsiteId());
$session->set("remote", $website->getUrl());
