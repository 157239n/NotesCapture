<?php

namespace Kelvinho\Notes\Highlight;

use Kelvinho\Notes\Comment\Comment;
use Kelvinho\Notes\Comment\CommentFactory;
use mysqli;

/**
 * Class Highlight. A website can have multiple Highlights. Each of them has pieces of strings to save and later search
 * for. A Highlight also always have a root Comment attached to it, itself can form a linked list of other Comments.
 *
 * Each string in the list of strings to save is converted to base 64, joined together with a white space, then saved in the database.
 *
 * @package Kelvinho\Notes\Highlight
 * @author Quang Ho <157239q@gmail.com>
 * @copyright Copyright (c) 2020 Quang Ho <https://github.com/157239n>
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */
class Highlight {
    private mysqli $mysqli;
    private CommentFactory $commentFactory;
    private int $highlightId;
    private int $websiteId;
    private string $rawStrings;
    private ?Comment $rootComment = null;

    public function __construct(mysqli $mysqli, CommentFactory $commentFactory, int $highlightId, int $websiteId, string $rawStrings) {
        $this->mysqli = $mysqli;
        $this->commentFactory = $commentFactory;
        $this->highlightId = $highlightId;
        $this->websiteId = $websiteId;
        $this->rawStrings = $rawStrings;
    }

    /**
     * Returns base 64 encoded strings separated by spaces.
     *
     * @return string
     */
    public function getRawStrings(): string {
        return $this->rawStrings;
    }

    /**
     * Gets the root comment. Important to note that the root comment's information is not loaded until we explicitly
     * requested for it here.
     *
     * @return Comment
     */
    public function getRootComment(): Comment {
        if ($this->rootComment === null) $this->rootComment = $this->commentFactory->getRoot($this->highlightId);
        return $this->rootComment;
    }

    public function delete(): void {
        $this->getRootComment()->delete();
        $this->mysqli->query("delete from highlights where highlight_id = $this->highlightId");
    }

    public function getHighlightId(): int {
        return $this->highlightId;
    }

    public function getWebsiteId(): int {
        return $this->websiteId;
    }
}
