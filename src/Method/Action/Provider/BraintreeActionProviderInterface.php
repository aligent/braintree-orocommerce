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
use Aligent\BraintreeBundle\Method\Config\BraintreeConfigInterface;

interface BraintreeActionProviderInterface
{
    /**
     * @param string $action
     * @return BraintreeActionInterface
     */
    public function getAction($action);

    /**
     * @param $action
     * @param BraintreeActionInterface $braintreeAction
     * @return $this
     */
    public function addAction($action, BraintreeActionInterface $braintreeAction);

    /**
     * @param $action
     * @return bool
     */
    public function hasAction($action);
}
