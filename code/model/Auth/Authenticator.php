<?php

namespace Kelvinho\Notes\Auth;

use Kelvinho\Notes\Category\Category;
use Kelvinho\Notes\Website\Website;

/**
 * Interface Authenticator. Handles all the authentication work.
 *
 * @package Kelvinho\Notes\Auth
 * @author Quang Ho <157239q@gmail.com>
 * @copyright Copyright (c) 2020 Quang Ho <https://github.com/157239n>
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */
interface Authenticator {
    /**
     * Returns whether the user is authenticated.
     *
     * @param string|null $user_handle Optional user handle to make sure that the authenticated user is the same as the requesting user
     * @return bool Whether the user is authenticated
     */
    public function authenticated(string $user_handle = null): bool;

    /**
     * Authenticates a user.
     *
     * @param string $user_handle The user's handle
     * @param string $password The user's password
     * @return bool Whether the user is authenticated
     */
    public function authenticate(string $user_handle, string $password): bool;

    /**
     * Returns whether the user is allowed to access a website. Checks for cross site relationships as well as ownership.
     *
     * @param Website $website
     * @return bool
     */
    public function websiteAuthenticated(Website $website): bool;

    /**
     * Returns whether the user is allowed to access a category.
     *
     * @param Category $category
     * @return bool
     */
    public function categoryAuthenticated(Category $category): bool;
}
