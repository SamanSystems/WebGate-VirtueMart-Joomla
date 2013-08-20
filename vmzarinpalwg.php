<?php

 defined('_JEXEC') or die('Direct Access to ' . basename(__FILE__) . ' is not allowed.');
if (!class_exists('vmPSPlugin'))
require(JPATH_VM_PLUGINS . DS . 'vmpsplugin.php');
class plgVmPaymentVmzarinpalwg extends vmPSPlugin {
	public static $_this = false;
	function __construct(& $subject, $config) {
		parent::__construct($subject, $config);
		$this->_loggable = true;
		$this->tableFields = array_keys($this->getTableSQLFields());
		$this->_tablepkey = 'id';
		$this->_tableId = 'id';
			//error_reporting(E_ALL);
           //  ini_set("display_startup_errors","1");
           //  ini_set("display_errors","1");
		$varsToPush = array(
	    'code' => array('', 'char'),
		'mmm' => array('', 'char'),
	    'payment_currency' => array(0, 'int'),
	    'payment_logos' => array('', 'char'),
	    'status_pending' => array('', 'char'),
	    'status_success' => array('', 'char'),
	    'status_canceled' => array('', 'char'),
	    'countries' => array(0, 'char'),
	    'min_amount' => array(0, 'int'),
	    'max_amount' => array(0, 'int'),
	    'cost_per_transaction' => array(0, 'int'),
	    'cost_percent_total' => array(0, 'int'),
	    'tax_id' => array(0, 'int')
		);
		$this->setConfigParameterable($this->_configTableFieldName, $varsToPush);
	}

	public function getVmPluginCreateTableSQL() {

		return $this->createTableSQL('Payment ZarinPal Table');
	}

	function getTableSQLFields() {

		$SQLfields = array(
	    'id' => ' INT(11) unsigned NOT NULL AUTO_INCREMENT ',
	    'virtuemart_order_id' => ' int(1) UNSIGNED DEFAULT NULL',
	    'order_number' => ' char(32) DEFAULT NULL',
	    'virtuemart_paymentmethod_id' => ' mediumint(1) UNSIGNED DEFAULT NULL',
	    'payment_name' => 'varchar(5000)',
	    'payment_order_total' => 'decimal(15,5) NOT NULL DEFAULT \'0.00000\' ',
	    'payment_currency' => 'char(3) ',
	    'cost_per_transaction' => ' decimal(10,2) DEFAULT NULL ',
	    'cost_percent_total' => ' decimal(10,2) DEFAULT NULL ',
	    'tax_id' => ' smallint(1) DEFAULT NULL',
	    'Vmzarinpalwg_custom' => ' varchar(255)  ',
	    'Vmzarinpalwg_response_mc_gross' => ' decimal(10,2) DEFAULT NULL ',
	    'Vmzarinpalwg_response_mc_currency' => ' char(10) DEFAULT NULL',
	    'Vmzarinpalwg_response_invoice' => ' char(32) DEFAULT NULL',
	    'Vmzarinpalwg_response_protection_eligibility' => ' char(128) DEFAULT NULL',
	    'Vmzarinpalwg_response_payer_id' => ' char(13) DEFAULT NULL',
	    'Vmzarinpalwg_response_tax' => ' decimal(10,2) DEFAULT NULL ',
	    'Vmzarinpalwg_response_payment_date' => ' char(28) DEFAULT NULL',
	    'Vmzarinpalwg_response_payment_status' => ' char(50) DEFAULT NULL',
	    'Vmzarinpalwg_response_mc_fee' => ' decimal(10,2) DEFAULT NULL ',
	    'Vmzarinpalwg_response_payer_email' => ' char(128) DEFAULT NULL',
	    'Vmzarinpalwg_response_last_name' => ' char(64) DEFAULT NULL',
	    'Vmzarinpalwg_response_first_name' => ' char(64) DEFAULT NULL',
	    'Vmzarinpalwg_response_business' => '  char(128) DEFAULT NULL',
	    'Vmzarinpalwg_response_receiver_email' => '  char(128) DEFAULT NULL',
	    'Vmzarinpalwg_response_transaction_subject' => ' char(128) DEFAULT NULL',
	    'Vmzarinpalwg_response_residence_country' => ' char(2) DEFAULT NULL',
	    'Vmzarinpalwgresponse_raw' => ' char DEFAULT NULL'
		);
		return $SQLfields;
	}

