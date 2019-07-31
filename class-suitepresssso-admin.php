<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       mailto:joshuaslaven42@gmail.com
 * @since      1.0.0
 *
 * @package    Suitepresssso
 * @subpackage Suitepresssso/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Suitepresssso
 * @subpackage Suitepresssso/admin
 * @author     Joshua Slaven <joshuaslaven42@gmail.com>
 */
class Suitepresssso_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	private $suitepress_sso_options;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Add yes/no selector on the page edit screen
	 */
	public function add_members_only_selector() {
		add_meta_box( 'iagcms_meta_box', 'Member Selector', [ self::class, 'meta_html' ], 'page' );
	}

	/**
	 * Add the form field on the page edit screen
	 *
	 * @param $post
	 */
	public static function meta_html( $post ) {
		$value = get_post_meta( $post->ID, '_iagcms_members', true );
		?>
        <label for="iagcms_members">Members Only</label>
        <select name="iagcms_members" id="iagcms_members">
            <option value="no" <?php selected( $value, 'no' ) ?>>No</option>
            <option value="yes" <?php selected( $value, 'yes' ) ?>>Yes</option>
        </select>
		<?php
	}

	public function save_members_only_information( $post_id ) {
		if ( array_key_exists( 'iagcms_members', $_POST ) ) {
			update_post_meta(
				$post_id,
				'_iagcms_members',
				$_POST['iagcms_members']
			);
		}
	}

	public function admin_menu() {
		add_options_page(
			'Suitepress SSO',
			'Suitepress SSO',
			'manage_options',
			'suitepress-sso',
			array( $this, 'spsso_options_page' )
		);
	}

	public function spsso_options_page() {
		$this->suitepress_sso_options = get_option( 'suitepress_sso_option_name' );
		?>
        <div class="wrap">
            <h2>Suitepress SSO</h2>
            <p>MemberSuite API settings</p>
			<?php settings_errors(); ?>

            <form method="post" action="options.php">
				<?php
				settings_fields( 'suitepress_sso_option_group' );
				do_settings_sections( 'suitepress-sso-admin' );
				submit_button();
				?>
            </form>
        </div>
		<?php
	}

	public function suitepress_sso_page_init() {
		register_setting(
			'suitepress_sso_option_group', // option_group
			'suitepress_sso_option_name', // option_name
			array( $this, 'suitepress_sso_sanitize' ) // sanitize_callback
		);

		add_settings_section(
			'suitepress_sso_setting_section', // id
			'Settings', // title
			array( $this, 'suitepress_sso_section_info' ), // callback
			'suitepress-sso-admin' // page
		);

		add_settings_field(
			'accesskeyid_0', // id
			'AccessKeyId', // title
			array( $this, 'accesskeyid_0_callback' ), // callback
			'suitepress-sso-admin', // page
			'suitepress_sso_setting_section' // section
		);

		add_settings_field(
			'associationid_1', // id
			'AssociationId', // title
			array( $this, 'associationid_1_callback' ), // callback
			'suitepress-sso-admin', // page
			'suitepress_sso_setting_section' // section
		);

		add_settings_field(
			'secretaccesskey_2', // id
			'SecretAccessKey', // title
			array( $this, 'secretaccesskey_2_callback' ), // callback
			'suitepress-sso-admin', // page
			'suitepress_sso_setting_section' // section
		);

		add_settings_field(
			'signingcertificateid_3', // id
			'SigningcertificateId', // title
			array( $this, 'signingcertificateid_3_callback' ), // callback
			'suitepress-sso-admin', // page
			'suitepress_sso_setting_section' // section
		);

		add_settings_field(
			'singingcertificatexml_4', // id
			'Singing Certificate Xml', // title
			array( $this, 'singingcertificatexml_4_callback' ), // callback
			'suitepress-sso-admin', // page
			'suitepress_sso_setting_section' // section
		);

		add_settings_field(
			'portalurl_5', // id
			'PortalUrl', // title
			array( $this, 'portalurl_5_callback' ), // callback
			'suitepress-sso-admin', // page
			'suitepress_sso_setting_section' // section
		);
	}

	public function suitepress_sso_section_info() {

	}

	public function accesskeyid_0_callback() {
		printf(
			'<input class="regular-text" type="text" name="suitepress_sso_option_name[accesskeyid_0]" id="accesskeyid_0" value="%s">',
			isset( $this->suitepress_sso_options['accesskeyid_0'] ) ? esc_attr( $this->suitepress_sso_options['accesskeyid_0'] ) : ''
		);
	}

	public function associationid_1_callback() {
		printf(
			'<input class="regular-text" type="text" name="suitepress_sso_option_name[associationid_1]" id="associationid_1" value="%s">',
			isset( $this->suitepress_sso_options['associationid_1'] ) ? esc_attr( $this->suitepress_sso_options['associationid_1'] ) : ''
		);
	}

	public function secretaccesskey_2_callback() {
		printf(
			'<input class="regular-text" type="text" name="suitepress_sso_option_name[secretaccesskey_2]" id="secretaccesskey_2" value="%s">',
			isset( $this->suitepress_sso_options['secretaccesskey_2'] ) ? esc_attr( $this->suitepress_sso_options['secretaccesskey_2'] ) : ''
		);
	}

	public function signingcertificateid_3_callback() {
		printf(
			'<input class="regular-text" type="text" name="suitepress_sso_option_name[signingcertificateid_3]" id="signingcertificateid_3" value="%s">',
			isset( $this->suitepress_sso_options['signingcertificateid_3'] ) ? esc_attr( $this->suitepress_sso_options['signingcertificateid_3'] ) : ''
		);
	}

	public function singingcertificatexml_4_callback() {
		printf(
			'<textarea class="large-text" rows="10" name="suitepress_sso_option_name[singingcertificatexml_4]" id="singingcertificatexml_4">%s</textarea>',
			isset( $this->suitepress_sso_options['singingcertificatexml_4'] ) ? $this->suitepress_sso_options['singingcertificatexml_4'] : ''
		);
	}

	public function portalurl_5_callback() {
		printf(
			'<input class="regular-text" type="text" name="suitepress_sso_option_name[portalurl_5]" id="portalurl_5" value="%s">',
			isset( $this->suitepress_sso_options['portalurl_5'] ) ? esc_attr( $this->suitepress_sso_options['portalurl_5'] ) : ''
		);
	}
}

/* 
 * Retrieve this value with:
 * $suitepress_sso_options = get_option( 'suitepress_sso_option_name' ); // Array of All Options
 * $accesskeyid_0 = $suitepress_sso_options['accesskeyid_0']; // AccessKeyId
 * $associationid_1 = $suitepress_sso_options['associationid_1']; // AssociationId
 * $secretaccesskey_2 = $suitepress_sso_options['secretaccesskey_2']; // SecretAccessKey
 * $signingcertificateid_3 = $suitepress_sso_options['signingcertificateid_3']; // SigningcertificateId
 * $singingcertificatexml_4 = $suitepress_sso_options['singingcertificatexml_4']; // singingcertificatexml
 * $portalurl_5 = $suitepress_sso_options['portalurl_5']; // PortalUrl
 */