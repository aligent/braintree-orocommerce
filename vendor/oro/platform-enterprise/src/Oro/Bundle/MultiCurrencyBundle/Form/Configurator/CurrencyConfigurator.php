<?php

namespace Oro\Bundle\MultiCurrencyBundle\Form\Configurator;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Oro\Bundle\FormBundle\Utils\FormUtils;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CurrencyBundle\Form\Type\CurrencyType;
use Oro\Bundle\MultiCurrencyBundle\Form\Type\CurrencyRatesType;
use Oro\Bundle\MultiCurrencyBundle\Form\Type\CurrencyGridType;
use Oro\Bundle\MultiCurrencyBundle\Validator\Constraints\OrganizationCurrency;
use Oro\Bundle\MultiCurrencyBundle\DependencyInjection\Configuration;
use Oro\Bundle\ConfigBundle\Event\ConfigSettingsUpdateEvent;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationAwareInterface;

class CurrencyConfigurator
{
    /**
     * @var ConfigManager
     */
    protected $configManager;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    protected $childToHide = [
        CurrencyType::CONFIG_FORM_NAME,
        CurrencyRatesType::CONFIG_FORM_NAME
    ];

    public function __construct(EventDispatcherInterface $eventDispatcher, EntityManager $entityManager)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->entityManager = $entityManager;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function configure(FormBuilderInterface $builder, $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $this->hideConfigForm($event);
            if ($this->configManager &&
                $this->configManager->getScopeEntityName() === Configuration::SCOPE_NAME_ORGANIZATION) {
                $this->updateConstraints($event);
            }
        });

        /**
         * This event calling in oro_config.form.handler.config
         * right before set data to form.
         *
         * We use scope entity name to make right validation of field 'available_currencies'
         */
        $this->eventDispatcher->addListener(
            ConfigSettingsUpdateEvent::FORM_PRESET,
            function (ConfigSettingsUpdateEvent $event) {
                $this->configManager = $event->getConfigManager();
            }
        );
    }

    /**
     * @param FormEvent $event
     */
    protected function updateConstraints(FormEvent $event)
    {
        $data = $event->getData();
        if (null === $data) {
            return;
        }

        $form = $event->getForm();
        if ($form->has(CurrencyGridType::CONFIG_FORM_NAME)) {
            $currencyGridFormWrapper = $form->get(CurrencyGridType::CONFIG_FORM_NAME);
            $currencyGridForm = $currencyGridFormWrapper->get('value');
            $options = $currencyGridForm->getConfig()->getOptions();

            if (! (isset($options['constraints']) && is_array($options['constraints']))) {
                return;
            }

            $this->removeOrganizationCurrencyConstraintFromOrganizationConfigForm(
                $currencyGridFormWrapper,
                $options['constraints']
            );

            $this->populateConstraintWithOrganization($options['constraints']);
        }
    }

    /**
     * @param array $constraints
     *
     * @return void
     */
    protected function populateConstraintWithOrganization(array $constraints)
    {
        foreach ($constraints as $constraint) {
            if ($constraint instanceof OrganizationAwareInterface) {
                $organization = $this->entityManager->getReference(
                    'OroOrganizationBundle:Organization',
                    $this->configManager->getScopeId()
                );
                $constraint->setOrganization($organization);
            }
        }
    }



    /**
     * @param       $currencyGridFormWrapper
     * @param array $constraints
     *
     * @return bool
     */
    protected function removeOrganizationCurrencyConstraintFromOrganizationConfigForm(
        $currencyGridFormWrapper,
        array $constraints
    ) {
        $filteredConstraints = array_values(
            array_filter(
                $constraints,
                function ($constraint) {
                    return !$constraint instanceof OrganizationCurrency;
                }
            )
        );

        FormUtils::replaceField(
            $currencyGridFormWrapper,
            'value',
            ['constraints' => $filteredConstraints]
        );

        return true;
    }

    /**
     * @param FormEvent $event
     */
    protected function hideConfigForm(FormEvent $event)
    {
        if (null === $event->getData()) {
            return;
        }

        $form = $event->getForm();
        foreach ($this->childToHide as $childName) {
            if ($form->has($childName)) {
                $options = $form
                    ->get($childName)
                    ->getConfig()
                    ->getOptions();

                $attributes = is_array($options['attr']) ? $options['attr'] : [];
                $attributes['class'] = isset($attributes['class']) ?
                    sprintf('hide %s', $attributes['class']) :
                    'hide';

                FormUtils::replaceField(
                    $form,
                    $childName,
                    ['attr' => $attributes]
                );

                /**
                 * We should reset choice_list and choice_label
                 * because another way we'll have issue with missed label on create view
                 *
                 * For type 'currency' we don't use choice_label so it won't broke anything
                 */
                if ($childName === CurrencyType::CONFIG_FORM_NAME) {
                    FormUtils::replaceField(
                        $form->get($childName),
                        'value',
                        [],
                        ['choice_list', 'choice_label']
                    );
                }
            }
        }
    }
}
