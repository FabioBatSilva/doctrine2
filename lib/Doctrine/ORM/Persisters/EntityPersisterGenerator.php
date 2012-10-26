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

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Persisters\Generator\BasicEntityPersisterGenerator;

/**
 * @author  Fabio B. Silva <fabio.bat.silva@gmail.com>
 * @since   2.4
 */
class EntityPersisterGenerator
{
    /**
     * The EntityManager.
     *
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * The namespace that contains all proxy classes. 
     */
    private $namespace;

    /**
     * Initializes a new EntityPersisterGenerator.
     *
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct(EntityManager $em, $namespace)
    {
        $this->em        = $em;
        $this->namespace = rtrim($namespace, '\\');
    }
 
    /**
     * Gets the Generator for an entity.
     *
     * @param \Doctrine\ORM\Mapping\ClassMetadata $class  The entity class metadata.
     *
     * @return \Doctrine\ORM\Tools\EntityPersisterGenerator\Generator
     */
    private function getPersisterGenerator(ClassMetadata $class)
    {
        if ($class->isInheritanceTypeNone()) {
            return new BasicEntityPersisterGenerator($this->em, $class, $this->namespace);
        }

        return null;
    }

    /**
     * @param \Doctrine\ORM\Mapping\ClassMetadata $class
     *
     * @return string
     */
    public function generateEntityPersisterClass(ClassMetadata $class)
    {
        return $this->getPersisterGenerator($class)->generate();
    }

    /**
     * @param \Doctrine\ORM\Mapping\ClassMetadata $class
     * @param string $outputDirectory
     *
     * @return string
     */
    public function writeEntityPersisterClass(ClassMetadata $class, $outputDirectory)
    {

        $entityName = $class->name;
        $code       = $this->generateEntityPersisterClass($class);
        $filename   = str_replace('\\', DIRECTORY_SEPARATOR, $entityName) . '.php';
        $path       = $outputDirectory . DIRECTORY_SEPARATOR . $filename;
        $dirname    = dirname($path);

        if ( ! is_dir($dirname)) {
            mkdir($dirname, 0777, true);
        }

        file_put_contents($path, $code);

        return $path;
    }
}
