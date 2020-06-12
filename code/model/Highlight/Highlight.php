<?php

namespace Kelvinho\Notes\Highlight;

use Kelvinho\Notes\Singleton\Logs;
use mysqli;

class Highlight {
    private mysqli $mysqli;
    private int $highlightId;
    private int $websiteId;
    private string $comment;
    private string $rawStrings;

    /**
     * Highlight constructor.
     * @param mysqli $mysqli
     * @param int $highlightId
     * @param int $websiteId
     * @param string $rawStrings
     * @param string $comment
     */
    public function __construct(mysqli $mysqli, int $highlightId, int $websiteId, string $rawStrings, string $comment) {
        $this->mysqli = $mysqli;
        $this->highlightId = $highlightId;
        $this->websiteId = $websiteId;
        $this->rawStrings = $rawStrings;
        $this->comment = $comment;
    }

    public function getRawStrings(): string {
        return $this->rawStrings;
    }

    public function delete(): void {
        $this->mysqli->query("delete from highlights where highlight_id = $this->highlightId");
    }

    public function getHighlightId(): int {
        return $this->highlightId;
    }

    public function getWebsiteId(): int {
        return $this->websiteId;
    }

    public function setComment(string $comment): Highlight {
        $this->comment = $comment;
        return $this;
    }

    public function getComment(): string {
        return $this->comment;
    }

    public function saveState() {
        if (!$this->mysqli->query("update highlights set comment = '" . $this->mysqli->escape_string($this->comment) . "' where highlight_id = $this->highlightId")) Logs::mysql($this->mysqli);
    }
}