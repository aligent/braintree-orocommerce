<?php

namespace Oro\Bundle\SecurityProBundle\Tests\Unit\Entity;

use Oro\Bundle\SecurityProBundle\Entity\AclClass;

class AclClassTest extends \PHPUnit_Framework_TestCase
{
    const ENTITY_ID = 2;
    const CLASS_TYPE = 'Oro\Bundle\OrganizationProBundle\Entity\UserOrganization';

    /**
     * @var AclClass
     */
    protected $entity;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->entity = new AclClass();
    }

    public function testGettersSetters()
    {
        $class = new \ReflectionClass($this->entity);
        $prop = $class->getProperty('id');
        $prop->setAccessible(true);
        $prop->setValue($this->entity, self::ENTITY_ID);
        $this->assertEquals(self::ENTITY_ID, $this->entity->getId());

        $this->entity->setClassType(self::CLASS_TYPE);
        $this->assertEquals(self::CLASS_TYPE, $this->entity->getClassType());
    }
}
