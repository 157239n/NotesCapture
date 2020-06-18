<?php

/**
 * Opposite of inviteUser, this revokes a User's access to a Website given:
 * - user_handle: The User to revoke access from
 * - websiteId: The Website's id
 *
 * Must:
 * - Owns the Website
 */

use Kelvinho\Notes\Singleton\Header;

$guestUser = $userFactory->get($requestData->postCheck("user_handle"));
$website = $websiteFactory->get($requestData->postCheck("websiteId"));
if ($website->getUserHandle() !== $userFactory->currentUser()->getHandle()) Header::forbidden();
$permissionFactory->getFromWebsiteAndUser($website, $guestUser)->delete();
