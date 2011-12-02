<?php

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
		$ref = new \Nette\Reflection\ClassType($class);
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
	 * @param \Nette\Reflection\Property
	 * @return Metadata|null
	 */
	private function processProperty(\Nette\Reflection\Property $property) {
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
