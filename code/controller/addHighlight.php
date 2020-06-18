<?php

/**
 * Creates a new highlight given:
 * - strings: JSON array of strings to save
 * - websiteId: The current Website's id
 *
 * Must:
 * - Have read and write access to the Website
 *
 * Returns:
 * - The highlight id
 * - The root Comment's id
 * - The current User's name encoded in base 64
 * - The current User's picture url encoded in base 64
 * - The root Comment's formatted time according to the current User's timezone
 */

use Kelvinho\Notes\Singleton\Header;

$strings = $requestData->postCheck("strings");
$website = $websiteFactory->get($requestData->postCheck("websiteId"));

if (!$authenticator->websiteAuthenticated($website)) Header::forbidden();
$highlight = $highlightFactory->new($website, json_decode($strings, true));
$rootComment = $commentFactory->getRoot($highlight->getHighlightId());
$user = $userFactory->currentUser();

echo $highlight->getHighlightId() . "\n";
echo $rootComment->getCommentId() . "\n";
echo base64_encode($user->getName()) . "\n";
echo base64_encode($user->getPictureUrl()) . "\n";
echo $timezone->display($user->getTimezone(), $rootComment->getUnixTime());
