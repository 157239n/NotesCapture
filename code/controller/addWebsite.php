<?php

use Kelvinho\Notes\Singleton\Header;

if (!$authenticator->authenticated()) Header::forbidden();
$website = $websiteFactory->new($categoryFactory->get($requestData->postCheck("category_id")), $requestData->postCheck("url"), $requestData->post("title") ?? "");
echo $website->getWebsiteId();
