<?php

namespace Kelvinho\Notes\Category;

use Kelvinho\Notes\Network\Session;
use Kelvinho\Notes\Singleton\Logs;
use Kelvinho\Notes\Website\WebsiteFactory;
use mysqli;

/**
 * Class CategoryFactory. Constructs a Category.
 *
 * It is important to remember that there are 2 loading modes. 1 loads a category and every parent category and the
 * other loads a full graph of the categories. This fact is hidden from the outside, but is done to prevent excessive
 * database querying (O(log(n)) instead of O(n)).
 *
 * @package Kelvinho\Notes\Category
 * @author Quang Ho <157239q@gmail.com>
 * @copyright Copyright (c) 2020 Quang Ho <https://github.com/157239n>
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */
class CategoryFactory {
    private mysqli $mysqli;
    private Session $session;
    private WebsiteFactory $websiteFactory;
    private bool $active;

    public function __construct(mysqli $mysqli, Session $session, WebsiteFactory $websiteFactory) {
        $this->mysqli = $mysqli;
        $this->session = $session;
        $this->websiteFactory = $websiteFactory;
        $this->active = $this->session->has("user_handle");
    }

    /**
     * Gets a single category given id. This still has links all the way to the root node, but does not create a full graph.
     *
     * @param $categoryId
     * @param string $user_handle Custom user handle, if in scenarios where it can't be obtained from the session
     * @return Category
     */
    public function get($categoryId, string $user_handle = ""): Category {
        if (!$answer = $this->mysqli->query("select user_handle, parent_category_id, name from categories where category_id = '$categoryId'")) throw new CategoryNotFound();
        if (!$row = $answer->fetch_assoc()) throw new CategoryNotFound();
        $parentCategoryId = (int)$row["parent_category_id"];
        $name = $row["name"];
        if (!$this->session->has("user_handle")) {
            if ($user_handle !== $row["user_handle"]) throw new CategoryNotFound();
        } else {
            $user_handle = $row["user_handle"];
            if ($this->session->getCheck("user_handle") !== $user_handle) throw new CategoryNotFound();
        }
        if ($parentCategoryId === 0) return new Category($this->mysqli, $this, $this->websiteFactory, $categoryId, null, $name, 0, $user_handle, false);
        else return new Category($this->mysqli, $this, $this->websiteFactory, $categoryId, $this->get($parentCategoryId, $user_handle), $name, $parentCategoryId, $user_handle, false);
    }

    /**
     * Creates a full graph, then pass the root back.
     *
     * @return Category
     */
    public function getRoot(): Category {
        if (!$this->active) throw new CategoryNotFound();
        $user_handle = $this->session->getCheck("user_handle");
        if (!$answer = $this->mysqli->query("select category_id, parent_category_id, name from categories where user_handle = '" . $this->mysqli->escape_string($user_handle) . "'")) throw new CategoryNotFound();
        /** @var Category[] $categories */
        $categories = array();
        $randomCategory = null;
        while ($row = $answer->fetch_assoc()) {
            $parentCategoryId = (int)$row["parent_category_id"];
            $categoryId = (int)$row["category_id"];
            $name = $row["name"];
            if (isset($categories[$parentCategoryId])) {
                $category = new Category($this->mysqli, $this, $this->websiteFactory, $categoryId, $categories[$parentCategoryId], $name, $parentCategoryId, $user_handle, true);
                $categories[$parentCategoryId]->addChild($category);
            } else $category = new Category($this->mysqli, $this, $this->websiteFactory, $categoryId, null, $name, $parentCategoryId, $user_handle, true);
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

    /**
     * @param Category|null $parentCategory
     * @param string $name
     * @param string $user_handle Used for when creating the root and "Shared with me" categories when creating a new user, when the user hasn't existed yet
     * @return Category
     */
    public function new(Category $parentCategory = null, string $name = "", string $user_handle = ""): Category {
        if ($this->active) $user_handle = $this->session->getCheck("user_handle");
        if ($parentCategory == null) {
            if (!$this->mysqli->query("insert into categories (user_handle, name) values ('" . $this->mysqli->escape_string($user_handle) . "', 'Root')")) Logs::error($this->mysqli->error);
            return $this->get($this->mysqli->insert_id, $user_handle);
        } else {
            $parentCategoryId = $parentCategory->getCategoryId();
            if (!$this->mysqli->query("insert into categories (user_handle, parent_category_id, name) values ('" . $this->mysqli->escape_string($user_handle) . "', $parentCategoryId, '" . $this->mysqli->escape_string($name) . "')")) Logs::error($this->mysqli->error);
            return $this->get($this->mysqli->insert_id, $user_handle);
        }
    }
}
