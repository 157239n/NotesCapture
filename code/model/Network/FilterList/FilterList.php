<?php

namespace Kelvinho\Notes\Network\FilterList;

use Kelvinho\Notes\Network\Ip\IpSchemaConverter;

/**
 * Class FilterList. Represents either a whitelist or a blacklist of ip address. You can add ip addresses and ranges and
 * test if another ip address passes the test.
 *
 * @package Kelvinho\Notes\Network
 * @author Quang Ho <157239q@gmail.com>
 * @copyright Copyright (c) 2020 Quang Ho <https://github.com/157239n>
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */
abstract class FilterList {
    /** @var IpSchemaConverter[] */
    private array $ipSchemaConverters;
    protected bool $isWhitelist = true;

    /** @var double[] */
    private array $whitelists = [];

    public function __construct(array $ipSchemaConverters) {
        $this->ipSchemaConverters = $ipSchemaConverters;
    }

    /**
     * Add ip address to list
     *
     * @param string $ipAddressRepresentation
     */
    public function addIp(string $ipAddressRepresentation) {
        foreach ($this->ipSchemaConverters as $ipSchemaConverter)
            if ($ipSchemaConverter->valid($ipAddressRepresentation))
                $this->whitelists[] = $ipSchemaConverter->convert($ipAddressRepresentation);
    }

    /**
     * Whether this ip address is allowed.
     *
     * @param string $ipAddress Ip address to check. Must look like 192.168.0.1
     * @return bool
     */
    public function allowed(string $ipAddress) {
        $ip = ip2long($ipAddress);

        foreach ($this->whitelists as $whitelist)
            if ($ip >= $whitelist[0] && $ip <= $whitelist[1]) return $this->isWhitelist;

        return !$this->isWhitelist;
    }
}
