<?php

namespace Oro\Bundle\MultiCurrencyBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class Rates extends Constraint
{
    public $messageOnEmptyValue = 'oro.multi.currency.validator.rates.message.empty_value';
    public $messageOnNotNumeric = 'oro.multi.currency.validator.rates.message.not_number';
    public $messageOnLessOrEqualToZero = 'oro.multi.currency.validator.rates.message.less_or_equal_to_zero';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'oro_multi_currency.validator.rates';
    }
}
