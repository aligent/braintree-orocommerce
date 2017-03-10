<?php

namespace Oro\Bundle\CustomerProBundle\Driver;

use Oro\Bundle\VisibilityBundle\Driver\AbstractCustomerPartialUpdateDriver;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\VisibilityBundle\Indexer\ProductVisibilityIndexer;
use Oro\Bundle\VisibilityBundle\Visibility\Provider\ProductVisibilityProvider;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\SearchBundle\Query\Criteria\Criteria;
use Oro\Bundle\SearchBundle\Query\Query;
use Oro\Bundle\VisibilityBundle\Entity\VisibilityResolved\BaseVisibilityResolved;
use Oro\Bundle\WebsiteElasticSearchBundle\Manager\ElasticSearchPartialUpdateManager;
use Oro\Bundle\WebsiteSearchBundle\Provider\PlaceholderProvider;

class ElasticSearchCustomerPartialUpdateDriver extends AbstractCustomerPartialUpdateDriver
{
    /**
     * @var ElasticSearchPartialUpdateManager
     */
    private $partialUpdateManager;

    /**
     * @param PlaceholderProvider $placeholderProvider
     * @param ProductVisibilityProvider $productVisibilityProvider
     * @param DoctrineHelper $doctrineHelper
     * @param ElasticSearchPartialUpdateManager $partialUpdateManager
     */
    public function __construct(
        PlaceholderProvider $placeholderProvider,
        ProductVisibilityProvider $productVisibilityProvider,
        DoctrineHelper $doctrineHelper,
        ElasticSearchPartialUpdateManager $partialUpdateManager
    ) {
        parent::__construct($placeholderProvider, $productVisibilityProvider, $doctrineHelper);

        $this->partialUpdateManager = $partialUpdateManager;
    }

    /**
     * {@inheritdoc}
     */
    public function createCustomerWithoutCustomerGroupVisibility(Customer $customer)
    {
        $visibilityFieldName = $this->getCustomerVisibilityFieldName($customer);
        $query = $this->getItemsForCreateCustomerVisibilityFieldQuery();
        $this->partialUpdateManager->createCustomerWithoutCustomerGroupVisibility(
            $visibilityFieldName,
            $query,
            BaseVisibilityResolved::VISIBILITY_VISIBLE
        );
    }

    /**
     * {@inheritdoc}
     */
    public function deleteCustomerVisibility(Customer $customer)
    {
        $visibilityFieldName = $this->getCustomerVisibilityFieldName($customer);
        $query = $this->getItemsForDeleteCustomerVisibilityFieldQuery($visibilityFieldName);
        $this->partialUpdateManager->deleteCustomerVisibility($visibilityFieldName, $query);
    }

    /**
     * {@inheritdoc}
     */
    protected function addCustomerVisibility(array $productIds, $productAlias, $visibilityFieldName)
    {
        $this->partialUpdateManager->addCustomerVisibility(
            $productIds,
            $productAlias,
            $visibilityFieldName,
            BaseVisibilityResolved::VISIBILITY_VISIBLE
        );
    }

    /**
     * @return Query
     */
    private function getItemsForCreateCustomerVisibilityFieldQuery()
    {
        $exprBuilder = Criteria::expr();
        $query = new Query();
        $query
            ->from('*')
            ->getCriteria()
            ->andWhere(
                $exprBuilder->orX(
                    $exprBuilder->andX(
                        $exprBuilder->eq(
                            ProductVisibilityIndexer::FIELD_IS_VISIBLE_BY_DEFAULT,
                            BaseVisibilityResolved::VISIBILITY_VISIBLE
                        ),
                        $exprBuilder->neq(
                            ProductVisibilityIndexer::FIELD_VISIBILITY_NEW,
                            BaseVisibilityResolved::VISIBILITY_VISIBLE
                        )
                    ),
                    $exprBuilder->andX(
                        $exprBuilder->eq(
                            ProductVisibilityIndexer::FIELD_IS_VISIBLE_BY_DEFAULT,
                            BaseVisibilityResolved::VISIBILITY_HIDDEN
                        ),
                        $exprBuilder->neq(
                            ProductVisibilityIndexer::FIELD_VISIBILITY_NEW,
                            BaseVisibilityResolved::VISIBILITY_HIDDEN
                        )
                    )
                )
            );

        return $query;
    }

    /**
     * @param string $visibilityFieldName
     * @return Query
     */
    private function getItemsForDeleteCustomerVisibilityFieldQuery($visibilityFieldName)
    {
        $exprBuilder = Criteria::expr();
        $query = new Query();
        $query
            ->from('*')
            ->getCriteria()
            ->andWhere($exprBuilder->eq($visibilityFieldName, BaseVisibilityResolved::VISIBILITY_VISIBLE));

        return $query;
    }
}
