<?php

class EntryTest extends PHPUnit_Framework_TestCase {
	/**
	 * @expectedException Exception
	 */
	public function testExceptionIsRaisedForInvalidConstructorArguments() {
		new Entry();
	}

	/**
	 * @expectedException Exception
	 */
	public function testExceptionIsRaisedForInvalidConstructorArguments2() {
		new Entry(2);
	}

	public function testObjectCanBeConstructedForValidConstructorArguments() {
		$storage = new AbstractStorage();
		$e = new Entry($storage);
		$this->assertInstanceOf('Entry', $e);
	}

	public function testCleanAuthors() {
	}

	public function testGetLink() {
	}

	public function testHasTag() {
	}
}
