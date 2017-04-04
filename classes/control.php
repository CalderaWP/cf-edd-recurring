<?php


/**
 * Hook into add-ons to change how they process payments
 */
class CF_EDD_Recur_Control {

	/**
	 * @param EDD_Payment $payment
	 * @param Caldera_Forms_Processor_Get_Data $data_object
	 * @param array $config
	 * @param array $form
	 */
	public static function post_purchase_create( $payment, $data_object, $config, $form, $download_id, $processid ){
		if(  cf_edd_recurr_use( $form[ 'ID' ], 'stripe' ) && is_object( $payment ) ){
			$saved_data = CF_EDD_Recur_Data::get( $processid );
			if( is_object( $saved_data ) ){
				$payment = CF_EDD_Recur_Payment::factory( $payment );
				$payment->set_renewal_charge( $saved_data->get_renewal_charge() );
				$payment->set_subscription_period( $saved_data->get_subscription_period() );
				$payment->set_trial_length( $saved_data->get_trial_length() );
				$payment->set_profile_id( $saved_data->get_profile_id() );

				$creator = new CF_EDD_Recur_Subscription( $payment, $download_id );
				$creator->create();
			}else{
				$x= 1;
			}


		}
	}

	public static function stripe_pre_pay( $return, $token, $config, $form, $process_id ){
		if( ! cf_edd_recurr_use( $form[ 'ID' ], 'stripe' ) ){
			return $return;
		}

		EDD_Recurring();

		$recur = new CF_EDD_Recur_Stripe();
		$recur->set_token( $token );
		$recur->setup_form_cf( $config, $form );

		$user = get_user_by( 'ID', get_current_user_id() );
		$customer = new EDD_Customer( $user->user_email );
		$created = $recur->create_payment_profiles( $customer->id );
		if( is_string( $created ) ){
			$return = array(
				'type' => 'error',
				'note' => $created
			);
		}elseif ( true === $created ){
			$return = true;
		}

		$meta = CF_EDD_Recur_Data::from_data( $recur );
		$meta->save( $process_id );

		global  $transdata;
		$transdata['stripe'] = $recur->get_customer();

		return $return;

	}

}