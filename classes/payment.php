<?php


/**
 * EDD Payment with contextual helper methods
 */
class CF_EDD_Recur_Payment extends EDD_Payment implements CF_EDD_RI_Subscription {


	/**
	 * Meta key for storing renewal charge amount
	 */
	CONST RENEWAL_KEY = 'cf_edd_reccur_renewal_charge';

	/**
	 * Meta key for storing subscription ID
	 */
	const SUB_ID_KEY = 'cf_edd_reccur_sub_id';

	/**
	 * Meta key for storing trial length
	 */
	const SUB_TRIAL_LENGTH_KEY = 'cf_edd_reccur_trial_length';

	/**
	 * Meta key for storing the subscription period
	 */
	const SUB_PERIOD = 'cf_edd_reccur_period';


	/**
	 * @var EDD_Subscription
	 */
	protected $subscription;

	/**
	 * Get the total for this payment
	 *
	 * @return float
	 */
	public function get_amount(){
		return $this->get_total();
	}

	/**
	 * Get the total for this payment	 *
	 * @return float
	 */
	public function get_total(){
		return $this->total;
	}

	/**
	 * Get renewal charge from meta
	 *
	 * @return float|int|string
	 */
	public function get_renewal_charge(){
		$charge = $this->get_meta( self::RENEWAL_KEY );
		if( empty( $charge ) ){
			$charge = round( $this->get_total() / 2, 2, PHP_ROUND_HALF_DOWN  );
		}

		return (float) $charge;

	}

	/**
	 * Set the renewal charge as mets
	 *
	 * @param float|null $charge
	 *
	 * @return bool|int
	 */
	public function set_renewal_charge( $charge = null ){
		if( null === $charge ){
			$charge = $this->get_renewal_charge();
		}

		return $this->update_meta( self::RENEWAL_KEY, $charge );
	}

	/**
	 * @param $id
	 *
	 * @return bool|int
	 */
	public function set_subscription_id( $id ){
		return $this->update_meta( self::SUB_ID_KEY, $id );
	}

	/**
	 * @return mixed
	 */
	public function get_subscription_id(){
		return $this->get_meta( self::SUB_ID_KEY );
	}

	/**
	 * @return EDD_Subscription
	 */
	public function get_subscription(){
		if (  ! $this->subscription ) {
			$this->subscription = new EDD_Subscription( $this->get_subscription_id() );
		}

		return $this->subscription;
	}

	/**
	 * @param int $length
	 *
	 * @return bool|int
	 */
	public function set_trial_length( $length ){
		return $this->update_meta( self::SUB_TRIAL_LENGTH_KEY, $length  );
	}

	/**
	 * @return int|mixed
	 */
	public function get_trial_length(){
		$length = $this->get_meta( self::SUB_TRIAL_LENGTH_KEY );
		if( ! $length   ){
			$length = 1;
		}

		return $length;
	}

	/**
	 * @param string $period Optional. Default is year. Should be "day" "year" or "month"
	 *
	 * @return bool|int
	 */
	public function set_subscription_period( $period = 'year' ){
		return $this->update_meta( self::SUB_PERIOD, $period );
	}

	/**
	 * @return string
	 */
	public function get_subscription_period(){
		$period = $this->get_meta( self::SUB_PERIOD );
		if( ! is_string( $period ) ){
			$period = 'year';
		}

		return $period;
	}

	/**
	 * @param $download_id
	 *
	 * @return EDD_Subscription
	 */
	public function create_subscription( $download_id ){
		$subscriber = new EDD_Recurring_Subscriber( $this->user_id, true );
		$args = array (
			'product_id' => $download_id,
			'user_id' => $this->user_id,
			'parent_payment_id' => $this->ID,
			'status' => 'active',
			'period' => $this->get_subscription_period(),
			'initial_amount' => $this->total,
			'recurring_amount' => $this->get_renewal_charge(),
			'bill_times' => 0,
			'expiration' => date('Y-m-d', strtotime('+1 years')),
		);

		$subscription = $subscriber->add_subscription( $args );
		$this->set_subscription_id( $subscription->ID );
		return $subscription;
	}

	/**
	 * Convert EDD Payment object to object of this class
	 *
	 * @param EDD_Payment $payment
	 *
	 * @return static
	 */
	public static function factory( EDD_Payment $payment ){
		return new static( $payment->ID );
	}




}