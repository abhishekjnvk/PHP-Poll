<?php
namespace CodeIgniter\Database;

/**
 * Database Connection Factory
 *
 * Creates and returns an instance of the appropriate DatabaseConnection
 *
 * @package CodeIgniter\Database
 */
class Database
{

	/**
	 * Maintains an array of the instances of all connections
	 * that have been created. Helps to keep track of all open
	 * connections for performance monitoring, logging, etc.
	 *
	 * @var array
	 */
	protected $connections = [];

	//--------------------------------------------------------------------

	/**
	 * Parses the connection binds and returns an instance of
	 * the driver ready to go.
	 *
	 * @param array  $params
	 * @param string $alias
	 *
	 * @return   mixed
	 * @internal param bool $useBuilder
	 */
	public function load(array $params = [], string $alias)
	{
		// No DB specified? Beat them senseless...
		if (empty($params['DBDriver']))
		{
			throw new \InvalidArgumentException('You have not selected a database type to connect to.');
		}

		$className = strpos($params['DBDriver'], '\\') === false
			? '\CodeIgniter\Database\\' . $params['DBDriver'] . '\\Connection'
			: $params['DBDriver'] . '\\Connection';

		$class = new $className($params);

		// Store the connection
		$this->connections[$alias] = $class;

		return $this->connections[$alias];
	}

	//--------------------------------------------------------------------

	/**
	 * Creates a new Forge instance for the current database type.
	 *
	 * @param ConnectionInterface|BaseConnection $db
	 *
	 * @return mixed
	 */
	public function loadForge(ConnectionInterface $db)
	{
		$className = strpos($db->DBDriver, '\\') === false ? '\CodeIgniter\Database\\' . $db->DBDriver . '\\Forge' : $db->DBDriver . '\\Forge';

		// Make sure a connection exists
		if (! $db->connID)
		{
			$db->initialize();
		}

		return new $className($db);
	}

	//--------------------------------------------------------------------

	/**
	 * Loads the Database Utilities class.
	 *
	 * @param ConnectionInterface|BaseConnection $db
	 *
	 * @return mixed
	 */
	public function loadUtils(ConnectionInterface $db)
	{
		$className = strpos($db->DBDriver, '\\') === false ? '\CodeIgniter\Database\\' . $db->DBDriver . '\\Utils' : $db->DBDriver . '\\Utils';

		// Make sure a connection exists
		if (! $db->connID)
		{
			$db->initialize();
		}

		return new $className($db);
	}

	//--------------------------------------------------------------------
}
