<?php


/**
 * Create payments/plans with Stripe
 */
class CF_EDD_Recur_Stripe  implements CF_EDD_RI_Gateway, CF_EDD_RI_Subscription {

	protected $token;

	/**
	 * Total charge amount, in cents
	 *
	 * @var int
	 */
	protected $amount;

	/**
	 * @var string
	 */
	public $email;

	/** @var  \Stripe\Customer */
	protected $customer;

	/** @var  \Stripe\Plan */
	protected $plan;

	/** @var  \Stripe\Subscription */
	protected $subscription;

	/** @var  \Stripe\Charge */
	protected $charge;

	/** @var string  */
	protected $interval = 'day';

	protected $trial_period_days;

	/**
	 * @var EDD_Customer
	 */
	protected $edd_customer;

	public function get_customer(){
		return $this->customer;
	}

	public function create_payment_profiles( $customer_id ){
		$plan_name = 'Caldera Forms Custom Bundle For ' . $this->email . ' Created on' . date( Caldera_Forms::time_format() );
		try {
			$this->customer = \Stripe\Customer::create( array(
				"description" => $this->email,
				"source"      => $this->token,
				'email' => $this->email,
				'metadata'    => array(
					'edd_customer_id' => $customer_id
				)
			) );
		} catch ( Exception $e ) {
			return $e->getMessage();
		}

		try {
			$this->charge = \Stripe\Charge::create( [
				'amount'   => $this->amount,
				'description' => $plan_name . ' Initial Charge',
				'currency' => 'usd',
				'customer' => $this->customer->id,
			] );
		} catch ( Exception $e ) {
			return $e->getMessage();
		}

		$this->trial_period_days = 365;
		$this->trial_period_days = 1;

		try {
			$this->plan = \Stripe\Plan::retrieve( sanitize_title_with_dashes( $plan_name ) );
		} catch ( Exception $e ) {
			try {
				$this->plan = \Stripe\Plan::create( array(
					"amount"            => $this->amount,
					"interval"          => $this->interval,
					"name"              => $plan_name,
					"currency"          => "usd",
					'trial_period_days' => $this->trial_period_days,
					"id"                => sanitize_title_with_dashes( $plan_name ),
					'statement_descriptor' => 'cf-custom-bundle'
				) );
			} catch ( Exception $e ) {
				return $e->getMessage();
			}

		}

		try {
			$this->subscription = \Stripe\Subscription::create( array(
				'plan'     => $this->plan->id,
				'customer' => $this->customer->id
			) );
		} catch ( Exception $e ) {
			return $e->getMessage();
		}


		return true;

	}

	/**
	 * @return \Stripe\Subscription
	 */
	public function get_subscription(){
		return $this->subscription;
	}

	/**
	 * @param $token
	 */
	public function set_token( $token ){
		$this->token = $token;
	}

	/**
	 *
	 *
	 * @param array $config Processor configuration.
	 * @param array $form Form configuration.
	 */
	public function setup_form_cf( array $config, array $form ){
		\Stripe\Stripe::setApiKey($config['secret']);
		$this->amount = Caldera_Forms::get_field_data( $config['amount'], $form ) * 100;
		$this->amount = round( $this->amount / 2, 2, PHP_ROUND_HALF_DOWN );

	}

	public function get_amount(){
		//needs to be put back to dollars instead of cents
		return $this->amount / 100;
	}

	public function get_subscription_period(){
		return $this->interval;
	}

	public function get_tri(){
		return $this->trial_period_days;
	}

	public function get_trial_length(){
		return $this->trial_period_days;
	}

	public function get_renewal_charge(){
		return $this->get_amount();
	}

}