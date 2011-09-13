<?php

namespace mako
{
	use \Mako;
	use \PDO;
	use \PDOException;
	use \mako\database\Exception as DatabaseException;
	
	/**
	* Class that handles database connections.
	*
	* @author     Frederic G. Østby
	* @copyright  (c) 2008-2011 Frederic G. Østby
	* @license    http://www.makoframework.com/license
	*/

	class Database
	{
		//---------------------------------------------
		// Class variables
		//---------------------------------------------

		/**
		* Holds instance of itself.
		*/

		protected static $instance = null;

		/**
		* Holds the configuration.
		*/

		protected static $config;

		/**
		* Holds all the database objects.
		*/

		protected static $databases = array();

		//---------------------------------------------
		// Class constructor, destructor etc ...
		//---------------------------------------------

		/**
		* Protected constructor that prevents direct creation of object.
		*
		* @access  protected
		*/

		protected function __construct()
		{
			// Nothing here
		}

		/**
		* Closes all database connections.
		*
		* @access  public
		*/

		public function __destruct()
		{
			foreach(static::$databases as $k => $v)
			{
				static::close($k);
			}
		}

		//---------------------------------------------
		// Class methods
		//---------------------------------------------
		
		/**
		* Opens a new connection or returns existing connection if it already exists.
		*
		* @access  public
		* @param   string  (optional) Database configuration name
		* @return  PDO
		*/

		public static function instance($name = null)
		{
			if(static::$instance === null)
			{
				static::$instance = new static();
				
				static::$config = Mako::config('database');
			}
			
			if(isset(static::$databases[$name]))
			{
				return static::$databases[$name];
			}
			else
			{
				$name = ($name === null) ? static::$config['default'] : $name;
				
				if(isset(static::$config['configurations'][$name]) === false)
				{
					throw new DatabaseException(__CLASS__ . ": '{$name}' has not been defined in the database configuration.");
				}
				
				static::connect($name);
				
				return static::$databases[$name];
			}
		}

		/**
		* Connect to database using PDO.
		*
		* @access  protected
		* @param   string     Database name as defined in the database config file
		*/

		protected static function connect($name)
		{
			$config = static::$config['configurations'][$name];

			// Connect to the database

			$user = isset($config['username']) ? $config['username'] : null;
			$pass = isset($config['password']) ? $config['password'] : null;

			$options = array
			(
				PDO::ATTR_PERSISTENT         => isset($config['persistent']) ? $config['persistent'] : false,
				PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
			);

			try
			{
				static::$databases[$name] = new PDO($config['dsn'], $user, $pass, $options);
			}
			catch(PDOException $e)
			{
				throw new DatabaseException(__CLASS__ . ": Failed to connect to the {$name} database.<br /><br />" . $e->getMessage());
			}

			// Run queries

			if(isset($config['queries']))
			{
				foreach($config['queries'] as $query)
				{
					static::$databases[$name]->exec($query);
				}
			}

			// Set table prefix and database name

			static::$databases[$name]->prefix = isset($config['table_prefix']) ? $config['table_prefix'] : '';
			static::$databases[$name]->name   = $name;
		}

		/**
		* Closes the connection to a database and destroys the object.
		*
		* @access  public
		* @param   mixed    (optional) Database name as defined in the database config file or database object.
		* @return  boolean
		*/

		public static function close($name = null)
		{
			$name = ($name === null) ? static::$config['default'] : $name;
			
			if($name instanceof PDO)
			{
				$name = $name->name;	
			}
			
			if(isset(static::$databases[$name]))
			{
				static::$databases[$name] = null;

				unset(static::$databases[$name]);

				return true;
			}
			else
			{
				return false;
			}
		}
	}
}

/** -------------------- End of file --------------------**/