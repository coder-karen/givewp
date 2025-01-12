<?php

namespace Give\DonorDashboards;

use Give\DonorDashboards\Helpers\LocationList;

/**
 * Class App
 * @package Give\DonorDashboards
 *
 * @unreleased
 */
class App {
	/**
	 * @var Profile
	 */
	protected $profile;

	/**
	 * App constructor.
	 */
	public function __construct() {
		$this->profile = new Profile();
	}

	/**
	 * @param array $attributes
	 *
	 * @return string
	 */
	public function getOutput( $attributes ) {

		$url = get_site_url() . '/?give-embed=donor-dashboard';

		if ( isset( $attributes['accent_color'] ) ) {
			$url = $url . '&accent-color=' . urlencode( $attributes['accent_color'] );
		}

		$loader = $this->getIframeLoader( $attributes['accent_color'] );

		return sprintf(
			'<div style="position: relative; max-width: 100%%;"><iframe
				name="give-embed-donor-profile"
				%1$s
				%4$s
				data-autoScroll="%2$s"
				onload="if( \'undefined\' !== typeof Give ) { Give.initializeIframeResize(this) }"
				style="border: 0;visibility: hidden;%3$s"></iframe>%5$s</div>',
			"src=\"{$url}#/dashboard\"",
			true,
			'min-height: 776px; width: 100%; max-width: 100% !important;',
			'',
			$loader
		);
	}

	/**
	 * Get output markup for Donor Dashboard app
	 *
	 * @since 2.10.0
	 *
	 * @param  string  $accentColor
	 *
	 * @return string
	 */
	public function getIframeLoader( $accentColor ) {
		ob_start();

		require $this->getLoaderTemplatePath();

		return ob_get_clean();
	}

	/**
	 * Get output markup for Donor Dashboard app
	 *
	 * @since 2.10.0
	 * @return string
	 */
	public function getIframeContent() {
		ob_start();

		require $this->getTemplatePath();

		return ob_get_clean();
	}

	/**
	 * Get template path for Donor Dashboard component template
	 * @since 2.10.0
	 **/
	public function getTemplatePath() {
		return GIVE_PLUGIN_DIR . '/src/DonorDashboards/resources/views/donordashboard.php';
	}

	/**
	 * Get template path for Donor Dashboard component template
	 * @since 2.10.0
	 **/
	public function getLoaderTemplatePath() {
		return GIVE_PLUGIN_DIR . '/src/DonorDashboards/resources/views/donordashboardloader.php';
	}

	/**
	 * Enqueue assets for front-end donor dashboards
	 *
	 * @since 2.10.0
	 **@return void
	 */
	public function loadAssets() {
		// Load assets only if rendering donor dashboard.
		if ( ! isset( $_GET['give-embed'] ) || 'donor-dashboard' !== $_GET['give-embed'] ) {
			return;
		}

		wp_enqueue_script(
			'give-donor-dashboards-app',
			GIVE_PLUGIN_URL . 'assets/dist/js/donor-dashboards-app.js',
			[],
			GIVE_VERSION,
			true
		);

		$recaptcha_key     = give_get_option( 'recaptcha_key' );
		$recaptcha_secret  = give_get_option( 'recaptcha_secret' );
		$recaptcha_enabled = ( give_is_setting_enabled( give_get_option( 'enable_recaptcha' ) ) ) && ! empty( $recaptcha_key ) && ! empty( $recaptcha_secret ) ? true : false;

		wp_localize_script(
			'give-donor-dashboards-app',
			'giveDonorDashboardData',
			[
				'apiRoot'              => esc_url_raw( rest_url() ),
				'apiNonce'             => wp_create_nonce( 'wp_rest' ),
				'profile'              => give()->donorDashboard->getProfileData(),
				'countries'            => LocationList::getCountries(),
				'states'               => LocationList::getStates( give()->donorDashboard->getCountry() ),
				'id'                   => give()->donorDashboard->getId(),
				'emailAccessEnabled'   => give_is_setting_enabled( give_get_option( 'email_access' ) ),
				'registeredTabs'       => give()->donorDashboardTabs->getRegisteredIds(),
				'loggedInWithoutDonor' => get_current_user_id() !== 0 && give()->donorDashboard->getId() === null ? true : false,
				'recaptchaKey'         => $recaptcha_enabled ? $recaptcha_key : '',
			]
		);

		wp_enqueue_style(
			'give-google-font-montserrat',
			'https://fonts.googleapis.com/css?family=Montserrat:500,500i,600,600i,700,700i&display=swap',
			[],
			null
		);

		wp_enqueue_style(
			'give-donor-dashboards-app',
			GIVE_PLUGIN_URL . 'assets/dist/css/donor-dashboards-app.css',
			[ 'give-google-font-montserrat' ],
			GIVE_VERSION
		);
	}
}
