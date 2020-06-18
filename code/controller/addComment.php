<?php

/**
 * Creates a new Comment given:
 * - highlightId: A highlight id
 * - parentCommentId: The parent comment's id
 * - content: The comment content
 *
 * Must:
 * - Have permission to read and write to the current Website.
 *
 * Returns 4 lines:
 * - The comment id
 * - The current/commenting user's name encoded in base 64
 * - The current/commenting user's picture url encoded in base 64
 * - The comment's formatted time according to the current user
 */

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
