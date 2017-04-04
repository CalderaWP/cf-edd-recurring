<?php
/**
 Plugin Name: Caldera Forms EDD Recurring
 Description: Support layer for EDD recurring payments for Caldera Forms EDD
 Version: 0.0.4.1
 */
//CF58c9fd77084c3
/**
 * @param string $form_id Form ID
 * @param string $type Optional. Processor type. stripe|paypal Default is stripe.
 *
 * @return bool
 */
function cf_edd_recurr_use( $form_id, $type ){
	if( class_exists( 'EDD_Recurring_Subscriber' ) && function_exists( 'cf_edd_pro_init' )  ){
		return cf_edd_recurr_use_form( $form_id, $type );
	}

	return false;

}

/**
 * Check if a form should be used, by type
 *
 * @since 0.0.1
 *
 * @param string $form_id Form ID
 * @param string $type Optional. Processor type. stripe|paypal Default is stripe.
 *
 * @return bool
 */
function cf_edd_recurr_use_form( $form_id, $type = 'stripe'){
	switch ($type ){
		case 'stripe' :
			if( ! defined( 'EDD_STRIPE_VERSION'  ) ){
				return false;
			}
			break;
		case 'paypal' :

			break;
		default:
			return false;
		break;
	}
	return in_array( $form_id, apply_filters( "cf_edd_recurr_$type", array() ) );
}

/**
 * Register autoloader
 */
add_action( 'caldera_forms_includes_complete', function(){
	Caldera_Forms_Autoloader::add_root( 'CF_EDD_Recur', __DIR__ . '/classes' );
	Caldera_Forms_Autoloader::add_root( 'CF_EDD_RI', __DIR__ . '/classes/interfaces' );
});


/**
 * Steal token from CF-Stripe and process directly
 */

add_filter( 'cf_stripe_pre_payment', [ 'CF_EDD_Recur_Control', 'stripe_pre_pay' ], 10, 5 );

/**
 * After CF EDD creates payment, setup subscription with EDD
 */
add_action( 'cf_cf_edd_pro_payment_created', [ 'CF_EDD_Recur_Control', 'post_purchase_create' ], 10, 6 );


