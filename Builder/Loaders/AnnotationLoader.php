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

namespace Vodacek\Form\Builder\Loaders;

use Vodacek\Form\Builder;

/**
 * @author Ondřej Vodáček
 */
class AnnotationLoader implements ILoader {

	/**
	 * @param string $class
	 * @return array array<Builder\Metadata>
	 */
	public function load($class) {
		$meta = array();
		$ref = new \Nette\Reflection\ClassReflection($class);
		do {
			foreach ($ref->getProperties() as $property) {
				$m = $this->processProperty($property);
				if ($m) {
					$meta[$m->name] = $m;
				}
			}
			$ref = $ref->getParentClass();
		} while ($ref);
		return $meta;
	}

	/**
	 * @param \Nette\Reflection\PropertyReflection
	 * @return Metadata|null
	 */
	private function processProperty(\Nette\Reflection\PropertyReflection $property) {
		if (!$property->hasAnnotation('Input')) {
			return;
		}
		$input = $property->getAnnotation('Input');

		$meta = new Builder\Metadata();
		$meta->name = $property->getName();
		$meta->getter = 'get'.ucfirst($meta->name);
		$meta->setter = 'set'.ucfirst($meta->name);

		if ($input instanceof \ArrayObject) {
			$input = (array)($input);
			foreach ($input as $name => $value) {
				switch ($name) {
					case 'label':
					case 'type':
					case 'getter':
					case 'setter':
						$meta->$name = $value;
						unset($input[$name]);
						break;
					case 'required':
					case 'minLength':
					case 'maxLength':
					case 'min':
					case 'max':
						$meta->conditions[$name] = $value;
						unset($input[$name]);
						break;
					default:
						break;
				}
			}
			$meta->custom = $input;
		}
		return $meta;
	}

}
