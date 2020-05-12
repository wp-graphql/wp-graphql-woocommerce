<?php 
$I = new AcceptanceTester($scenario);
$I->createOldSession();

// Begin Tests.
$I->wantTo('login');
$login_input = array(
    'clientMutationId' => 'someId',
    'username'         => 'jimbo1234',
    'password'         => 'password',
);

$success = $I->login( $login_input );

$I->assertArrayNotHasKey( 'errors', $success );
$I->assertArrayHasKey('data', $success );
$I->assertArrayHasKey('login', $success['data'] );
$I->assertArrayHasKey('customer', $success['data']['login'] );
$I->assertArrayHasKey('authToken', $success['data']['login'] );
$I->assertArrayHasKey('refreshToken', $success['data']['login'] );
$authToken   = $success['data']['login']['authToken'];
$customer_id = $success['data']['login']['customer']['customerId'];

$I->wantTo('Get current username');
$query = '
    query {
        customer {
            customerId
            username
        }
    }
';

$success = $I->sendGraphQLRequest(
    $query,
    null,
    array( 'Authorization' => "Bearer {$authToken}" )
);

$expected_results = array(
    'data' => array(
        'customer' => array(
            'customerId' => $customer_id,
            'username'   => 'jimbo1234'
        )
    )
);

$I->assertEquals( $expected_results, $success );