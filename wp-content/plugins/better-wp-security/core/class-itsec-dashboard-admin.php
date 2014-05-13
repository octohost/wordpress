<?php

/**
 * Brand plugins with iThemes sidebar items in the admin
 *
 * @version 1.0
 */
class ITSEC_Dashboard_Admin {

	function __construct() {

		if ( is_admin() ) {

			$this->initialize();

		}

	}

	/**
	 * Add meta boxes to primary options pages.
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function add_admin_meta_boxes() {

		add_meta_box(
			'itsec_status',
			__( 'Security Status', 'it-l10n-better-wp-security' ),
			array( $this, 'metabox_normal_status' ),
			'toplevel_page_itsec',
			'advanced',
			'core'
		);

		add_meta_box(
			'itsec_system_info',
			__( 'System Information', 'it-l10n-better-wp-security' ),
			array( $this, 'metabox_normal_system_info' ),
			'toplevel_page_itsec',
			'advanced',
			'core'
		);

	}

	/**
	 * Enqueue CSS for iThemes Security dashboard
	 *
	 * @return void
	 */
	public function enqueue_admin_css() {

		wp_enqueue_style( 'itsec_admin_dashboard' );

	}

	/**
	 * Initializes all admin functionality.
	 *
	 * @since 4.0
	 *
	 * @param ITSEC_Core $core The $itsec_core instance
	 *
	 * @return void
	 */
	private function initialize() {

		add_action( 'itsec_add_admin_meta_boxes', array( $this, 'add_admin_meta_boxes' ) );
		add_action( 'wp_ajax_itsec_sidebar', array( $this, 'save_ajax_options' ) );

	}

