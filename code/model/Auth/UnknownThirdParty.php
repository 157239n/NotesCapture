<?php

namespace Kelvinho\Notes\Auth;

use RuntimeException;

/**
 * Class UnknownThirdParty. Used for federated logins where the application doesn't know about the 3rd party login mechanism.
 *
 * @package Kelvinho\Notes\Auth
 * @author Quang Ho <157239q@gmail.com>
 * @copyright Copyright (c) 2020 Quang Ho <https://github.com/157239n>
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */
class UnknownThirdParty extends RuntimeException {
}