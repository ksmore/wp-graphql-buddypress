<?php

/**
 * Test_Friendship_Delete_Mutation Class.
 *
 * @group friends
 */
class Test_Friendship_Delete_Mutation extends WP_UnitTestCase {

	public static $bp_factory;
	public static $user;
	public static $bp;
	public static $client_mutation_id;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::$bp                 = new BP_UnitTestCase();
		self::$bp_factory         = new BP_UnitTest_Factory();
		self::$client_mutation_id = 'someUniqueId';
		self::$user               = self::factory()->user->create();
	}

	public function test_initiator_withdraw_friendship() {
		$u1 = self::$bp_factory->user->create();
		$u2 = self::$bp_factory->user->create();

		$this->create_friendship_object( $u1, $u2 );

		self::$bp->set_current_user( $u1 );

		$mutation = $this->delete_friendship( $u1, $u2 );

		$this->assertEquals(
			[
				'data' => [
					'deleteFriendship' => [
						'clientMutationId' => self::$client_mutation_id,
						'deleted' => true,
						'friendship' => [
							'initiator' => [
								'userId' => $u1,
							],
							'friend' => [
								'userId' => $u2,
							],
						],
					],
				],
			],
			do_graphql_request( $mutation[0], 'deleteFriendshipTest', $mutation[1] )
		);
	}

	public function test_friend_reject_friendship() {
		$u1 = self::$bp_factory->user->create();
		$u2 = self::$bp_factory->user->create();

		$this->create_friendship_object( $u1, $u2 );

		self::$bp->set_current_user( $u2 );

		$mutation = $this->delete_friendship( $u1, $u2 );

		$this->assertEquals(
			[
				'data' => [
					'deleteFriendship' => [
						'clientMutationId' => self::$client_mutation_id,
						'deleted' => true,
						'friendship' => [
							'initiator' => [
								'userId' => $u1,
							],
							'friend' => [
								'userId' => $u2,
							],
						],
					],
				],
			],
			do_graphql_request( $mutation[0], 'deleteFriendshipTest', $mutation[1] )
		);
	}

	public function test_delete_friendship_with_user_not_logged_in() {
		$u1 = self::$bp_factory->user->create();
		$u2 = self::$bp_factory->user->create();

		$this->create_friendship_object( $u1, $u2 );

		$mutation = $this->delete_friendship( $u1, $u2 );
		$response = do_graphql_request( $mutation[0], 'deleteFriendshipTest', $mutation[1] );

		$this->assertArrayHasKey( 'errors', $response );
		$this->assertSame( 'Sorry, you do not have permission to perform this action.', $response['errors'][0]['message'] );
	}

	public function test_user_can_not_delete_or_reject_other_user_friendship_request() {
		$u1 = self::$bp_factory->user->create();
		$u2 = self::$bp_factory->user->create();
		$u3 = self::$bp_factory->user->create();

		$this->create_friendship_object( $u1, $u2 );

		self::$bp->set_current_user( $u3 );

		$mutation = $this->delete_friendship( $u1, $u2 );
		$response = do_graphql_request( $mutation[0], 'deleteFriendshipTest', $mutation[1] );

		$this->assertArrayHasKey( 'errors', $response );
		$this->assertSame( 'Sorry, you do not have permission to perform this action.', $response['errors'][0]['message'] );
	}

	public function test_delete_with_invalid_users() {
		$u1 = self::$bp_factory->user->create();
		$u2 = self::$bp_factory->user->create();

		$this->create_friendship_object( $u1, $u2 );

		self::$bp->set_current_user( $u1 );

		// Invalid friend.
		$mutation = $this->delete_friendship( $u1, 111 );
		$response = do_graphql_request( $mutation[0], 'deleteFriendshipTest', $mutation[1] );

		$this->assertArrayHasKey( 'errors', $response );
		$this->assertSame( 'There was a problem confirming if user is valid.', $response['errors'][0]['message'] );

		self::$bp->set_current_user( $u2 );

		// Invalid initiator.
		$mutation = $this->update_friendship( 111, $u2 );
		$response = do_graphql_request( $mutation[0], 'deleteFriendshipTest', $mutation[1] );

		$this->assertArrayHasKey( 'errors', $response );
		$this->assertSame( 'There was a problem confirming if user is valid.', $response['errors'][0]['message'] );
	}

	protected function delete_friendship( $initiator, $friend ) {
		$mutation = '
		mutation deleteFriendshipTest( $clientMutationId: String!, $initiatorId: Int!, $friendId: Int! ) {
			deleteFriendship(
				input: {
					clientMutationId: $clientMutationId
					initiatorId: $initiatorId
					friendId: $friendId
				}
			)
          	{
				clientMutationId
				deleted
		    	friendship {
					initiator {
						userId
					}
					friend {
						userId
					}
		    	}
          	}
        }
		';

		$variables = wp_json_encode(
			[
				'clientMutationId' => self::$client_mutation_id,
				'initiatorId'      => $initiator,
				'friendId'         => $friend,
			]
		);

		return [ $mutation, $variables ];
	}

	protected function update_friendship( $initiator, $friend ) {
		$mutation = '
		mutation updateFriendshipTest( $clientMutationId: String!, $initiatorId: Int!, $friendId: Int! ) {
			updateFriendship(
				input: {
					clientMutationId: $clientMutationId
					initiatorId: $initiatorId
					friendId: $friendId
				}
			)
          	{
				clientMutationId
		    	friendship {
					isConfirmed
					initiator {
						userId
					}
					friend {
						userId
					}
		    	}
          	}
        }
		';

		$variables = wp_json_encode(
			[
				'clientMutationId' => self::$client_mutation_id,
				'initiatorId'      => $initiator,
				'friendId'         => $friend,
			]
		);

		return [ $mutation, $variables ];
	}

	protected function create_friendship_object( $u = 0, $a = 0 ) {
		if ( empty( $u ) ) {
			$u = self::factory()->user->create();
		}

		if ( empty( $a ) ) {
			$a = self::factory()->user->create();
		}

		$friendship                    = new BP_Friends_Friendship();
		$friendship->initiator_user_id = $u;
		$friendship->friend_user_id    = $a;
		$friendship->is_confirmed      = 0;
		$friendship->date_created      = bp_core_current_time();
		$friendship->save();

		return $friendship->id;
	}
}
