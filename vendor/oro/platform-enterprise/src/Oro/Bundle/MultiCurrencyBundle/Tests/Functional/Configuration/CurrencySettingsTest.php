<?php

namespace Oro\Bundle\MultiCurrencyBundle\Tests\Functional\Configuration;

use Symfony\Component\DomCrawler\Field\InputFormField;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @dbIsolation
 */
class CurrencySettings extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->loadFixtures(['Oro\Bundle\MultiCurrencyBundle\Tests\Functional\DataFixtures\LoadConfig']);
        $this->client->useHashNavigation(true);
    }

    public function testGetCurrencyConfigFormInSystemScope()
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_config_configuration_system', [
                'activeGroup' => 'platform',
                'activeSubGroup' => 'currency'
            ])
        );
        $form  = $crawler->selectButton('Save settings')->form();
        $this->assertEquals($form->has('currency[oro_multi_currency___allowed_currencies][value]'), true);
        $this->assertEquals($form->has('currency[oro_multi_currency___currency_rates][value]'), true);
        $response = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($response, 200);
    }

    public function testGetCurrencyConfigFormInOrganizationScope()
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_organization_config', [
                'id' => self::AUTH_ORGANIZATION,
                'activeGroup' => 'platform',
                'activeSubGroup' => 'currency'
            ])
        );
        $form  = $crawler->selectButton('Save settings')->form();
        $this->assertEquals($form->has('currency[oro_multi_currency___allowed_currencies][value]'), true);
        $this->assertEquals($form->has('currency[oro_multi_currency___currency_rates][value]'), true);
        $response = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($response, 200);
    }

    /**
     * @depends testGetCurrencyConfigFormInSystemScope
     */
    public function testAddNewCurrencyInConfigFormInSystemScope()
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_config_configuration_system', [
                'activeGroup' => 'platform',
                'activeSubGroup' => 'currency'
            ])
        );

        $form  = $crawler->selectButton('Save settings')->form();
        /**
         * @var InputFormField $allowedCurrenciesFormField
         * @var InputFormField $currencyRatesFormField
         */
        $allowedCurrenciesFormField = $form['currency[oro_multi_currency___allowed_currencies][value]'];
        $allowedCurrenciesFormFieldValue = $this->addValueToField(
            $allowedCurrenciesFormField,
            ['UAH']
        );

        $allowedCurrenciesFormField->setValue($allowedCurrenciesFormFieldValue);
        $currencyRatesFormField = $form['currency[oro_multi_currency___currency_rates][value]'];
        $currencyRatesFormFieldValue = $this->addValueToField(
            $currencyRatesFormField,
            ['UAH' => ['rateFrom' => 0.039, 'rateTo' => 25.63]]
        );
        $currencyRatesFormField->setValue($currencyRatesFormFieldValue);

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains("Configuration saved", $crawler->html());
        $this->assertContains($currencyRatesFormFieldValue, $crawler->html());
        $this->assertContains($allowedCurrenciesFormFieldValue, $crawler->html());
    }

    /**
     * @depends testAddNewCurrencyInConfigFormInSystemScope
     */
    public function testRemoveCurrencyInConfigFormInSystemScope()
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_config_configuration_system', [
                'activeGroup' => 'platform',
                'activeSubGroup' => 'currency'
            ])
        );

        $form  = $crawler->selectButton('Save settings')->form();
        /**
         * @var InputFormField $allowedCurrenciesFormField
         * @var InputFormField $currencyRatesFormField
         */
        $allowedCurrenciesFormField = $form['currency[oro_multi_currency___allowed_currencies][value]'];
        $allowedCurrenciesFormFieldValue = $this->removeValueFromField(
            $allowedCurrenciesFormField,
            ['UAH']
        );

        $allowedCurrenciesFormField->setValue($allowedCurrenciesFormFieldValue);
        $currencyRatesFormField = $form['currency[oro_multi_currency___currency_rates][value]'];
        $currencyRatesFormFieldValue = $this->removeValueFromField(
            $currencyRatesFormField,
            ['UAH' => '']
        );
        $currencyRatesFormField->setValue($currencyRatesFormFieldValue);

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains("Configuration saved", $crawler->html());
        $this->assertContains($currencyRatesFormFieldValue, $crawler->html());
        $this->assertContains($allowedCurrenciesFormFieldValue, $crawler->html());
    }

    /**
     * @depends testGetCurrencyConfigFormInOrganizationScope
     */
    public function testAddNewCurrencyInConfigFormInOrganizationScope()
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_organization_config', [
                'id' => self::AUTH_ORGANIZATION,
                'activeGroup' => 'platform',
                'activeSubGroup' => 'currency'
            ])
        );

        $form  = $crawler->selectButton('Save settings')->form();
        /**
         * @var InputFormField $allowedCurrenciesFormField
         * @var InputFormField $currencyRatesFormField
         */
        $allowedCurrenciesFormField = $form['currency[oro_multi_currency___allowed_currencies][value]'];
        $allowedCurrenciesFormFieldValue = $this->addValueToField($allowedCurrenciesFormField, ['EUR']);
        $allowedCurrenciesFormField->setValue($allowedCurrenciesFormFieldValue);

        $currencyRatesFormField = $form['currency[oro_multi_currency___currency_rates][value]'];
        $currencyRatesFormFieldValue = $this->addValueToField(
            $currencyRatesFormField,
            ['EUR' => ['rateFrom' => 0.81, 'rateTo' => 1.21]]
        );
        $currencyRatesFormField->setValue($currencyRatesFormFieldValue);

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains("Configuration saved", $crawler->html());
        $this->assertContains($currencyRatesFormFieldValue, $crawler->html());
        $this->assertContains($allowedCurrenciesFormFieldValue, $crawler->html());
    }

    /**
     * @depends testGetCurrencyConfigFormInOrganizationScope
     */
    public function testValidationErrorOnRemoveCurrencyThatExistInOrganizationScope()
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_config_configuration_system', [
                'activeGroup' => 'platform',
                'activeSubGroup' => 'currency'
            ])
        );

        $form  = $crawler->selectButton('Save settings')->form();
        /**
         * @var InputFormField $allowedCurrenciesFormField
         * @var InputFormField $currencyRatesFormField
         */
        $allowedCurrenciesFormField = $form['currency[oro_multi_currency___allowed_currencies][value]'];
        $allowedCurrenciesFormField->setValue($this->removeValueFromField($allowedCurrenciesFormField, ['EUR']));

        $currencyRatesFormField = $form['currency[oro_multi_currency___currency_rates][value]'];
        $currencyRatesFormField->setValue($this->removeValueFromField($currencyRatesFormField, ['EUR' => '']));

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains(
            'Cannot remove "EUR" currency because it is used in the following organizations',
            $crawler->html()
        );
    }

    /**
     * @depends testAddNewCurrencyInConfigFormInOrganizationScope
     */
    public function testRemoveCurrencyInConfigFormInOrganizationScope()
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_organization_config', [
                'id' => self::AUTH_ORGANIZATION,
                'activeGroup' => 'platform',
                'activeSubGroup' => 'currency'
            ])
        );

        $form  = $crawler->selectButton('Save settings')->form();
        /**
         * @var InputFormField $allowedCurrenciesFormField
         * @var InputFormField $currencyRatesFormField
         */
        $allowedCurrenciesFormField = $form['currency[oro_multi_currency___allowed_currencies][value]'];
        $allowedCurrenciesFormFieldValue = $this->removeValueFromField($allowedCurrenciesFormField, ['EUR']);
        $allowedCurrenciesFormField->setValue($allowedCurrenciesFormFieldValue);

        $currencyRatesFormField = $form['currency[oro_multi_currency___currency_rates][value]'];
        $currencyRatesFormFieldValue = $this->removeValueFromField($currencyRatesFormField, ['EUR' => '']);
        $currencyRatesFormField->setValue($currencyRatesFormFieldValue);

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains("Configuration saved", $crawler->html());
        $this->assertContains($currencyRatesFormFieldValue, $crawler->html());
        $this->assertContains($allowedCurrenciesFormFieldValue, $crawler->html());
    }


    public function testValidationErrorOnInvalidRateJsonValue()
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_config_configuration_system', [
                'activeGroup' => 'platform',
                'activeSubGroup' => 'currency'
            ])
        );

        $form  = $crawler->selectButton('Save settings')->form();
        /**
         * @var InputFormField $currencyRatesFormField
         */
        $currencyRatesFormField = $form['currency[oro_multi_currency___currency_rates][value]'];
        $currencyRatesFormFieldValue = 'invalid_json';
        $currencyRatesFormField->setValue($currencyRatesFormFieldValue);

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains('This value is not valid.', $crawler->html());
    }

    public function testValidationErrorOnInvalidCurrenciesJsonValue()
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_config_configuration_system', [
                'activeGroup' => 'platform',
                'activeSubGroup' => 'currency'
            ])
        );

        $form  = $crawler->selectButton('Save settings')->form();
        /**
         * @var InputFormField $allowedCurrenciesFormField
         */
        $allowedCurrenciesFormField = $form['currency[oro_multi_currency___allowed_currencies][value]'];
        $allowedCurrenciesFormFieldValue = 'invalid_json';
        $allowedCurrenciesFormField->setValue($allowedCurrenciesFormFieldValue);

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains('This value is not valid.', $crawler->html());
    }

    /**
     * @param InputFormField $formField
     * @param array $valueToRemove
     *
     * @return string
     */
    protected function removeValueFromField(InputFormField $formField, array $valueToRemove)
    {
        if (is_numeric(key($valueToRemove))) {
            $newFieldValue = array_diff(
                json_decode($formField->getValue(), true),
                $valueToRemove
            );
        } else {
            $newFieldValue = array_diff_key(
                json_decode($formField->getValue(), true),
                $valueToRemove
            );
        }

        return json_encode($newFieldValue);
    }

    /**
     * @param InputFormField $formField
     * @param array $valueToAdd
     *
     * @return string
     */
    protected function addValueToField(InputFormField $formField, array $valueToAdd)
    {
        return json_encode(
            array_merge(
                json_decode($formField->getValue(), true),
                $valueToAdd
            )
        );
    }
}
