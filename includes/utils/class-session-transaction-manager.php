<?php
/**
 * Manages concurrent requests that executes mutations on the session data.
 *
 * @package WPGraphQL\WooCommerce\Utils
 * @since 0.7.1
 */

namespace WPGraphQL\WooCommerce\Utils;

use GraphQL\Error\UserError;

/**
 * Class - Session_Transaction_Manager
 */
class Session_Transaction_Manager {
	/**
	 * The request's transaction ID.
	 *
	 * @var null|string
	 */
	public $transaction_id = null;

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
		add_action( 'graphql_mutation_response', [ $this, 'pop_transaction_id' ], 20, 6 );
		add_action( 'woographql_session_transaction_complete', [ $this->session_handler, 'save_if_dirty' ], 10 );
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
			]
		);
	}

	/**
	 * Transaction queue workhorse.
	 *
	 * Creates an transaction ID if executing mutations that alter the session data, and stales
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

		// Bail if transaction has already been completed. There are times when the underlying action runs twice.
		if ( ! is_null( $this->transaction_id ) ) {
			$transaction_queue = get_transient( "woo_session_transactions_queue_{$this->session_handler->get_customer_id()}" );
			if ( in_array( $this->transaction_id, array_column( $transaction_queue, 'transaction_id' ), true ) ) {
				return;
			}
		} else {
			// Initialize transaction ID.
			$mutation             = $info->fieldName; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$this->transaction_id = \uniqid( "wooSession_{$mutation}_" );
		}

		// Wait until our transaction ID is at the top of the queue before continuing.
		if ( ! $this->next_transaction() ) {
			usleep( 500000 );
			$this->update_transaction_queue( $source, $args, $context, $info );
		} else {
			$this->session_handler->reload_data();

			// Set a timestamp on the transaction, which will allow us to check for any stale transactions that accidentally get left behind.
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
			array_shift( $transaction_queue );
			$this->save_transaction_queue( $transaction_queue );

			// If current transaction is the lead exit loop.
		} elseif ( $this->transaction_id === $transaction_queue[0]['transaction_id'] ) {
			return true;
		} elseif ( true === $this->did_transaction_expire( $transaction_queue ) ) {
			// If transaction has expired, remove it from the queue array and continue loop.
			array_shift( $transaction_queue );
			$this->save_transaction_queue( $transaction_queue );
		}

		return false;
	}

	/**
	 * Adds transaction ID to the end of the queue, officially starting the transaction,
	 * and returns the transaction queue.
	 *
	 * @return array
	 */
	public function get_transaction_queue() {
		// Get transaction queue.
		$transaction_queue = get_transient( "woo_session_transactions_queue_{$this->session_handler->get_customer_id()}" );
		if ( ! $transaction_queue ) {
			$transaction_queue = [];
		}

		// If transaction ID not in queue, add it, and start transaction.
		if ( ! in_array( $this->transaction_id, array_column( $transaction_queue, 'transaction_id' ), true ) ) {
			$transaction_id = $this->transaction_id;
			$snapshot       = $this->session_handler->get_session_data();

			$transaction_queue[] = compact( 'transaction_id', 'snapshot' );

			// Update queue.
			$this->save_transaction_queue( $transaction_queue );
		}

		return $transaction_queue;
	}

	/**
	 * Pop transaction ID off the top of the queue, ending the transaction.
	 *
	 * @param array                                $payload          The Payload returned from the mutation.
	 * @param array                                $input            The mutation input args, after being filtered by 'graphql_mutation_input'.
	 * @param array                                $unfiltered_input The unfiltered input args of the mutation
	 * @param \WPGraphQL\AppContext                $context          The AppContext object.
	 * @param \GraphQL\Type\Definition\ResolveInfo $info             The ResolveInfo object.
	 * @param string                               $mutation         The name of the mutation field.
	 *
	 * @throws \GraphQL\Error\UserError If transaction ID is not on the top of the queue.
	 *
	 * @return void
	 */
	public function pop_transaction_id( $payload, $input, $unfiltered_input, $context, $info, $mutation ) {
		// Bail if transaction not started.
		if ( is_null( $this->transaction_id ) ) {
			return;
		}

		// Bail if not the expected mutation.
		if ( str_starts_with( $this->transaction_id, "wooSession_{$mutation}_" ) ) {
			return;
		}

		// Get transaction queue.
		$transaction_queue = get_transient( "woo_session_transactions_queue_{$this->session_handler->get_customer_id()}" );

		// Throw if transaction ID not on top.
		if ( $this->transaction_id !== $transaction_queue[0]['transaction_id'] ) {
			throw new UserError( __( 'Woo session transaction executed out of order', 'wp-graphql-woocommerce' ) );
		} else {

			// Remove Transaction ID and update queue.
			array_shift( $transaction_queue );
			$this->save_transaction_queue( $transaction_queue );

			/**
			 * Mark transaction completion
			 * 
			 * @param string|null $transition_id     Removed transaction ID.
			 * @param array       $transaction_queue Transaction Queue.
			 */
			do_action( 'woographql_session_transaction_complete', $this->transaction_id, $transaction_queue );
			
			// Clear transaction ID.
			$this->transaction_id = null;
		}
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
		$transaction_queue = $this->get_transaction_queue();

		// Bail if we don't have a queue to add a timestamp against.
		if ( empty( $transaction_queue[0] ) ) {
			return;
		}

		$transaction_queue[0]['timestamp'] = time();

		$this->save_transaction_queue( $transaction_queue );
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
