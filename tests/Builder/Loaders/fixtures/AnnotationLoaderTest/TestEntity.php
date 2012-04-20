<?php

/**
 * @author Ondřej Vodáček
 */
class AnnotationLoaderTest_TestEntity {

	/**
	 * @var string
	 * @Input(label="String", type="string")
	 */
	private $var1;

	/**
	 * @var integer
	 * @Input(label="Integer", type="integer", min=0, max=32)
	 */
	private $var2 = 0;

	/**
	 * @var type
	 * @Input(label="Boolean", type="boolean", getter='isVar3')
	 */
	private $var3 = true;

	/**
	 * @var string
	 * @Input(label="Conditions", type="string", minLength=0, maxLength=15, required=true)
	 */
	private $var4;

	/**
	 * @var string
	 * @Input(label="Custom", type="string", foo="Lorem ipsum...", bar=false, baz="-123.456")
	 */
	private $var5;

	/**
	 * @var mixed
	 */
	private $var6;
}
