<?php
/**
 * Created by PhpStorm.
 * User: adamhall
 * Date: 3/8/19
 * Time: 6:37 PM
 */

namespace Aligent\BraintreeBundle\Method\Action;


use Aligent\BraintreeBundle\Method\Option\Resolver\OptionResolverInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

abstract class AbstractBraintreeAction implements BraintreeActionInterface, LoggerAwareInterface
{

    use LoggerAwareTrait;

    /**
     * @var OptionResolverInterface
     */
    protected $optionResolver;

    /**
     * AbstractBraintreeAction constructor.
     * @param OptionResolverInterface $optionResolver
     */
    public function __construct(OptionResolverInterface $optionResolver)
    {
        $this->optionResolver = $optionResolver;
    }
}