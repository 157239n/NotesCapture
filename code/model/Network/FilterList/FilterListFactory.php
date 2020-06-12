<?php

namespace Kelvinho\Notes\Network\FilterList;

use Kelvinho\Notes\Network\Ip\Any;
use Kelvinho\Notes\Network\Ip\Cidr;
use Kelvinho\Notes\Network\Ip\Localhost;
use Kelvinho\Notes\Network\Ip\Single;

/**
 * Class FilterListFactory. Base class of WhitelistFactory and BlacklistFactory.
 *
 * @package Kelvinho\Notes\Network
 * @author Quang Ho <157239q@gmail.com>
 * @copyright Copyright (c) 2020 Quang Ho <https://github.com/157239n>
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */
abstract class FilterListFactory {
    protected bool $isWhitelist = true;

    public function new(): FilterList {
        $converters = [
            new Any(),
            new Cidr(),
            new Localhost(),
            new Single()];
        return $this->isWhitelist ? new Whitelist($converters) : new Blacklist($converters);
    }
}