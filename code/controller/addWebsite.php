<?php

/**
 * Creates a new Website given:
 * - categoryId: The containing Category's id
 * - url: The Website's url
 * - (optional) title: The Website's title
 *
 * Must:
 * - Be logged in
 */

use Kelvinho\Notes\Singleton\Header;

if (!$authenticator->authenticated()) Header::forbidden();
$website = $websiteFactory->new($categoryFactory->get($requestData->postCheck("categoryId")), $requestData->postCheck("url"), $requestData->post("title") ?? "");
echo $website->getWebsiteId();
