<?php
/**
 * Created by PhpStorm.
 * User: adamhall
 * Date: 3/4/19
 * Time: 2:02 AM
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
     * @var BraintreeConfigInterface
     */
    protected $config;


    /**
     * @param string $action
     * @return BraintreeActionInterface
     */
    public function getAction($action)
    {
        if (!array_key_exists($action, $this->actions)) {
            throw new \InvalidArgumentException("{$action} is not supported.");
        }

        if (!$this->config) {
            throw new \InvalidArgumentException("Unable to initialize {$action}, as a configuration has not been set. ");
        }

        $action = $this->actions[$action];
        $action->initialize($this->getConfig());

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

    /**
     * @param BraintreeConfigInterface $braintreeConfig
     * @return mixed
     */
    public function setConfig(BraintreeConfigInterface $braintreeConfig)
    {
        $this->config = $braintreeConfig;
    }

    /**
     * @return BraintreeConfigInterface
     */
    public function getConfig()
    {
        return $this->config;
    }
}