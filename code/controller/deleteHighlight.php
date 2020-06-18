<?php

/**
 * Deletes a Highlight, given:
 * - highlightId: The Highlight's id
 *
 * Must:
 * - Owns the Website, or owns the Highlight's root Comment.
 */

use Kelvinho\Notes\Singleton\Header;

$highlight = $highlightFactory->get($requestData->postCheck("highlightId"));
$website = $websiteFactory->get($highlight->getWebsiteId());
$user = $userFactory->currentUser();
if ($website->getUserHandle() !== $user->getHandle())
    if ($highlight->getRootComment()->getUserHandle() !== $user->getHandle())
        Header::forbidden();
$highlight->delete();
