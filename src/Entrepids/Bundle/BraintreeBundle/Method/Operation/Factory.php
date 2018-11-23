<?php
/**
 * Factory class for individual operations.
 *
 * @category  charlesparsons
 * @package
 * @author    Jim O'Halloran <jim@aligent.com.au>
 * @copyright 2018 Aligent Consulting
 * @license   Proprietary
 * @link      http://www.aligent.com.au/
 **/

namespace Entrepids\Bundle\BraintreeBundle\Method\Operation;

class Factory
{
    private $operations;

    public function __construct()
    {
        $this->operations = [];
    }

    public function addOperation(OperationInterface $transport, $alias)
    {
        $this->operations[$alias] = $transport;
    }

    public function getOperation($transactionType)
    {
        if (array_key_exists($transactionType, $this->operations)) {
            return $this->operations[$transactionType];
        }
    }
}
