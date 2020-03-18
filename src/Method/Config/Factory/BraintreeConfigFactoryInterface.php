<?php
/**
 * @category  Aligent
 * @package   BraintreeBundle
 * @author    Adam Hall <adam.hall@aligent.com.au>
 * @copyright 2020 Aligent Consulting.
 * @license
 * @link      http://www.aligent.com.au/
 */

namespace Aligent\BraintreeBundle\Method\Config\Factory;


use Aligent\BraintreeBundle\Entity\BraintreeIntegrationSettings;
use Aligent\BraintreeBundle\Method\Config\BraintreeConfigInterface;

interface BraintreeConfigFactoryInterface
{
    /**
     * @param BraintreeIntegrationSettings $settings
     * @return BraintreeConfigInterface
     */
    public function create(BraintreeIntegrationSettings $settings);
}