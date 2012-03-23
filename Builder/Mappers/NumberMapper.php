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

namespace Vodacek\Form\Builder\Mappers;

use Vodacek\Form\Builder;

/**
 * @author Ondřej Vodáček <ondrej.vodacek@gmail.com>
 * @copyright 2011, Ondřej Vodáček
 * @license New BSD License
 */
class NumberMapper extends DefaultMapper {

	/**
	 * @param \Nette\Forms\Form $form
	 * @param Builder\Metadata $meta
	 * @return \Nette\Forms\Controls\BaseControl
	 */
	public function addFormControl(\Nette\Forms\Form $form, Builder\Metadata $meta) {
		$input = $form->addText($meta->name, $meta->label);
		$input->getControlPrototype()->type('number');
		$input->addCondition(Builder\EntityForm::FILLED)
				->addRule($meta->type === 'float' ? Builder\EntityForm::FLOAT : Builder\EntityForm::INTEGER);
		if ($meta->type === 'float') {
			$step = isset($meta->custom['step']) ? $meta->custom['step'] : '0.1';
			$input->getControlPrototype()->step($step);
		}

		if (isset($meta->conditions['min'])) {
			$input->getControlPrototype()->min($meta->conditions['min']);
		}
		if (isset($meta->conditions['max'])) {
			$input->getControlPrototype()->max($meta->conditions['max']);
		}
		$this->addConditions($input, $meta->conditions);
		return $input;
	}

	/**
	 * @param mixed $value
	 * @param Builder\Metadata $metadata
	 * @return mixed
	 */
	public function toControlValue($value, Builder\Metadata $metadata) {
		return strtr($value, ',', '.');
	}

	/**
	 * @param \Nette\Forms\Controls\BaseControl $control
	 * @param Builder\Metadata $metadata
	 * @return mixed
	 */
	public function toPropertyValue(\Nette\Forms\Controls\BaseControl $control, Builder\Metadata $metadata) {
		$value = $control->getValue();
		if ($value !== null) {
			$value = $metadata->type === 'float' ? (float)$value : (int)$value;
		}
		return $value;
	}
}
