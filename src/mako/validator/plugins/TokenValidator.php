<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\plugins;

use mako\session\Session;
use mako\validator\plugins\ValidatorPlugin;

/**
 * Token validator plugin.
 *
 * @author Frederic G. Østby
 */
class TokenValidator extends ValidatorPlugin
{
	/**
	 * Rule name.
	 *
	 * @var string
	 */
	protected $ruleName = 'token';

	/**
	 * Session instance.
	 *
	 * @var \mako\session\Session
	 */
	protected $session;

	/**
	 * Constructor.
	 *
	 * @param \mako\session\Session $session Session instance
	 */
	public function __construct(Session $session)
	{
		$this->session = $session;
	}

	/**
	 * Validates a token.
	 *
	 * @param  string|null $input Input
	 * @return bool
	 */
	public function validate(string $input = null): bool
	{
		return $this->session->validateToken($input);
	}
}
