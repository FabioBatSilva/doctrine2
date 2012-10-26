<?php

namespace Doctrine\Tests\ORM\Tools;

use Doctrine\ORM\Persisters\EntityPersisterGenerator;

require_once __DIR__ . '/../../../TestInit.php';

/**
 * @group DDC-1889
 */
class EntityPersisterGeneratorTest extends \Doctrine\Tests\OrmFunctionalTestCase
{

    private $tmpDir;
    
    private $namespace;

    /**
     * @var \Doctrine\ORM\Tools\EntityPersisterGenerator
     */
    private $generator;

    public function setUp()
    {
        parent::setUp();

        $this->namespace    = 'Doctrine_CG_Test_Persisters';
        $this->tmpDir       = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid("doctrine_");
        $this->generator    = new EntityPersisterGenerator($this->_em, $this->namespace);

        if ( ! is_dir($this->tmpDir)) {
            mkdir($this->tmpDir, 0777, true);
        }
    }

    protected function tearDown()
    {
        parent::tearDown();

        $iterator = new \RecursiveDirectoryIterator($this->tmpDir);

        foreach (new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::CHILD_FIRST) as $file) {
            if ($file->isFile()) {
                @unlink($file->getRealPath());
            } else {
                @rmdir($file->getRealPath());
            }
        }
    }

    public function testGenerateEntityPersisterClass()
    {
        $metadata   = $this->_em->getClassMetadata('Doctrine\Tests\Models\CMS\CmsAddress');
        $code       = $this->generator->generateEntityPersisterClass($metadata);
        $filename   = $this->tmpDir . DIRECTORY_SEPARATOR . uniqid() . '.php';

        file_put_contents($filename , $code);

        include $filename;

        $class      = $this->namespace . '\CmsAddressPersister';
        $persister  = new $class($this->_em, $metadata);
        $reflection = new \ReflectionClass($persister);

        $this->assertInstanceOf('\Doctrine\ORM\Persisters\BasicEntityPersister', $persister);
        $this->assertEquals($reflection->name, $reflection->getMethod('getInsertSQL')->getDeclaringClass()->name);
    }

    public function testWriteEntityPersisterClass()
    {
        $metadata   = $this->_em->getClassMetadata('Doctrine\Tests\Models\CMS\CmsUser');
        $filename   = $this->generator->writeEntityPersisterClass($metadata, $this->tmpDir);

        $this->assertFileExists($filename);

        $class      = $this->namespace . '\CmsAddressPersister';
        $persister  = new $class($this->_em, $metadata);
        $reflection = new \ReflectionClass($persister);

        $this->assertInstanceOf('\Doctrine\ORM\Persisters\BasicEntityPersister', $persister);
        $this->assertEquals($reflection->name, $reflection->getMethod('getInsertSQL')->getDeclaringClass()->name);
    }
}