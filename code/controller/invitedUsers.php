<?php

/**
 * Fetches the current invited Users given:
 * - websiteId: The Website's id
 *
 * Must:
 * - Owns the Website
 *
 * Returns:
 * - The amount of Users to be listed, and for each User:
 *   - The User's handle
 *   - The User's name, base 64 encoded
 *   - The User's picture url, base 64 encoded
 */

use Kelvinho\Notes\Permission\Permission;
use Kelvinho\Notes\Singleton\Header;
use Kelvinho\Notes\User\User;
use function Kelvinho\Notes\map;

$website = $websiteFactory->get($requestData->postCheck("websiteId"));
if ($website->getUserHandle() !== $userFactory->currentUser()->getHandle()) Header::forbidden();
/** @var User[] $users */
$users = map($permissionFactory->getFromWebsite($website), fn(Permission $permission) => $userFactory->get($permission->getUserHandle()));
echo count($users) . "\n";
foreach ($users as $user) {
    echo $user->getHandle() . "\n";
    echo base64_encode($user->getName()) . "\n";
    echo base64_encode($user->getPictureUrl()) . "\n";
}
