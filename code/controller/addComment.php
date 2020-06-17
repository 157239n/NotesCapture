<?php

use Kelvinho\Notes\Singleton\Header;

$user = $userFactory->currentUser();
$highlight = $highlightFactory->get($requestData->postCheck("highlightId"));
$website = $websiteFactory->get($highlight->getWebsiteId());
if (!$authenticator->websiteAuthenticated($website)) Header::forbidden();

$parentComment = $commentFactory->getRoot($highlight->getHighlightId())->findChild($requestData->postCheck("parentCommentId"));
$comment = $commentFactory->new($highlight, $user->getHandle(), $requestData->postCheck("content"), $parentComment);

// returning commentId (0), user's name in base64 (1), user's picture in base 64 (2), commentTime (3)
echo $comment->getCommentId() . "\n";
echo base64_encode($user->getName()) . "\n";
echo base64_encode($user->getPictureUrl()) . "\n";
echo $timezone->display($user->getTimezone(), $comment->getUnixTime());
