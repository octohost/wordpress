<?php

class Ithemes_Sync_Verb_ITSEC_Get_Everything extends Ithemes_Sync_Verb {

	public static $name = 'itsec-get-everything';
	public static $description = 'Retrieve iThemes Security Status and other information.';

	private $default_arguments = array();

	public function run( $arguments ) {

		global $itsec_sync;

		$modules        = $itsec_sync->get_modules();
		$module_results = array();

		//return $modules;

		foreach ( $modules as $name => $module ) {

			if ( isset( $module['verbs'] ) && isset( $module['path'] ) && isset( $module['everything'] ) && isset( $module['verbs'][$module['everything']] ) ) {

				$class = $module['verbs'][$module['everything']];

				if ( ! class_exists( $class ) ) {

					require( trailingslashit( $module['path'] ) . 'class-ithemes-sync-verb-' . $module['everything'] . '.php' );

				}

				$obj = new $class;

				$module_results[$name] = $obj->run( array() );

			}

		}

		return array_merge( array(
			                    'api' => '0',
		                    ),
		                    $module_results
		);

	}

}
