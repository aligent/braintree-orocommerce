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

        $action = $this->actions[$action];

        return $action;
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