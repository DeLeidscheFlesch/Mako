<?php

namespace mako\view\renderers;

/**
 * Renderer interface.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

interface RendererInterface
{
	public function __construct($view, array $variables);
	public function assign($key, $value);
	public function render();
}

/** -------------------- End of file -------------------- **/