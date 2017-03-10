<?php

namespace Oro\Bundle\MultiCurrencyBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Translation\TranslatorInterface;

class RatesValidator extends ConstraintValidator
{
    protected $directions = [
        "rateFrom" => 'oro.multi.currency.system_configuration.currency_grid.rate_from',
        "rateTo"   => 'oro.multi.currency.system_configuration.currency_grid.rate_to'
    ];

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param mixed $value
     * @param Constraint $constraint
     *
     * @throws \Exception
     *
     * @return false | void
     */
    public function validate($value, Constraint $constraint)
    {
        /**
         * We skip this validator if type isn't correct
         */
        if (!is_array($value)) {
            return;
        }

        foreach ($value as $currency => $rate) {
            foreach ($rate as $exchangeDirection => $exchangeValue) {

                /** @var Rates $constraint */
                $validationMessage = $this->getValidationMessage($exchangeValue, $constraint);

                if (empty($this->directions[$exchangeDirection])) {
                    throw new \Exception(
                        sprintf(
                            'Expected exchange direction will be "%s", got %s',
                            implode('" or "', array_keys($this->directions)),
                            $exchangeDirection
                        )
                    );
                }

                if (false !== $validationMessage) {
                    $translatedDirectionLabel = $this->translator->trans($this->directions[$exchangeDirection]);
                    $this->context->buildViolation(
                        $validationMessage,
                        [
                            '%direction%' => $translatedDirectionLabel,
                            '%currency%' => $currency,
                        ]
                    )
                        ->setTranslationDomain('messages')
                        ->addViolation();
                }
            }
        }
    }

    /**
     * @param string $exchangeValue
     * @param Rates $constraint
     *
     * @return string
     */
    protected function getValidationMessage($exchangeValue, $constraint)
    {
        if ('' === trim($exchangeValue)) {
            return $constraint->messageOnEmptyValue;
        }

        if (!is_numeric($exchangeValue)) {
            return $constraint->messageOnNotNumeric;
        }

        if ($exchangeValue <= 0) {
            return $constraint->messageOnLessOrEqualToZero;
        }

        return false;
    }
}
