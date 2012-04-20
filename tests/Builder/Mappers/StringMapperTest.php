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
 * @author Ondřej Vodáček
 */
class StringMapperTest extends \PHPUnit_Framework_TestCase {

	/** @var StringMapper */
	protected $object;
	/** @var \Nette\Forms\Controls\TextBase */
	protected $control;
	/** @var \Nette\Forms\Form */
	protected $form;
	/** @var Builder\Metadata */
	protected $metadata;

	protected function setUp() {
		$this->object = new StringMapper();
		$this->control = $this->getMock('\Nette\Forms\Controls\TextInput');
		$this->form = $this->getMock('\Nette\Forms\Form');
		$this->metadata = $meta = new Builder\Metadata();
		$meta->type = 'string';
		$meta->name = 'var';
		$meta->label = 'String';
	}

	public function testAddFormControl() {
		$this->form->expects($this->once())
				->method('addText')
				->with('var', 'String')
				->will($this->returnValue($this->control));
		$result = $this->object->addFormControl($this->form, $this->metadata);
		$this->assertSame($this->control, $result);
	}

	public function testAddingTextArea() {
		$this->metadata->type = 'text';
		$this->form->expects($this->once())
				->method('addTextArea')
				->with('var', 'String')
				->will($this->returnValue($this->control));
		$this->object->addFormControl($this->form, $this->metadata);
	}

	/**
	 * @dataProvider dp_testToPropertyValue
	 */
	public function testToPropertyValue($value, $expected) {
		$this->control->expects($this->any())
				->method('getValue')
				->will($this->returnValue($value));
		$result = $this->object->toPropertyValue($this->control, $this->metadata);
		$this->assertSame($expected, $result);
	}
	public function dp_testToPropertyValue() {
		return array(
			array('Lorem ipsum', 'Lorem ipsum'),
			array('', null)
		);
	}
}
