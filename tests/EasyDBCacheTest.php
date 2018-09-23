<?php
namespace ParagonIE\EasyDB\Tests;

use ParagonIE\EasyDB\EasyDB;
use ParagonIE\EasyDB\EasyDBCache;
use PHPUnit\Framework\TestCase;

/**
 * Class EasyDBCacheTest
 * @package ParagonIE\EasyDB\Tests
 */
class EasyDBCacheTest extends TestCase
{
    /** @var EasyDBCache $db */
    private $db;

    /** @var string $fuzz */
    private $fuzz;

    /** @var EasyDBCache $db */
    private $db2;

    public function setUp()
    {
        if (!\extension_loaded('sqlite3')) {
            $this->markTestSkipped('SQLite driver not installed.');
        }
        $pdo = new \PDO('sqlite::memory:');
        $this->db = new EasyDBCache($pdo);
        $this->db->query("CREATE TABLE foo (bar TEXT, baz TEXT);");
        $this->fuzz = bin2hex(random_bytes(16));
        $this->db->insert('foo', ['bar' => 'easydb', 'baz' => $this->fuzz]);
        $this->db->insert('foo', ['bar' => 'ezdb', 'baz' => $this->fuzz]);

        $this->db2 = new EasyDB($pdo);
    }

    /**
     * @throws \SodiumException
     */
    public function testPrepareReuse()
    {
        $query = "SELECT * FROM foo WHERE bar = ?";
        $query2 = "SELECT * FROM foo WHERE baz = ?";

        // Preliminary:
        $this->assertFalse(
            $this->db->isCached($query),
            'Prepared statement was already cached.'
        );

        $resultA = $this->db->run($query, 'easydb');
        $this->assertCount(1, $resultA);
        $this->assertTrue(
            $this->db->isCached($query),
            'Prepared statement cache miss.'
        );

        $resultB = $this->db->run($query, 'easydb');
        $this->assertCount(1, $resultA);
        $this->assertEquals(
            $resultA,
            $resultB,
            'Different results from same query?'
        );

        $this->assertFalse(
            $this->db->isCached($query2),
            'Prepared statement #2 was already cached.'
        );

        $results = $this->db->run($query2, $this->fuzz);
        $this->assertTrue(
            $this->db->isCached($query2),
            'Prepared statement #2 cache miss.'
        );
        $this->assertCount(2, $results);
    }

    /**
     * @throws \SodiumException
     */
    public function testSpeed()
    {
        if (!extension_loaded('sodium')) {
            $this->markTestSkipped('Do not run this test without ext/sodium');
        }

        // Initialize variables:
        $stop = $uncacheTime = $cacheTime = 0;

        $start = microtime(true);
        for ($i = 0; $i < 100000; ++$i) {
            $this->db->prepare("SELECT * FROM foo WHERE bar = ? OR baz = ?");
        }
        $stop = microtime(true);
        $cacheTime = $stop - $start;

        $start = microtime(true);
        for ($i = 0; $i < 100000; ++$i) {
            $this->db2->prepare("SELECT * FROM foo WHERE bar = ? OR baz = ?");
        }
        $stop = microtime(true);
        $uncacheTime = $stop - $start;

        $this->assertLessThan($uncacheTime, $cacheTime);
    }
}
