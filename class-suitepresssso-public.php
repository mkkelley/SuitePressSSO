<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       mailto:joshuaslaven42@gmail.com
 * @since      1.0.0
 *
 * @package    Suitepresssso
 * @subpackage Suitepresssso/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Suitepresssso
 * @subpackage Suitepresssso/public
 * @author     Joshua Slaven <joshuaslaven42@gmail.com>
 */
class Suitepresssso_Public {

	/**
	 * The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	public function login_redirect( $redirect_to, $request, $user ) {
		//is there a user to check?
		if ( isset( $user->roles ) && is_array( $user->roles ) ) {
			//check for admins
			if ( in_array( 'administrator', $user->roles ) ) {
				// redirect them to the default place
				return $redirect_to;
			} else if ( $redirect_to != null && $redirect_to != admin_url() ) {
				return $redirect_to;
			} else {
				return home_url();
			}
		} else {
			return $redirect_to;
		}
	}

	public function filter_members_pages() {
		if ( is_user_logged_in() ) {
			return; // User is logged in and can view any page
		} else if ( is_page() && get_post_meta( get_the_ID(), '_iagcms_members', true ) == "yes" ) {
			wp_redirect( wp_login_url( get_permalink( get_the_ID() ) ) );
			exit;
		} else {
			return; // Not a page or not members only, pass through
		}
	}

	public function remove_admin_bar() {
		if ( ! current_user_can( 'administrator' ) && ! is_admin() ) {
			show_admin_bar( false );
		}
	}

	private function membersuite_api_login() {
		$api = new MemberSuite();

		$api->accesskeyId          = Userconfig::read( 'AccessKeyId' );
		$api->associationId        = Userconfig::read( 'AssociationId' );
		$api->secretaccessId       = Userconfig::read( 'SecretAccessKey' );
		$api->signingcertificateId = Userconfig::read( 'SigningcertificateId' );

		return $api;
	}

	public function authenticate( $user, $username, $password ) {
		// Make sure a username and password are present for us to work with
		if ( $username == '' || $password == '' ) {
			return;
		}

		if ( Userconfig::read( 'WPUsers' ) !== null ) {
			return $user;
		}

		$api = $this->membersuite_api_login();

		if ( is_null( $api ) ) {
			return $user;
		}
		$api->portalusername = $username;
		$api->portalPassword = $password;

		// Verify username and password
		$response = $api->LoginToPortal( $api->portalusername, $api->portalPassword );

		if ( $response->aSuccess == 'false' ) {
			$loginarr = $response->aErrors->bConciergeError->bMessage;
			$user     = new WP_Error( 'denied', __( $loginarr ) );
		} else {
			// Good login, verrify WP side.
			$msUser = new msUser( $response->aResultValue->aPortalEntity );

			// External user exists, try to load the user info from the WordPress user table
			$userobj = new WP_User();
			$user    = $userobj->get_data_by( 'email', $msUser->EmailAddress ); // Does not return a WP_User object 🙁
			$user    = new WP_User( $user->ID ); // Attempt to load up the user with that ID

			if ( $user->ID == 0 ) {
				// The user does not currently exist in the WordPress user table.
				$userdata    = array(
					'user_email' => $msUser->EmailAddress,
					'user_login' => $username,
					'user_pass'  => $password,
					'first_name' => $msUser->FirstName,
					'last_name'  => $msUser->LastName
				);
				$new_user_id = wp_insert_user( $userdata ); // A new user has been created

				$portal_user_guid = $this->get_ms_portal_user_id_by_indiv_id( $api, $msUser->ID );

				add_user_meta(
					$new_user_id,
					'iagcms_ms_uid',
					$portal_user_guid
				);

				// Load the new user info
				$user = new WP_User ( $new_user_id );
			}
		}

		return $user;
	}

	private function get_ms_portal_user_id_by_indiv_id( $api, $indiv_id ) {
		$query    = "select ID, Owner from PortalUser where Owner='$indiv_id'";
		$response = $api->ExecuteMSQL( $query, "0", "" );
		$result   = $response->aResultValue->aSearchResult->aTable->diffgrdiffgram->NewDataSet;

		return $result->Table->ID;
	}

	public function reset_ms_password( $user, $new_pass ) {
		$api = $this->membersuite_api_login();
		if ( $api == null ) {
			return;
		} else {
			$ms_uid = get_user_meta( $user->ID, 'iagcms_ms_uid', true );
			if ( $ms_uid == "" ) {
				return;
			}
			$result = $api->ResetPassword( $ms_uid, $new_pass );
		}
	}

}
