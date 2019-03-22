<?php

class ProductMutationsTest extends \Codeception\TestCase\WPTestCase {

	public $shopManager;
	public $customer;

	public function setUp() {
		// before
		parent::setUp();

		$this->shopManager = $this->factory->user->create(
			[
				'role' => 'shop_manager',
			]
		);
		$this->customer    = $this->factory->user->create(
			[
				'role' => 'customer',
			]
		);
	}

	public function tearDown() {
		// your tear down methods here
		// then
		parent::tearDown();
	}

	// tests
	public function testMe() {  }
}
