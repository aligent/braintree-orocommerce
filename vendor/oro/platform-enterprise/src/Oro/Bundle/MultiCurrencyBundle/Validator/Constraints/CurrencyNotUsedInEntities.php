<?php

namespace Oro\Bundle\MultiCurrencyBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

use Oro\Bundle\OrganizationBundle\Entity\OrganizationAwareInterface;
use Oro\Bundle\OrganizationBundle\Entity\Ownership\OrganizationAwareTrait;

class CurrencyNotUsedInEntities extends Constraint implements OrganizationAwareInterface
{
    use OrganizationAwareTrait;

    public $message = 'oro.multi.currency.validator.currency_not_used_in_entities.message';

    protected $value = [];

    /**
     * @return array
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param array $value
     */
    public function setValue(array $value)
    {
        $this->value = $value;
    }

    /**
     * @inheritdoc
     */
    public function validatedBy()
    {
        return 'oro_multi_currency.validator.currency_not_used_in_entities';
    }
}
