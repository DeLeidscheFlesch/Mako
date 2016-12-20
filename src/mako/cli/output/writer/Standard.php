<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\output\writer;

use mako\cli\output\writer\WriterInterface;

/**
 * Standard writer.
 *
 * @author Frederic G. Østby
 */
class Standard implements WriterInterface
{
	/**
	 * Is the stream direct?
	 *
	 * @var bool
	 */
	protected $isDirect;

	/**
	 * {@inheritdoc}
	 */
	public function isDirect(): bool
	{
		if($this->isDirect === null)
		{
			$this->isDirect = (0020000 === (fstat(STDOUT)['mode'] & 0170000));
		}

		return $this->isDirect;
	}

	/**
	 * {@inheritdoc}
	 */
	public function write(string $string)
	{
		fwrite(STDOUT, $string);
	}
}