	function plgVmConfirmedOrder($cart, $order) {

		if (!($method = $this->getVmPluginMethod($order['details']['BT']->virtuemart_paymentmethod_id))) {
			return null; 
		}
		if (!$this->selectedThisElement($method->payment_element)) {
			return false;
		}


		$this->logInfo('plgVmConfirmedOrder order number: ' . $order['details']['BT']->order_number, 'message');

		if (!class_exists('VirtueMartModelOrders'))
		require( JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php' );
		if (!class_exists('VirtueMartModelCurrency'))
		require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'currency.php');

		
		$new_status = '';

		$usrBT = $order['details']['BT'];
		$address = ((isset($order['details']['ST'])) ? $order['details']['ST'] : $order['details']['BT']);
		$this->getPaymentCurrency($method);
		$q = 'SELECT `currency_code_3` FROM `#__virtuemart_currencies` WHERE `virtuemart_currency_id`="' . $method->payment_currency . '" ';
		$db = &JFactory::getDBO();
		$db->setQuery($q);
		$currency_code_3 = $db->loadResult();

		$paymentCurrency = CurrencyDisplay::getInstance($method->payment_currency);
		$totalInPaymentCurrency = round($paymentCurrency->convertCurrencyTo($method->payment_currency, $order['details']['BT']->order_total, false), 2);
		$cd = CurrencyDisplay::getInstance($cart->pricesCurrency);



		$dbValues['order_number'] = $order['details']['BT']->order_number;
		$dbValues['payment_name'] = $this->renderPluginName($method, $order);
		$dbValues['virtuemart_paymentmethod_id'] = $cart->virtuemart_paymentmethod_id;
		$dbValues['vnzarinpal_custom'] = $return_context;
		$dbValues['cost_per_transaction'] = $method->cost_per_transaction;
		$dbValues['cost_percent_total'] = $method->cost_percent_total;
		$dbValues['payment_currency'] = $method->payment_currency;
		$dbValues['payment_order_total'] = $totalInPaymentCurrency;
		$dbValues['tax_id'] = $method->tax_id;
		$this->storePSPluginInternalData($dbValues);
$mmm = $method->mmm;
$transid=$order['details']['BT']->order_number;
$desc =  'شماره فاکتور : '. $transid;

$code = $method->code;
$merchantID = $code;
$amount = round($totalInPaymentCurrency); // مبلغ فاكتور
$callBackUrl = "".JURI::root()."index.php?option=com_virtuemart&view=pluginresponse&task=pluginresponsereceived&on=".$order['details']['BT']->order_number."&pm=".$order['details']['BT']->virtuemart_paymentmethod_id."";
include_once('nusoap.php');
 echo '<h2> در حال انتقال به بانک ......</h2>';
   $client = new nusoap_client('https://de.zarinpal.com/pg/services/WebGate/wsdl', 'wsdl');

	$res = $client->call('PaymentRequest', array(
			array(
					'MerchantID' 	=> $merchantID ,
					'Amount' 		=> $amount ,
					'Description' 	=> $desc ,
					'Email' 		=> '' ,
					'Mobile' 		=> '' ,
					'CallbackURL' 	=> $callBackUrl

					)
	
	
	
	));
if($res->Status == 100){
	$html .= "<form name='vm_zarin_form' Method='post' Action='https://www.zarinpal.com/pg/StartPay/' . $res->Authority . '/'";
    	$html .= "</form>";
	$html.= ' <script type="text/javascript">';
	$html.= ' document.vm_zarin_form.submit();';
	$html.= ' </script>';
}else{
		echo 'ERR:'.$res->Status ;
	}



	

    //	Redirect to URL You can do it also by creating a form
    //Header('Location: https://www.zarinpal.com/pg/StartPay/" . $res->Authority . "/ZarinGate');
	

		// 	2 = don't delete the cart, don't send email and don't redirect
		return $this->processConfirmedOrderPaymentResponse(2, $cart, $order, $html, $dbValues['payment_name'], $new_status);

	}

