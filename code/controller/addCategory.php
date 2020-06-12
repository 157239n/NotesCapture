<?php

use Kelvinho\Notes\Singleton\Header;

if (!$authenticator->authenticated()) Header::forbidden();
$categoryFactory->new($categoryFactory->get($requestData->postCheck("category_id")), $requestData->postCheck("name"));
