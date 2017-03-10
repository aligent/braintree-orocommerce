<?php

namespace Oro\Bundle\MultiCurrencyBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

use Oro\Bundle\OrganizationBundle\Entity\OrganizationAwareInterface;
use Oro\Bundle\OrganizationBundle\Entity\Ownership\OrganizationAwareTrait;

class CurrencyNotUsedInConfig extends Constraint implements OrganizationAwareInterface
{
    use OrganizationAwareTrait;

    public $message = 'oro.multicurrency.message.currency_not_used_in_config';
    public $messagePrefix = 'oro.multicurrency.message';

    /**
     * @inheritdoc
     */
    public function validatedBy()
    {
        return 'oro_multi_currency.validator.currency_not_used_in_config';
    }
}
