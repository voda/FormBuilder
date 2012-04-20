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
class NumberMapperTest extends \PHPUnit_Framework_TestCase {

	/** @var NumberMapper */
	protected $object;
	/** @var \Nette\Forms\Controls\TextInput */
	protected $control;
	/** @var \Nette\Utils\Html */
	protected $controlPrototype;
	/** @var \Nette\Forms\Form */
	protected $form;
	/** @var Builder\Metadata */
	protected $metadata;

	protected function setUp() {
		$this->object = new NumberMapper();
		$this->control = $this->getMock('\Nette\Forms\Controls\TextInput');
		$this->controlPrototype = $this->getMock('\Nette\Utils\Html');
		$this->control->expects($this->any())
				->method('getControlPrototype')
				->will($this->returnValue($this->controlPrototype));
		$this->control->expects($this->any())
				->method('addCondition')
				->will($this->returnValue($this->getMockBuilder('\stdClass')->setMethods(array('addRule'))->getMock()));
		$this->form = $this->getMock('\Nette\Forms\Form');
		$this->metadata = $meta = new Builder\Metadata();
		$meta->type = 'integer';
		$meta->name = 'var';
		$meta->label = 'Int';
		$meta->conditions['max'] = 12345;
		$meta->conditions['min'] = 12;
	}

	public function testAddInteger() {
		$this->form->expects($this->once())
				->method('addText')
				->with('var', 'Int')
				->will($this->returnValue($this->control));
		$this->controlPrototype->expects($this->any())
				->method('type')
				->with('number');
		$this->controlPrototype->expects($this->any())
				->method('min')
				->with(12);
		$this->controlPrototype->expects($this->any())
				->method('max')
				->with(12345);
		$result = $this->object->addFormControl($this->form, $this->metadata);
		$this->assertSame($this->control, $result);
	}

	public function testAddFloat() {
		$this->metadata->type = 'float';
		$this->form->expects($this->once())
				->method('addText')
				->with('var', 'Int')
				->will($this->returnValue($this->control));
		$this->controlPrototype->expects($this->any())
				->method('type')
				->with('number');
		$this->controlPrototype->expects($this->any())
				->method('step')
				->with('0.1');
		$this->object->addFormControl($this->form, $this->metadata);
	}

	public function testAddFloatWithCustomStep() {
		$this->metadata->type = 'float';
		$this->metadata->custom['step'] = '0.002';
		$this->form->expects($this->once())
				->method('addText')
				->with('var', 'Int')
				->will($this->returnValue($this->control));
		$this->controlPrototype->expects($this->any())
				->method('type')
				->with('number');
		$this->controlPrototype->expects($this->any())
				->method('step')
				->with('0.002');
		$this->object->addFormControl($this->form, $this->metadata);
	}

	/**
	 * @dataProvider dp_testToControlValue
	 */
	public function testToControlValue($value, $expected) {
		$result = $this->object->toControlValue($value, $this->metadata);
		$this->assertSame($expected, $result);
	}
	public function dp_testToControlValue() {
		return array(
			array(5, '5'),
			array(0.1, '0.1'),
			array(0.02, '0.02'),
			array(0.000006, '6.0E-6'),
		);
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
			array('5', 5),
			array('0', 0),
			array('', 0),
			array('987654321', 987654321),
		);
	}

	/**
	 * @dataProvider dp_testToPropertyValueWithFloats
	 */
	public function testToPropertyValueWithFloats($value, $expected) {
		$this->metadata->type = 'float';
		$this->control->expects($this->any())
				->method('getValue')
				->will($this->returnValue($value));
		$result = $this->object->toPropertyValue($this->control, $this->metadata);
		$this->assertSame($expected, $result);
	}
	public function dp_testToPropertyValueWithFloats() {
		return array(
			array('5', 5.0),
			array('0', 0.0),
			array('', 0.0),
			array('0.1', 0.1),
			array('6.0e-6', 0.000006),
		);
	}
}
