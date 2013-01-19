<?php

namespace Doctrine\Tests\ORM\Persisters\Generator;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Persisters\JoinedSubclassPersister;
use Doctrine\ORM\Persisters\Generator\JoinedSubclassPersisterGenerator;

/**
 * @group DDC-1889
 */
class JoinedSubclassPersisterGeneratorTest extends PersisterGeneratorTest
{
    public static function getGenerateProvider()
    {
        return array(
            array('Doctrine\Tests\Models\Company\CompanyEmployee'),
            array('Doctrine\Tests\Models\Company\CompanyManager'),
            array('Doctrine\Tests\Models\Company\CompanyPerson'),
            array('Doctrine\Tests\Models\Company\CompanyEvent'),
        );
    }

    private function getEntityPersister($metadata)
    {
        $className = ClassUtils::generateProxyClassName($metadata->name, 'JoinedSubclassPersisterGeneratorTest') . 'Persister';

        if (class_exists($className)) {
            return new $className($this->_em, $metadata);
        }

        $shortName  = substr($className, strrpos($className, '\\') + 1);
        $namespace  = substr($className, 0, strripos($className, '\\'));

        $generator  = new JoinedSubclassPersisterGenerator($this->_em, $metadata);
        $code       = $generator->generate($namespace, $shortName);
        $filename   = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid() . '.php';

        file_put_contents($filename , $code);

        include $filename;

        unlink($filename);

        $persister = new $className($this->_em, $metadata);

        $this->assertInstanceOf('Doctrine\ORM\Persisters\Generator\GeneratedPersister', $persister);
        $this->assertInstanceOf('Doctrine\ORM\Persisters\JoinedSubclassPersister', $persister);

        return $persister;
    }

    /**
     * @dataProvider getGenerateProvider
     */
    public function testGenerateGetSelectColumnsSQL($class)
    {
        $metadata   = $this->_em->getClassMetadata($class);
        $generated  = $this->getEntityPersister($metadata);
        $parent     = new JoinedSubclassPersister($this->_em, $metadata);

        $this->assertEquals(
            $this->invokePersisterMethod($parent, 'getSelectColumnsSQL'),
            $this->invokePersisterMethod($generated, 'getSelectColumnsSQL')
        );

        $this->assertDeclaringClassNotEquals(
            $this->getAccessiblePersisterProperty($parent, 'selectColumnListSql'),
            $this->getAccessiblePersisterProperty($generated, 'selectColumnListSql')
        );

        $this->assertEquals(
            $this->getDefaultPropertyValue($generated, 'selectColumnListSql'),
            $this->invokePersisterMethod($parent, 'getSelectColumnsSQL')
        );

        $this->assertEquals(
            $this->getDefaultPropertyValue($generated, 'selectJoinSql'),
            $this->getPersisterPropertyValue($parent, 'selectJoinSql')
        );
    }

    /**
     * @dataProvider getGenerateProvider
     */
    public function testGenerateGetInsertSQL($class)
    {
        $metadata   = $this->_em->getClassMetadata($class);
        $generated  = $this->getEntityPersister($metadata);
        $parent     = new JoinedSubclassPersister($this->_em, $metadata);
        
        $this->assertEquals(
            $this->invokePersisterMethod($parent, 'getInsertSQL'),
            $this->invokePersisterMethod($generated, 'getInsertSQL')
        );

        $this->assertDeclaringClassNotEquals(
            $this->getAccessiblePersisterMethod($parent, 'getInsertSQL'),
            $this->getAccessiblePersisterMethod($generated, 'getInsertSQL')
        );
    }
}