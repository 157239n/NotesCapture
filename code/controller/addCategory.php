<?php

/**
 * Creates a new a Category given:
 * - categoryId: The id of a parent Category
 * - name: The category name
 *
 * Must:
 * - Be logged in.
 */

use Kelvinho\Notes\Singleton\Header;

if (!$authenticator->authenticated()) Header::forbidden();
$categoryFactory->new($categoryFactory->get($requestData->postCheck("categoryId")), $requestData->postCheck("name"));
