<?php

namespace Oro\Bundle\MultiCurrencyBundle\Tests\Unit\Validator;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

use Oro\Bundle\MultiCurrencyBundle\Tests\Unit\Stub\ConfigManagerStub;
use Oro\Bundle\MultiCurrencyBundle\Validator\Constraints\OrganizationCurrency;
use Oro\Bundle\MultiCurrencyBundle\Validator\Constraints\OrganizationCurrencyValidator;

class OrganizationCurrencyValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OrganizationCurrencyValidator
     */
    protected $validator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | ExecutionContextInterface
     */
    protected $context;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | EntityManager
     */
    protected $entityManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | QueryBuilder
     */
    protected $queryBuilder;

    /**
     * @var ConfigManagerStub
     */
    protected $configManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | OrganizationCurrency
     */
    protected $constraint;

    /**
     * @var ConstraintViolationBuilder
     */
    protected $constraintViolation;

    protected function setUp()
    {
        $this->context       = $this
            ->getMockBuilder('Symfony\Component\Validator\Context\ExecutionContext')
            ->disableOriginalConstructor()
            ->setMethods(['buildViolation'])
            ->getMock();

        $this->constraintViolation = $this
            ->getMockBuilder('Symfony\Component\Validator\Violation\ConstraintViolationBuilder')
            ->disableOriginalConstructor()
            ->setMethods(['setTranslationDomain', 'addViolation'])
            ->getMock();

        $this->constraintViolation
            ->expects($this->any())
            ->method('setTranslationDomain')
            ->with('messages')
            ->will($this->returnSelf());

        $this->constraintViolation
            ->expects($this->any())
            ->method('addViolation')
            ->withAnyParameters()
            ->will($this->returnSelf());

        $this->configManager = new ConfigManagerStub();

        $this->entityManager = $this
            ->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->setMethods(['createQueryBuilder'])
            ->getMock();

        $this->constraint = new OrganizationCurrency();
        $this->validator = new OrganizationCurrencyValidator(
            $this->entityManager,
            $this->configManager
        );
        $this->validator->initialize($this->context);
    }

    public function testValidateBy()
    {
        $this->assertEquals(
            $this->constraint->validatedBy(),
            'oro_multi_currency.validator.organization_currency'
        );
    }

    /**
     * @dataProvider validationDataProvider
     *
     * @param       $value
     * @param       $configs
     * @param       $queryResult
     * @param       $message
     * @param array $messageParameters
     */
    public function testValidate(
        $value,
        $configs,
        $queryResult,
        $message,
        array $messageParameters
    ) {

        $this->configManager->setConfigs($configs);

        if ($message) {
            $this->context
                ->expects($this->once())
                ->method('buildViolation')
                ->with($message, $messageParameters)
                ->will($this->returnValue($this->constraintViolation));
        } else {
            $this->context
                ->expects($this->never())
                ->method('buildViolation');
        }

        $this->setupQB($queryResult);

        $this->validator->validate($value, $this->constraint);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     *
     * @return array
     */
    public function validationDataProvider()
    {
        return [
            'Only system available' => [
                'value' => [],
                'configs' => [],
                'queryResult' => [],
                'message' => false,
                'messageParameters' => []
            ],
            'With main organization and enabled "use_parent_scope_value"' => [
                'value' => ['USD', 'EUR'],
                'configs' => [
                    1 => [
                        'value' => ['USD', 'EUR'],
                        'use_parent_scope_value' => true
                    ]
                ],
                'queryResult' => [
                    [
                        'name' => 'OroCRM', 'id' => 1
                    ]
                ],
                'message' => false,
                'messageParameters' => []
            ],
            'With main organization and disabled "use_default"' => [
                'value' => ['USD', 'EUR'],
                'configs' => [
                    1 => [
                        'value' => ['EUR'],
                        'use_parent_scope_value' => false
                    ]
                ],
                'queryResult' => [
                    [
                        'name' => 'OroCRM', 'id' => 1
                    ]
                ],
                'message' => false,
                'messageParameters' => []
            ],
            'With missed single currency on main organization' => [
                'value' => ['USD', 'EUR'],
                'configs' => [
                    1 => [
                        'value' => ['EUR', 'UAH'],
                        'use_parent_scope_value' => false
                    ]
                ],
                'queryResult' => [
                    [
                        'name' => 'OroCRM', 'id' => 1
                    ]
                ],
                'message' => 'oro.multi.currency.validator.organization_currency.message.missed_currency',
                'messageParameters' => [
                    '%currencies%' => 'UAH',
                    '%orgNames%'   => 'OroCRM'
                ]
            ],
            'With missed several currencies on main organization' => [
                'value' => ['USD', 'EUR'],
                'configs' => [
                    1 => [
                        'value' => ['EUR', 'UAH', 'GBP'],
                        'use_parent_scope_value' => false
                    ]
                ],
                'queryResult' => [
                    [
                        'name' => 'OroCRM', 'id' => 1
                    ]
                ],
                'message' => 'oro.multi.currency.validator.organization_currency.message.missed_currencies',
                'messageParameters' => [
                    '%currencies%' => 'UAH, GBP',
                    '%orgNames%'   => 'OroCRM'
                ]
            ],
            'With multiorganization' => [
                'value' => ['USD', 'EUR'],
                'configs' => [
                    1 => [
                        'value' => ['EUR'],
                        'use_parent_scope_value' => false
                    ],
                    3 => [
                        'value' => ['EUR'],
                        'use_parent_scope_value' => false
                    ]
                ],
                'queryResult' => [
                    [
                        'name' => 'OroCRM', 'id' => 1
                    ],
                    [
                        'name' => 'OroCRM2', 'id' => 3
                    ]
                ],
                'message' => false,
                'messageParameters' => []
            ],
            'With missed several currencies and multiorganization' => [
                'value' => ['USD', 'EUR'],
                'configs' => [
                    1 => [
                        'value' => ['EUR', 'UAH'],
                        'use_parent_scope_value' => false
                    ],
                    3 => [
                        'value' => ['EUR', 'GBP', 'UAH'],
                        'use_parent_scope_value' => false
                    ]
                ],
                'queryResult' => [
                    [
                        'name' => 'OroCRM', 'id' => 1
                    ],
                    [
                        'name' => 'OroCRM2', 'id' => 3
                    ]
                ],
                'message' => 'oro.multi.currency.validator.organization_currency.message.missed_currencies',
                'messageParameters' => [
                    '%currencies%' => 'UAH, GBP',
                    '%orgNames%'   => 'OroCRM, OroCRM2'
                ]
            ]
        ];
    }

    protected function setupQB($queryResult)
    {
        $query = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->setMethods(['getResult', 'from', 'select', 'orderBy', 'getQuery'])
            ->getMockForAbstractClass();

        $query
            ->expects($this->once())
            ->method('from')
            ->will($this->returnSelf());

        $query
            ->expects($this->once())
            ->method('select')
            ->will($this->returnSelf());

        $query
            ->expects($this->once())
            ->method('orderBy')
            ->will($this->returnSelf());

        $query
            ->expects($this->once())
            ->method('getQuery')
            ->will($this->returnSelf());

        $query
            ->expects($this->once())
            ->method('getResult')
            ->will($this->returnValue($queryResult));

        $this->entityManager
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->will($this->returnValue($query));
    }
}
