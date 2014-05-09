<?php

namespace mako\tests\unit\database\query;

use \mako\database\query\Query;

use \Mockery as m;

/**
 * @group unit
 */

class MySQLBuilderTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * 
	 */

	public function tearDown()
	{
		m::close();
	}

	/**
	 * 
	 */

	protected function getConnection()
	{
		$connection = m::mock('\mako\database\Connection');

		$connection->shouldReceive('getCompiler')->andReturn('mysql');

		return $connection;
	}

	/**
	 * 
	 */

	protected function getBuilder($table = 'foobar')
	{
		return (new Query($this->getConnection()))->from($table);
	}

	/**
	 * 
	 */

	public function testBasicSelect()
	{
		$query = $this->getBuilder();

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM `foobar`', $query['sql']);
		$this->assertEquals(array(), $query['params']);
	}
}