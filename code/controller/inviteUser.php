<?php

/**
 * Invites another User to a Website, given:
 * - user_handle: The new User's handle
 * - websiteId: The Website's id
 *
 * Must:
 * - Owns the Website
 */

use Kelvinho\Notes\Permission\Permission;
use Kelvinho\Notes\Singleton\Header;

$guestUser = $userFactory->get($requestData->postCheck("user_handle"));
$website = $websiteFactory->get($requestData->postCheck("websiteId"));
if ($website->getUserHandle() !== $userFactory->currentUser()->getHandle()) Header::forbidden();
$permissionFactory->new($website, $guestUser, Permission::READ_AND_WRITE);
