<?php


/**
 * This is used to persist payment data between pending and saved data, possibly between sessions, using transients API identified by CF process ID.
 */
class CF_EDD_Recur_Data implements  CF_EDD_RI_Subscription{

	/**
	 * @var float
	 */
	protected  $amount;

	/**
	 * @var string
	 */
	protected $subscription_period;

	/**
	 * @var int
	 */
	protected $trial_length;

	/**
	 * @var float
	 */
	protected $renewal_charge;

	/**
	 * @return float
	 */
	public function get_amount(){
		return $this->amount;
	}

	/**
	 * @return string
	 */
	public function get_subscription_period(){
		return $this->subscription_period;
	}

	/**
	 * @return int
	 */
	public function get_trial_length(){
		return $this->trial_length;
	}

	/**
	 * @return float
	 */
	public function get_renewal_charge(){
		return $this->renewal_charge;
	}


	/**
	 * @return array
	 */
	public function to_array(){
		return get_object_vars( $this );
	}

	/**
	 * @param $name
	 * @param $value
	 */
	public function __set( $name, $value ){
		if( property_exists( $this, $name ) ){
			$this->$name = $value;
		}
	}

	/**
	 * Save object to transient
	 *
	 * @param string $process_id
	 */
	public function save( $process_id ){
		Caldera_Forms_Transient::set_transient( __CLASS__ . $process_id, $this->to_array() );
	}


	/**
	 * Factory from process ID (gets from transient API)
	 *
	 * @param string $process_id
	 *
	 * @return static
	 */
	public static function get( $process_id ){
		$saved = Caldera_Forms_Transient::get_transient( __CLASS__ . $process_id );
		if( is_array( $saved ) ){
			$obj = new static();
			foreach ( $saved as $prop => $value ){
				$obj->$prop = $value;
			}

			return $obj;
		}


	}

	/**
	 * Factory based on a compatible object (IE One implementing CF_EDD_RI_Subscription interface)
	 *
	 * @param CF_EDD_RI_Subscription $data_source
	 *
	 * @return static
	 */
	public static function from_data( CF_EDD_RI_Subscription $data_source ){
		$obj = new static();
		$obj->renewal_charge = $data_source->get_renewal_charge();
		$obj->amount = $data_source->get_renewal_charge();
		$obj->trial_length = $data_source->get_trial_length();
		$obj->subscription_period = $data_source->get_subscription_period();

		return $obj;
	}

}