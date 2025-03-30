<?php
/**
 * @license BSD-3-Clause
 *
 * Modified by Geoff Taylor using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace WPGraphQL\WooCommerce\Vendor\Firebase\JWT;

class ExpiredException extends \UnexpectedValueException implements JWTExceptionWithPayloadInterface
{
    private object $payload;

    public function setPayload(object $payload): void
    {
        $this->payload = $payload;
    }

    public function getPayload(): object
    {
        return $this->payload;
    }
}
