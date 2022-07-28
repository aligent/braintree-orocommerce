<?php
/**
 * @category  Aligent
 * @package   BraintreeBundle
 * @author    Adam Hall <adam.hall@aligent.com.au>
 * @copyright 2020 Aligent Consulting.
 * @license
 * @link      http://www.aligent.com.au/
 */
namespace Aligent\BraintreeBundle\Method\Config\Provider;

use Aligent\BraintreeBundle\Method\Config\BraintreeConfigInterface;

interface BraintreeConfigProviderInterface
{
    /**
     * @return array<string,BraintreeConfigInterface>
     */
    public function getPaymentConfigs(): array;

    public function getPaymentConfig(string $identifier): ?BraintreeConfigInterface;

    public function hasPaymentConfig(string $identifier): bool;
}
