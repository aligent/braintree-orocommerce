<?php
/**
 * Created by PhpStorm.
 * User: adamhall
 * Date: 3/4/19
 * Time: 1:34 AM
 */

namespace Aligent\BraintreeBundle\Method\Action\Provider;


use Aligent\BraintreeBundle\Method\Action\BraintreeActionInterface;

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