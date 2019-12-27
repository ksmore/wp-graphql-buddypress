<?php
/**
 * Registers BuddyPress Attachment object.
 *
 * @package \WPGraphQL\Extensions\BuddyPress\Type\Object
 * @since 0.0.1-alpha
 */

namespace WPGraphQL\Extensions\BuddyPress\Type\Object;

/**
 * Attachment Class.
 */
class Attachment {

	/**
	 * Name of the type.
	 *
	 * @var string Type name.
	 */
	public static $type_name = 'Attachment';

	/**
	 * Register the member avatar object.
	 */
	public static function register() {
		register_graphql_object_type(
			self::$type_name,
			[
				'description' => __( 'BuddyPress attachment object used in Avatar and Cover images.', 'wp-graphql-buddypress' ),
				'fields'      => [
					'thumb'          => [
						'type'        => 'String',
						'description' => __( 'URL for the attachment with the thumb size.', 'wp-graphql-buddypress' ),
					],
					'full'          => [
						'type'        => 'String',
						'description' => __( 'URL for the attachment with the full size.', 'wp-graphql-buddypress' ),
					],
				],
			]
		);
	}
}
