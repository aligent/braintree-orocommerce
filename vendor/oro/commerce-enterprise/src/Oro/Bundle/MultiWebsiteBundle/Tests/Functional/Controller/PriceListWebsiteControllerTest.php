<?php

namespace Oro\Bundle\MultiWebsiteBundle\Tests\Functional\Controller;

use Symfony\Component\DomCrawler\Form;
use Symfony\Component\PropertyAccess\PropertyAccess;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\PricingBundle\Entity\PriceList;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Tests\Functional\DataFixtures\LoadWebsiteData;

use Oro\Bundle\MultiWebsiteBundle\Form\Extension\PriceListFormExtension;
use Oro\Bundle\MultiWebsiteBundle\Form\Type\WebsiteType;

/**
 * @dbIsolation
 */
class PriceListWebsiteControllerTest extends WebTestCase
{
    /** @var  Website */
    protected $website;

    /** @var string */
    protected $formExtensionPath;

    /** @var PriceList[] $priceLists */
    protected $priceLists;

    public function setUp()
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->client->useHashNavigation(true);
        $this->loadFixtures(['Oro\Bundle\PricingBundle\Tests\Functional\DataFixtures\LoadPriceListRelations']);
        $this->website = $this->getReference(LoadWebsiteData::WEBSITE1);
        $this->priceLists = [
            $this->getReference('price_list_1'),
            $this->getReference('price_list_2'),
            $this->getReference('price_list_3'),
        ];
        $this->formExtensionPath = sprintf(
            '%s[%s]',
            WebsiteType::NAME,
            PriceListFormExtension::PRICE_LISTS_TO_WEBSITE_FIELD
        );
    }

    public function testDelete()
    {
        $this->assertCount(3, $this->getPriceListsByWebsite());
        $form = $this->getUpdateForm();
        $this->assertTrue(isset($form[$this->formExtensionPath]));
        $form->remove($this->formExtensionPath);
        $this->client->submit($form);
        $this->assertCount(0, $this->getPriceListsByWebsite());
    }

    /**
     * @depends testDelete
     */
    public function testAdd()
    {
        $form = $this->getUpdateForm();
        $formValues = $form->getValues();
        $elementsCount = count($this->priceLists);
        $i = $elementsCount - 1;
        foreach ($this->priceLists as $priceList) {
            $collectionElementPath = sprintf('%s[%d]', $this->formExtensionPath, $i);
            $formValues[sprintf('%s[priceList]', $collectionElementPath)] = $priceList->getId();
            $formValues[sprintf('%s[priority]', $collectionElementPath)] = $elementsCount - $i;
            $i--;
        }
        $params = $this->explodeArrayPaths($formValues);
        $this->client->request(
            'POST',
            $this->getUrl('oro_multiwebsite_update', ['id' => $this->website->getId()]),
            $params
        );
        $form = $this->getUpdateForm();
        $formValues = $form->getValues();
        $i = 0;
        foreach ($this->priceLists as $priceList) {
            $collectionElementPath = sprintf('%s[%d]', $this->formExtensionPath, $i);
            $this->assertTrue(isset($formValues[sprintf('%s[priceList]', $collectionElementPath)]));
            $this->assertTrue(isset($formValues[sprintf('%s[priority]', $collectionElementPath)]));
            $this->assertEquals($formValues[sprintf('%s[priceList]', $collectionElementPath)], $priceList->getId());
            $this->assertEquals($formValues[sprintf('%s[priority]', $collectionElementPath)], ++$i);
        }
    }

    /**
     * @depends testAdd
     */
    public function testView()
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_multiwebsite_view', ['id' => $this->website->getId()])
        );
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $html = $crawler->html();
        $i = 0;
        foreach ($this->priceLists as $priceList) {
            $this->assertContains($priceList->getName(), $html);
            $this->assertContains((string)++$i, $html);
        }
    }

    public function testValidation()
    {
        $form = $this->getUpdateForm();
        $formValues = $form->getValues();
        /** @var PriceList $priceList */
        $priceList = $this->getReference('price_list_1');
        $collectionElementPath1 = sprintf('%s[%d]', $this->formExtensionPath, 0);
        $collectionElementPath2 = sprintf('%s[%d]', $this->formExtensionPath, 1);
        $formValues[sprintf('%s[priceList]', $collectionElementPath1)] = $priceList->getId();
        $formValues[sprintf('%s[priority]', $collectionElementPath1)] = '';
        $this->checkValidationMessage($formValues, 'This value should not be blank');
        $formValues[sprintf('%s[priority]', $collectionElementPath1)] = 'not_integer';
        $this->checkValidationMessage($formValues, 'This value should be integer number');
        $formValues[sprintf('%s[priority]', $collectionElementPath1)] = 1;
        $formValues[sprintf('%s[priceList]', $collectionElementPath2)] = $priceList->getId();
        $formValues[sprintf('%s[priority]', $collectionElementPath2)] = 2;

        $this->checkValidationMessage($formValues, 'Price list is duplicated.');
    }

    /**
     * @param array $formValues
     * @param string $message
     */
    protected function checkValidationMessage(array $formValues, $message)
    {
        $params = $this->explodeArrayPaths($formValues);
        $crawler = $this->client->request(
            'POST',
            $this->getUrl('oro_multiwebsite_update', ['id' => $this->website->getId()]),
            $params
        );
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains($message, $crawler->html());
    }

    /**
     * @return Form
     */
    protected function getUpdateForm()
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_multiwebsite_update', ['id' => $this->website->getId()])
        );
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        return $crawler->selectButton('Save and Close')->form();
    }

    /**
     * {@inheritdoc}
     */
    public function getPriceListsByWebsite()
    {
        return $this->client
            ->getContainer()
            ->get('doctrine')
            ->getManager()
            ->getRepository('OroPricingBundle:PriceListToWebsite')
            ->findBy(['website' => $this->website]);
    }

    /**
     * @param array $values
     * @return array
     */
    protected function explodeArrayPaths($values)
    {
        $accessor = PropertyAccess::createPropertyAccessor();
        $parameters = [];
        foreach ($values as $key => $val) {
            if (!$pos = strpos($key, '[')) {
                continue;
            }
            $key = '[' . substr($key, 0, $pos) . ']' . substr($key, $pos);
            $accessor->setValue($parameters, $key, $val);
        }

        return $parameters;
    }
}
