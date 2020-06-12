<?php

namespace Kelvinho\Notes\Highlight;

use Kelvinho\Notes\Singleton\Logs;
use Kelvinho\Notes\Website\Website;
use mysqli;
use function Kelvinho\Notes\map;

class HighlightFactory {
    private mysqli $mysqli;

    public function __construct(mysqli $mysqli) {
        $this->mysqli = $mysqli;
    }

    /**
     * Gets a comment given a comment id.
     *
     * @param $highlightId
     * @return Highlight
     */
    public function get($highlightId): Highlight {
        if (!$answer = $this->mysqli->query("select website_id, strings, comment from highlights where highlight_id = $highlightId")) throw new HighlightNotFound();
        if (!$row = $answer->fetch_assoc()) throw new HighlightNotFound("Highlight id: $highlightId");
        return new Highlight($this->mysqli, $highlightId, (int)$row["website_id"], $row["strings"], $row["comment"]);
    }

    /**
     * Gets all comments that belongs to a website.
     *
     * @param Website $website
     * @return Highlight[]
     */
    public function all(Website $website): array {
        $websiteId = $website->getWebsiteId();
        $comments = [];
        if (!$answer = $this->mysqli->query("select highlight_id, strings, comment from highlights where website_id = $websiteId")) throw new HighlightNotFound();
        while ($row = $answer->fetch_assoc()) $comments[] = new Highlight($this->mysqli, $row["highlight_id"], $websiteId, $row["strings"], $row["comment"]);
        return $comments;
    }

    /**
     * @param Website $website
     * @param string $comment
     * @param string[] $strings
     * @return Highlight
     */
    public function new(Website $website, string $comment, array $strings = null): Highlight {
        if ($strings == null) $strings = [];
        $strings = implode(" ", map($strings, fn ($str) => base64_encode($str)));
        $websiteId = $website->getWebsiteId();
        if (!$this->mysqli->query("insert into highlights (website_id, strings, comment) values ($websiteId, '$strings', '" . $this->mysqli->escape_string($comment) . "')")) Logs::error($this->mysqli->error);
        return $this->get($this->mysqli->insert_id);
    }
}
