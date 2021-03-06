<?php
/**
 * Friendship Model Class
 *
 * @package WPGraphQL\Extensions\BuddyPress\Model
 * @since 0.0.1-alpha
 */

namespace WPGraphQL\Extensions\BuddyPress\Model;

use GraphQLRelay\Relay;
use WPGraphQL\Model\Model;
use WPGraphQL\Utils\Utils;
use BP_Friends_Friendship;

/**
 * Class Friendship - Models the data for the Friendship object type.
 */
class Friendship extends Model {

	/**
	 * Stores the Friendship object for the incoming data.
	 *
	 * @var BP_Friends_Friendship
	 */
	protected $data;

	/**
	 * Friendship constructor.
	 *
	 * @param BP_Friends_Friendship $friendship The BP_Friends_Friendship object.
	 */
	public function __construct( BP_Friends_Friendship $friendship ) {
		$this->data = $friendship;
		parent::__construct();
	}

	/**
	 * Initialize the Friendship object.
	 */
	protected function init() {
		if ( empty( $this->fields ) ) {
			$this->fields = [
				'id' => function() {
					return ! empty( $this->data->id )
						? Relay::toGlobalId( 'friendship', $this->data->id )
						: null;
				},
				'friendshipId' => function() {
					return $this->data->id ?? null;
				},
				'initiator' => function() {
					return $this->data->initiator_user_id ?? null;
				},
				'friend' => function() {
					return $this->data->friend_user_id ?? null;
				},
				'isConfirmed' => function() {
					return $this->data->is_confirmed ?? null;
				},
				'dateCreated' => function() {
					return Utils::prepare_date_response( $this->data->date_created );
				},
			];
		}
	}
}
