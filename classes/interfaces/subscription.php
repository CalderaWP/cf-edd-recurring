<?php

/**
 * Anything the deals with subscriptions -- gateways or subscription describing objects should impliment this.
 */
interface CF_EDD_RI_Subscription {

	/**
	 * @return float
	 */
	public function get_amount();

	/**
	 * @return string
	 */
	public function get_subscription_period();

	/**
	 * @return string
	 */
	public function get_trial_length();

	/**
	 * @return float
	 */
	public function get_renewal_charge();

	/**
	 * @return int|string
	 */
	public function get_profile_id();


}