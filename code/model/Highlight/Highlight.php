<?php

namespace Kelvinho\Notes\Highlight;

use Kelvinho\Notes\Comment\Comment;
use Kelvinho\Notes\Comment\CommentFactory;
use mysqli;

class Highlight {
    private mysqli $mysqli;
    private CommentFactory $commentFactory;
    private int $highlightId;
    private int $websiteId;
    private string $rawStrings;
    private ?Comment $rootComment = null;

    /**
     * Highlight constructor.
     * @param mysqli $mysqli
     * @param CommentFactory $commentFactory
     * @param int $highlightId
     * @param int $websiteId
     * @param string $rawStrings
     */
    public function __construct(mysqli $mysqli, CommentFactory $commentFactory, int $highlightId, int $websiteId, string $rawStrings) {
        $this->mysqli = $mysqli;
        $this->commentFactory = $commentFactory;
        $this->highlightId = $highlightId;
        $this->websiteId = $websiteId;
        $this->rawStrings = $rawStrings;
    }

    public function getRawStrings(): string {
        return $this->rawStrings;
    }

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
