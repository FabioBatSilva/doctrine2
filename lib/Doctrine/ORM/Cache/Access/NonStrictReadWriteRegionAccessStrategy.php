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

namespace Doctrine\ORM\Cache\Access;

use Doctrine\ORM\Cache\RegionAccess;
use Doctrine\ORM\Cache\CacheKey;
use Doctrine\ORM\Cache\Region;
use Doctrine\ORM\Cache\Lock;

/**
 * Specific non-strict read/write region access strategy
 *
 * @since   2.5
 * @author  Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
class NonStrictReadWriteRegionAccessStrategy implements RegionAccess
{
    /**
     * @var \Doctrine\ORM\Cache\Region
     */
    private $region;

    /**
     * @param \Doctrine\ORM\Cache\Region $region
     */
    public function __construct(Region $region)
    {
        $this->region = $region;
    }

    /**
     * {@inheritdoc}
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * {@inheritdoc}
     */
    public function afterInsert(CacheKey $key, array $value)
    {
        return $this->region->put($key, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function afterUpdate(CacheKey $key, array $value, Lock $lock = null)
    {
        return $this->region->put($key, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function get(CacheKey $key)
    {
        return $this->region->get($key);
    }

    /**
     * {@inheritdoc}
     */
    public function put(CacheKey $key, array $value)
    {
        return $this->region->put($key, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function evict(CacheKey $key)
    {
        return $this->region->evict($key);
    }

    /**
     * {@inheritdoc}
     */
    public function evictAll()
    {
        return $this->region->evictAll();
    }
}
