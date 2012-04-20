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

/**
 * @author Ondřej Vodáček
 */
class DoctrineAnnotationLoaderTest extends \PHPUnit_Framework_TestCase {

	/** @var DoctrineAnnotationLoader */
	protected $object;
	/** @var \Doctrine\ORM\Mapping\ClassMetadata */
	protected $classMetadata;
	/** @var string */
	private $testClass = 'DoctrineAnnotationLoaderTest_TestEntity';

	public static function getFixtuteDir() {
		$ref = new \ReflectionClass(__CLASS__);
		return dirname($ref->getFileName()) . '/fixtures/' . $ref->getShortName();
	}

	public static function setUpBeforeClass() {
		require_once self::getFixtuteDir() . '/TestEntity.php';
	}

	protected function setUp() {
		$this->classMetadata = $this->getMockBuilder('\Doctrine\ORM\Mapping\ClassMetadata')
				->setConstructorArgs(array($this->testClass))
				->getMock();
		$em = $this->getMockBuilder('\Doctrine\ORM\EntityManager')->disableOriginalConstructor()->getMock();
		$em->expects($this->any())
				->method('getClassMetadata')
				->with($this->testClass)
				->will($this->returnValue($this->classMetadata));
		$this->object = new DoctrineAnnotationLoader($em);
	}

	/**
	 * @dataProvider dp_testTypeAliases
	 */
	public function testTypeAliases($loaded, $expected) {
		$map = array(
			'fieldName' => 'var1',
			'type' => $loaded
		);
		$this->classMetadata->expects($this->any())
				->method('hasField')
				->will($this->returnValue(true));
		$this->classMetadata->expects($this->any())
				->method('getFieldMapping')
				->will($this->returnValue($map));

		$meta = $this->object->load($this->testClass);
		$this->assertSame($expected, $meta['var1']->type);
	}
	public function dp_testTypeAliases() {
		return array(
			array('smallint', 'integer'),
			array('bigint', 'integer')
		);
	}

	public function testNullableSetsRequiered() {
		$map = array(
			'fieldName' => 'var1',
			'type' => 'string',
			'nullable' => false
		);
		$this->classMetadata->expects($this->any())
				->method('hasField')
				->will($this->returnValue(true));
		$this->classMetadata->expects($this->any())
				->method('getFieldMapping')
				->will($this->returnValue($map));

		$meta = $this->object->load($this->testClass);
		$this->assertTrue($meta['var1']->conditions['required']);
	}

	public function testLengthSetsMaxLength() {
		$map = array(
			'fieldName' => 'var1',
			'type' => 'string',
			'length' => 123
		);
		$this->classMetadata->expects($this->any())
				->method('hasField')
				->will($this->returnValue(true));
		$this->classMetadata->expects($this->any())
				->method('getFieldMapping')
				->will($this->returnValue($map));

		$meta = $this->object->load($this->testClass);
		$this->assertSame(123, $meta['var1']->conditions['maxLength']);
	}

	public function testScaleSetsStep() {
		$map = array(
			'fieldName' => 'var1',
			'type' => 'decimal',
			'scale' => 3
		);
		$this->classMetadata->expects($this->any())
				->method('hasField')
				->will($this->returnValue(true));
		$this->classMetadata->expects($this->any())
				->method('getFieldMapping')
				->will($this->returnValue($map));

		$meta = $this->object->load($this->testClass);
		$this->assertSame('float', $meta['var1']->type);
		$this->assertSame(0.001, $meta['var1']->custom['step']);
	}

	public function testTypeIsSetFromAssociation() {
		$map = array(
			'fieldName' => 'var1',
			'targetEntity' => '\stdClass'
		);
		$this->classMetadata->expects($this->any())
				->method('hasField')
				->will($this->returnValue(false));
		$this->classMetadata->expects($this->any())
				->method('hasAssociation')
				->will($this->returnValue(true));
		$this->classMetadata->expects($this->any())
				->method('getAssociationMapping')
				->will($this->returnValue($map));

		$meta = $this->object->load($this->testClass);
		$this->assertSame('\stdClass', $meta['var1']->type);
	}
}
