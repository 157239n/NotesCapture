<?php

namespace Kelvinho\Notes\Website;

use Kelvinho\Notes\Category\Category;
use Kelvinho\Notes\Highlight\HighlightFactory;
use Kelvinho\Notes\Network\Session;
use Kelvinho\Notes\Permission\PermissionFactory;
use Kelvinho\Notes\Singleton\Logs;
use mysqli;
use function Kelvinho\Notes\filter;
use function Kelvinho\Notes\getTitle;

/**
 * Class WebsiteFactory.
 *
 * @package Kelvinho\Notes\Website
 * @author Quang Ho <157239q@gmail.com>
 * @copyright Copyright (c) 2020 Quang Ho <https://github.com/157239n>
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */
class WebsiteFactory {
    private mysqli $mysqli;
    private HighlightFactory $highlightFactory;
    private Session $session;
    private PermissionFactory $permissionFactory;
    private bool $active;

    public function __construct(mysqli $mysqli, HighlightFactory $highlightFactory, Session $session, PermissionFactory $permissionFactory) {
        $this->mysqli = $mysqli;
        $this->highlightFactory = $highlightFactory;
        $this->session = $session;
        $this->permissionFactory = $permissionFactory;
        $this->active = $session->has("user_handle");
    }

    public function get(int $websiteId): Website {
        if (!$this->active) throw new WebsiteNotFound();
        if (!$answer = $this->mysqli->query("select website_url, user_handle, category_id, title from websites where website_id = $websiteId")) throw new WebsiteNotFound();
        if (!$row = $answer->fetch_assoc()) throw new WebsiteNotFound();
        return new Website($this->mysqli, $this->highlightFactory, $this->permissionFactory, $websiteId, $row["website_url"], $row["category_id"], $row["title"], $row["user_handle"]);
    }

    /**
     * Gets all websites that belongs to the current user.
     *
     * @return Website[]
     */
    public function all(): array {
        if (!$this->active) return [];
        $user_handle = $this->session->getCheck("user_handle");
        $websites = [];
        if (!$answer = $this->mysqli->query("select website_url, website_id, category_id, title from websites where user_handle = '" . $this->mysqli->escape_string($user_handle) . "'")) throw new WebsiteNotFound();
        while ($row = $answer->fetch_assoc()) $websites[] = new Website($this->mysqli, $this->highlightFactory, $this->permissionFactory, $row["website_id"], $row["website_url"], (int)$row["category_id"], $row["title"], $user_handle);
        return $websites;
    }

    /**
     * Gets all Websites under a Category and every of its child Categories.
     *
     * @param Category $category
     * @return Website[]
     */
    public function allUnderCategory(Category $category): array {
        if (!$this->active) return [];
        $websites = $this->all();
        $categoryIds = $category->getCategoryIdsBelow();
        return filter($websites, function (Website $website) use ($categoryIds) {
            return $categoryIds[$website->getCategoryId()];
        });
    }

    /**
     * Gets all Websites under a Category alone.
     *
     * @param Category $category
     * @return Website[]
     */
    public function allUnderStrictCategory(Category $category): array {
        if (!$this->active) return [];
        $websites = [];
        $categoryId = $category->getCategoryId();
        if (!$answer = $this->mysqli->query("select website_url, user_handle, website_id, title from websites where category_id = $categoryId")) throw new WebsiteNotFound();
        while ($row = $answer->fetch_assoc()) $websites[] = new Website($this->mysqli, $this->highlightFactory, $this->permissionFactory, $row["website_id"], $row["website_url"], $categoryId, $row["title"], $row["user_handle"]);
        return $websites;
    }

    public function new(Category $category, string $url, string $title = ""): Website {
        if (!$this->active) throw new WebsiteNotFound();
        $user_handle = $this->session->getCheck("user_handle");
        $categoryId = $category->getCategoryId();
        if ($title == "") $title = getTitle($url);
        if ($title == "") $title = $url;
        if (!$this->mysqli->query("insert into websites (user_handle, website_url, category_id, title) values ('" . $this->mysqli->escape_string($user_handle) . "', '" . $this->mysqli->escape_string($url) . "', $categoryId, '" . $this->mysqli->escape_string($title) . "')")) Logs::error($this->mysqli->error);
        return $this->get($this->mysqli->insert_id);
    }
}