	/**
	 * Display security status
	 *
	 * @return void
	 */
	public function metabox_normal_status() {

		$statuses = array(
			'safe-high'   => array(),
			'high'        => array(),
			'safe-medium' => array(),
			'medium'      => array(),
			'safe-low'    => array(),
			'low'         => array(),
		);

		$statuses = apply_filters( 'itsec_add_dashboard_status', $statuses );

		echo '<div id="itsec_tabbed_dashboard_content">';
		echo '<ul class="itsec-tabs">';
		echo '<li><a href="#itsec_showall">' . __( 'All', 'it-l10n-better-wp-security' ) . '</a></li>';
		echo '<li><a href="#itsec_high">' . __( 'High', 'it-l10n-better-wp-security' ) . '</a></li>';
		echo '<li><a href="#itsec_medium">' . __( 'Medium', 'it-l10n-better-wp-security' ) . '</a></li>';
		echo '<li><a href="#itsec_low">' . __( 'Low', 'it-l10n-better-wp-security' ) . '</a></li>';
		echo '<li><a href="#itsec_completed">' . __( 'Completed', 'it-l10n-better-wp-security' ) . '</a></li>';
		echo '</ul>';

		// Begin High Priority Tab
		echo '<div id="itsec_high">';
		if ( isset ( $statuses['high'][0] ) ) {

			printf( '<h2>%s</h2>', __( 'High Priority', 'it-l10n-better-wp-security' ) );
			_e( 'These are items that should be secured immediately.', 'it-l10n-better-wp-security' );

			echo '<ul class="statuslist high-priority">';

			if ( isset ( $statuses['high'] ) ) {

				$this->status_loop( $statuses['high'], __( 'Fix it', 'it-l10n-better-wp-security' ), 'primary' );

			}

			echo '</ul>';

		} else {
			echo '<div class="itsec-priority-items-completed">';
			printf( '<h2>%s</h2>', __( 'High Priority', 'it-l10n-better-wp-security' ) );
			printf( '<p>%s</p>', __( 'You have secured all High Priority items.', 'it-l10n-better-wp-security' ) );
			echo '</div>';
		}

		echo '</div>';

		// Begin Medium Priority Tab
		echo '<div id="itsec_medium">';

		if ( isset ( $statuses['medium'][0] ) ) {

			printf( '<h2>%s</h2>', __( 'Medium Priority', 'it-l10n-better-wp-security' ) );
			_e( 'These are medium priority items that should be fixed if no conflicts are present, but they are not critical to the overall security of your site.', 'it-l10n-better-wp-security' );

			echo '<ul class="statuslist medium-priority">';

			if ( isset ( $statuses['medium'] ) ) {

				$this->status_loop( $statuses['medium'], __( 'Fix it', 'it-l10n-better-wp-security' ), 'primary' );

			}

			echo '</ul>';

		} else {
			echo '<div class="itsec-priority-items-completed">';
			printf( '<h2>%s</h2>', __( 'Medium Priority', 'it-l10n-better-wp-security' ) );
			printf( '<p>%s</p>', __( 'You have secured all Medium Priority items.', 'it-l10n-better-wp-security' ) );
			echo '</div>';
		}

		echo '</div>';

		// Begin Low Priority Tab
		echo '<div id="itsec_low">';

		if ( isset ( $statuses['low'][0] ) ) {

			printf( '<h2>%s</h2>', __( 'Low Priority', 'it-l10n-better-wp-security' ) );
			_e( 'These are low priority items that should be secured if, and only if, your plugins or theme do not conflict with their use.', 'it-l10n-better-wp-security' );

			echo '<ul class="statuslist low-priority">';

			if ( isset ( $statuses['low'] ) ) {

				$this->status_loop( $statuses['low'], __( 'Fix it', 'it-l10n-better-wp-security' ), 'primary' );

			}

			echo '</ul>';

		} else {
			echo '<div class="itsec-priority-items-completed">';
			printf( '<h2>%s</h2>', __( 'Low Priority', 'it-l10n-better-wp-security' ) );
			printf( '<p>%s</p>', __( 'You have secured all Low Priority items.', 'it-l10n-better-wp-security' ) );
			echo '</div>';
		}

		echo '</div>';

		// Begin Completed Tab
		echo '<div id="itsec_completed">';

		if ( isset ( $statuses['safe-high'] ) || isset ( $statuses['safe-medium'] ) || isset ( $statuses['safe-low'] ) ) {

			printf( '<h2>%s</h2>', __( 'Completed', 'it-l10n-better-wp-security' ) );
			_e( 'These are items that you have successfully secured.', 'it-l10n-better-wp-security' );

			echo '<ul class="statuslist completed">';

			if ( isset ( $statuses['safe-high'] ) ) {

				$this->status_loop( $statuses['safe-high'], __( 'Edit', 'it-l10n-better-wp-security' ), 'secondary' );

			}

			if ( isset ( $statuses['safe-medium'] ) ) {

				$this->status_loop( $statuses['safe-medium'], __( 'Edit', 'it-l10n-better-wp-security' ), 'secondary' );

			}

			if ( isset ( $statuses['safe-low'] ) ) {

				$this->status_loop( $statuses['safe-low'], __( 'Edit', 'it-l10n-better-wp-security' ), 'secondary' );

			}

			echo '</ul>';

		}

		echo '</div>';

		// Begin Show All Tab
		echo '<div id="itsec_showall">';

		if ( isset ( $statuses['high'][0] ) ) {

			printf( '<h2>%s</h2>', __( 'High Priority', 'it-l10n-better-wp-security' ) );
			_e( 'These are items that should be secured immediately.', 'it-l10n-better-wp-security' );

			echo '<ul class="statuslist high-priority">';

			if ( isset ( $statuses['high'] ) ) {

				$this->status_loop( $statuses['high'], __( 'Fix it', 'it-l10n-better-wp-security' ), 'primary' );

			}

			echo '</ul>';

		} else {
			echo '<div class="itsec-priority-items-completed">';
			printf( '<h2>%s</h2>', __( 'High Priority', 'it-l10n-better-wp-security' ) );
			printf( '<p>%s</p>', __( 'You have secured all High Priority items.', 'it-l10n-better-wp-security' ) );
			echo '</div>';
		}

		if ( isset ( $statuses['medium'][0] ) ) {

			printf( '<h2>%s</h2>', __( 'Medium Priority', 'it-l10n-better-wp-security' ) );
			_e( 'These are items that should be secured if possible however they are not critical to the overall security of your site.', 'it-l10n-better-wp-security' );

			echo '<ul class="statuslist medium-priority">';

			if ( isset ( $statuses['medium'] ) ) {

				$this->status_loop( $statuses['medium'], __( 'Fix it', 'it-l10n-better-wp-security' ), 'primary' );

			}

			echo '</ul>';

		} else {
			echo '<div class="itsec-priority-items-completed">';
			printf( '<h2>%s</h2>', __( 'Medium Priority', 'it-l10n-better-wp-security' ) );
			printf( '<p>%s</p>', __( 'You have secured all Medium Priority items.', 'it-l10n-better-wp-security' ) );
			echo '</div>';
		}

		if ( isset ( $statuses['low'][0] ) ) {

			printf( '<h2>%s</h2>', __( 'Low Priority', 'it-l10n-better-wp-security' ) );
			_e( 'These are items that should be secured if, and only if, your plugins or theme do not conflict with their use.', 'it-l10n-better-wp-security' );

			echo '<ul class="statuslist low-priority">';

			if ( isset ( $statuses['low'] ) ) {

				$this->status_loop( $statuses['low'], __( 'Fix it', 'it-l10n-better-wp-security' ), 'primary' );

			}

			echo '</ul>';

		} else {
			echo '<div class="itsec-priority-items-completed">';
			printf( '<h2>%s</h2>', __( 'Low Priority', 'it-l10n-better-wp-security' ) );
			printf( '<p>%s</p>', __( 'You have secured all Low Priority items.', 'it-l10n-better-wp-security' ) );
			echo '</div>';
		}

		if ( isset ( $statuses['safe-high'] ) || isset ( $statuses['safe-medium'] ) || isset ( $statuses['safe-low'] ) ) {

			printf( '<h2>%s</h2>', __( 'Completed', 'it-l10n-better-wp-security' ) );
			_e( 'These are items that you have successfuly secured.', 'it-l10n-better-wp-security' );

			echo '<ul class="statuslist completed">';

			if ( isset ( $statuses['safe-high'] ) ) {

				$this->status_loop( $statuses['safe-high'], __( 'Edit', 'it-l10n-better-wp-security' ), 'secondary' );

			}

			if ( isset ( $statuses['safe-medium'] ) ) {

				$this->status_loop( $statuses['safe-medium'], __( 'Edit', 'it-l10n-better-wp-security' ), 'secondary' );

			}

			if ( isset ( $statuses['safe-low'] ) ) {

				$this->status_loop( $statuses['safe-low'], __( 'Edit', 'it-l10n-better-wp-security' ), 'secondary' );

			}

			echo '</ul>';

		}

		echo '</div>';
		echo '</div>';

	}

	/**
	 * Display the system information metabox
	 *
	 * @return void
	 */
	public function metabox_normal_system_info() {

		require_once( 'content/system.php' );

	}

	/**
	 * Displays required status array
	 *
	 * @since 4.0
	 *
	 * @param array  $status_array array of statuses
	 * @param string $button_text  string for button
	 * @param string $button_class string for button
	 *
	 * @return void
	 */
	private function status_loop( $status_array, $button_text, $button_class ) {

		foreach ( $status_array as $status ) {

			if ( isset( $status['advanced'] ) && $status['advanced'] === true ) {
				$page = 'advanced';
			} elseif ( isset( $status['pro'] ) && $status['pro'] === true ) {
				$page = 'pro';
			} else {
				$page = 'settings';
			}

			if ( strpos( $status['link'], 'http:' ) === false && strpos( $status['link'], '?page=' ) === false ) {

				$setting_link = '?page=toplevel_page_itsec_' . $page . $status['link'];

			} else {

				$setting_link = $status['link'];

			}

			printf( '<li><p>%s</p><div class="itsec_status_action"><a class="button-%s" href="%s">%s</a></div></li>', $status['text'], $button_class, $setting_link, $button_text );

		}

	}

}