<?php

use Kelvinho\Notes\Singleton\Header;

$comment = $commentFactory->getRoot($requestData->postCheck("highlightId"))->findChild($requestData->postCheck("commentId"));
if ($comment->getUserHandle() !== $userFactory->currentUser()->getHandle()) Header::forbidden();
$comment->delete();
