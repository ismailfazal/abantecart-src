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
if (! defined ( 'DIR_CORE' )) {
	header ( 'Location: static_pages/' );
}
class ControllerPagesCheckoutSuccess extends AController {
	public function main() {

        //init controller data
        $this->extensions->hk_InitData($this,__FUNCTION__);

		if (isset($this->session->data['order_id'])) {

			$amount = $this->session->data['used_balance']; // in default currency
			if($amount){
				$transaction_data = array(
							'order_id'=>(int)$this->session->data['order_id'],
							'amount' => $amount,
							'transaction_type'=>'order',
							'created_by' => $this->customer->getId(),
							'description' => sprintf($this->language->get('text_applied_balance_to_order'),
							$this->currency->format($this->currency->convert($amount,$this->config->get('config_currency'), $this->session->data['currency']),$this->session->data['currency'],1),
							(int)$this->session->data['order_id']));
				$this->customer->creditTransaction($transaction_data);
			}

			$this->cart->clear();
			
			unset(  $this->session->data['shipping_method'],
					$this->session->data['shipping_methods'],
					$this->session->data['payment_method'],
					$this->session->data['payment_methods'],
					$this->session->data['guest'],
					$this->session->data['comment'],
					$this->session->data['order_id'],
					$this->session->data['coupon'],
					$this->session->data['used_balance']);
		}
									   
		$this->document->setTitle( $this->language->get('heading_title') );
		
		$this->document->resetBreadcrumbs(); 

      	$this->document->addBreadcrumb( array ( 
        	'href'      => $this->html->getURL('index/home'),
        	'text'      => $this->language->get('text_home'),
        	'separator' => FALSE
      	 )); 

		
      	$this->document->addBreadcrumb( array ( 
        	'href'      => $this->html->getURL('checkout/cart'),
        	'text'      => $this->language->get('text_basket'),
        	'separator' => $this->language->get('text_separator')
      	 ));
		
		if ($this->customer->isLogged()) {
			$this->document->addBreadcrumb( array ( 
				'href'      => $this->html->getURL('checkout/shipping'),
				'text'      => $this->language->get('text_shipping'),
				'separator' => $this->language->get('text_separator')
			 ));
	
			$this->document->addBreadcrumb( array ( 
				'href'      => $this->html->getURL('checkout/payment'),
				'text'      => $this->language->get('text_payment'),
				'separator' => $this->language->get('text_separator')
			 ));
	
			$this->document->addBreadcrumb( array ( 
				'href'      => $this->html->getURL('checkout/confirm'),
				'text'      => $this->language->get('text_confirm'),
				'separator' => $this->language->get('text_separator')
			 ));
		} else {
			$this->document->addBreadcrumb( array ( 
				'href'      => $this->html->getURL('checkout/guest'),
				'text'      => $this->language->get('text_guest'),
				'separator' => $this->language->get('text_separator')
			 ));
	
			$this->document->addBreadcrumb( array ( 
				'href'      => $this->html->getURL('checkout/guest/confirm'),
				'text'      => $this->language->get('text_confirm'),
				'separator' => $this->language->get('text_separator')
			 ));			
		}
		
      	$this->document->addBreadcrumb( array ( 
        	'href'      => $this->html->getURL('checkout/success'),
        	'text'      => $this->language->get('text_success'),
        	'separator' => $this->language->get('text_separator')
      	 ));
		
    	$this->view->assign('heading_title', $this->language->get('heading_title'));
		if($this->session->data['account']=='guest'){

			$this->view->assign('text_message',
								sprintf( $this->language->get('text_message_guest'),$this->html->getURL('content/contact')));
		}else{
			$this->view->assign('text_message',
								sprintf( $this->language->get('text_message'),
										$this->html->getSecureURL('account/account'),
										$this->html->getSecureURL('account/history'),
										$this->html->getURL('content/contact')));
		}
    	$this->view->assign('button_continue', $this->language->get('button_continue'));
    	$this->view->assign('continue', $this->html->getURL('index/home'));
		$continue = HtmlElementFactory::create( array ('type' => 'button',
		                                               'name' => 'continue_button',
			                                           'text'=> $this->language->get('button_continue'),
			                                           'style' => 'button'));
		$this->view->assign('continue_button', $continue);

		$this->processTemplate('common/success.tpl' );

        //init controller data
        $this->extensions->hk_UpdateData($this,__FUNCTION__);
  	}
}
