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
 *     * Neither the name of the Ondřej Vodáček nor the
 *       names of its contributors may be used to endorse or promote products
 *       derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL Ondřej Vodáček BE LIABLE FOR ANY
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
class Builder {

	/** @var \Nette\DI\Container */
	private $mapperContainer;

	/** @var array */
	private $aliases = array(
		'text' => 'string',
		'integer' => 'number',
		'float' => 'number',
		'time' => 'date',
		'month' => 'date',
		'datetime' => 'date'
	);

	/** @var Loaders\ILoader */
	private $loader;

	/** @var \SplObjectStorage */
	private $entities;

	public function __construct(Loaders\ILoader $loader) {
		$this->entities = new \SplObjectStorage();
		$this->loader = $loader;
		$this->mapperContainer = new \Nette\DI\Container();
		$this->mapperContainer->addService('string', function() {
			return new Mappers\StringMapper();
		});
		$this->mapperContainer->addService('number', function() {
			return new Mappers\NumberMapper();
		});
		$this->mapperContainer->addService('date', function() {
			return new Mappers\DateMapper();
		});
		$this->mapperContainer->addService('id', function() {
			return new Mappers\IdMapper();
		});
		$this->mapperContainer->addService('boolean', function() {
			return new Mappers\BooleanMapper();
		});
	}

	public function build($entity) {
		$form = new EntityForm($this);
		$metadata = $this->loader->load(is_object($entity) ? get_class($entity) : $entity);
		foreach ($metadata as $meta) {
			$this->getMapper($meta)->addFormControl($form, $meta);
		}
		$this->entities[$form] = $entity;
		return $form;
	}

	/**
	 * @param EntityForm $form
	 */
	public function setDefaults(EntityForm $form) {
		$entity = $this->entities[$form];
		if (is_object($entity)) {
			$form->setDefaults($entity);
		}
	}

	/**
	 * @param object $values
	 * @return array
	 */
	public function formatForFrom(EntityForm $form, $values) {
		$entity = $this->entities[$form];
		$metadata = $this->loader->load(is_object($entity) ? get_class($entity) : $entity);
		$formated = array();
		if (is_array($values) || $values instanceof \Traversable) {
			foreach ($values as $name => $value) {
				if (isset($metadata[$name])) {
					$meta = $metadata[$name];
					$value = $this->getMapper($meta)->toControlValue($value, $meta);
				}
				$formated[$name] = $value;
			}
		} else {
			foreach ($metadata as $meta) {
				$getter = $meta->getter;
				$formated[$meta->name] = $this->getMapper($meta)->toControlValue($values->$getter(), $meta);
			}
		}
		return $formated;
	}

	/**
	 * @param EntityForm $form
	 * @return object
	 */
	public function buildEntity(EntityForm $form) {
		$entity = $this->entities[$form];
		$class = null;
		if (is_object($entity)) {
			$class = get_class($entity);
		} else {
			$class = $entity;
			$entity = null;
		}
		$metadata = $this->loader->load($class);
		$values = array();
		foreach ($metadata as $name => $meta) {
			$values[$name] = $this->getMapper($meta)->toPropertyValue($form[$name], $meta);
		}

		if (!$entity) {
			$ref = new \ReflectionClass($class);
			$entity = null;
			if ($ref->hasMethod('__construct')) {
				$args = array();
				foreach ($ref->getMethod('__construct')->getParameters() as $param) {
					$args[] = $values[$param->getName()];
					unset($values[$param->getName()]);
				}
				$entity = $ref->newInstanceArgs($args);
			} else {
				$entity = $ref->newInstance();
			}
		}

		foreach ($values as $name => $value) {
			$setter = $metadata[$name]->setter;
			$entity->$setter($value);
		}

		return $entity;
	}

	/**
	 * @param Metadata $meta
	 * @return Mappers\IMapper
	 */
	private function getMapper(Metadata $meta) {
		$type = isset($this->aliases[$meta->type]) ? $this->aliases[$meta->type] : $meta->type;
		return $this->mapperContainer->getService($type);
	}

	/**
	 * @param string $name
	 * @param string $target
	 */
	public function addAlias($name, $target) {
		$this->aliases[$name] = $target;
	}

	/**
	 * @param string $name
	 * @param IMapper|callback $mapper
	 */
	public function addMapper($name, $mapper) {
		$this->mapperContainer->addService($name, $mapper);
	}
}
