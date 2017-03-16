<?php
/**
 Plugin Name: Caldera Forms EDD Recurring
 Description: Support layer for EDD recurring payments for Caldera Forms EDD
 Version: 0.0.1
 */


function cf_edd_recurr_use_form( $form_id, $type = 'stripe'){

	return in_array( $form_id, apply_filters( "cf_edd_recurr_$type", array() ) );

}


add_filter( 'cf_stripe_pre_payment', function( $return, $token, $config, $form ){
	//@todo CONDITIONAL FOR FORM!
	include __DIR__ . '/EDD_Recur_Stripe.php';
	$recur = new EDD_Recur_Stripe();
	$recur->set_token( $token );
	$recur->setup_form_cf( $config, $form );
	$created = $recur->create_payment_profiles();
	if( is_string( $created ) ){
		$return = array(
			'type' => 'error',
			'note' => $created
		);
	}elseif ( true === $created ){
		$return = true;
	}

	global  $transdata;
	$transdata['stripe'] = $recur->get_customer();

	return $return;

}, 10, 4 );

/**
 *
 */
add_action( 'cf_cf_edd_pro_payment_created', function( $payment, $data_object, $config, $form ){
	cf_edd_recurr_post_make_payment( $payment, $data_object );
}, 10, 4 );

/**
 * @param EDD_Payment$payment
 * @param Caldera_Forms_Processor_Get_Data $data_object
 */
function cf_edd_recurr_post_make_payment( $payment, $data_object = null ){

	//set as subscription from Stripe
	$payment->update_meta( '_edd_subscription_payment', true );
	$payment->update_meta( '_edd_payment_gateway', 'stripe' );
	$subscriber = new EDD_Recurring_Subscriber( $payment->user_id, true );
	$args = array (
		'product_id' => $data_object->get_value( 'cf-edd-pro-payment-download' ),
		'user_id' => $payment->user_id,
		'parent_payment_id' => $payment->ID,
		'status' => 'active',
		'period' => 'year',
		'initial_amount' => '49.00',
		'recurring_amount' => '29.00',
		'bill_times' => 0,
		'expiration' => date('Y-m-d', strtotime('+1 years')),

	);

	$subscription = $subscriber->add_subscription( $args );
	$x =1;


}

