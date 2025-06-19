<?php
/**
 * @category  Aligent
 * @package   BraintreeBundle
 * @author    Adam Hall <adam.hall@aligent.com.au>
 * @copyright 2020 Aligent Consulting.
 * @license
 * @link      http://www.aligent.com.au/
 */

namespace Aligent\BraintreeBundle\Braintree\PaymentMethod\Settings\Builder;

use Oro\Bundle\PaymentBundle\Context\PaymentContextInterface;

interface ConfigurationBuilderInterface
{
    /**
     * Build the settings object to pass to Drop-in
     * @param PaymentContextInterface $context
     * @param array<string,mixed> $configuration
     * @return array<string,mixed>
     */
    public function build(PaymentContextInterface $context, array $configuration): array;
}
