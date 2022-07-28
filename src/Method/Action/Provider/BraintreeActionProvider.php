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

class BraintreeActionProvider implements BraintreeActionProviderInterface
{
    /**
     * @var array<string,BraintreeActionInterface>
     */
    protected array $actions = [];

    public function getAction(string $action): BraintreeActionInterface
    {
        if (!array_key_exists($action, $this->actions)) {
            throw new \InvalidArgumentException("{$action} is not supported.");
        }

        return $this->actions[$action];
    }

    public function addAction(string $action, BraintreeActionInterface $braintreeAction): static
    {
        if (array_key_exists($action, $this->actions)) {
            throw new \InvalidArgumentException("{$action} already exists.");
        }

        $this->actions[$action] = $braintreeAction;

        return $this;
    }

    public function hasAction(string $action): bool
    {
        return array_key_exists($action, $this->actions);
    }
}
