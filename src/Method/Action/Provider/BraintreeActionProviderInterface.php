<?php
/**
 * @category  Aligent
 * @package   BraintreeBundle
 * @author    Adam Hall <adam.hall@aligent.com.au>
 * @copyright 2020 Aligent Consulting.
 * @license
 * @link      http://www.aligent.com.au/
 */

namespace Aligent\BraintreeBundle\Method\Action\Provider;

use Aligent\BraintreeBundle\Method\Action\BraintreeActionInterface;

interface BraintreeActionProviderInterface
{
    public function getAction(string $action): BraintreeActionInterface;

    public function addAction(string $action, BraintreeActionInterface $braintreeAction): static;

    public function hasAction(string $action): bool;
}
