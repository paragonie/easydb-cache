<?php
declare(strict_types=1);
namespace ParagonIE\EasyDB;

use ParagonIE\HiddenString\HiddenString;

/**
 * Class CacheDB
 */
class EasyDBCache extends EasyDB
{
    /** @var HiddenString $cacheKey */
    protected $cacheKey;

    /** @var array<string, \PDOStatement> $cache */
    protected $cache = [];

    /**
     * Dependency-Injectable constructor
     *
     * @param \PDO   $pdo
     * @param string $dbEngine
     * @param array  $options             Extra options
     * @param HiddenString|null $cacheKey Key for cache lookups
     */
    public function __construct(
        \PDO $pdo,
        string $dbEngine = '',
        array $options = [],
        HiddenString $cacheKey = null
    ) {
        parent::__construct($pdo, $dbEngine, $options);
        if (\is_null($cacheKey)) {
            $cacheKey = new HiddenString(
                \sodium_crypto_shorthash_keygen()
            );
        }
        $this->cacheKey = $cacheKey;
    }

    /**
     * Flushes the cache of prepared statentes
     *
     * @return void
     */
    public function clearStatementCache()
    {
        $this->cache = [];
    }

    /**
     * @param string $statement
     * @return bool
     * @throws \SodiumException
     */
    public function isCached(string $statement): bool
    {
        $cacheKey = $this->getCacheIndex($statement);
        return !empty($this->cache[$cacheKey]);
    }

        /**
     * @param string $statement
     * @return string
     * @throws \SodiumException
     */
    protected function getCacheIndex(string $statement): string
    {
        return sodium_crypto_shorthash(
            $statement,
            $this->cacheKey->getString()
        );
    }

    /**
     * @param string ...$args
     * @return \PDOStatement
     * @throws \SodiumException
     */
    public function prepare(...$args): \PDOStatement
    {
        if (count($args) < 1) {
            throw new \Error(__FUNCTION__ . ' expects 1 argument, 0 given.');
        }
        /** @var string $statement */
        $statement = $args[0];
        $cacheKey = $this->getCacheIndex($statement);
        if (empty($this->cache[$cacheKey])) {
            $this->cache[$cacheKey] = parent::prepare(...$args);
        }
        return $this->cache[$cacheKey];
    }
}
