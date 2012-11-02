<?php

namespace Doctrine\Tests\ORM\Tools;

use Doctrine\ORM\Persisters\PersisterFactory;

/**
 * @group DDC-1889
 */
class PersisterFactoryTest extends \Doctrine\Tests\OrmFunctionalTestCase
{

    public static function getGenerateProvider()
    {
        return array(
            array('Doctrine\Tests\Models\CMS\CmsAddress', 'Doctrine\ORM\Persisters\BasicEntityPersister'),
            array('Doctrine\Tests\Models\CMS\CmsUser', 'Doctrine\ORM\Persisters\BasicEntityPersister'),
            array('Doctrine\Tests\Models\CMS\CmsArticle', 'Doctrine\ORM\Persisters\BasicEntityPersister'),
            array('Doctrine\Tests\Models\Quote\User', 'Doctrine\ORM\Persisters\BasicEntityPersister'),
        );
    }
    
    private $directory;
    
    private $namespace;

    /**
     * @var \Doctrine\ORM\Persisters\PersisterFactory
     */
    private $factory;

    public function setUp()
    {
        parent::setUp();

        $this->namespace  = 'PersisterFactoryTest'.uniqid();
        $this->directory  = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid("doctrine_");
        $this->factory    = new PersisterFactory($this->_em, $this->directory, $this->namespace, true);

        if ( ! is_dir($this->directory)) {
            mkdir($this->directory, 0777, true);
        }
    }

    protected function tearDown()
    {
        parent::tearDown();

        $iterator = new \RecursiveDirectoryIterator($this->directory);

        foreach (new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::CHILD_FIRST) as $file) {
            if ($file->isFile()) {
                @unlink($file->getRealPath());
            } else {
                @rmdir($file->getRealPath());
            }
        }

        @rmdir($this->directory);
    }

    /**
     * @dataProvider getGenerateProvider
     */
    public function testGetEntityPersister($entityClass, $parentPersisterClass)
    {
        $metadata   = $this->_em->getClassMetadata($entityClass);
        $class      = $this->factory->getEntityPersisterClassName($metadata);
        $generated  = $this->factory->getEntityPersister($metadata);

        $this->assertInstanceOf('Doctrine\ORM\Persisters\Generator\GeneratedPersister', $generated);

        $this->assertInstanceOf($parentPersisterClass, $generated);
        $this->assertInstanceOf($class, $generated);
        
        $this->assertNotEquals($class, $parentPersisterClass);
    }

    public function testGeneratePersisterClasses()
    {
        $classes    = array();
        $provider   = self::getGenerateProvider();

        foreach ($provider as $item) {
            $classes[] = $this->_em->getClassMetadata($item[0]);
        }

        $result = $this->factory->generatePersisterClasses($classes, $this->directory);

        $this->assertCount(count($classes), $result);

        foreach ($result as $filename => $persisterClass) {
            $this->assertFileExists($filename);

            include $filename;

            $reflectionClass = new \ReflectionClass($persisterClass);

            $this->assertTrue($reflectionClass->isSubclassOf('Doctrine\ORM\Persisters\Generator\GeneratedPersister'));
        }
    }
}