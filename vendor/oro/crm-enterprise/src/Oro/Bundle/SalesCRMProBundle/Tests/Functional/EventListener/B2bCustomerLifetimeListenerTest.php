<?php

namespace Oro\Bundle\SalesCRMProBundle\Tests\Functional\EventListener;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\CurrencyBundle\Entity\MultiCurrency;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;
use Oro\Bundle\SecurityBundle\Authentication\Token\UsernamePasswordOrganizationToken;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\SalesBundle\Entity\Opportunity;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class B2bCustomerLifetimeListenerTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient([], array_merge($this->generateBasicAuthHeader(), ['HTTP_X-CSRF-Header' => 1]));
        $this->loadFixtures(['Oro\Bundle\SalesCRMProBundle\Tests\Functional\Fixture\LoadSalesProBundleFixtures']);

        $user = $this->getContainer()->get('doctrine')
            ->getRepository('OroUserBundle:User')
            ->findOneBy(['username' => 'admin']);

        $organization = $this->getContainer()->get('doctrine')
            ->getRepository('OroOrganizationBundle:Organization')
            ->getFirst();
        $token = new UsernamePasswordOrganizationToken($user, 'admin', 'key', $organization);
        $this->client->getContainer()->get('security.token_storage')->setToken($token);
    }

    /**
     * @return Opportunity
     * @throws \Doctrine\ORM\ORMException
     */
    public function testCreateAffectsLifetimeIfValuable()
    {
        $em = $this->getEntityManager();
        /** @var B2bCustomer $b2bCustomer */
        $b2bCustomer = $this->getReference('default_b2bcustomer');
        $opportunity = new Opportunity();
        $opportunity->setName(uniqid('name'));
        $opportunity->setCustomerAssociation(
            $this->getAccountCustomerManager()->getAccountCustomerByTarget($b2bCustomer)
        );
        $closeRevenue = MultiCurrency::create(50, 'GBP');
        $opportunity->setCloseRevenue($closeRevenue);
        $opportunity2 = clone $opportunity;

        $this->assertEquals(0, $b2bCustomer->getLifetime());

        $em->persist($opportunity);
        $em->flush();
        $em->refresh($b2bCustomer);

        $this->assertEquals(0, $b2bCustomer->getLifetime());

        $enumClass = ExtendHelper::buildEnumValueClassName(Opportunity::INTERNAL_STATUS_CODE);
        $opportunity2->setStatus($em->getReference($enumClass, 'won'));
        $em->persist($opportunity2);
        $em->flush();
        $em->refresh($b2bCustomer);

        $converter = $this->getContainer()->get('oro_currency.converter.rate');
        $lifetime = $converter->getBaseCurrencyAmount($closeRevenue);
        $this->assertEquals($lifetime, $b2bCustomer->getLifetime());

        return $opportunity2;
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getContainer()->get('doctrine')->getManager();
    }

    /**
     * @return AccountCustomerManager
     */
    protected function getAccountCustomerManager()
    {
        return $this->getContainer()->get('oro_sales.manager.account_customer');
    }
}
