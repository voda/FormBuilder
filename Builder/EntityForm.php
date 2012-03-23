<?php
/*
 * Copyright (c) 2011, Ondřej Vodáček
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *     * Neither the name of the author nor the
 *       names of its contributors may be used to endorse or promote products
 *       derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

namespace Vodacek\Form\Builder;

/**
 * @author Ondřej Vodáček <ondrej.vodacek@gmail.com>
 * @copyright 2011, Ondřej Vodáček
 * @license New BSD License
 */
class EntityForm extends \Nette\Application\UI\Form {

	/** @var Builder */
	private $builder;

	/**
	 * @param Builder $builder
	 */
	public function __construct(Builder $builder) {
		parent::__construct();
		$this->builder = $builder;
	}

	/**
	 * Fill-in with default values.
	 * @param  array|Traversable|object $values
	 * @param  bool $erase erase other default values?
	 * @return EntityForm provides a fluent interface
	 */
	public function setDefaults($values, $erase = FALSE) {
		$values = $this->builder->formatForFrom($this, $values);
		return parent::setDefaults($values, $erase);
	}

	/**
	 * This method will be called when the component (or component's parent)
	 * becomes attached to a monitored object. Do not call this method yourself.
	 * @param  Nette\ComponentModel\IComponent
	 * @return void
	 */
	protected function attached($presenter) {
		parent::attached($presenter);
		if ($presenter instanceof \Nette\Application\UI\Presenter) {
			$this->builder->setDefaults($this);
		}
	}

	/**
	 * Call to undefined method.
	 * @param  string  method name
	 * @param  array   arguments
	 * @return mixed
	 * @throws MemberAccessException
	 */
	public function __call($name, $args) {
		if ($name === 'onSuccess') {
			$entity = $this->builder->buildEntity($args[0]);
			if (!$entity) {
				return;
			}
			$args = array($entity, $args[0]);
		}
		return parent::__call($name, $args);
	}
}
