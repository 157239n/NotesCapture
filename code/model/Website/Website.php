<?php

namespace Kelvinho\Notes\Website;

use Kelvinho\Notes\Highlight\Highlight;
use Kelvinho\Notes\Highlight\HighlightFactory;
use mysqli;

class Website {
    private mysqli $mysqli;
    private HighlightFactory $highlightFactory;
    private int $websiteId;
    private string $websiteUrl;
    private int $categoryId;
    private string $title;
    private string $user_handle;
    /** @var Highlight[] */
    private ?array $highlights = null;

    public function __construct(mysqli $mysqli, HighlightFactory $highlightFactory, int $websiteId, string $websiteUrl, int $categoryId, string $title, string $user_handle) {
        $this->mysqli = $mysqli;
        $this->highlightFactory = $highlightFactory;
        $this->websiteId = $websiteId;
        $this->websiteUrl = $websiteUrl;
        $this->categoryId = $categoryId;
        $this->title = $title;
        $this->user_handle = $user_handle;
    }

    public function getUserHandle(): string {
        return $this->user_handle;
    }

    public function getWebsiteId(): int {
        return $this->websiteId;
    }

    public function getCategoryId(): int {
        return $this->categoryId;
    }

    public function getUrl(): string {
        return $this->websiteUrl;
    }

    public function getTitle(): string {
        return $this->title;
    }

    /**
     * @return Highlight[]
     */
    public function getHighlights(): array {
        if ($this->highlights == null) $this->highlights = $this->highlightFactory->all($this);
        return $this->highlights;
    }

    public function delete(): void {
        $highlights = $this->getHighlights();
        foreach ($highlights as $highlight) $highlight->delete();
        $this->mysqli->query("delete from websites where website_id = $this->websiteId");
    }
}
