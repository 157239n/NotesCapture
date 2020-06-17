<?php

use Kelvinho\Notes\Singleton\Header;

$guestUser = $userFactory->get($requestData->postCheck("user_handle"));
$website = $websiteFactory->get($requestData->postCheck("websiteId"));
if ($website->getUserHandle() !== $userFactory->currentUser()->getHandle()) Header::forbidden();
$permissionFactory->getFromWebsiteAndUser($website, $guestUser)->delete();
