<?php

use Kelvinho\Notes\Singleton\Header;

if (!$authenticator->authenticated()) Header::forbidden();
$website = $websiteFactory->get($requestData->postCheck("websiteId"));
$session->set("websiteId", $website->getWebsiteId());
$session->set("remote", $website->getUrl());
$session->set("remoteExpires", time() + REMOTE_EXPIRES_DURATION); // after 60 seconds, main dashboard will direct to main dashboard
