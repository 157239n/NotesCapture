<?php

use Kelvinho\Notes\Permission\Permission;
use Kelvinho\Notes\Singleton\Header;
use Kelvinho\Notes\User\User;
use function Kelvinho\Notes\map;

$website = $websiteFactory->get($requestData->postCheck("websiteId"));
if ($website->getUserHandle() !== $userFactory->currentUser()->getHandle()) Header::forbidden();
/** @var User[] $users */
$users = map($permissionFactory->getFromWebsite($website), fn(Permission $permission) => $permission->getUser());
echo count($users) . "\n";
foreach ($users as $user) {
    echo $user->getHandle() . "\n";
    echo base64_encode($user->getName()) . "\n";
    echo base64_encode($user->getPictureUrl()) . "\n";
}
