<?php
/**
 * @category  Aligent
 * @package   BraintreeBundle
 * @author    Adam Hall <adam.hall@aligent.com.au>
 * @copyright 2020 Aligent Consulting.
 * @license
 * @link      http://www.aligent.com.au/
 */

namespace Aligent\BraintreeBundle\Method\Factory;

use Aligent\BraintreeBundle\Method\Action\Provider\BraintreeActionProviderInterface;
use Aligent\BraintreeBundle\Method\AligentBraintreeMethod;
use Aligent\BraintreeBundle\Method\Config\BraintreeConfigInterface;
use Oro\Bundle\PaymentBundle\Method\PaymentMethodInterface;
use Psr\Log\LoggerInterface;

class BraintreeMethodFactory implements BraintreeMethodFactoryInterface
{
    protected BraintreeActionProviderInterface $provider;
    protected LoggerInterface $logger;

    public function __construct(
        BraintreeActionProviderInterface $provider,
        LoggerInterface $logger,
    ) {
        $this->provider = $provider;
        $this->logger = $logger;
    }

    public function create(BraintreeConfigInterface $config): PaymentMethodInterface
    {
        return new AligentBraintreeMethod($config, $this->provider, $this->logger);
    }
}
