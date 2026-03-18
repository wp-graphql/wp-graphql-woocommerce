<?php
/**
 * Manages concurrent requests that executes mutations on the session data.
 *
 * @package WPGraphQL\WooCommerce\Utils
 * @since 0.7.1
 */

namespace WPGraphQL\WooCommerce\Utils;

/**
 * Class - Session_Transaction_Manager
 */
class Session_Transaction_Manager {
	/**
	 * The request's transaction ID. Shared across all mutations in the same HTTP request.
	 *
	 * @var null|string
	 */
	public $transaction_id = null;

	/**
	 * Whether the transaction has been queued (added to the transaction queue).
	 *
	 * @var bool
	 */
	private $is_queued = false;

	/**
	 * Instance of parent session handler
	 *
	 * @var \WPGraphQL\WooCommerce\Utils\QL_Session_Handler
	 */
	private $session_handler = null;

	/**
	 * Singleton instance of class.
	 *
	 * @var \WPGraphQL\WooCommerce\Utils\Session_Transaction_Manager
	 */
	private static $instance = null;

	/**
	 * Singleton retriever and cleaner.
	 * Should not be called anywhere but in the session handler init function.
	 *
	 * @param \WPGraphQL\WooCommerce\Utils\QL_Session_Handler $session_handler  WooCommerce Session Handler instance.
	 *
	 * @return \WPGraphQL\WooCommerce\Utils\Session_Transaction_Manager
	 */
	public static function get( &$session_handler ) {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self( $session_handler );
		}

