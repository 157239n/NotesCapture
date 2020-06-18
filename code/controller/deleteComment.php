<?php

/**
 * Deletes a Comment, given:
 * - highlightId: The highlight id of the comment
 * - commentId: The comment id
 *
 * Must:
 * - Owns the comment
 */

use Kelvinho\Notes\Singleton\Header;

$comment = $commentFactory->getRoot($requestData->postCheck("highlightId"))->findChild($requestData->postCheck("commentId"));
if ($comment->getUserHandle() !== $userFactory->currentUser()->getHandle()) Header::forbidden();
$comment->delete();
