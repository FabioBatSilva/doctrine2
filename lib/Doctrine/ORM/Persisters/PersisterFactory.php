<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\ORM\Persisters;

use Doctrine\ORM\Proxy\Proxy;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Persisters\PersisterGenerationException;
use Doctrine\ORM\Persisters\Generator\BasicEntityPersisterGenerator;

/**
 * @author  Fabio B. Silva <fabio.bat.silva@gmail.com>
 * @since   2.4
 */
class PersisterFactory
{
    /**
     * The EntityManager.
     *
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var boolean Whether to automatically (re)generate proxy classes.
     */
    private $auto;

    /**
     * @var string The directory that contains all persisters classes.
     */
    private $directory;

    /**
     * @var string The namespace that contains all proxy classes.
     */
    private $namespace;

    /**
     * @var array The entity persister instances used to persist entity instances.
     */
    private $persisters = array();

    /**
     * @var string The generated class name suffix.
     */
    private $classSuffix = 'Persister';

    /**
     * Initializes a new EntityPersisterGenerator.
     *
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct(EntityManager $em, $directory, $namespace, $auto = false)
    {
        $this->em        = $em;
        $this->auto      = $auto;
        $this->directory = $directory;
        $this->namespace = rtrim($namespace, '\\');
    }

    /**
     * @return string
     */
    public function getClassSuffix()
    {
        return $this->classSuffix;
    }

    /**
     * @param string $classSuffix
     */
    public function setClassSuffix($classSuffix)
    {
        $this->classSuffix = $classSuffix;
    }

    /**
     * @return string
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Gets the Generator for an entity.
     *
     * @param \Doctrine\ORM\Mapping\ClassMetadata $class  The entity class metadata.
     *
     * @return \Doctrine\ORM\Persisters\Generator\PersisterGenerator
     */
    private function getPersisterGenerator(ClassMetadata $class)
    {
        if ($class->isInheritanceTypeNone()) {
            return new BasicEntityPersisterGenerator($this->em, $class);
        }

        return null;
    }

    /**
     * Generate the persister file name
     *
     * @param string $className
     * @param string $directory Optional base directory for persister file name generation.
     *
     * @return string
     */
    private function getEntityPersisterFileName($className, $directory = null)
    {
        $directory = $directory ?: $this->directory;
        $path      = str_replace('\\', DIRECTORY_SEPARATOR, $className) . $this->classSuffix;

        return $directory . DIRECTORY_SEPARATOR . Proxy::MARKER . DIRECTORY_SEPARATOR . $path . '.php';
    }

    /**
     * @param \Doctrine\ORM\Mapping\ClassMetadata $class
     *
     * @return string
     */
    public function getEntityPersisterClassName(ClassMetadata $class)
    {
        return ClassUtils::generateProxyClassName($class->name, $this->namespace) . $this->classSuffix;
    }

    /**
     *
     * @param \Doctrine\ORM\Mapping\ClassMetadata $class
     * @param string $persisterClass
     * @param string $filename
     *
     * @throws \Doctrine\ORM\Persisters\PersisterGenerationException
     */
    private function generateEntityPersisterClass(ClassMetadata $class, $persisterClass, $filename)
    {
        $generator  = $this->getPersisterGenerator($class);
        $namespace  = substr($persisterClass, 0, strripos($persisterClass, '\\'));
        $shortName  = substr($persisterClass, strrpos($persisterClass, '\\') + 1);
        $code       = $generator->generate(trim($namespace, '\\'), $shortName);
        $dirname    = pathinfo($filename, PATHINFO_DIRNAME);

        if (( ! is_dir($dirname) && ! @mkdir($dirname, 0775, true)) || ! is_writable($dirname)) {
            throw PersisterGenerationException::persisterDirectoryNotWritable();
        }

        file_put_contents($filename, $code);
    }

    /**
     * Generates proxy classes for all given classes.
     *
     * @param array $classes The classes (ClassMetadata instances) for which to generate persisters.
     * @param string $directory The target directory of the persister classes.
     * 
     * @return array of generated persister classes.
     */
    public function generatePersisterClasses(array $classes, $directory = null)
    {
        $list = array();

        foreach ($classes as $class) {
            $persisterClass = $this->getEntityPersisterClassName($class);
            $filename       = $this->getEntityPersisterFileName($class->name, $directory);

            $this->generateEntityPersisterClass($class, $persisterClass, $filename);

            $list[$filename] = $persisterClass;
        }

        return $list;
    }


    /**
     * Gets the EntityPersister for an Entity.
     *
     * @param \Doctrine\ORM\Mapping\ClassMetadata $class The Entity class metadata.
     *
     * @return \Doctrine\ORM\Persisters\BasicEntityPersister
     */
    public function getEntityPersister(ClassMetadata $class)
    {
        if (isset($this->persisters[$class->name])) {
            return $this->persisters[$class->name];
        }

        $persisterClass = $this->getEntityPersisterClassName($class);

        if ( ! class_exists($persisterClass, false)) {

            $filename = $this->getEntityPersisterFileName($class->name);
            
            if ($this->auto) {
                $this->generateEntityPersisterClass($class, $persisterClass, $filename);
            }

            require $filename;
        }

        $this->persisters[$class->name] = new $persisterClass($this->em, $class);

        return $this->persisters[$class->name];
    }
}