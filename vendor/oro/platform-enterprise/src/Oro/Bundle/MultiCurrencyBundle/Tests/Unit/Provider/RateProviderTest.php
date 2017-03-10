<?php

namespace Oro\Bundle\MultiCurrencyBundle\Tests\Unit\Provider;

use Oro\Bundle\MultiCurrencyBundle\Provider\RateProvider;

class RateProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RateProvider
     */
    protected $rateProvider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Oro\Bundle\SecurityBundle\SecurityFacade
     */
    protected $securityFacade;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Doctrine\Common\Cache\CacheProvider
     */
    protected $cache;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Oro\Bundle\MultiCurrencyBundle\Entity\Repository\RateRepository
     */
    protected $repository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Oro\Bundle\OrganizationBundle\Entity\Organization
     */
    protected $organization;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Psr\Log\LoggerInterface
     */
    protected $logger;

    public function setUp()
    {
        $this->cache = $this->getMockBuilder('Doctrine\Common\Cache\CacheProvider')
                            ->disableOriginalConstructor()
                            ->setMethods([
                                'save',
                                'fetch',
                                'delete',
                                'doFetch',
                                'doContains',
                                'doSave',
                                'doDelete',
                                'doFlush',
                                'doGetStats'
                            ])
                            ->getMock();

        $securityFacadeLink = $this->getMockBuilder('\Oro\Component\DependencyInjection\ServiceLink')
                                     ->disableOriginalConstructor()
                                     ->setMethods(['getService'])
                                     ->getMock();

        $this->securityFacade = $this->getMockBuilder('Oro\Bundle\SecurityBundle\SecurityFacade')
            ->disableOriginalConstructor()
            ->setMethods(['getOrganization'])
            ->getMock();

        $securityFacadeLink->method('getService')->willReturn($this->securityFacade);

        $entityManagerLink = $this->getMockBuilder('\Oro\Component\DependencyInjection\ServiceLink')
            ->disableOriginalConstructor()
            ->setMethods(['getService'])
            ->getMock();

        $this->repository  = $this->getMockBuilder('Oro\Bundle\MultiCurrencyBundle\Entity\Repository\RateRepository')
                                  ->disableOriginalConstructor()
                                  ->setMethods(['getFlatCollectionWithFieldRateFrom'])
                                  ->getMock();

        $this->entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
                                    ->disableOriginalConstructor()
                                    ->setMethods(['getRepository'])
                                    ->getMock();

        $entityManagerLink->method('getService')->willReturn($this->entityManager);

        $this->entityManager->method('getRepository')->willReturn($this->repository);

        $this->logger = $this->getMockBuilder('Psr\Log\LoggerInterface')
                             ->disableOriginalConstructor()
                             ->getMock();

        $this->organization = $this
            ->getMockBuilder('Oro\Bundle\OrganizationBundle\Entity\Organization')
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getIsGlobal'])
            ->getMock();

        $this->rateProvider = new RateProvider(
            $entityManagerLink,
            $securityFacadeLink,
            $this->cache,
            $this->logger
        );
    }

    /**
     * @dataProvider rateListProvider
     */
    public function testGetCurrentOrganizationRateList(
        $repositoryCollection,
        $isGlobalOrg,
        $cache,
        $expected
    ) {
        $this
            ->securityFacade
            ->expects($this->once())
            ->method('getOrganization')
            ->willReturn($this->organization);

        if (!$isGlobalOrg) {
            $this->organization
                ->expects($this->atLeastOnce())
                ->method('getId')
                ->willReturn(1);
        }

        $this->repository
            ->method('getFlatCollectionWithFieldRateFrom')
            ->willReturn($repositoryCollection);

        $this
            ->organization
            ->expects($this->once())
            ->method('getIsGlobal')
            ->willReturn($isGlobalOrg);

        $this->cache
            ->expects($this->once())
            ->method('fetch')
            ->willReturn($cache);

        $this->assertEquals($this->rateProvider->getCurrentOrganizationRateList(), $expected);
    }

    public function rateListProvider()
    {
        return [
            'Main organization, without cache' => [
                'repositoryCollection' => [
                    0 => [
                        'USD' => 1,
                        'EUR' => 1.1
                    ],
                    1 => [
                        'USD' => 1,
                        'EUR' => 1.15
                    ]
                ],
                'isGlobalOrg' => false,
                'cache'    => false,
                'expected' => [
                    'USD' => 1,
                    'EUR' => 1.15
                ]
            ],
            'Global organization, without cache' => [
                'repositoryCollection' => [
                    0 => [
                        'USD' => 1,
                        'EUR' => 1.1
                    ],
                    1 => [
                        'USD' => 1,
                        'EUR' => 1.15
                    ]
                ],
                'isGlobalOrg' => true,
                'cache'    => false,
                'expected' => [
                    'USD' => 1,
                    'EUR' => 1.1
                ]
            ],
            'Main organization, with cache' => [
                'repositoryCollection' => [
                    0 => [
                        'USD' => 1,
                        'EUR' => 1.1
                    ],
                    1 => [
                        'USD' => 1,
                        'EUR' => 1.15
                    ]
                ],
                'isGlobalOrg' => false,
                'cache'    => [
                    0 => [
                        'USD' => 1,
                        'EUR' => 1.1
                    ],
                    1 => [
                        'USD' => 1,
                        'EUR' => 1.15
                    ]
                ],
                'expected' => [
                    'USD' => 1,
                    'EUR' => 1.15
                ]
            ],
            'Global organization, with cache' => [
                'repositoryCollection' => [
                    0 => [
                        'USD' => 1,
                        'EUR' => 1.1
                    ],
                    1 => [
                        'USD' => 1,
                        'EUR' => 1.15
                    ]
                ],
                'isGlobalOrg' => true,
                'cache'    => [
                    0 => [
                        'USD' => 1,
                        'EUR' => 1.1
                    ],
                    1 => [
                        'USD' => 1,
                        'EUR' => 1.15
                    ]
                ],
                'expected' => [
                    'USD' => 1,
                    'EUR' => 1.1
                ]
            ]
        ];
    }

    /**
     * @dataProvider rateProvider
     */
    public function testGetRate(
        $repositoryCollection,
        $isGlobalOrg,
        $cache,
        $currencyCode,
        $alertMessage,
        $expected
    ) {
        $this
            ->securityFacade
            ->expects($this->once())
            ->method('getOrganization')
            ->willReturn($this->organization);

        if (!$isGlobalOrg) {
            $this->organization
                ->expects($this->atLeastOnce())
                ->method('getId')
                ->willReturn(1);
        }

        $this->repository
            ->method('getFlatCollectionWithFieldRateFrom')
            ->willReturn($repositoryCollection);

        $this
            ->organization
            ->expects($this->once())
            ->method('getIsGlobal')
            ->willReturn($isGlobalOrg);

        $this->cache
            ->expects($this->once())
            ->method('fetch')
            ->willReturn($cache);

        if ($alertMessage) {
            $this->logger
                ->expects($this->once())
                ->method('alert')
                ->with(
                    sprintf($alertMessage, $currencyCode)
                )
                ->willReturnSelf();
        } else {
            $this->logger->expects($this->never())->method('alert');
        }

        $result = $this->rateProvider->getRate($currencyCode);
        $this->assertEquals($result, $expected);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function rateProvider()
    {
        return [
            'Main organization without cache' => [
                'repositoryCollection' => [
                    0 => [
                        'USD' => 1,
                        'EUR' => 1.1
                    ],
                    1 => [
                        'USD' => 1,
                        'EUR' => 1.15
                    ]
                ],
                'isGlobalOrg' => false,
                'cache'    => false,
                'currencyCode' => 'EUR',
                'alertMessage'=> false,
                'expected' => 1.15
            ],
            'Global organization without cache' => [
                'repositoryCollection' => [
                    0 => [
                        'USD' => 1,
                        'EUR' => 1.1
                    ],
                    1 => [
                        'USD' => 1,
                        'EUR' => 1.15
                    ]
                ],
                'isGlobalOrg' => true,
                'cache'    => false,
                'currencyCode' => 'EUR',
                'alertMessage'=> false,
                'expected' => 1.1
            ],
            'Main organization with cache' => [
                'repositoryCollection' => [
                    0 => [
                        'USD' => 1,
                        'EUR' => 1.1
                    ],
                    1 => [
                        'USD' => 1,
                        'EUR' => 1.15
                    ]
                ],
                'isGlobalOrg' => false,
                'cache'    => [
                    0 => [
                        'USD' => 1,
                        'EUR' => 1.1
                    ],
                    1 => [
                        'USD' => 1,
                        'EUR' => 1.15
                    ]
                ],
                'currencyCode' => 'EUR',
                'alertMessage'=> false,
                'expected' => 1.15
            ],
            'Global organization with cache' => [
                'repositoryCollection' => [
                    0 => [
                        'USD' => 1,
                        'EUR' => 1.1
                    ],
                    1 => [
                        'USD' => 1,
                        'EUR' => 1.15
                    ]
                ],
                'isGlobalOrg' => true,
                'cache'    => [
                    0 => [
                        'USD' => 1,
                        'EUR' => 1.1
                    ],
                    1 => [
                        'USD' => 1,
                        'EUR' => 1.15
                    ]
                ],
                'currencyCode' => 'EUR',
                'alertMessage'=> false,
                'expected' => 1.1
            ],
            'Main organization without cache with alert' => [
                'repositoryCollection' => [
                    0 => [
                        'USD' => 1,
                        'EUR' => 1.1
                    ],
                    1 => [
                        'USD' => 1,
                        'EUR' => 1.15
                    ]
                ],
                'isGlobalOrg' => false,
                'cache'    => false,
                'currencyCode' => 'AUR',
                'alertMessage'=> 'Can\'t get exchange rate for currency "%s".',
                'expected' => 1
            ]
        ];
    }

    public function testEmptyResultFromDatabase()
    {
        $this->repository
            ->method('getFlatCollectionWithFieldRateFrom')
            ->willReturn([]);

        $this->logger
            ->expects($this->exactly(2))
            ->method('alert')
            ->willReturnSelf();

        $this->cache
            ->expects($this->once())
            ->method('fetch')
            ->willReturn(false);

        $this->assertEquals($this->rateProvider->getRate('EUR'), 1);
    }

    public function testClearCache()
    {
        $this
            ->cache
            ->expects($this->once())
            ->method('delete');

        $this->rateProvider->clearCache();
    }
}
