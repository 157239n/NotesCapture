<?php

namespace Kelvinho\Notes\Auth;

use Kelvinho\Notes\Category\Category;
use Kelvinho\Notes\Network\Session;
use Kelvinho\Notes\Website\Website;
use mysqli;

/**
 * Class Authenticator. Handles all the authentication work.
 *
 * @package Kelvinho\Notes\Auth
 * @author Quang Ho <157239q@gmail.com>
 * @copyright Copyright (c) 2020 Quang Ho <https://github.com/157239n>
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */
class AuthenticatorImp implements Authenticator {
    private Session $session;
    private mysqli $mysqli;

    public function __construct(Session $session, mysqli $mysqli) {
        $this->session = $session;
        $this->mysqli = $mysqli;
    }

    /**
     * Returns whether the user is authenticated.
     *
     * @param string|null $user_handle Optional user handle to make sure that the authenticated user is the same as the requesting user
     * @return bool Whether the user is authenticated
     */
    public function authenticated(string $user_handle = null): bool {
        if (empty($user_handle)) return $this->session->has("user_handle");
        return $this->session->get("user_handle") === $user_handle;
    }

    public function websiteAuthenticated(Website $website): bool {
        if (!$this->authenticated()) return false;
        return $this->session->getCheck("user_handle") === $website->getUserHandle();
    }

    public function categoryAuthenticated(Category $category): bool {
        if (!$this->authenticated()) return false;
        return $this->session->getCheck("user_handle") === $category->getUserHandle();
    }

    /**
     * Authenticates a user.
     *
     * @param string $user_handle The user's handle
     * @param string $password The user's password
     * @return bool Whether the user is authenticated
     */
    public function authenticate(string $user_handle, string $password): bool {
        $authenticated = false;
        if ($answer = $this->mysqli->query("select password_salt, password_hash from users where user_handle = '" . $this->mysqli->escape_string($user_handle) . "'"))
            if ($row = $answer->fetch_assoc())
                if (hash("sha256", $row["password_salt"] . $password) == $row["password_hash"])
                    $authenticated = true;
        if ($authenticated) {
            $this->session->set("user_handle", $user_handle);
            return true;
        } else return false;
    }
}