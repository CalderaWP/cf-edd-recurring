<?php

/**
 * Integrations with for payment gateways should implement this
 */
interface CF_EDD_RI_Gateway {

	/**
	 * @param array $config Processor config
	 * @param array $form Form config
	 */
	public function setup_form_cf( array $config, array $form );

}