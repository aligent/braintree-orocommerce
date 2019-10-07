<?php
/**
 * Created by PhpStorm.
 * User: adamhall
 * Date: 3/4/19
 * Time: 1:38 AM
 */

namespace Aligent\BraintreeBundle\Method\Factory;


use Aligent\BraintreeBundle\Method\AligentBraintreeMethod;
use Aligent\BraintreeBundle\Method\Config\BraintreeConfigInterface;
use Aligent\BraintreeBundle\Method\Action\Provider\BraintreeActionProviderInterface;
use Oro\Bundle\PaymentBundle\Method\PaymentMethodInterface;
use Psr\Log\LoggerInterface;

class BraintreeMethodFactory implements BraintreeMethodFactoryInterface
{

    /**
     * @var BraintreeActionProviderInterface
     */
    protected $provider;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * BraintreeMethodFactory constructor.
     * @param BraintreeActionProviderInterface $provider
     * @param LoggerInterface $logger
     */
    public function __construct(
        BraintreeActionProviderInterface $provider,
        LoggerInterface $logger
    )
    {
        $this->provider = $provider;
        $this->logger = $logger;
    }

    /**
     * @param BraintreeConfigInterface $config
     * @return PaymentMethodInterface
     */
    public function create(BraintreeConfigInterface $config)
    {
        return new AligentBraintreeMethod($config, $this->provider, $this->logger);
    }
}