		return self::$instance;
	}

	/**
	 * Session_Transaction_Manager constructor
	 *
	 * @param \WPGraphQL\WooCommerce\Utils\QL_Session_Handler $session_handler  Reference back to session handler.
	 */
	public function __construct( &$session_handler ) {
		$this->session_handler = $session_handler;

		add_action( 'graphql_before_resolve_field', [ $this, 'update_transaction_queue' ], 10, 4 );
		add_action( 'graphql_mutation_response', [ $this, 'complete_mutation' ], 20, 6 );

		add_action( 'woographql_session_transaction_complete', [ $this->session_handler, 'save_if_dirty' ], 10 );

		add_action( 'woocommerce_add_to_cart', [ $this->session_handler, 'mark_dirty' ] );
		add_action( 'woocommerce_cart_item_removed', [ $this->session_handler, 'mark_dirty' ] );
		add_action( 'woocommerce_cart_item_restored', [ $this->session_handler, 'mark_dirty' ] );
		add_action( 'woocommerce_cart_item_set_quantity', [ $this->session_handler, 'mark_dirty' ] );
		add_action( 'woocommerce_cart_emptied', [ $this->session_handler, 'mark_dirty' ] );

		// Pop the transaction at the end of the request so all mutations in a batch
		// execute under the same queue entry without interleaving from other requests.
		register_shutdown_function( [ $this, 'pop_transaction_id' ] );
	}

	/**
	 * Pass all member call upstream to the session handler.
	 *
	 * @param string $name  Name of class member.
	 *
	 * @return mixed
	 */
	public function __get( $name ) {
		return $this->session_handler->{$name};
	}

	/**
	 * Return array of all mutations that alter the session data.
	 * a.k.a. Session Mutations
	 *
	 * @return array
	 */
	public static function get_session_mutations() {
		/**
		 * All session altering mutations should be passed to the array.
		 */
		return \apply_filters(
			'woographql_session_mutations',
			[
				'addToCart',
				'updateItemQuantities',
				'addFee',
				'applyCoupon',
				'removeCoupons',
				'emptyCart',
				'removeItemsFromCart',
				'restoreCartItems',
				'updateItemQuantities',
				'updateShippingMethod',
				'updateCustomer',
				'updateSession',
				'forgetSession',
			]
		);
	}

	/**
	 * Returns the MySQL advisory lock name for the session's transaction queue.
	 *
	 * @return string
	 */
	private function get_lock_name() {
		// MySQL advisory lock names are limited to 64 characters.
		$customer_id = $this->session_handler->get_customer_id();
		return 'woo_stq_' . substr( md5( (string) $customer_id ), 0, 20 );
	}

	/**
	 * Acquires a MySQL advisory lock for atomic queue operations.
	 *
	 * @param int $timeout  Seconds to wait for lock acquisition.
	 *
	 * @return bool Whether the lock was acquired.
	 */
	private function acquire_lock( $timeout = 10 ) {
		global $wpdb;
		$lock_name = $this->get_lock_name();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$result = $wpdb->get_var( $wpdb->prepare( 'SELECT GET_LOCK(%s, %d)', $lock_name, $timeout ) );
		return '1' === $result;
	}

	/**
	 * Releases the MySQL advisory lock.
	 *
	 * @return void
	 */
	private function release_lock() {
		global $wpdb;
		$lock_name = $this->get_lock_name();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->get_var( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', $lock_name ) );
	}

	/**
	 * Generates a timestamp-based transaction ID.
	 *
	 * Uses microtime to ensure chronological ordering when sorted alphabetically.
	 *
	 * @return string
	 */
	private static function generate_transaction_id() {
		// Use zero-padded microtime for consistent alphabetical/chronological sorting.
		list( $usec, $sec ) = explode( ' ', microtime() );
		return sprintf( '%010d_%06d', $sec, intval( absint( $usec ) * 1000000 ) );
	}

	/**
	 * Transaction queue workhorse.
	 *
	 * Creates a transaction ID if executing mutations that alter the session data, and stalls
	 * execution until the transaction ID is at the top of the queue.
	 *
	 * @param mixed                                $source   Operation root object.
	 * @param array                                $args     Operation arguments.
	 * @param \WPGraphQL\AppContext                $context  AppContext instance.
	 * @param \GraphQL\Type\Definition\ResolveInfo $info     Operation ResolveInfo object.
	 *
	 * @return void
	 */
	public function update_transaction_queue( $source, $args, $context, $info ) {
		// Bail early, if not one of the session mutations.
		if ( ! in_array( $info->fieldName, self::get_session_mutations(), true ) ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			return;
		}

		// If transaction ID already exists and is queued, this is a subsequent mutation in the
		// same batch request. The queue entry is still at position [0], so just reload and proceed.
		if ( ! is_null( $this->transaction_id ) && $this->is_queued ) {
			$this->session_handler->reload_data();
			return;
		}

		// Initialize transaction ID once per request.
		if ( is_null( $this->transaction_id ) ) {
			$this->transaction_id = self::generate_transaction_id();
		}

		// Wait until our transaction ID is at the top of the queue before continuing.
		if ( ! $this->next_transaction() ) {
			usleep( 500000 );
			$this->update_transaction_queue( $source, $args, $context, $info );
		} else {
			$this->session_handler->reload_data();

			// Set a timestamp on the transaction, which will allow us to check for any stale
			// transactions that accidentally get left behind.
			$this->set_timestamp();
		}
	}

	/**
	 * Processes next transaction and returns whether the current transaction is the next transaction.
	 *
	 * @return bool
	 */
	public function next_transaction() {
		// Update transaction queue.
		$transaction_queue = $this->get_transaction_queue();

		// If lead transaction object invalid pop transaction and loop.
		if ( ! is_array( $transaction_queue[0] ) ) {
			$this->acquire_lock();
			$transaction_queue = get_transient( "woo_session_transactions_queue_{$this->session_handler->get_customer_id()}" );
			if ( ! empty( $transaction_queue ) ) {
				array_shift( $transaction_queue );
				$this->save_transaction_queue( $transaction_queue );
			}
			$this->release_lock();

			// If current transaction is the lead exit loop.
		} elseif ( $this->transaction_id === $transaction_queue[0]['transaction_id'] ) {
			return true;
		} elseif ( true === $this->did_transaction_expire( $transaction_queue ) ) {
			// If transaction has expired, remove it from the queue array and continue loop.
			$this->acquire_lock();
			$transaction_queue = get_transient( "woo_session_transactions_queue_{$this->session_handler->get_customer_id()}" );
			if ( ! empty( $transaction_queue ) ) {
				array_shift( $transaction_queue );
				$this->save_transaction_queue( $transaction_queue );
			}
			$this->release_lock();
		}

		return false;
	}

	/**
	 * Adds transaction ID to the queue in sorted order and returns the transaction queue.
	 *
	 * Transaction IDs are timestamp-based, so alphabetical sorting preserves chronological order.
	 * This ensures mutations from earlier requests always execute before mutations from later
	 * requests, even if they are queued out of order.
	 *
	 * @return array
	 */
	public function get_transaction_queue() {
		$this->acquire_lock();

		// Get transaction queue.
		$transaction_queue = get_transient( "woo_session_transactions_queue_{$this->session_handler->get_customer_id()}" );
		if ( ! $transaction_queue ) {
			$transaction_queue = [];
		}

		// If transaction ID not in queue, add it in sorted order, and start transaction.
		if ( ! in_array( $this->transaction_id, array_column( $transaction_queue, 'transaction_id' ), true ) ) {
			$transaction_id = $this->transaction_id;
			$snapshot       = $this->session_handler->get_session_data();

			$entry = compact( 'transaction_id', 'snapshot' );

			// Insert in sorted position based on transaction ID (timestamp-based).
			$inserted = false;
			foreach ( $transaction_queue as $index => $queued ) {
				if ( ! empty( $transaction_id ) && strcmp( $transaction_id, $queued['transaction_id'] ) < 0 ) {
					array_splice( $transaction_queue, $index, 0, [ $entry ] );
					$inserted = true;
					break;
				}
			}

			if ( ! $inserted ) {
				$transaction_queue[] = $entry;
			}

			// Update queue.
			$this->save_transaction_queue( $transaction_queue );
			$this->is_queued = true;
		}

		$this->release_lock();

		return $transaction_queue;
	}

	/**
	 * Called after each mutation completes. Saves session data but does NOT pop
	 * the transaction from the queue. The queue entry stays at position [0] to
	 * block other requests until the entire HTTP request completes.
	 *
	 * @param array                                $payload          The Payload returned from the mutation.
	 * @param array                                $input            The mutation input args, after being filtered by 'graphql_mutation_input'.
	 * @param array                                $unfiltered_input The unfiltered input args of the mutation
	 * @param \WPGraphQL\AppContext                $context          The AppContext object.
	 * @param \GraphQL\Type\Definition\ResolveInfo $info             The ResolveInfo object.
	 * @param string                               $mutation         The name of the mutation field.
	 *
	 * @return void
	 */
	public function complete_mutation( $payload, $input, $unfiltered_input, $context, $info, $mutation ) {
		// Bail if transaction not started.
		if ( is_null( $this->transaction_id ) || ! $this->is_queued ) {
			return;
		}

		// Bail if not a session mutation.
		if ( ! in_array( $mutation, self::get_session_mutations(), true ) ) {
			return;
		}

		/**
		 * Mark mutation completion and save session data.
		 *
		 * @param string|null $transition_id     Current transaction ID.
		 * @param array       $transaction_queue Transaction Queue (not re-read here for performance).
		 */
		do_action( 'woographql_session_transaction_complete', $this->transaction_id, [] );
	}

	/**
	 * Pop transaction ID off the top of the queue, ending the transaction.
	 *
	 * Called via register_shutdown_function at the end of the HTTP request, ensuring
	 * all mutations in a batch complete before the queue position is released to
	 * other requests.
	 *
	 * @return void
	 */
	public function pop_transaction_id() {
		// Bail if transaction not started.
		if ( is_null( $this->transaction_id ) || ! $this->is_queued ) {
			return;
		}

		$this->acquire_lock();

		// Get transaction queue.
		$transaction_queue = get_transient( "woo_session_transactions_queue_{$this->session_handler->get_customer_id()}" );

		if ( ! empty( $transaction_queue[0]['transaction_id'] ) && $this->transaction_id === $transaction_queue[0]['transaction_id'] ) {
			// Remove Transaction ID and update queue.
			array_shift( $transaction_queue );
			$this->save_transaction_queue( $transaction_queue );
		}

		$this->release_lock();

		// Clear transaction state.
		$this->transaction_id = null;
		$this->is_queued      = false;
	}

	/**
	 * Saves transaction queue.
	 *
	 * @param array $queue  Transaction queue.
	 *
	 * @return void
	 */
	public function save_transaction_queue( $queue = [] ) {
		// If queue empty delete transient and bail.
		if ( empty( $queue ) ) {
			delete_transient( "woo_session_transactions_queue_{$this->session_handler->get_customer_id()}" );
			return;
		}

		// Save transaction queue.
		set_transient( "woo_session_transactions_queue_{$this->session_handler->get_customer_id()}", $queue, 5 * MINUTE_IN_SECONDS );
	}

	/**
	 * Create transaction timestamp.
	 *
	 * @return void
	 */
	public function set_timestamp() {
		$this->acquire_lock();

		$transaction_queue = get_transient( "woo_session_transactions_queue_{$this->session_handler->get_customer_id()}" );
		if ( ! $transaction_queue ) {
			$transaction_queue = [];
		}

		// Bail if we don't have a queue to add a timestamp against.
		if ( empty( $transaction_queue[0] ) ) {
			$this->release_lock();
			return;
		}

		$transaction_queue[0]['timestamp'] = time();

		$this->save_transaction_queue( $transaction_queue );

		$this->release_lock();
	}

	/**
	 * The length of time in seconds a transaction should stay in the queue
	 *
	 * @return mixed|void
	 */
	public function get_timestamp_threshold() {
		return apply_filters( 'woographql_session_transaction_timeout', 30 );
	}

	/**
	 * Whether the transaction has expired. This helps prevent infinite loops while searching through the transaction
	 * queue.
	 *
	 * @param array $transaction_queue  Transaction queue.
	 *
	 * @return bool
	 */
	public function did_transaction_expire( $transaction_queue ) {
		// Guard against empty transaction queue. We assume that it is invalid since we cannot calculate.
		if ( empty( $transaction_queue ) ) {
			return true;
		}

		// Guard against empty timestamp. We assume that it is invalid since we cannot calculate.
		if ( empty( $transaction_queue[0] ) || empty( $transaction_queue[0]['timestamp'] ) ) {
			return true;
		}

		$now        = time();
		$stamp      = $transaction_queue[0]['timestamp'];
		$threshold  = $this->get_timestamp_threshold();
		$difference = $now - $stamp;

		return $difference > $threshold;
	}
}
