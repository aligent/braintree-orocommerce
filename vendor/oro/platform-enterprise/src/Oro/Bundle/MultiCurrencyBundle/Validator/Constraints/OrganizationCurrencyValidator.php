<?php

namespace Oro\Bundle\MultiCurrencyBundle\Validator\Constraints;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\MultiCurrencyBundle\DependencyInjection\Configuration as MultiCurrencyConfig;

class OrganizationCurrencyValidator extends ConstraintValidator
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var ConfigManager
     */
    protected $configManager;

    /**
     * @param ConfigManager $configManager
     */
    public function __construct(EntityManager $entityManager, ConfigManager $configManager)
    {
        $this->entityManager = $entityManager;
        $this->configManager = $configManager;
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

        $qb = $this->entityManager->createQueryBuilder();
        $organizationIds = $qb
            ->from('OroOrganizationBundle:Organization', 'org')
            ->select('org.id, org.name')
            ->orderBy('org.id')
            ->getQuery()
            ->getResult();

        $requiredCurrencies = [];
        $organizationWithNonListedCurrency = [];
        $currentOrganizationId = $this->configManager->getScopeId();

        foreach ($organizationIds as $orgId) {
            $this->configManager->setScopeId($orgId['id']);
            $allowedCurrenciesConfig = $this->configManager->get(
                MultiCurrencyConfig::getConfigKeyByName(MultiCurrencyConfig::KEY_ALLOWED_CURRENCIES),
                false,
                true
            );
            if (empty($allowedCurrenciesConfig['use_parent_scope_value'])) {
                $allowedCurrenciesDiff = array_diff($allowedCurrenciesConfig['value'], $value);
                if (count($allowedCurrenciesDiff)) {
                    $requiredCurrencies = array_unique(
                        array_merge($requiredCurrencies, $allowedCurrenciesDiff)
                    );

                    array_push($organizationWithNonListedCurrency, $orgId['name']);
                }
            }
        }

        $requiredCurrenciesCount = count($requiredCurrencies);

        /**
         * @var OrganizationCurrency $constraint
         */
        if ($requiredCurrenciesCount) {
            $message = $requiredCurrenciesCount === 1 ?
                $constraint->messageForMissedCurrency :
                $constraint->messageForMissedCurrencies;

            /**
             * @var ExecutionContextInterface $context
             */
            $context = $this->context;
            $messageParameters = [
                '%currencies%' => implode(', ', $requiredCurrencies),
                '%orgNames%'   => implode(', ', $organizationWithNonListedCurrency)
            ];

            $context
                ->buildViolation($message, $messageParameters)
                ->setTranslationDomain('messages')
                ->addViolation();
        }
        
        $this->configManager->setScopeId($currentOrganizationId);
    }
}
