<?php
declare(strict_types=1);
namespace ParagonIE\EasyDB;

use ParagonIE\HiddenString\HiddenString;
use Exception;
use PDO;
use PDOStatement;
use SodiumException;
use TypeError;
use function
    is_null,
    is_string,
    sodium_crypto_shorthash,
    sodium_crypto_shorthash_keygen;

/**
 * Class CacheDB
 */
class EasyDBCache extends EasyDB
{
    protected HiddenString $cacheKey;

    /** @var array<string, PDOStatement> $cache */
    protected array $cache = [];

    /**
     * Dependency-Injectable constructor
     *
     * @param PDO   $pdo
     * @param string $dbEngine
     * @param array  $options             Extra options
     * @param HiddenString|null $cacheKey Key for cache lookups
     *
     * @throws Exception
     */
    public function __construct(
        PDO $pdo,
        string $dbEngine = '',
        array $options = [],
        ?HiddenString $cacheKey = null
    ) {
        parent::__construct($pdo, $dbEngine, $options);
        if (is_null($cacheKey)) {
            $cacheKey = new HiddenString(
                sodium_crypto_shorthash_keygen()
            );
        }
        $this->cacheKey = $cacheKey;
    }

    /**
     * @param EasyDB $db
     * @param ?HiddenString $cacheKey
     * @return EasyDBCache
     * @throws Exception
     */
    public static function fromEasyDB(
        EasyDB $db,
        ?HiddenString $cacheKey = null
    ): EasyDBCache {
        return new EasyDBCache(
            $db->pdo,
            $db->dbEngine,
            $db->options,
            $cacheKey
        );
    }

    /**
     * Flushes the cache of prepared statements
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
     *
     * @throws SodiumException
     */
    public function isCached(string $statement): bool
    {
        $cacheKey = $this->getCacheIndex($statement);
        return !empty($this->cache[$cacheKey]);
    }

    /**
     * @param string $statement
     * @return string
     *
     * @throws SodiumException
     */
    protected function getCacheIndex(string $statement): string
    {
        return sodium_crypto_shorthash(
            $statement,
            $this->cacheKey->getString()
        );
    }

    /**
     * @param mixed ...$args
     * @return PDOStatement
     *
     * @throws Exception
     * @throws SodiumException
     */
    public function prepare(mixed ...$args): PDOStatement
    {
        if (count($args) < 1) {
            throw new Exception(__FUNCTION__ . ' expects 1 argument, 0 given.');
        }
        $statement = $args[0];
        if (!is_string($statement)) {
            throw new TypeError(__FUNCTION__ . ' argument 1 must be a string.');
        }
        $cacheKey = $this->getCacheIndex($statement);
        if (empty($this->cache[$cacheKey])) {
            $this->cache[$cacheKey] = parent::prepare(...$args);
        }
        return $this->cache[$cacheKey];
    }
}
