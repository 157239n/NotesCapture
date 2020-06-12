<?php

use Kelvinho\Notes\Singleton\Header;

$category = $categoryFactory->get($requestData->postCheck("category_id"));
if ($category->isRoot()) Header::badRequest();
if (!$authenticator->categoryAuthenticated($category)) Header::forbidden();
if ($category->isRoot()) Header::forbidden();
$category->delete();
