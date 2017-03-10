<?php

namespace Oro\Bundle\MultiWebsiteBundle\Form\Type;

use Oro\Bundle\MultiWebsiteBundle\Matcher\WebsiteMatcherRegistry;
use Oro\Component\PhpUtils\ArrayUtil;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WebsiteMatcherSettingsCollectionType extends AbstractType
{
    const NAME = 'oro_multiwebsite_matcher_settings_collection';

    /**
     * @var WebsiteMatcherRegistry
     */
    protected $matcherRegistry;

    /**
     * @param WebsiteMatcherRegistry $matcherRegistry
     */
    public function __construct(WebsiteMatcherRegistry $matcherRegistry)
    {
        $this->matcherRegistry = $matcherRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'entry_type' => WebsiteMatcherSettingsType::class,
            'show_form_when_empty' => true,
            'allow_add' => false,
            'allow_delete' => false,
            'mapped' => true,
            'label' => false,
            'error_bubbling' => false
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                $currentSettings = [];
                $data = $event->getData();
                if (null === $data) {
                    return;
                }
                if (is_array($data) && count($data) > 0) {
                    foreach ($data as $settingsRow) {
                        $currentSettings[$settingsRow['matcher_alias']] = $settingsRow;
                    }
                }

                $settings = [];
                foreach ($this->getDefaultSettings() as $alias => $defaultSettingsRow) {
                    if (array_key_exists($alias, $currentSettings)) {
                        $currentSettings[$alias]['label'] = $defaultSettingsRow['label'];
                        $currentSettings[$alias]['tooltip'] = $defaultSettingsRow['tooltip'];
                        $settings[] = $currentSettings[$alias];
                    } else {
                        $settings[] = $defaultSettingsRow;
                    }
                }
                ArrayUtil::sortBy($settings, true, 'priority');
                $event->setData($settings);
            },
            50
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CollectionType::class;
    }

    /**
     * @return array
     */
    protected function getDefaultSettings()
    {
        $result = [];
        foreach ($this->matcherRegistry->getRegisteredMatchers() as $alias => $matcher) {
            $result[$alias] = [
                'enabled' => true,
                'matcher_alias' => $alias,
                'label' => $matcher->getLabel(),
                'tooltip' => $matcher->getTooltip(),
                'priority' => $matcher->getPriority()
            ];
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }
}
