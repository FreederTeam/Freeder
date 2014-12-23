<?php

class Feed extends Framework_TestCase {
	/**
	 * @expectedException Exception
	 */
	public function testExceptionIsRaisedForInvalidConstructorArguments() {
		new Feed();
	}

	/**
	 * @expectedException Exception
	 */
	public function testExceptionIsRaisedForInvalidConstructorArguments2() {
		new Feed(2);
	}

	/**
	 * @expectedException Exception
	 */
	public function testExceptionIsRaisedForInvalidConstructorArguments3() {
		new Feed($storage, '', 1);
	}

	public function testObjectCanBeConstructedForValidConstructorArguments() {
		$storage = new AbstractStorage();
		$e = new Feed($storage);
		$this->assertInstanceOf('Entry', $e);
	}

	public function testObjectCanBeConstructedForValidConstructorArguments2() {
		$storage = new AbstractStorage();
		$e = new Feed($storage, 'id', 1);
		$this->assertInstanceOf('Entry', $e);
	}

	public function testDelete() {
	}

	public function testSave() {
	}

	public function testLoadBy() {
	}

	public function testGetId() {
	}

	public function testSetId() {
	}

	public function testGetTitle() {
	}

	public function testSetTitle() {
	}

	public function testGetUserTitle() {
	}

	public function testSetUserTitle() {
	}

	public function testGetURL() {
	}

	public function testSetURL() {
	}

	public function testGetLinks() {
	}

	public function testSetLinks() {
	}

	public function testGetDescription() {
	}

	public function testSetDescription() {
	}

	public function testGetTTL() {
	}

	public function testSetTTL() {
	}

	public function testGetUserTTL() {
	}

	public function testSetUserTTL() {
	}

	public function testGetImages() {
	}

	public function testSetImages() {
	}

	public function testGetPOST() {
	}

	public function testSetPOST() {
	}

	public function testGetImportTagsFromFeed() {
	}

	public function testSetImportTagsFromFeed() {
	}
}
