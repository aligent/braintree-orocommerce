UPGRADE FROM 1.11 to 1.12
=========================

####OroProOrganizationBundle
- Removed parameter `OroCRM\Bundle\ChannelBundle\Provider\StateProvider $stateProvider` from constructor of `OroPro\Bundle\OrganizationBundle\Form\Handler\OrganizationProHandler` class
- Added parameter `EventDispatcherInterface $eventDispatcher` to constructor of `OroPro\Bundle\OrganizationBundle\Form\Handler\OrganizationProHandler` class

####OroProElasticSearchBundle
- The constructor of the `OroPro\Bundle\ElasticSearchBundle\Engine\ElasticSearch` class was changed. Before: `__construct(ManagerRegistry $registry, EventDispatcherInterface $eventDispatcher, DoctrineHelper $doctrineHelper, ObjectMapper $mapper, IndexAgent $indexAgent)`. After: `__construct(ManagerRegistry $registry, EventDispatcherInterface $eventDispatcher, DoctrineHelper $doctrineHelper, ObjectMapper $mapper, EntityTitleResolverInterface $entityTitleResolver, IndexAgent $indexAgent)`.
