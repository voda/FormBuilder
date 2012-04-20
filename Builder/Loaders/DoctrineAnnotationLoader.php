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

namespace Vodacek\Form\Builder\Loaders;

use Vodacek\Form\Builder;

/**
 * Loader using metadata from Doctrine merged with @Input annotations.
 *
 * @author Ondřej Vodáček <ondrej.vodacek@gmail.com>
 * @copyright 2011, Ondřej Vodáček
 * @license New BSD License
 */
class DoctrineAnnotationLoader extends AnnotationLoader {

	/** @var \Doctrine\ORM\EntityManager */
	private $em;

	/**
	 * @param \Doctrine\ORM\EntityManager $em
	 */
	public function __construct(\Doctrine\ORM\EntityManager $em) {
		$this->em = $em;
	}

	public function load($class) {
		$meta = parent::load($class);
		$cm = $this->em->getClassMetadata($class);
		foreach ($meta as $value) {
			$this->loadMeta($value, $cm);
		}
		return $meta;
	}

	/**
	 * @param Builder\Metadata $meta
	 * @param \Doctrine\ORM\Mapping\ClassMetadata $cm
	 */
	private function loadMeta(Builder\Metadata $meta, \Doctrine\ORM\Mapping\ClassMetadata $cm) {
		$type = null;
		if ($cm->hasField($meta->name)) {
			$map = $cm->getFieldMapping($meta->name);
			$type = $map['type'];
			switch ($type) {
				case 'smallint':
				case 'bigint':
					$type = 'integer';
					break;
				default:
					break;
			}
			if (!isset($map['nullable']) || $map['nullable'] === false && !isset($meta->conditions['required'])) {
				$meta->conditions['required'] = true;
			}
			if (isset($map['length']) && $map['length'] && !isset($meta->conditions['maxLenght'])) {
				$meta->conditions['maxLength'] = $map['length'];
			}
			if ($type === 'decimal' && isset($map['scale'])) {
				$type = 'float';
				$meta->custom['step'] = pow(10, -$map['scale']);
			}
		} elseif ($cm->hasAssociation($meta->name)) {
			$map = $cm->getAssociationMapping($meta->name);
			$type = $map['targetEntity'];
		}
		if (!$meta->type) {
			$meta->type = $type;
		}
	}
}
