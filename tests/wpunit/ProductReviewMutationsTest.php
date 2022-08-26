<?php

use GraphQLRelay\Relay;

class ProductReviewMutationsTest extends \Codeception\TestCase\WPTestCase {

	public function setUp(): void {
		// before
		parent::setUp();

		// your set up methods here
		$this->shop_manager = $this->factory->user->create( [ 'role' => 'shop_manager' ] );
		$this->customer     = $this->factory->user->create( [ 'role' => 'customer' ] );
		$this->products     = $this->getModule( '\Helper\Wpunit' )->product();
	}

	private function run_mutation( $mutation_name, $input = null ) {
		$input_type = ucfirst( $mutation_name ) . 'Input!';
		$mutation   = "
            mutation ( \$input: {$input_type} ) {
                {$mutation_name}( input: \$input ) {
                    clientMutationId
                    rating
                    review {
                        content( format: RAW )
                    }
                }
            }
        ";

		$results = graphql(
			[
				'query'     => $mutation,
				'variables' => [ 'input' => $input ],
			]
		);

		// Use --debug flag to view
		codecept_debug( $results );

		return $results;
	}

	// tests
	public function testCreateNewReviewMutation() {
		wp_set_current_user( $this->shop_manager );
		$input = [
			'clientMutationId' => 'some_id',
			'rating'           => 1,
			'commentOn'        => $this->products->create_simple(),
			'content'          => 'It came covered in poop!!!',
			'author'           => 'Rude customer',
			'authorEmail'      => 'rude-guy@example.com',
		];

		$actual   = $this->run_mutation( 'writeReview', $input );
		$expected = [
			'data' => [
				'writeReview' => [
					'clientMutationId' => 'some_id',
					'rating'           => 1.0,
					'review'           => [
						'content' => 'It came covered in poop!!!',
					],
				],
			],
		];
		$this->assertEquals( $expected, $actual );
	}

	public function testUpdateReviewMutation() {
		wp_set_current_user( $this->shop_manager );
		$comment_id = $this->factory()->comment->create(
			[
				'comment_author'       => 'Rude customer',
				'comment_author_email' => 'rude-guy@example.com',
				'comment_post_ID'      => $this->products->create_simple(),
				'comment_content'      => 'It came covered in poop!!!',
				'comment_approved'     => 1,
				'comment_type'         => 'review',
			]
		);
		update_comment_meta( $comment_id, 'rating', 1 );

		$input = [
			'clientMutationId' => 'some_id',
			'rating'           => 5,
			'id'               => Relay::toGlobalId( 'comment', $comment_id ),
			'content'          => 'Turns out it was Nutella. My bad =P',
		];

		$actual   = $this->run_mutation( 'updateReview', $input );
		$expected = [
			'data' => [
				'updateReview' => [
					'clientMutationId' => 'some_id',
					'rating'           => 5.0,
					'review'           => [
						'content' => 'Turns out it was Nutella. My bad =P',
					],
				],
			],
		];
		$this->assertEquals( $expected, $actual );
	}

	public function testDeleteReviewMutation() {
		wp_set_current_user( $this->shop_manager );
		$comment_id = $this->factory()->comment->create(
			[
				'comment_author'       => 'Rude customer',
				'comment_author_email' => 'rude-guy@example.com',
				'comment_post_ID'      => $this->products->create_simple(),
				'comment_content'      => 'It came covered in poop!!!',
				'comment_approved'     => 1,
				'comment_type'         => 'review',
			]
		);
		update_comment_meta( $comment_id, 'rating', 1 );

		$input = [
			'clientMutationId' => 'some_id',
			'id'               => Relay::toGlobalID( 'comment', $comment_id ),
		];

		$actual   = $this->run_mutation( 'deleteReview', $input );
		$expected = [
			'data' => [
				'deleteReview' => [
					'clientMutationId' => 'some_id',
					'rating'           => 1.0,
					'review'           => [
						'content' => 'It came covered in poop!!!',
					],
				],
			],
		];
		$this->assertEquals( $expected, $actual );
	}

	public function testRestoreReviewMutation() {
		wp_set_current_user( $this->shop_manager );
		$comment_id = $this->factory()->comment->create(
			[
				'comment_author'       => 'Rude customer',
				'comment_author_email' => 'rude-guy@example.com',
				'comment_post_ID'      => $this->products->create_simple(),
				'comment_content'      => 'It came covered in poop!!!',
				'comment_approved'     => 1,
				'comment_type'         => 'review',
			]
		);
		update_comment_meta( $comment_id, 'rating', 1 );

		// Trash comment
		wp_delete_comment( $comment_id );

		$input = [
			'clientMutationId' => 'some_id',
			'id'               => Relay::toGlobalID( 'comment', $comment_id ),
		];

		$actual   = $this->run_mutation( 'restoreReview', $input );
		$expected = [
			'data' => [
				'restoreReview' => [
					'clientMutationId' => 'some_id',
					'rating'           => 1.0,
					'review'           => [
						'content' => 'It came covered in poop!!!',
					],
				],
			],
		];
		$this->assertEquals( $expected, $actual );
	}
}