	function plgVmgetPaymentCurrency($virtuemart_paymentmethod_id, &$paymentCurrencyId) {

		if (!($method = $this->getVmPluginMethod($virtuemart_paymentmethod_id))) {
			return null; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement($method->payment_element)) {
			return false;
		}
		$this->getPaymentCurrency($method);
		$paymentCurrencyId = $method->payment_currency;
	}

	function plgVmOnPaymentResponseReceived(&$html) {


		// the payment itself should send the parameter needed.
		$virtuemart_paymentmethod_id = JRequest::getInt('pm', 0);
		$order_number = JRequest::getVar('on', 0);
		
		$vendorId = 0;
		if (!($method = $this->getVmPluginMethod($virtuemart_paymentmethod_id))) {
			return null; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement($method->payment_element)) {
			return false;
		}
	if (!class_exists('VirtueMartCart'))
	    require(JPATH_VM_SITE . DS . 'helpers' . DS . 'cart.php');
	if (!class_exists('shopFunctionsF'))
	    require(JPATH_VM_SITE . DS . 'helpers' . DS . 'shopfunctionsf.php');
	if (!class_exists('VirtueMartModelOrders'))
	    require( JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php' );
		$Vmzarinpalwg_data = JRequest::getVar('on');
		$payment_name = $this->renderPluginName($method);
$virtuemart_order_id = VirtueMartModelOrders::getOrderIdByOrderNumber($order_number);
		    if ($virtuemart_order_id) {
			if (!class_exists('VirtueMartCart'))
			 
			$cart = VirtueMartCart::getCart();			
$ons = $_GET['on'];
  $authority = $_REQUEST['Authority'];
  $status = $_REQUEST['Status'];

  if ($authority) {

  }

  if ($status== "OK") {
	include_once('nusoap.php');
	$au = $_GET['Authority'];
	$soapclient  = new nusoap_client('https://de.zarinpal.com/pg/services/WebGate/wsdl', 'wsdl');
	$res = $soapclient->call("PaymentVerification", array(
	array(
					'MerchantID'	 => $merchantID ,
					'Authority' 	 => $au ,
					'Amount'	 	=> $amount
				)
	
	));
	//if ( (!$soapclient) OR ($err = $soapclient->getError()) ) {
	   // this is unsucccessfull connection
  //    echo  $err . "<br />" ;

    } else {
	 // $status = 0 ;   // default status
      
      
	  $status = $res->Status;

	  if ($status == 100) {
	   $dbcoupon = JFactory::getDBO();  
      
    $inscoupon = new stdClass();  
      
    $inscoupon->order_status = "C";  
    $inscoupon->order_number = "$ons";    
      
    if($dbcoupon->updateObject("#__virtuemart_orders", $inscoupon, 'order_number')){  
          
        unset($dbcoupon);  
          
    }else{  
          
        echo $dbcoupon->stderr();  
    }  	
								$dbcccwpp =& JFactory::getDBO();
								$dbcccowpp = "select * from `#__virtuemart_orders` where `order_number` = '$ons' AND `order_status` ='C'";
								$dbcccwpp->setQuery($dbcccowpp);
								$dbcccwpp->query();
								$dbcccowpp = $dbcccwpp->loadobject();	
			$opass=	$dbcccowpp->order_pass;	
$vmid=	$dbcccowpp->virtuemart_user_id;			
								$dbcccw =& JFactory::getDBO();
								$dbcccow = "select * from `#__users` where `id` = '$vmid'";
								$dbcccw->setQuery($dbcccow);
								$dbcccw->query();
								$dbcccow = $dbcccw->loadobject();
$mm=$dbcccow->email;							
			$app =& JFactory::getApplication();			
			 $sitename = $app->getCfg('sitename');
				$subject ="".$sitename." - فاکتور خرید";
			    $add = JURI::base()."index.php?option=com_virtuemart&view=orders&layout=details&order_number=" . $ons . "&order_pass=" . $opass ;
				$body = "از خرید شما ممنونیم". '<br />'  . '<b>شناسه خرید شما'. ':</b>' . ' ' . $SaleReferenceId . '<br />' . '<b>شماره فاکتور'. ':</b>' . ' ' . $ons.'<br/>'. '<a href="'. $add.'">نمایش فاکتور</a>';
				$to = array( $mm , $mmm ); 
				$config =& JFactory::getConfig();
				$from = array( 
				$config->getValue( 'config.mailfrom' ),
				$config->getValue( 'config.fromname' ) );
				# Invoke JMail Class
				$mailer = JFactory::getMailer();
				 
				# Set sender array so that my name will show up neatly in your inbox
				$mailer->setSender($from);
				 
				# Add a recipient -- this can be a single address (string) or an array of addresses
				$mailer->addRecipient($to);
				 
				$mailer->setSubject($subject);
				$mailer->setBody($body);
				$mailer->isHTML();
				$mailer->send();
				
	$payment_name = $this->renderPluginName($method);

	//We delete the old stuff
	// get the correct cart / session
	$cart = VirtueMartCart::getCart();
	$cart->emptyCart();

	  } else {

	   // this is a UNsucccessfull payment
	   // we update our DataBase

	    echo  "Couldn't Validate Payment with Vmzarinpal ".$status  ;

	  }

	}


  } else {
	   // this is a UNsucccessfull payment

  }

		}
			
		if (!($paymentTable = $this->_getPasargadInternalData($virtuemart_order_id, $order_number) )) {
			return '';
		}
		$html = $this->_getPaymentResponseHtml($paymentTable, $payment_name);

		//We delete the old stuff
		// get the correct cart / session
		$cart = VirtueMartCart::getCart();
		$cart->emptyCart();
		return true;
	}

