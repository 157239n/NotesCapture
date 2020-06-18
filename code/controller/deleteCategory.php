<?php

/**
 * Deletes a Category given:
 * - categoryId: A category id
 *
 * Must:
 * - Have permission to access the Category
 * - The Category must not be root
 * - The Category must not be "Shared with me"
 */

use Kelvinho\Notes\Singleton\Header;

$category = $categoryFactory->get($requestData->postCheck("categoryId"));
if (!$authenticator->categoryAuthenticated($category)) Header::forbidden();
if ($category->isRoot()) Header::forbidden();
if ($category->getCategoryId() === $category->getRootCategory()->getCategoryId() + 1) Header::forbidden();
$category->delete();
