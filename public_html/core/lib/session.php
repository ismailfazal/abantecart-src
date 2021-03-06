<?php
/*------------------------------------------------------------------------------
  $Id$

  AbanteCart, Ideal OpenSource Ecommerce Solution
  http://www.AbanteCart.com

  Copyright © 2011-2013 Belavier Commerce LLC

  This source file is subject to Open Software License (OSL 3.0)
  License details is bundled with this package in the file LICENSE.txt.
  It is also available at this URL:
  <http://www.opensource.org/licenses/OSL-3.0>

 UPGRADE NOTE:
   Do not edit or add to this file if you wish to upgrade AbanteCart to newer
   versions in the future. If you wish to customize AbanteCart for your
   needs please refer to http://www.AbanteCart.com for more information.
------------------------------------------------------------------------------*/
if (!defined('DIR_CORE')) {
	header('Location: static_pages/');
}

final class ASession {
	public $data = array();
	public $ses_name = SESSION_ID;

	public function __construct( $ses_name = '' ) {
	
		if (!session_id() || has_value($ses_name)) {
			$this->ses_name = $ses_name;
			$this->init( $this->ses_name );
		}

		$registry = Registry::getInstance();
		if ($registry->get('config')) {
			$session_ttl = $registry->get('config')->get('config_session_ttl');
			if ((isset($_SESSION[ 'user_id' ]) || isset($_SESSION[ 'customer_id' ]))
					&& isset($_SESSION[ 'LAST_ACTIVITY' ]) && ((time() - $_SESSION[ 'LAST_ACTIVITY' ]) / 60 > $session_ttl)
			) {
				// last request was more than 30 minutes ago
				$this->clear();
				header('Location: ' . $registry->get('html')->currentURL( array('token') ) );
			}
		}
		$_SESSION[ 'LAST_ACTIVITY' ] = time(); // update last activity time stamp

		$this->data =& $_SESSION;
	}

	public function init( $session_name ) {
		$path = dirname($_SERVER[ 'PHP_SELF' ]);
		session_set_cookie_params(0,
		    $path,
		    null,
		    (defined('HTTPS') && HTTPS),
		    true);
		session_name( $session_name );
		session_start();
	}

	public function clear() {
		session_name($this->ses_name);
		session_start();
		session_unset();
		session_destroy();
		$_SESSION = array();
	}

}