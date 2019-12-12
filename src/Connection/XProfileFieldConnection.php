<?php
/**
 * Registers XProfile Field Connections
 *
 * @package \WPGraphQL\Extensions\BuddyPress\Connection
 * @since 0.0.1-alpha
 */

namespace WPGraphQL\Extensions\BuddyPress\Connection;

use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\Extensions\BuddyPress\Data\Factory;

/**
 * Class XProfileFieldConnection
 */
class XProfileFieldConnection {

	/**
	 * Register connections to XProfile Field.
	 */
	public static function register_connections() {

		/**
		 * Register connection from RootQuery to XProfile Groups.
		 */
		register_graphql_connection( self::get_connection_config() );
	}

	/**
	 * This returns a XProfileGroup > XProfileField connection config.
	 *
	 * @param array $args Array of arguments.
	 *
	 * @return array
	 */
	public static function get_connection_config( $args = [] ) {
		$defaults = [
			'fromType'       => 'XProfileGroup',
			'toType'         => 'XProfileField',
			'fromFieldName'  => 'fields',
			'connectionArgs' => self::get_connection_args(),
			'resolveNode'    => function ( $field_id, array $args, AppContext $context ) {
				return Factory::resolve_xprofile_field_object( $field_id, $context );
			},
			'resolve'        => function ( $source, array $args, AppContext $context, ResolveInfo $info ) {
				return Factory::resolve_xprofile_fields_connection( $source, $args, $context, $info );
			},
		];

		return array_merge( $defaults, $args );
	}

	/**
	 * This returns the connection args for the XProfile Groups to the XProfile Fields connection.
	 *
	 * @return array
	 */
	public static function get_connection_args() {
		return [
			'hideEmptyFields'  => [
				'type'        => 'Boolean',
				'description' => __( 'Whether to hide XProfile fields where the user has no provided data.', 'wp-graphql-buddypress' ),
			],
			'excludeFields'  => [
				'type'        => [
					'list_of' => 'Int',
				],
				'description' => __( 'Ensure result set excludes specific fields IDs.', 'wp-graphql-buddypress' ),
			],
		];
	}
}
