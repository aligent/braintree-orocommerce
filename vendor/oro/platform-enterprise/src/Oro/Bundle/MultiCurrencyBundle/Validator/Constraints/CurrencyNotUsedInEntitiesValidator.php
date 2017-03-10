<?php

namespace Oro\Bundle\MultiCurrencyBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

use Oro\Bundle\MultiCurrencyBundle\Provider\CurrencyCheckerProviderChain;

class CurrencyNotUsedInEntitiesValidator extends ConstraintValidator
{
    /**
     * @var CurrencyCheckerProviderChain
     */
    protected $currencyCheckerProviderChain;

    /**
     * @param CurrencyCheckerProviderChain $currencyCheckerProviderChain
     */
    public function __construct(CurrencyCheckerProviderChain $currencyCheckerProviderChain)
    {
        $this->currencyCheckerProviderChain = $currencyCheckerProviderChain;
    }

    /**
     * @param array      $value
     * @param Constraint $constraint
     *
     * @return void
     */
    public function validate($value, Constraint $constraint)
    {
        /**
         * We skip this validator if type isn't correct
         */
        if (!is_array($value)) {
            return;
        }

        /**
         * @var CurrencyNotUsedInEntities $constraint
         * @var ExecutionContextInterface $context
         */
        $currentValue = $constraint->getValue();
        $removingCurrencies = array_values(array_diff($currentValue, $value));

        if (empty($removingCurrencies)) {
            return;
        }

        $entityLabelsWithMissedCurrencies = $this->currencyCheckerProviderChain->getEntityLabelsWithMissedCurrencies(
            $removingCurrencies,
            $constraint->getOrganization()
        );

        $context = $this->context;
        if (! empty($entityLabelsWithMissedCurrencies)) {
            $context
                ->buildViolation($constraint->message, [
                    '%currencies%' => implode(', ', $removingCurrencies),
                    '%entities%'   => implode(', ', $entityLabelsWithMissedCurrencies)
                ])
                ->setTranslationDomain('messages')
                ->addViolation();
        }
    }
}
