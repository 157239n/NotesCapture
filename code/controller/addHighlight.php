<?php

use Kelvinho\Notes\Singleton\Header;

$strings = $requestData->postCheck("strings");
$website = $websiteFactory->get($requestData->postCheck("websiteId"));

if (!$authenticator->websiteAuthenticated($website)) Header::forbidden();
$highlight = $highlightFactory->new($website, json_decode($strings, true));
$comment = $commentFactory->getRoot($highlight->getHighlightId());
$user = $userFactory->currentUser();
// returning serverHighlightId (0), commentId (1), user's name in base64 (2), user's picture in base 64 (3), commentTime (4)
echo $highlight->getHighlightId() . "\n";
echo $comment->getCommentId() . "\n";
echo base64_encode($user->getName()) . "\n";
echo base64_encode($user->getPictureUrl()) . "\n";
echo $timezone->display($user->getTimezone(), $comment->getUnixTime());