	function plgVmOnUserPaymentCancel() {

		if (!class_exists('VirtueMartModelOrders'))
		require( JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php' );

		$order_number = JRequest::getVar('on');
		if (!$order_number)
		return false;
		$db = JFactory::getDBO();
		$query = 'SELECT ' . $this->_tablename . '.`virtuemart_order_id` FROM ' . $this->_tablename . " WHERE  `order_number`= '" . $order_number . "'";

		$db->setQuery($query);
		$virtuemart_order_id = $db->loadResult();

		if (!$virtuemart_order_id) {
			return null;
		}
		$this->handlePaymentUserCancel($virtuemart_order_id);

		return true;
	}


	function plgVmOnPaymentNotification() {

		if (!class_exists('VirtueMartModelOrders'))
		require( JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php' );
		$Vmzarinpalwg_data = JRequest::get('post');
		if (!isset($Vmzarinpalwg_data['on'])) {
			return;
		}
		$order_number = $Vmzarinpalwg_data['on'];
		$virtuemart_order_id = VirtueMartModelOrders::getOrderIdByOrderNumber($Vmzarinpalwg_data['invoice']);
		//$this->logInfo('plgVmOnPaymentNotification: virtuemart_order_id  found ' . $virtuemart_order_id, 'message');

		if (!$virtuemart_order_id) {
			return;
		}
		$vendorId = 0;
		$payment = $this->getDataByOrderId($virtuemart_order_id);

		$method = $this->getVmPluginMethod($payment->virtuemart_paymentmethod_id);
		if (!$this->selectedThisElement($method->payment_element)) {
			return false;
		}

		if (!$payment) {
			$this->logInfo('getDataByOrderId payment not found: exit ', 'ERROR');
			return null;
		}
		$this->logInfo('Vmzarinpalwg_data' . implode('   ', $Vmzarinpalwg_data), 'message');

		$this->_storemellatgadnternalData($method, $Vmzarinpalwg_data, $virtuemart_order_id);

		$new_status = $this->_getPaymentStatus($method, $Vmzarinpalwg_data['payment_status']);

		$this->logInfo('plgVmOnPaymentNotification return new_status:' . $new_status, 'message');

		$modelOrder = VmModel::getModel('orders');

		$order = array();
		$order['order_status'] = $new_status;
		$order['customer_notified'] =1;
		$order['comments'] = JText::sprintf('وضعیت سفارش به تایید شده تغییر وضعیت داده شد', $order_number);

		$modelOrder->updateStatusForOneOrder($virtuemart_order_id, $order, true);

		$this->logInfo('Notification, sentOrderConfirmedEmail ' . $order_number . ' ' . $new_status, 'message');


	}

	function _storeVmzarinpalwgnternalData($method, $Vmzarinpalwg_data, $virtuemart_order_id) {

		// get all know columns of the table
		$db = JFactory::getDBO();
		$query = 'SHOW COLUMNS FROM `' . $this->_tablename . '` ';
		$db->setQuery($query);
		$columns = $db->loadResultArray(0);
		$post_msg = '';
		foreach ($Vmzarinpalwg_data as $key => $value) {
			$post_msg .= $key . "=" . $value . "<br />";
			$table_key = 'Pasargad_response_' . $key;
			if (in_array($table_key, $columns)) {
				$response_fields[$table_key] = $value;
			}
		}

		//$response_fields[$this->_tablepkey] = $this->_getTablepkeyValue($virtuemart_order_id);
		$response_fields['payment_name'] = $this->renderPluginName($method);
		$response_fields['Pasargadresponse_raw'] = $post_msg;
		$return_context = $Vmzarinpalwg_data['custom'];
		$response_fields['order_number'] = $Vmzarinpalwg_data['invoice'];
		$response_fields['virtuemart_order_id'] = $virtuemart_order_id;
		//$preload=true   preload the data here too preserve not updated data
		$this->storePSPluginInternalData($response_fields, 'virtuemart_order_id', true);
	}

	function _getTablepkeyValue($virtuemart_order_id) {
		$db = JFactory::getDBO();
		$q = 'SELECT ' . $this->_tablepkey . ' FROM `' . $this->_tablename . '` '
		. 'WHERE `virtuemart_order_id` = ' . $virtuemart_order_id;
		$db->setQuery($q);

		if (!($pkey = $db->loadResult())) {
			JError::raiseWarning(500, $db->getErrorMsg());
			return '';
		}
		return $pkey;
	}

	function _getPaymentStatus($method, $Vmzarinpalwg_status) {
		$new_status = '';
		if (strcmp($Vmzarinpalwg_status, 'Completed') == 0) {
			$new_status = $method->status_success;
		} elseif (strcmp($Vmzarinpalwg_status, 'Pending') == 0) {
			$new_status = $method->status_pending;
		} else {
			$new_status = $method->status_canceled;
		}
		return $new_status;
	}

	/**
	 * Display stored payment data for an order
	 * @see components/com_virtuemart/helpers/vmPSPlugin::plgVmOnShowOrderBEPayment()
	 */
	function plgVmOnShowOrderBEPayment($virtuemart_order_id, $payment_method_id) {

		if (!$this->selectedThisByMethodId($payment_method_id)) {
			return null; // Another method was selected, do nothing
		}


		if (!($paymentTable = $this->_getPasargadInternalData($virtuemart_order_id) )) {
			// JError::raiseWarning(500, $db->getErrorMsg());
			return '';
		}
		$this->getPaymentCurrency($paymentTable);
		$q = 'SELECT `currency_code_3` FROM `#__virtuemart_currencies` WHERE `virtuemart_currency_id`="' . $paymentTable->payment_currency . '" ';
		$db = &JFactory::getDBO();
		$db->setQuery($q);
		$currency_code_3 = $db->loadResult();
		$html = '<table class="adminlist">' . "\n";
		$html .=$this->getHtmlHeaderBE();
		$html .= $this->getHtmlRowBE('پرداخت زرين پال', $paymentTable->payment_name);
		//$html .= $this->getHtmlRowBE('PAYPAL_PAYMENT_TOTAL_CURRENCY', $paymentTable->payment_order_total.' '.$currency_code_3);
		$code = "Pasargad_response_";
		foreach ($paymentTable as $key => $value) {
			if (substr($key, 0, strlen($code)) == $code) {
				$html .= $this->getHtmlRowBE($key, $value);
			}
		}
		$html .= '</table>' . "\n";
		return $html;
	}

	function _getPasargadInternalData($virtuemart_order_id, $order_number='') {
		$db = JFactory::getDBO();
		$q = 'SELECT * FROM `' . $this->_tablename . '` WHERE ';
		if ($order_number) {
			$q .= " `order_number` = '" . $order_number . "'";
		} else {
			$q .= ' `virtuemart_order_id` = ' . $virtuemart_order_id;
		}

		$db->setQuery($q);
		if (!($paymentTable = $db->loadObject())) {
			// JError::raiseWarning(500, $db->getErrorMsg());
			return '';
		}
		return $paymentTable;
	}


	function _getPaymentResponseHtml($VmzarinpalwgTable, $payment_name) {

		$html = '<table>' . "\n";
		$html .= $this->getHtmlRow('زرين پال', $payment_name);
		if (!empty($VmzarinpalwgTable)) {
			$html .= $this->getHtmlRow('شماره سفارش', $VmzarinpalwgTable->order_number);
			//$html .= $this->getHtmlRow('PAYPAL_AMOUNT', $paypalTable->payment_order_total. " " . $paypalTable->payment_currency);
		}
		$html .= '</table>' . "\n";

		return $html;
	}

	function getCosts(VirtueMartCart $cart, $method, $cart_prices) {
		if (preg_match('/%$/', $method->cost_percent_total)) {
			$cost_percent_total = substr($method->cost_percent_total, 0, -1);
		} else {
			$cost_percent_total = $method->cost_percent_total;
		}
		return ($method->cost_per_transaction + ($cart_prices['salesPrice'] * $cost_percent_total * 0.01));
	}

	/**
	 * Check if the payment conditions are fulfilled for this payment method
	 * @author: Valerie Isaksen
	 *
	 * @param $cart_prices: cart prices
	 * @param $payment
	 * @return true: if the conditions are fulfilled, false otherwise
	 *
	 */
	protected function checkConditions($cart, $method, $cart_prices) {


		$address = (($cart->ST == 0) ? $cart->BT : $cart->ST);

		$amount = $cart_prices['salesPrice'];
		$amount_cond = ($amount >= $method->min_amount AND $amount <= $method->max_amount
		OR
		($method->min_amount <= $amount AND ($method->max_amount == 0) ));

		$countries = array();
		if (!empty($method->countries)) {
			if (!is_array($method->countries)) {
				$countries[0] = $method->countries;
			} else {
				$countries = $method->countries;
			}
		}
		// probably did not gave his BT:ST address
		if (!is_array($address)) {
			$address = array();
			$address['virtuemart_country_id'] = 0;
		}

		if (!isset($address['virtuemart_country_id']))
		$address['virtuemart_country_id'] = 0;
		if (in_array($address['virtuemart_country_id'], $countries) || count($countries) == 0) {
			if ($amount_cond) {
				return true;
			}
		}

		return false;
	}

//####################################################################	
	function plgVmOnStoreInstallPaymentPluginTable($jplugin_id) {

		return $this->onStoreInstallPluginTable($jplugin_id);
	}

	public function plgVmOnSelectCheckPayment(VirtueMartCart $cart) {
		return $this->OnSelectCheck($cart);
	}

	public function plgVmDisplayListFEPayment(VirtueMartCart $cart, $selected = 0, &$htmlIn) {
		return $this->displayListFE($cart, $selected, $htmlIn);
	}

	public function plgVmonSelectedCalculatePricePayment(VirtueMartCart $cart, array &$cart_prices, &$cart_prices_name) {
		return $this->onSelectedCalculatePrice($cart, $cart_prices, $cart_prices_name);
	}

	function plgVmOnCheckAutomaticSelectedPayment(VirtueMartCart $cart, array $cart_prices = array()) {
		return $this->onCheckAutomaticSelected($cart, $cart_prices);
	}

	public function plgVmOnShowOrderFEPayment($virtuemart_order_id, $virtuemart_paymentmethod_id, &$payment_name) {
		$this->onShowOrderFE($virtuemart_order_id, $virtuemart_paymentmethod_id, $payment_name);
	}


	function plgVmonShowOrderPrintPayment($order_number, $method_id) {
		return $this->onShowOrderPrint($order_number, $method_id);
	}

	function plgVmDeclarePluginParamsPayment($name, $id, &$data) {
		return $this->declarePluginParams('payment', $name, $id, $data);
	}

	function plgVmSetOnTablePluginParamsPayment($name, $id, &$table) {
		return $this->setOnTablePluginParams($name, $id, $table);
	}
function VERIFY_PAYMENT($on,$pm){
include('../../../configuration.php');
$jconfig = new JConfig();

/*********** mysql-setting **************/
$GLOBALS['sqlhost'] = 	$jconfig->host;
$GLOBALS['sqluser'] = 	$jconfig->user;
$GLOBALS['sqlpass'] = 	$jconfig->password;
$GLOBALS['dtbname'] = 	$jconfig->db;
$GLOBALS['dbprefix'] = 	$jconfig->dbprefix;
/*********** mysql-setting **************/
$con = mysql_connect($GLOBALS['sqlhost'],$GLOBALS['sqluser'],$GLOBALS['sqlpass']);
if (!$con)
  {
  die('Could not connect: ' . mysql_error());
  }
mysql_select_db($GLOBALS['dtbname'], $con);
mysql_query("UPDATE ".$GLOBALS['dbprefix']."virtuemart_orders SET order_status='C'
WHERE order_number='".$on."' AND virtuemart_paymentmethod_id='".$pm."'");
mysql_close($con);
  }
 //////////////////////////////////////////////
 function CANCEL_PAYMENT($on,$pm){
include('../../../configuration.php');
$jconfig = new JConfig();

/*********** mysql-setting **************/
$GLOBALS['sqlhost'] = 	$jconfig->host;
$GLOBALS['sqluser'] = 	$jconfig->user;
$GLOBALS['sqlpass'] = 	$jconfig->password;
$GLOBALS['dtbname'] = 	$jconfig->db;
$GLOBALS['dbprefix'] = 	$jconfig->dbprefix;
/*********** mysql-setting **************/
$con = mysql_connect($GLOBALS['sqlhost'],$GLOBALS['sqluser'],$GLOBALS['sqlpass']);
if (!$con)
  {
  die('Could not connect: ' . mysql_error());
  }
mysql_select_db($GLOBALS['dtbname'], $con);
mysql_query("UPDATE ".$GLOBALS['dbprefix']."virtuemart_orders SET order_status='X'
WHERE order_number='".$on."' AND virtuemart_paymentmethod_id='".$pm."'");
mysql_close($con);
  }
}
