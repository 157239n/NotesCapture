<?php

namespace Kelvinho\Notes\Network\FilterList;

/**
 * Class WhitelistFactory
 *
 * @package Kelvinho\Notes\Network\Ip\FilterList
 * @author Quang Ho <157239q@gmail.com>
 * @copyright Copyright (c) 2020 Quang Ho <https://github.com/157239n>
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */
class WhitelistFactory extends FilterListFactory {
    protected bool $isWhitelist = true;
}