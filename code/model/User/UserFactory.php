<?php

namespace Kelvinho\Notes\User;

/**
 * Interface UserFactory. Responsible for getting and creating users
 *
 * @package Kelvinho\Notes\User
 * @author Quang Ho <157239q@gmail.com>
 * @copyright Copyright (c) 2020 Quang Ho <https://github.com/157239n>
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */
interface UserFactory {
    /**
     * Gets the current user. Returns 400: Bad Request http code if no current user is found.
     *
     * @return User
     */
    public function currentUser(): User;

    /**
     * Creates a new user with a handle, a password and a name. Returns null if handle exists.
     *
     * @param string $user_handle User handle. Must be unique.
     * @param string $password Password
     * @param string $name Name
     * @param string $timezone
     * @param string $pictureUrl
     * @return User The new user. Returns null if handle already exists
     */
    public function new(string $user_handle, string $password, string $name, string $timezone, string $pictureUrl = ""): User;

    /**
     * Creates a new user using federated sign in, where a 3rd party handles all of the authentication.
     *
     * @param string $type 3rd party code, like "google", or "facebook"
     * @param string $user_handle
     * @param string $name
     * @param string $timezone
     * @param string $pictureUrl
     * @return User
     */
    public function newFederated(string $type, string $user_handle, string $name, string $timezone, string $pictureUrl = ""): User;

    /**
     * Get a user from a user handle. Returns null if not found
     *
     * @param string $user_handle The user handle
     * @return User|null
     */
    public function get(string $user_handle): User;

    /**
     * Checks whether a particular user handle exists.
     *
     * @param string $user_handle The user handle
     * @return bool Whether it exists
     */
    public function exists(string $user_handle): bool;

    /**
     * Gets 10 first users that has a name that starts with something.
     *
     * @param string $start
     * @return User[]
     */
    public function startingWith(string $start): array;
}
