<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\redis;

use Throwable;

/**
 * Redis connection.
 *
 * @author Frederic G. Østby
 */
class Connection
{
	/**
	 * Socket connection.
	 *
	 * @var resource
	 */
	protected $connection;

	/**
	 * Is the socket persistent?
	 *
	 * @var bool
	 */
	protected $isPersistent = false;

	/**
	 * Last command.
	 *
	 * @var string
	 */
	protected $lastCommand;

	/**
	 * Constructor.
	 *
	 * @param string $host       Redis host
	 * @param int    $port       Redis port
	 * @param bool   $persistent Should the connection be persistent?
	 */
	public function __construct(string $host, int $port = 6379, bool $persistent = false)
	{
		if(filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false)
		{
			$host = '[' . $host . ']';
		}

		try
		{
			if($persistent)
			{
				$this->isPersistent = true;

				$this->connection = pfsockopen('tcp://' . $host, $port, $errNo);
			}
			else
			{
				$this->connection = fsockopen('tcp://' . $host, $port, $errNo);
			}
		}
		catch(Throwable $e)
		{
			throw new RedisException(vsprintf('%s', [$e->getMessage()]), (int) $errNo);
		}
	}

	/**
	 * Destructor.
	 */
	public function __desctruct()
	{
		if(!$this->isPersistent && is_resource($this->connection))
		{
			fclose($this->connection);
		}
	}

	/**
	 * Creates a new connection.
	 *
	 * @param  string                 $host       Redis host
	 * @param  int                    $port       Redis port
	 * @param  bool                   $persistent Should the connection be persistent?
	 * @return \mako\redis\Connection
	 */
	public static function create(string $host, int $port, bool $persistent = false): Connection
	{
		return new static($host, $port, $persistent);
	}

	/**
	 * Appends the read error reason to the error message if possible.
	 *
	 * @param  string $message Error message
	 * @return string
	 */
	protected function appendReadErrorReason($message): string
	{
		if(stream_get_meta_data($this->connection)['timed_out'])
		{
			return $message . ' The stream timed out while waiting for data.';
		}

		return $message;
	}

	/**
	 * Gets line from the server.
	 *
	 * @return string
	 */
	public function readLine(): string
	{
		$line = fgets($this->connection);

		if($line === false || $line === '')
		{
			throw new RedisException($this->appendReadErrorReason('Failed to read line from the server.'));
		}

		return $line;
	}

	/**
	 * Reads n bytes from the server.
	 *
	 * @param  int    $bytes Number of bytes to read
	 * @return string
	 */
	public function read(int $bytes): string
	{
		$bytesLeft = $bytes;

		$data = '';

		do
		{
			$chunk = fread($this->connection, min($bytesLeft, 4096));

			if($chunk === false || $chunk === '')
			{
				throw new RedisException($this->appendReadErrorReason('Failed to read data from the server.'));
			}

			$data .= $chunk;

			$bytesLeft = $bytes - strlen($data);
		}
		while($bytesLeft > 0);

		return $data;
	}

	/**
	 * Writes data to the server.
	 *
	 * @param  string $data Data to write
	 * @return int
	 */
	public function write(string $data): int
	{
		$totalBytesWritten = 0;

		$bytesLeft = strlen($data);

		do
		{
			$totalBytesWritten += $bytesWritten = fwrite($this->connection, $data);

			if($bytesWritten === false || $bytesWritten === 0)
			{
				throw new RedisException('Failed to write data to the server.');
			}

			$bytesLeft -= $bytesWritten;

			$data = substr($data, $bytesWritten);
		}
		while($bytesLeft > 0);

		return $totalBytesWritten;
	}

	/**
	 * Is the connection persistent?
	 *
	 * @return bool
	 */
	public function isPersistent(): bool
	{
		return $this->isPersistent;
	}
}
