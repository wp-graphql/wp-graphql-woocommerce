<?php

class SessionTransactionManagerTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {
	/**
	 * @var \WPGraphQL\WooCommerce\Utils\Session_Transaction_Manager
	 */
	private $manager;

	public function setUp(): void {
		parent::setUp();

		$mock_session = $this->getMockBuilder( \WPGraphQL\WooCommerce\Utils\QL_Session_Handler::class )
			->disableOriginalConstructor()
			->getMock();

		$mock_session->method( 'get_customer_id' )->willReturn( 'test_customer_stm' );
		$mock_session->method( 'get_session_data' )->willReturn( [] );

		$this->manager = new \WPGraphQL\WooCommerce\Utils\Session_Transaction_Manager( $mock_session );
	}

	public function tearDown(): void {
		delete_transient( 'woo_session_transactions_queue_test_customer_stm' );
		parent::tearDown();
	}

	/**
	 * Test that did_transaction_expire returns true for empty queue.
	 */
	public function testDidTransactionExpireReturnsTrueForEmptyQueue() {
		$this->assertTrue( $this->manager->did_transaction_expire( [] ) );
	}

	/**
	 * Test that did_transaction_expire returns true for missing timestamp.
	 */
	public function testDidTransactionExpireReturnsTrueForMissingTimestamp() {
		$queue = [
			[ 'transaction_id' => 'test_123' ],
		];
		$this->assertTrue( $this->manager->did_transaction_expire( $queue ) );
	}

	/**
	 * Test that did_transaction_expire returns true for expired timestamp.
	 */
	public function testDidTransactionExpireReturnsTrueForExpiredTimestamp() {
		$queue = [
			[
				'transaction_id' => 'test_123',
				'timestamp'      => time() - 60,
			],
		];
		$this->assertTrue( $this->manager->did_transaction_expire( $queue ) );
	}

	/**
	 * Test that did_transaction_expire returns false for fresh timestamp.
	 */
	public function testDidTransactionExpireReturnsFalseForFreshTimestamp() {
		$queue = [
			[
				'transaction_id' => 'test_123',
				'timestamp'      => time(),
			],
		];
		$this->assertFalse( $this->manager->did_transaction_expire( $queue ) );
	}

	/**
	 * Test that next_transaction handles invalid lead entry in the queue.
	 */
	public function testNextTransactionHandlesInvalidLeadEntry() {
		$customer_id = \WC()->session->get_customer_id();

		// Set a transaction ID on the manager.
		$this->manager->transaction_id = 'test_txn_001';

		// Seed the queue with an invalid (non-array) lead entry followed by our transaction.
		set_transient(
			"woo_session_transactions_queue_{$customer_id}",
			[
				'invalid_string_entry',
				[
					'transaction_id' => 'test_txn_001',
					'snapshot'       => [],
				],
			],
			300
		);

		// next_transaction should pop the invalid entry, then our transaction
		// moves to position 0 and it returns true.
		$result = $this->manager->next_transaction();
		$this->assertTrue( $result );
	}

	/**
	 * Test that next_transaction handles expired lead entry.
	 */
	public function testNextTransactionHandlesExpiredLeadEntry() {
		$customer_id = \WC()->session->get_customer_id();

		// Set a transaction ID on the manager.
		$this->manager->transaction_id = 'test_txn_002';

		// Seed the queue with an expired lead entry followed by our transaction.
		set_transient(
			"woo_session_transactions_queue_{$customer_id}",
			[
				[
					'transaction_id' => 'expired_txn',
					'snapshot'       => [],
					'timestamp'      => time() - 60,
				],
				[
					'transaction_id' => 'test_txn_002',
					'snapshot'       => [],
				],
			],
			300
		);

		// next_transaction should pop the expired entry, then our transaction
		// moves to position 0 and it returns true.
		$result = $this->manager->next_transaction();
		$this->assertTrue( $result );
	}

	/**
	 * Test that next_transaction returns true when current transaction is at head.
	 */
	public function testNextTransactionReturnsTrueWhenAtHead() {
		$customer_id = \WC()->session->get_customer_id();

		$this->manager->transaction_id = 'test_txn_003';

		set_transient(
			"woo_session_transactions_queue_{$customer_id}",
			[
				[
					'transaction_id' => 'test_txn_003',
					'snapshot'       => [],
					'timestamp'      => time(),
				],
			],
			300
		);

		$result = $this->manager->next_transaction();
		$this->assertTrue( $result );
	}
}
