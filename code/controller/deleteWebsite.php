<?php

use Kelvinho\Notes\Permission\PermissionNotFound;
use Kelvinho\Notes\Singleton\Header;

$website = $websiteFactory->get($requestData->postCheck("website_id"));
if ($website->getUserHandle() !== $userFactory->currentUser()->getHandle()) { // current user doesn't own the website, so delete the permission only
    try {
        $permission = $permissionFactory->getFromWebsiteAndUser($website, $userFactory->currentUser());
        $permission->delete();
    } catch (PermissionNotFound $permissionNotFound) {
        Header::forbidden();
    }
} else $website->delete();
