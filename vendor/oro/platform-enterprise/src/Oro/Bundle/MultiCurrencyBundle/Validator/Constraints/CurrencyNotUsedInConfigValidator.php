<?php

namespace Oro\Bundle\MultiCurrencyBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

use Oro\Bundle\MultiCurrencyBundle\Provider\DependentConfigProvider;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationAwareInterface;

class CurrencyNotUsedInConfigValidator extends ConstraintValidator
{
    /** @var DependentConfigProvider  */
    protected $dependencyCollection;

    /**
     * @inheritDoc
     */
    public function __construct(DependentConfigProvider $dependencyCollection)
    {
        $this->dependencyCollection = $dependencyCollection;
    }
    /**
     * @param array      $value
     * @param Constraint $constraint
     *
     * @return void
     */
    public function validate($value, Constraint $constraint)
    {
        $currentOrganization = null;
        if ($constraint instanceof OrganizationAwareInterface) {
            $currentOrganization = $constraint->getOrganization();
        }
        if (!$this->dependencyCollection->isDependenciesValid($value, $currentOrganization)) {
            $failedDependencyName = $this->dependencyCollection->getFailedDependencyName();
            if ($failedDependencyName !== '') {
                $message = sprintf('%s.%s', $constraint->messagePrefix, $failedDependencyName);
            } else {
                $message = $constraint->message;
            }
            /** @var ExecutionContextInterface $context */
            $context = $this->context;
            $context->buildViolation($message, [])
                ->addViolation();
        }
    }
}
