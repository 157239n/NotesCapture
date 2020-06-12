<?php

namespace Kelvinho\Notes\Category;

use Kelvinho\Notes\Network\Session;
use Kelvinho\Notes\Singleton\Logs;
use Kelvinho\Notes\Website\WebsiteFactory;
use mysqli;

class CategoryFactory {
    private mysqli $mysqli;
    private Session $session;
    private WebsiteFactory $websiteFactory;
    private bool $active;

    public function __construct(mysqli $mysqli, Session $session) {
        $this->mysqli = $mysqli;
        $this->session = $session;
        $this->active = $this->session->has("user_handle");
    }

    public function addContext(WebsiteFactory $websiteFactory) {
        $this->websiteFactory = $websiteFactory;
    }

    /**
     * Gets a single category given id. This still has links all the way to the root node.
     *
     * @param $categoryId
     * @return Category
     */
    public function get($categoryId): Category {
        if (!$this->active) throw new CategoryNotFound();
        if (!$answer = $this->mysqli->query("select user_handle, parent_category_id, name from categories where category_id = '$categoryId'")) throw new CategoryNotFound();
        if (!$row = $answer->fetch_assoc()) throw new CategoryNotFound();
        $parentCategoryId = (int)$row["parent_category_id"];
        $name = $row["name"];
        $user_handle = $row["user_handle"];
        if ($this->session->getCheck("user_handle") !== $user_handle) throw new CategoryNotFound();
        if ($parentCategoryId === 0) return new Category($this->websiteFactory, $categoryId, null, $name, 0, $user_handle, false);
        else return new Category($this->websiteFactory, $categoryId, $this->get($parentCategoryId), $name, $parentCategoryId, $user_handle, false);
    }

    public function getRoot(): Category {
        if (!$this->active) throw new CategoryNotFound();
        $user_handle = $this->session->getCheck("user_handle");
        if (!$answer = $this->mysqli->query("select category_id, parent_category_id, name from categories where user_handle = '$user_handle'")) throw new CategoryNotFound();
        /** @var Category[] $categories */
        $categories = array();
        $randomCategory = null;
        while ($row = $answer->fetch_assoc()) {
            $parentCategoryId = (int)$row["parent_category_id"];
            $categoryId = (int)$row["category_id"];
            $name = $row["name"];
            if (isset($categories[$parentCategoryId])) {
                $category = new Category($this->websiteFactory, $categoryId, $categories[$parentCategoryId], $name, $parentCategoryId, $user_handle, true);
                $categories[$parentCategoryId]->addChild($category);
            } else $category = new Category($this->websiteFactory, $categoryId, null, $name, $parentCategoryId, $user_handle, true);
            $categories[$categoryId] = $category;
            $randomCategory = $category;
        }
        foreach ($categories as $categoryId => $category)
            if ($category->getParent() == null)
                if (isset($categories[$category->getParentCategoryId()]))
                    $category->setParent($categories[$category->getParentCategoryId()]);
        if ($randomCategory != null) return $randomCategory->getRootCategory();
        throw new CategoryNotFound();
    }

    public function new(Category $parentCategory = null, string $name = ""): Category {
        if (!$this->active) throw new CategoryNotFound();
        $user_handle = $this->session->getCheck("user_handle");
        if ($parentCategory == null) {
            if (!$this->mysqli->query("insert into categories (user_handle, name) values ('$user_handle', 'Root')")) Logs::error($this->mysqli->error);
            return $this->get($this->mysqli->insert_id);
        } else {
            $parentCategoryId = $parentCategory->getCategoryId();
            if (!$this->mysqli->query("insert into categories (user_handle, parent_category_id, name) values ('$user_handle', $parentCategoryId, '" . $this->mysqli->escape_string($name) . "')")) Logs::error($this->mysqli->error);
            return $this->get($this->mysqli->insert_id);
        }
    }
}
