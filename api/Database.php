<?php
/**
 * Provides a connector to the database using PDO
 * setup in config or provided as an options array
 * SINGLETON (per database)
 * @author Ivan
 *
 */
class Database {

    private static $connections = array();

    protected function __clone() {}
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }

    public static function getInstance($db = DB_DATABASE)
    {
        if (!isset(self::$connections[$db])) {
            self::$connections[$db] = new static($db);
        }
        return self::$connections[$db]->con;
    }

	/**
	 * Constructor for the Database Handler Class
	 *
	 * @param string $db :	Name of database on server
	 */
	protected function __construct($db = DB_DATABASE) {
		// Default connection from config.php
		$dsn = 'mysql:host=' . DB_HOSTNAME . ';dbname=' . $db;
		$this->con = new PDO ( $dsn, DB_USERNAME, DB_PASSWORD );
	}
	/**
	 * Closes the DB connection when class is destroyed
	 */
	public function __destruct() {
		if (isset($this->connections)) {
			unset($this->connections);
		}
	}
}