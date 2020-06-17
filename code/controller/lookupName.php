<?php

use Kelvinho\Notes\User\User;
use function Kelvinho\Notes\filter;
use function Kelvinho\Notes\map;

$starts = base64_decode($requestData->postCheck("starts"));
$excludedUserHandles = map(explode("\n", trim($requestData->postCheck("exclude"), "\n")), fn($user_handle_64) => base64_decode($user_handle_64));
$excludedUserHandles[] = $userFactory->currentUser()->getHandle();
$users = $userFactory->startingWith($starts);
$users = filter($users, function (User $user) use ($excludedUserHandles) {
    return !in_array($user->getHandle(), $excludedUserHandles);
});
echo count($users) . "\n";
foreach ($users as $user) {
    echo $user->getHandle() . "\n";
    echo base64_encode($user->getName()) . "\n";
    echo base64_encode($user->getPictureUrl()) . "\n";
}
