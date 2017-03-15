<?php

namespace Entrepids\Bundle\BraintreeBundle\Layout\DataProvider;

use Symfony\Component\Form\FormView;

use Oro\Bundle\LayoutBundle\Layout\DataProvider\AbstractFormProvider;
use Entrepids\Bundle\BraintreeBundle\Form\Type\CreditCardType;

class CreditCardFormProvider extends AbstractFormProvider
{
    /**
     * @return FormView
     */
    public function getCreditCardFormView()
    {
        return $this->getFormView(CreditCardType::NAME, null, []);
    }
}
