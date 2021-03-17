<?php

namespace Give\PaymentGateways\Stripe\Repository;

/**
 * Class AccountDetail
 *
 * @package Give\PaymentGateways\Stripe\Repository
 * @unreleased
 */
class AccountDetail {
	/**
	 * Return Stripe account id for donation form.
	 *
	 * @unreleased
	 * @param int $formId
	 *
	 * @return \Give\PaymentGateways\Stripe\Models\AccountDetail
	 */
	public function getDonationFormStripeAccountId( $formId ) {
		// Global Stripe account.
		$accountId = give_get_option( '_give_stripe_default_account', '' );

		// Return default Stripe account of the form, if enabled.
		$formHasStripeAccount = give_is_setting_enabled( give_get_meta( $formId, 'give_stripe_per_form_accounts', true ) );
		if ( $formId > 0 && $formHasStripeAccount ) {
			$accountId = give_get_meta( $formId, '_give_stripe_default_account', true );
		}

		$accountDetail = array_filter(
			give_stripe_get_all_accounts(),
			static function ( $data ) use ( $accountId ) {
				return $data['account_id'] === $accountId;
			}
		);

		$accountDetail = \Give\PaymentGateways\Stripe\Models\AccountDetail::fromArray( $accountDetail );

		return $accountDetail;
	}
}