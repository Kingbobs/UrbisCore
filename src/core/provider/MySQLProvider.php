<?php

declare(strict_types = 1);

namespace core\provider;

use core\Urbis;
use mysqli;

class MySQLProvider {

	const DATABASE = "s259_Season1";

	/** @var Urbis */
	private $core;

	/** @var mysqli */
	private $database;
	/**
	 * MySQLProvider constructor.
	 *
	 * @param Urbis $core
	 */
	public function __construct(Urbis $core) {
		$this->core = $core;
		$this->database = new mysqli("139.99.40.254:3306", "u259_OsXN4xeJhs", "8QjMbCLsX1Zk=WS.vXD4RvrB", self::DATABASE);
		$this->init();
	}

	public function init(): void {
		$this->database->query("CREATE TABLE IF NOT EXISTS players(xuid VARCHAR(36) PRIMARY KEY, username VARCHAR(16), faction VARCHAR(16) DEFAULT NULL, factionRole TINYINT DEFAULT NULL, balance BIGINT DEFAULT 0, questPoints BIGINT DEFAULT 0, rankId TINYINT DEFAULT 0, permissions VARCHAR(600) DEFAULT '', tags VARCHAR(600) DEFAULT '', currentTag VARCHAR(150) DEFAULT '', kills BIGINT DEFAULT 0, mobcoins BIGINT DEFAULT 0, minedblocks BIGINT DEFAULT 0, shards SMALLINT DEFAULT 0, bounty BIGINT DEFAULT 0, class VARCHAR(50) DEFAULT NULL);");
		$this->database->query("CREATE TABLE IF NOT EXISTS extraData(xuid VARCHAR(36) PRIMARY KEY, username VARCHAR(16), permissions VARCHAR(600) DEFAULT '', rewardCooldown BIGINT DEFAULT 0)");
		$this->database->query("ALTER TABLE extraData ADD COLUMN rewardCooldown BIGINT DEFAULT 0");
		$this->database->query("CREATE TABLE IF NOT EXISTS ipAddress(xuid VARCHAR(36), username VARCHAR(16), ipAddress VARCHAR(20), riskLevel TINYINT);");
		$this->database->query("CREATE TABLE IF NOT EXISTS factions(name VARCHAR(30) NOT NULL, x SMALLINT DEFAULT NULL, y SMALLINT DEFAULT NULL, z SMALLINT DEFAULT NULL, members TEXT NOT NULL, allies TEXT DEFAULT NULL, balance BIGINT DEFAULT 0 NOT NULL, strength BIGINT DEFAULT 100 NOT NULL);");
		$this->database->query("CREATE TABLE IF NOT EXISTS claims(faction VARCHAR(30) NOT NULL, chunkX SMALLINT DEFAULT NULL, chunkZ SMALLINT DEFAULT NULL);");
		$this->database->query("CREATE TABLE IF NOT EXISTS rewards(xuid VARCHAR(36) PRIMARY KEY, username VARCHAR(16), items BLOB DEFAULT NULL);");
		$this->database->query("CREATE TABLE IF NOT EXISTS inboxes(xuid VARCHAR(36) PRIMARY KEY, username VARCHAR(16), items BLOB DEFAULT NULL);");
		$this->database->query("CREATE TABLE IF NOT EXISTS crates(xuid VARCHAR(36) PRIMARY KEY, username VARCHAR(16), common SMALLINT DEFAULT 0 NOT NULL, legendary SMALLINT DEFAULT 0 NOT NULL, mythic SMALLINT DEFAULT 0 NOT NULL, epic SMALLINT DEFAULT 0 NOT NULL, rare SMALLINT DEFAULT 0 NOT NULL, vote SMALLINT DEFAULT 0 NOT NULL);");
		$this->database->query("CREATE TABLE IF NOT EXISTS kitCooldowns(xuid VARCHAR(36) PRIMARY KEY, username VARCHAR(16));");
		$this->database->query("CREATE TABLE IF NOT EXISTS homes(xuid VARCHAR(36) NOT NULL, username VARCHAR(16), name VARCHAR(16) NOT NULL, x SMALLINT NOT NULL, y SMALLINT NOT NULL, z SMALLINT NOT NULL, level VARCHAR(30) NOT NULL);");
	}

	/**
	 * @return string
	 */
	public function getMainDatabaseName(): string {
		return self::DATABASE;
	}

	/**
	 * @return mysqli
	 */
	public function getDatabase(): mysqli {
		return $this->database;
	}
}
