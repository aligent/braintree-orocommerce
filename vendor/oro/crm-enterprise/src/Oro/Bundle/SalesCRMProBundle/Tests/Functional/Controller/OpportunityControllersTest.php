<?php

namespace Oro\Bundle\SalesCRMProBundle\Tests\Functional\Controller;

use Symfony\Component\DomCrawler\Form;

use Oro\Bundle\DataGridBundle\Tests\Functional\AbstractDatagridTestCase;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class OpportunityControllersTest extends AbstractDatagridTestCase
{

    protected function setUp()
    {
        $this->initClient(
            ['debug' => false],
            array_merge($this->generateBasicAuthHeader(), array('HTTP_X-CSRF-Header' => 1))
        );
        $this->client->useHashNavigation(true);
        $this->loadFixtures(['Oro\Bundle\SalesBundle\Tests\Functional\Fixture\LoadSalesBundleFixtures']);
    }

    public function testCreate()
    {
        $crawler = $this->client->request('GET', $this->getUrl('oro_sales_opportunity_create'));

        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $name = 'name' . $this->generateRandomString();
        $form['oro_sales_opportunity_form[name]']         = $name;
        $form['oro_sales_opportunity_form[probability]']  = 50;
        $form['oro_sales_opportunity_form[budgetAmount][value]'] = 10000;
        $form['oro_sales_opportunity_form[budgetAmount][currency]'] = 'USD';
        $form['oro_sales_opportunity_form[customerNeed]'] = 10001;
        $form['oro_sales_opportunity_form[closeReason]']  = 'won';
        $form['oro_sales_opportunity_form[status]']       = 'won';
        $form['oro_sales_opportunity_form[owner]']        = 1;
        $form['oro_sales_opportunity_form[customerAssociation]'] = '{"value":"Account"}'; //create with new Account

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains("Opportunity saved", $crawler->html());

        return $name;
    }

     /**
     * @param string $name
     * @depends testCreate
     *
     * @return string
     */
    public function testUpdate($name)
    {
        $response = $this->client->requestGrid(
            'sales-opportunity-grid',
            [
                'sales-opportunity-grid[_filter][name][type]' => '1',
                'sales-opportunity-grid[_filter][name][value]' => $name,
            ]
        );

        $result = $this->getJsonResponseContent($response, 200);
        $result = reset($result['data']);
        $returnValue = $result;
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_sales_opportunity_update', ['id' => $result['id']])
        );

        $this->assertContains("[budgetAmount][baseCurrencyValue]", $crawler->html());
        $this->assertContains("[closeRevenue][baseCurrencyValue]", $crawler->html());

        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $value = rand(10, 100);
        $currency = "USD";
        $form['oro_sales_opportunity_form[budgetAmount][value]'] = $value;
        $form['oro_sales_opportunity_form[budgetAmount][currency]'] = $currency;
        $form['oro_sales_opportunity_form[budgetAmount][baseCurrencyValue]'] = $value;

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains("Opportunity saved", $crawler->html());

        $returnValue['value'] = $value;
        $returnValue['currency'] = $currency;

        return $returnValue;
    }

    /**
     * @param array $returnValue
     * @depends testUpdate
     *
     * @return string
     */
    public function testView(array $returnValue)
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_sales_opportunity_view', ['id' => $returnValue['id']])
        );

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains(sprintf('$%1.2f', $returnValue['value']), $crawler->html());

        return $returnValue;
    }

    /**
     * @param array $returnValue
     * @depends testView
     *
     * @return string
     */
    public function testUpdateNullBaseCurrency($returnValue)
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_sales_opportunity_update', ['id' => $returnValue['id']])
        );

        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $currency = "USD";
        $form['oro_sales_opportunity_form[budgetAmount][currency]'] = $currency;
        $form['oro_sales_opportunity_form[budgetAmount][baseCurrencyValue]'] = '';

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $validationMessage = $crawler->filter('.validation-failed');
        $this->assertNotEmpty(
            $validationMessage,
            'Error message for [budgetAmount][baseCurrencyValue] not found'
        );
        $this->assertContains(
            "This value should not be blank",
            $validationMessage->html(),
            'Error message for [budgetAmount][baseCurrencyValue] not correct'
        );
    }

    /**
     * @param array $returnValue
     * @depends testView
     *
     * @return string
     */
    public function testUpdateWithEmptyBudgetAmount($returnValue)
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_sales_opportunity_update', ['id' => $returnValue['id']])
        );

        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $form['oro_sales_opportunity_form[budgetAmount][value]'] = '';

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains(
            "Opportunity saved",
            $crawler->html(),
            'Something went wrong, Form can not be saved'
        );

        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $this->assertEmpty(
            $form['oro_sales_opportunity_form[budgetAmount][baseCurrencyValue]']->getValue(),
            'Base budget amount should be erased when we erase main value, but it is not'
        );
    }

    /**
     * @param array $returnValue
     * @depends testUpdate
     */
    public function testDelete(array $returnValue)
    {
        $this->client->request(
            'DELETE',
            $this->getUrl('oro_api_delete_opportunity', ['id' => $returnValue['id']])
        );

        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);

        $this->client->request(
            'GET',
            $this->getUrl('oro_sales_opportunity_view', ['id' => $returnValue['id']])
        );
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 404);
    }

    /**
     * @return array
     */
    public function gridProvider()
    {
        return [
            'Opportunity grid'                => [
                [
                    'gridParameters'      => [
                        'gridName' => 'sales-opportunity-grid'
                    ],
                    'gridFilters'         => [],
                    'assert'              => [
                        'name'         => 'opname',
                        'budgetAmount' => 'USD50.0000',
                        'probability'  => 10,
                    ],
                    'expectedResultCount' => 1
                ],
            ],
            'Opportunity grid with filter'    => [
                [
                    'gridParameters'      => [
                        'gridName' => 'sales-opportunity-grid'
                    ],
                    'gridFilters'         => [
                        'sales-opportunity-grid[_filter][budgetAmountValue][value]' => '50.00',
                        'sales-opportunity-grid[_filter][budgetAmountValue][type]' => '3',
                    ],
                    'assert'              => [
                        'name'              => 'opname',
                        'budgetAmount'      => 'USD50.0000',
                        'probability'       => 10,
                    ],
                    'expectedResultCount' => 1
                ]
            ],
            'Opportunity grid without result' => [
                [
                    'gridParameters'      => [
                        'gridName' => 'sales-opportunity-grid'
                    ],
                    'gridFilters'         => [
                        'sales-opportunity-grid[_filter][budgetAmount][value]' => '150.00',
                    ],
                    'assert'              => [],
                    'expectedResultCount' => 0
                ],
            ]
        ];
    }
}
