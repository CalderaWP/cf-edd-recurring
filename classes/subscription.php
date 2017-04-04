<?php


/**
 * Creates EDD Recurring subscriptions
 */
class CF_EDD_Recur_Subscription {

	/**
	 * @var CF_EDD_Recur_Payment
	 */
	protected $payment;

	/**
	 * @var int
	 */
	protected $download_id;

	public function __construct( CF_EDD_Recur_Payment $payment, $download_id ){
		$this->payment = $payment;
		$this->download_id = $download_id;
	}

	/**
	 * @return EDD_Subscription
	 */
	public function create(){
		//For EDD recurring
		$this->payment->update_meta( '_edd_subscription_payment', true );
		$this->payment->update_meta( '_edd_payment_gateway', 'stripe' );

		$subscription = $this->payment->create_subscription( $this->download_id );
		return $subscription;

	}
}