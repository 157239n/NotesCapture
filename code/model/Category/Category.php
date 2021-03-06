<?php

namespace Kelvinho\Notes\Category;

use Kelvinho\Notes\Website\Website;
use Kelvinho\Notes\Website\WebsiteFactory;
use mysqli;

/**
 * Class Category. A user has a root category, which can have multiple other categories. You can think of them as folders.
 * For each root category, there is another category called "Shared with me", with an id right after the root category.
 *
 * The root and "Shared with me" category cannot be deleted and modified, unless the user is deleted. The latter cannot
 * create new websites and child categories too.
 *
 * @package Kelvinho\Notes\Category
 * @author Quang Ho <157239q@gmail.com>
 * @copyright Copyright (c) 2020 Quang Ho <https://github.com/157239n>
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */
class Category {
    private mysqli $mysqli;
    private CategoryFactory $categoryFactory;
    private WebsiteFactory $websiteFactory;
    private int $categoryId;
    private int $parentCategoryId;
    private ?Category $parentCategory;
    /** @type Category[] */
    private array $children = array();
    private string $name;
    private string $user_handle;
    private bool $fullGraph; // whether instantiation method involves constructing a whole graph
    /** @var Website[] */
    private ?array $websites = null;

    public function __construct(mysqli $mysqli, CategoryFactory $categoryFactory, WebsiteFactory $websiteFactory, int $categoryId, ?Category $parentCategory, string $name, int $parentCategoryId, string $user_handle, bool $fullGraph) {
        $this->mysqli = $mysqli;
        $this->categoryFactory = $categoryFactory;
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

    /**
     * @param Category $parentCategory
     * @internal For constructing the object only
     */
    public function setParent(Category $parentCategory) {
        $this->parentCategory = $parentCategory;
        $this->parentCategoryId = $parentCategory->categoryId;
    }

    public function getCategoryId(): int {
        return $this->categoryId;
    }

    /**
     * Will return the correct parent category id, but you're supposed to get the parent category first, then get its id.
     *
     * @return int
     * @internal For constructing the object only
     */
    public function getParentCategoryId(): int {
        return $this->parentCategoryId;
    }

    public function getParent(): ?Category {
        return $this->parentCategory;
    }

    /**
     * @param Category $childCategory
     * @internal For constructing the object only
     */
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
        if ($this->categoryId === $categoryId) return $this;
        if (!$this->fullGraph) return $this->getRootCategory()->findChildCategory($this->categoryId)->findChildCategory($categoryId);
        foreach ($this->children as $child) {
            $result = $child->findChildCategory($categoryId);
            if ($result !== null) return $result;
        }
        return null;
    }

    /**
     * Get websites under this category.
     *
     * @return Website[]
     */
    public function getWebsites(): array {
        if ($this->websites == null) $this->websites = $this->websiteFactory->allUnderStrictCategory($this);
        return $this->websites;
    }

    public function getName(): string {
        return $this->name;
    }

    public function isRoot(): bool {
        return $this->parentCategoryId === 0;
    }

    /**
     * @return Category[]
     */
    public function getChildren(): array {
        if (!$this->fullGraph) $this->children = $this->categoryFactory->getRoot()->findChildCategory($this->categoryId)->getChildren();
        return $this->children;
    }

    public function delete(): void {
        foreach ($this->getChildren() as $childCategory) $childCategory->delete();
        foreach ($this->getWebsites() as $website) $website->delete();
        $this->mysqli->query("delete from categories where category_id = $this->categoryId");
    }
}
