<?php

namespace Doctrine\Tests\ORM\Tools;

use Doctrine\ORM\Persisters\PersisterFactory;

require_once __DIR__ . '/../../../TestInit.php';

/**
 * @group DDC-1889
 */
class EntityPersisterGeneratorTest extends \Doctrine\Tests\OrmFunctionalTestCase
{

    private $directory;
    
    private $namespace;

    /**
     * @var \Doctrine\ORM\Persisters\PersisterFactory
     */
    private $factory;

    public function setUp()
    {
        parent::setUp();

        $this->namespace  = '';
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

    public function testGetEntityPersister()
    {
        $metadata   = $this->_em->getClassMetadata('Doctrine\Tests\Models\CMS\CmsUser');
        $class      = $this->factory->getEntityPersisterClassName($metadata);
        $persister  = $this->factory->getEntityPersister($metadata);
        $reflection = new \ReflectionClass($persister);

        $this->assertInstanceOf('\Doctrine\ORM\Persisters\BasicEntityPersister', $persister);
        $this->assertInstanceOf($class, $persister);
        $this->assertEquals($reflection->name, $reflection->getMethod('getInsertSQL')->getDeclaringClass()->name);
    }
}