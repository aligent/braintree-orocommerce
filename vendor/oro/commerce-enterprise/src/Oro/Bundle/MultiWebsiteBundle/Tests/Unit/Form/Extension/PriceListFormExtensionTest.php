<?php

namespace Oro\Bundle\MultiWebsiteBundle\Tests\Unit\Form\Extension;

use Symfony\Component\Form\PreloadedExtension;

use Oro\Component\Testing\Unit\FormIntegrationTestCase;
use Oro\Component\Testing\Unit\EntityTrait;
use Oro\Bundle\PricingBundle\Tests\Unit\Form\Type\PriceListCollectionTypeExtensionsProvider;
use Oro\Bundle\PricingBundle\Form\Type\PriceListSelectWithPriorityType;
use Oro\Bundle\PricingBundle\Tests\Unit\Form\Type\Stub\PriceListSelectTypeStub;
use Oro\Bundle\PricingBundle\Entity\PriceList;
use Oro\Bundle\PricingBundle\Entity\PriceListToWebsite;
use Oro\Bundle\MultiWebsiteBundle\Form\Extension\PriceListFormExtension;
use Oro\Bundle\MultiWebsiteBundle\EventListener\PriceListListener;
use Oro\Bundle\MultiWebsiteBundle\Tests\Unit\Form\Extension\Stub\WebsiteTypeStub;

use Oro\Bundle\MultiWebsiteBundle\Form\Type\WebsiteType;

class PriceListFormExtensionTest extends FormIntegrationTestCase
{
    use EntityTrait;

    /**
     * @return array
     */
    protected function getExtensions()
    {
        /** @var PriceListListener $listener */
        $listener = $this->getMockBuilder('Oro\Bundle\MultiWebsiteBundle\EventListener\PriceListListener')
            ->disableOriginalConstructor()
            ->getMock();

        $provider = new PriceListCollectionTypeExtensionsProvider();

        $extensions = [
            new PreloadedExtension(
                [
                    WebsiteType::NAME => new WebsiteTypeStub()
                ],
                [
                    WebsiteType::NAME => [
                        new PriceListFormExtension('Oro\Bundle\PricingBundle\Entity\PriceListToWebsite', $listener)
                    ]
                ]
            )
        ];

        return array_merge($provider->getExtensions(), $extensions);
    }

    /**
     * @dataProvider submitDataProvider
     *
     * @param array $submitted
     * @param array $expected
     */
    public function testSubmit(array $submitted, array $expected)
    {
        $form = $this->factory->create(WebsiteType::NAME, [], []);
        $form->submit($submitted);
        $this->assertTrue($form->isValid());
        $this->assertEquals(
            $expected[PriceListFormExtension::PRICE_LISTS_FALLBACK_FIELD],
            $form->get(PriceListFormExtension::PRICE_LISTS_FALLBACK_FIELD)->getData()
        );
        $this->assertEquals(
            $expected[PriceListFormExtension::PRICE_LISTS_TO_WEBSITE_FIELD],
            $form->get(PriceListFormExtension::PRICE_LISTS_TO_WEBSITE_FIELD)->getData()
        );
    }

    /**
     * @return array
     */
    public function submitDataProvider()
    {
        return [
            [
                'submitted' => [
                    PriceListFormExtension::PRICE_LISTS_FALLBACK_FIELD => '0',
                    PriceListFormExtension::PRICE_LISTS_TO_WEBSITE_FIELD => [
                        0 => [
                            PriceListSelectWithPriorityType::PRICE_LIST_FIELD
                            => (string)PriceListSelectTypeStub::PRICE_LIST_1,
                            PriceListSelectWithPriorityType::PRIORITY_FIELD => '200',
                            PriceListSelectWithPriorityType::MERGE_ALLOWED_FIELD => true,
                        ],
                        1 => [
                            PriceListSelectWithPriorityType::PRICE_LIST_FIELD
                            => (string)PriceListSelectTypeStub::PRICE_LIST_2,
                            PriceListSelectWithPriorityType::PRIORITY_FIELD => '100',
                            PriceListSelectWithPriorityType::MERGE_ALLOWED_FIELD => false,
                        ]
                    ],
                ],
                'expected' => [
                    PriceListFormExtension::PRICE_LISTS_FALLBACK_FIELD => 0,
                    PriceListFormExtension::PRICE_LISTS_TO_WEBSITE_FIELD => [
                        0 => (new PriceListToWebsite())
                            ->setPriceList($this->getPriceList(PriceListSelectTypeStub::PRICE_LIST_1))
                            ->setPriority(200)
                            ->setMergeAllowed(true),
                        1 => (new PriceListToWebsite())
                            ->setPriceList($this->getPriceList(PriceListSelectTypeStub::PRICE_LIST_2))
                            ->setPriority(100)
                            ->setMergeAllowed(false)
                    ],
                ],
            ]
        ];
    }

    /**
     * @param int $id
     * @return PriceList
     */
    protected function getPriceList($id)
    {
        return $this->getEntity('Oro\Bundle\PricingBundle\Entity\PriceList', ['id' => $id]);
    }
}
