<?php

namespace Kelvinho\Notes\Website;

use Kelvinho\Notes\Highlight\Highlight;
use Kelvinho\Notes\Highlight\HighlightFactory;
use Kelvinho\Notes\Permission\PermissionFactory;
use mysqli;

/**
 * Class Website
 *
 * @package Kelvinho\Notes\Website
 * @author Quang Ho <157239q@gmail.com>
 * @copyright Copyright (c) 2020 Quang Ho <https://github.com/157239n>
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */
class Website {
    private mysqli $mysqli;
    private HighlightFactory $highlightFactory;
    private PermissionFactory $permissionFactory;
    private int $websiteId;
    private string $websiteUrl;
    private int $categoryId;
    private string $title;
    private string $user_handle;
    /** @var Highlight[] */
    private ?array $highlights = null;

    public function __construct(mysqli $mysqli, HighlightFactory $highlightFactory, PermissionFactory $permissionFactory, int $websiteId, string $websiteUrl, int $categoryId, string $title, string $user_handle) {
        $this->mysqli = $mysqli;
        $this->highlightFactory = $highlightFactory;
        $this->permissionFactory = $permissionFactory;
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
        foreach ($this->permissionFactory->getFromWebsite($this) as $permission) $permission->delete();
        $this->mysqli->query("delete from websites where website_id = $this->websiteId");
    }
}
