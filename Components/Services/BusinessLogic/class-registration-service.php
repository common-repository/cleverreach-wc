<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Services\BusinessLogic;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Contracts\RegistrationService as Registration_Service_Interface;

/**
 * Class Registration_Service
 *
 * @package CleverReach\WooCommerce\Components\Services\BusinessLogic
 */
class Registration_Service implements Registration_Service_Interface {


	/**
	 * Returns shop owner registration data as base64 encoded json
	 *
	 * @return string base64 encoded json
	 */
	public function getData() {
		$user_data = $this->get_admin_user_data( get_current_user_id() );

		$registration_data = array(
			'email'     => ! empty( $user_data['email'] ) ? $user_data['email'] : '',
			'firstname' => ! empty( $user_data['firstname'] ) ? $user_data['firstname'] : '',
			'lastname'  => ! empty( $user_data['lastname'] ) ? $user_data['lastname'] : '',
			'company'   => ! empty( $user_data['company'] ) ? $user_data['company'] : '',
			'gender'    => '',
			'street'    => ! empty( $user_data['street'] ) ? $user_data['street'] : '',
			'zip'       => ! empty( $user_data['zip'] ) ? $user_data['zip'] : '',
			'city'      => ! empty( $user_data['city'] ) ? $user_data['city'] : '',
			'country'   => ! empty( $user_data['country'] ) ? $user_data['country'] : '',
			'phone'     => ! empty( $user_data['phone'] ) ? $user_data['phone'] : '',
		);

		$json_data = json_encode( $registration_data );
		if ( ! $json_data ) {
			$json_data = '';
		}

		return base64_encode( $json_data );
	}

	/**
	 * Gets administrator data for user with the provided ID.
	 *
	 * @param int $user_id User ID.
	 *
	 * @return array<string,mixed>
	 */
	private function get_admin_user_data( $user_id ) {
		$user     = get_user_by( 'id', $user_id );
		$address1 = get_user_meta( $user_id, 'billing_address_1', true );
		$address2 = get_user_meta( $user_id, 'billing_address_2', true );
		$street   = ( ! empty( $address1 ) ? $address1 : '' )
					. ( ! empty( $address2 ) ? ' ' . $address2 : '' );
		// Country code (ex. DE, FR, IT...).
		$country = get_user_meta( $user_id, 'billing_country', true );

		return array(
			'firstname' => get_user_meta( $user_id, 'first_name', true ),
			'lastname'  => get_user_meta( $user_id, 'last_name', true ),
			'email'     => $user->user_email,
			'company'   => get_user_meta( $user_id, 'billing_company', true ),
			'street'    => $street,
			'zip'       => get_user_meta( $user_id, 'billing_postcode', true ),
			'city'      => get_user_meta( $user_id, 'billing_city', true ),
			'country'   => ! empty( $country ) ? wc()->countries->countries[ $country ] : '',
			'phone'     => get_user_meta( $user_id, 'billing_phone', true ),
		);
	}
}
