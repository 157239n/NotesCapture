<?php

namespace Kelvinho\Notes\Category;

use Kelvinho\Notes\Singleton\Logs;
use Kelvinho\Notes\Website\Website;
use Kelvinho\Notes\Website\WebsiteFactory;
use mysqli;

/**
 * Class Category
 *
 * Represents a category of a website. The representation of this will be stored in table users only. No data is stored on disk.
 * But if needed in the future, it should be placed at DATA_FILE/users/{user_id}/
 *
 * @package Kelvinho\Notes\Category
 * @author Quang Ho <157239q@gmail.com>
 * @copyright Copyright (c) 2020 Quang Ho <https://github.com/157239n>
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */
class Category {
    private int $categoryId;
    private int $parentCategoryId;
    private ?Category $parentCategory;
    /** @type Category[] */
    private array $children = array();
    private string $name;
    private mysqli $mysqli;
    private WebsiteFactory $websiteFactory;
    private string $user_handle;
    private bool $fullGraph; // whether instantiation method involves constructing a whole graph
    /** @var Website[] */
    private ?array $websites = null;

    public function __construct(WebsiteFactory $websiteFactory, int $categoryId, ?Category $parentCategory, string $name, int $parentCategoryId, string $user_handle, bool $fullGraph) {
        $this->websiteFactory = $websiteFactory;
        $this->categoryId = $categoryId;
        $this->parentCategory = $parentCategory;
        $this->name = $name;
        $this->parentCategoryId = $parentCategoryId; // this mechanism is convoluted because of its interaction with CategoryFactory
        $this->user_handle = $user_handle;
        $this->fullGraph = $fullGraph;
    }

    public function getUserHandle(): string {
        return $this->user_handle;
    }

    public function setParent(Category $parentCategory) {
        $this->parentCategory = $parentCategory;
        $this->parentCategoryId = $parentCategory->categoryId;
    }

    public function getCategoryId(): int {
        return $this->categoryId;
    }

    public function getParentCategoryId(): int {
        return $this->parentCategoryId;
    }

    public function getParent(): ?Category {
        return $this->parentCategory;
    }

    public function addChild(Category $childCategory) {
        $this->children[$childCategory->categoryId] = $childCategory;
    }

    public function getRootCategory(): Category {
        if ($this->parentCategoryId !== 0) return $this->parentCategory->getRootCategory();
        return $this;
    }

    /**
     * Gets this and all children's category id recursively.
     *
     * @param array|null $answer Optional reference to array we're building
     * @return bool[]
     */
    public function getCategoryIdsBelow(array &$answer = null): array {
        if ($answer == null) $answer = [];
        $answer[$this->categoryId] = true;
        foreach ($this->children as /** @var Category $category */ $category) $category->getCategoryIdsBelow($answer);
        return $answer;
    }

    /**
     * Find a child category that has a specific category id
     *
     * @param int $categoryId
     * @return Category|null
     */
    public function findChildCategory(int $categoryId): ?Category {
        if (!$this->fullGraph) return $this->getRootCategory()->findChildCategory($this->categoryId)->findChildCategory($categoryId);
        foreach ($this->children as $child) {
            $result = $child->findChildCategory($categoryId);
            if ($result !== null) return $result;
        }
        return null;
    }

    /**
     * @return Website[]
     */
    public function getWebsites(): array {
        if ($this->websites == null) $this->websites = $this->websiteFactory->allUnderStrictCategory($this);
        return $this->websites;
    }

    public function getName(): string {
        return $this->name;
    }

    public function setName(string $name): void {
        $this->name = $name;
    }

    public function isRoot(): bool {
        return $this->parentCategoryId === 0;
    }

    /**
     * @return Category[]
     */
    public function getChildren(): array {
        if (!$this->fullGraph) return $this->getRootCategory()->findChildCategory($this->categoryId)->getChildren();
        return $this->children;
    }

    public function saveState(): void {
        if (!$this->mysqli->query("update categories set parent_category_id = $this->parentCategoryId, name = '" . $this->mysqli->escape_string($this->name) . "' where category_id = $this->categoryId")) Logs::mysql($this->mysqli);
    }

    public function delete(): void {
        foreach ($this->getWebsites() as $website) $website->delete();
        foreach ($this->getChildren() as $childCategory) $childCategory->delete();
        $this->mysqli->query("delete from categories where category_id = $this->categoryId");
    }
}
