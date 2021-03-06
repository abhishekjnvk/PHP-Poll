<?php
namespace CodeIgniter\Database\Postgre;

use CodeIgniter\Database\BasePreparedQuery;
use CodeIgniter\Database\PreparedQueryInterface;

/**
 * Prepared query for Postgre
 */
class PreparedQuery extends BasePreparedQuery implements PreparedQueryInterface
{

	/**
	 * Stores the name this query can be
	 * used under by postgres. Only used internally.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * The result resource from a successful
	 * pg_exec. Or false.
	 *
	 * @var
	 */
	protected $result;

	//--------------------------------------------------------------------

	/**
	 * Prepares the query against the database, and saves the connection
	 * info necessary to execute the query later.
	 *
	 * NOTE: This version is based on SQL code. Child classes should
	 * override this method.
	 *
	 * @param string $sql
	 * @param array  $options Passed to the connection's prepare statement.
	 *                        Unused in the MySQLi driver.
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function _prepare(string $sql, array $options = [])
	{
		$this->name = random_int(1, 10000000000000000);

		$sql = $this->parameterize($sql);

		// Update the query object since the parameters are slightly different
		// than what was put in.
		$this->query->setQuery($sql);

		if (! $this->statement = pg_prepare($this->db->connID, $this->name, $sql))
		{
			$this->errorCode   = 0;
			$this->errorString = pg_last_error($this->db->connID);
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Takes a new set of data and runs it against the currently
	 * prepared query. Upon success, will return a Results object.
	 *
	 * @param array $data
	 *
	 * @return boolean
	 */
	public function _execute(array $data): bool
	{
		if (is_null($this->statement))
		{
			throw new \BadMethodCallException('You must call prepare before trying to execute a prepared statement.');
		}

		$this->result = pg_execute($this->db->connID, $this->name, $data);

		return (bool) $this->result;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the result object for the prepared query.
	 *
	 * @return mixed
	 */
	public function _getResult()
	{
		return $this->result;
	}

	//--------------------------------------------------------------------

	/**
	 * Replaces the ? placeholders with $1, $2, etc parameters for use
	 * within the prepared query.
	 *
	 * @param string $sql
	 *
	 * @return string
	 */
	public function parameterize(string $sql): string
	{
		// Track our current value
		$count = 0;

		return preg_replace_callback('/\?/', function ($matches) use (&$count) {
			$count ++;
			return "\${$count}";
		}, $sql);
	}

	//--------------------------------------------------------------------
}
