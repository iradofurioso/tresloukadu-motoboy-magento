<?php 
/**
 * Carlos Eduardo da Silva (aka Tresloukadu).
 *
 * NOTICE OF LICENSE
 *
 *
 * The MIT License
 *
 * Copyright (c) 2012 TRESLOUKADU
 *
 * http://www.tresloukadu.com.br/category/php/magento/
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 *
 *
 * @category   Tresloukadu
 * @package    Tresloukadu_Motoboy
 * @copyright  Copyright (c) 2013 Carlos Eduardo da Silva (http://www.tresloukadu.com.br)
 * @author     Carlos Eduardo da Silva <carlosedasilva@gmail.com>
 * @license    http://opensource.org/licenses/MIT
 */


/**
 * Tresloukadu_Motoboy_Model_Carrier_MotoboyMethod
 * 
 * @category   Tresloukadu
 * @package    Tresloukadu_Motoboy
 * @author     Carlos Eduardo da Silva <carlosedasilva@gmail.com>
 */
class Tresloukadu_Motoboy_Model_Carrier_MotoboyMethod extends Mage_Shipping_Model_Carrier_Abstract implements Mage_Shipping_Model_Carrier_Interface
{
	
	/**
	 * Unique internal shipping method id.
	 * @var string
	 */
	protected $_code = 'tresloukadu_motoboy';
	
	
	/**
	 * Collect Rates for this shipping method based on information in $request
	 *
	 * @param Mage_Shipping_Model_Rate_Request $request
	 * @return Mage_Shipping_Model_Rate_Result
	 */
	public function collectRates(Mage_Shipping_Model_Rate_Request $request)
	{
		if (!$this->getConfigFlag('active'))
		{
			Mage::log('Tresloukadu_Motoboy: Disabled');
			return false;
		}
		
		//entregas gratis e produtos virtuais
		$freeBoxes = 0;
		if ($request->getAllItems()) {
			foreach ($request->getAllItems() as $item) {
				if ($item->getFreeShipping() && !$item->getProduct()->isVirtual()) {
					$freeBoxes+=$item->getQty();
				}
			}
		}
		$this->setFreeBoxes($freeBoxes);
		
		
		/**
		  * @todo Precisa trazer estes dados do banco de dados ou de um arquivo xml. O usuário deve 
		  * fornecer estes dados no admin e depois armazenar no banco/xml.
		 */
		$bairros = array();
		$bairros_data = $this->getConfigData('bairros');		 
		if($bairros_data){
			$aux = explode("\n", str_replace("\r", "", $bairros_data));
			$new_bairros = array();
			foreach($aux as $item){
				$aux_item = explode("#", $item);
				$valor = str_replace(",", ".", $aux_item[1]);
				$valor = str_replace(" ", ".", $valor);
				$new_bairros[trim($aux_item[0])] = $valor;
			}
			$bairros = $new_bairros;
		} 
				
		$result = Mage::getModel('shipping/rate_result');
		
		if(count($bairros)){
			foreach($bairros as $chave => $valor )
			{
				$shippingPrice = $valor; 
				  
				$shippingPrice = $this->getFinalPriceWithHandlingFee($shippingPrice);
				
				$method = Mage::getModel('shipping/rate_result_method');
				
				$method->setCarrier($this->_code);
				$method->setCarrierTitle($this->getConfigData('title'));
				$method->setMethod($chave);
				$method->setMethodTitle($chave); 
				if ($request->getFreeShipping() === true || $request->getPackageQty() == $this->getFreeBoxes()) {
					$shippingPrice = '0.00';
				}
				$method->setPrice($shippingPrice);
				$method->setCost($shippingPrice);
				
				$result->append($method);
			}
		}	
		return $result;
	}
	
	public function getAllowedMethods()
	{
		return array($this->_code => $this->getConfigData('title'));
	}
	
}