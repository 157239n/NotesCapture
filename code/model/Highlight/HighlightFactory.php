<?php

namespace Kelvinho\Notes\Highlight;

use Kelvinho\Notes\Comment\CommentFactory;
use Kelvinho\Notes\Network\Session;
use Kelvinho\Notes\Singleton\Logs;
use Kelvinho\Notes\Website\Website;
use mysqli;
use function Kelvinho\Notes\map;

class HighlightFactory {
    private mysqli $mysqli;
    private Session $session;
    private CommentFactory $commentFactory;

    public function __construct(mysqli $mysqli, Session $session, CommentFactory $commentFactory) {
        $this->mysqli = $mysqli;
        $this->session = $session;
        $this->commentFactory = $commentFactory;
    }

    /**
     * Gets a comment given a comment id.
     *
     * @param $highlightId
     * @return Highlight
     */
    public function get($highlightId): Highlight {
        if (!$answer = $this->mysqli->query("select website_id, strings from highlights where highlight_id = $highlightId")) throw new HighlightNotFound();
        if (!$row = $answer->fetch_assoc()) throw new HighlightNotFound("Highlight id: $highlightId");
        return new Highlight($this->mysqli, $this->commentFactory, $highlightId, (int)$row["website_id"], $row["strings"]);
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
        if (!$answer = $this->mysqli->query("select highlight_id, strings from highlights where website_id = $websiteId")) throw new HighlightNotFound();
        while ($row = $answer->fetch_assoc()) $comments[] = new Highlight($this->mysqli, $this->commentFactory, $row["highlight_id"], $websiteId, $row["strings"]);
        return $comments;
    }

    /**
     * @param Website $website
     * @param string[] $strings
     * @return Highlight
     */
    public function new(Website $website, array $strings = null): Highlight {
        if ($strings == null) $strings = [];
        $strings = implode(" ", map($strings, fn($str) => base64_encode($str)));
        $websiteId = $website->getWebsiteId();
        if (!$this->mysqli->query("insert into highlights (website_id, strings) values ($websiteId, '$strings')")) Logs::error($this->mysqli->error);
        $highlight = $this->get($this->mysqli->insert_id);
        $this->commentFactory->new($highlight, $this->session->getCheck("user_handle"), "");
        return $highlight;
    }
}
