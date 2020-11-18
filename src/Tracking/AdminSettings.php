<?php
namespace Give\Tracking;

use Give_Admin_Settings;

/**
 * Class AdminSettings
 * @package Give\Tracking
 *
 * @since 2.10.0
 */
class AdminSettings {
	/**
	 * Bootstrap
	 */
	public function boot() {
		add_filter( 'give_get_settings_advanced', [ $this, 'addSettings' ] );
	}

	/**
	 * @param array $settings
	 *
	 * @return mixed
	 */
	public function addSettings( $settings ) {
		if ( ! Give_Admin_Settings::is_setting_page( 'advanced', 'advanced-options' ) ) {
			return $settings;
		}

		array_splice(
			$settings,
			-2,
			0,
			[
				[
					'name'    => __( 'Usage Tracking', 'give' ),
					'label'   => esc_html__( 'Allow usage of GiveWP to be tracked.', 'give' ),
					'desc'    => sprintf(
						'%1$s <a href="%2$s">%3$s</a>',
						esc_html__( 'To opt out, choose "Disabled". Your website remains untracked, and no data will be collected. Read about what usage data is tracked at:', 'give' ),
						'#',
						esc_html__( 'GiveWP.com Usage Tracking Documentation', 'give' )
					),
					'id'      => 'usage_tracking',
					'type'    => 'radio_inline',
					'default' => 'disabled',
					'options' => [
						'enabled'  => esc_html__( 'Enabled', 'give' ),
						'disabled' => esc_html__( 'Disabled', 'give' ),
					],
				],
			]
		);

		return $settings;
	}
}
