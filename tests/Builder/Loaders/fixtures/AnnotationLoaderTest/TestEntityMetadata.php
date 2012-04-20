<?php

use Vodacek\Form\Builder\Metadata;

$metadata = array();

$meta = new Metadata();
$meta->name = 'var1';
$meta->label = 'String';
$meta->type = 'string';
$meta->getter = 'getVar1';
$meta->setter = 'setVar1';
$metadata[$meta->name] = $meta;

$meta = new Metadata();
$meta->name = 'var2';
$meta->label = 'Integer';
$meta->type = 'integer';
$meta->getter = 'getVar2';
$meta->setter = 'setVar2';
$meta->conditions = array(
	'min' => 0,
	'max' => 32
);
$metadata[$meta->name] = $meta;

$meta = new Metadata();
$meta->name = 'var3';
$meta->label = 'Boolean';
$meta->type = 'boolean';
$meta->getter = 'isVar3';
$meta->setter = 'setVar3';
$metadata[$meta->name] = $meta;

$meta = new Metadata();
$meta->name = 'var4';
$meta->label = 'Conditions';
$meta->type = 'string';
$meta->getter = 'getVar4';
$meta->setter = 'setVar4';
$meta->conditions = array(
	'minLength' => 0,
	'maxLength' => 15,
	'required' => true
);
$metadata[$meta->name] = $meta;

$meta = new Metadata();
$meta->name = 'var5';
$meta->label = 'Custom';
$meta->type = 'string';
$meta->getter = 'getVar5';
$meta->setter = 'setVar5';
$meta->custom = array(
	'foo' => 'Lorem ipsum...',
	'bar' => false,
	'baz' => -123.456
);
$metadata[$meta->name] = $meta;

return $metadata;
