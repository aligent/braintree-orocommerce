<?php
/**
 * Created by PhpStorm.
 * User: adamhall
 * Date: 3/4/19
 * Time: 2:02 AM
 */

namespace Aligent\BraintreeBundle\Method\Action\Provider;


use Aligent\BraintreeBundle\Method\Action\BraintreeActionInterface;

class BraintreeActionProvider implements BraintreeActionProviderInterface
{

    /**
     * @var BraintreeActionInterface[]
     */
    protected $actions = [];


    /**
     * @param string $action
     * @return BraintreeActionInterface
     */
    public function getAction($action)
    {
        if (!array_key_exists($action, $this->actions)) {
            throw new \InvalidArgumentException("{$action} is not supported.");
        }

        return $this->actions[$action];
    }

    /**
     * @param $action
     * @param BraintreeActionInterface $braintreeAction
     * @return $this
     */
    public function addAction($action, BraintreeActionInterface $braintreeAction)
    {
        if (array_key_exists($action, $this->actions)) {
            throw new \InvalidArgumentException("{$action} already exists.");
        }

        $this->actions[$action] = $braintreeAction;

        return $this;
    }

    /**
     * @param $action
     * @return bool
     */
    public function hasAction($action)
    {
        return array_key_exists($action, $this->actions);
    }
}