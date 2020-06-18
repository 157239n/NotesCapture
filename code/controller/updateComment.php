<?php

/**
 * Updates a Comment's content, given:
 * - highlightId: The Highlight's id the Comment is in
 * - commentId: The Comment's id
 * - content: The Comment's content
 *
 * Must:
 * - Owns the Comment
 */

use Kelvinho\Notes\Singleton\Header;

$comment = $commentFactory->getRoot($requestData->postCheck("highlightId"))->findChild((int)$requestData->postCheck("commentId"));
if ($comment->getUserHandle() !== $userFactory->currentUser()->getHandle()) Header::forbidden();
$comment->setContent($requestData->postCheck("content"))->saveState();
