<?php


/**
 * Class EDD_Recur_Stripe
 */
class EDD_Recur_Stripe  extends EDD_Recurring_Stripe {

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

	protected $renewal_rate = 2;
	public function create_payment_profiles(  ){
		$plan_name = 'Caldera Forms Custom Bundle For ' . $this->email . ' Created on' . date( Caldera_Forms::time_format() );
		try {
			$this->customer = \Stripe\Customer::create( array(
				"description" => $this->email,
				"source"      => $this->token,
				'email' => $this->email,
				'metadata'    => array(
					'edd_customer_id' => $this->customer_id
				)
			) );
		} catch ( Exception $e ) {
			return $e->getMessage();
		}

		$recurring_charge =  round( $this->charge / $this->renewal_rate, 2, PHP_ROUND_HALF_DOWN  );

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

		try {
			$this->plan = \Stripe\Plan::retrieve( sanitize_title_with_dashes( $plan_name ) );
		} catch ( Exception $e ) {
			try {
				$this->plan = \Stripe\Plan::create( array(
					"amount"            => $recurring_charge,
					"interval"          => $this->day,
					"name"              => $plan_name,
					"currency"          => "usd",
					'trial_period_days' => 365,
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
	 * @param $stripe_token
	 */
	public function set_token( $stripe_token ){
		$this->token = $stripe_token;
	}

	/**
	 *
	 *
	 * @param array $config Processor configuration.
	 * @param array $form Form configuration.
	 */
	public function setup_form_cf($config, $form ){
		$this->amount = Caldera_Forms::get_field_data( $config['amount'], $form ) * 100;
		$this->amount =  round( $this->amount / $this->renewal_rate, 2, PHP_ROUND_HALF_DOWN  );
		$this->email = Caldera_Forms::get_field_data( $config['email'], $form );
		$this->purchase_data[ 'purchase_email' ] = $this->email;
		$this->purchase_data[ 'user_email' ] = $this->email;
		$this->purchase_data[ 'purchase_key' ] = rand();
	}




}