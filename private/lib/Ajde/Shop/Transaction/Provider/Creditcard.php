<?php

class Ajde_Shop_Transaction_Provider_Creditcard extends Ajde_Shop_Transaction_Provider_Paypal
{
    public function getName() {
		return 'Creditcard';
	}
	
	public function getLogo() {
		return MEDIA_DIR . '_core/shop/creditcard.png';
	}
}