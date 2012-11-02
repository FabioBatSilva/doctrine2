<?php

namespace Doctrine\Tests\ORM\Persisters\Generator;

use Doctrine\ORM\Persisters\BasicEntityPersister;

/**
 * @group DDC-1889
 */
class PersisterGeneratorTest extends \Doctrine\Tests\OrmFunctionalTestCase
{
    protected function assertDeclaringClassNotEquals(\Reflector $expected, \Reflector$actual)
    {
        $this->assertNotEquals($expected->getDeclaringClass()->name, $actual->getDeclaringClass()->name);
    }

    protected function invokePersisterMethod(BasicEntityPersister $persister, $name)
    {
        return $this->getAccessiblePersisterMethod($persister, $name)->invoke($persister);
    }

    protected function getPersisterPropertyValue(BasicEntityPersister $persister, $name)
    {
        return $this->getAccessiblePersisterProperty($persister, $name)->getValue($persister);
    }

    protected function getAccessiblePersisterProperty(BasicEntityPersister $persister, $name)
    {
        $property = new \ReflectionProperty($persister, $name);

        $property->setAccessible(true);

        return $property;
    }

    protected function getAccessiblePersisterMethod(BasicEntityPersister $persister, $name)
    {
        $method = new \ReflectionMethod($persister, $name);

        $method->setAccessible(true);

        return $method;
    }

    protected function getDefaultPropertyValue(BasicEntityPersister $persister, $property)
    {
        $reflection = new \ReflectionClass($persister);
        $properties = $reflection->getDefaultProperties();

        return $properties[$property];
    }
}