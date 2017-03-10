<?php

namespace Oro\Bundle\MultiCurrencyBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class OrganizationCurrency extends Constraint
{
    public $messageForMissedCurrency = 'oro.multi.currency.validator.organization_currency.message.missed_currency';
    public $messageForMissedCurrencies = 'oro.multi.currency.validator.organization_currency.message.missed_currencies';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'oro_multi_currency.validator.organization_currency';
    }
}
