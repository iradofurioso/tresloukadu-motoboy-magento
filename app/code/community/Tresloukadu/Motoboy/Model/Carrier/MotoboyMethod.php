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
		$bairros = array(
				'Armacao'=>'50.00',
				'Area Industrial de sao Jose'=>'17.00',
				'Agronomica'=>'25.00',
				'Alto Aririu'=>'25.00',
				'Aeroporto'=>'35.00',
				'Av. das Torres'=>'20.00',
				'Abraao'=>'17.00',
				'Aririu'=>'25.00',
				'Areias'=>'17.00',
				'Area Ind. da Palhoca'=>'20.00',
				'Barreiros'=>'17.00',
				'Bela Vista'=>'15.00',
				'Bela Vista da Palhoca'=>'20.00',
				'Balneario do estreito'=>'17.00',
				'Biguacu'=>'25.00',
				'Barra da Lagoa'=>'40.00',
				'Barra do Aririu'=>'25.00',
				'Centro'=>'20.00',
				'Corrego Grande'=>'30.00',
				'Coloninha'=>'17.00',
				'Campeche'=>'30.00',
				'Cacupe'=>'35.00',
				'Canasvieiras'=>'50.00',
				'Ceniro Martins'=>'12.00',
				'Colonia Santana'=>'25.00',
				'Costeira'=>'25.00',
				'Caminho Novo'=>'25.00',
				'Centro de Sao Jose'=>'15.00',
				'Campinas'=>'15.00',
				'Carvoeira'=>'25.00',
				'Capoeiras'=>'17.00',
				'Coqueiros'=>'17.00',
				'Cachoeira do bom Jesus'=>'50.00',
				'Forquilinhas'=>'10.00',
				'Forquilhas'=>'15.00',
				'Floresta'=>'12.00',
				'Fazenda do Max'=>'15.00',
				'Ingleses'=>'50.00',
				'Itacorobi'=>'25.00',
				'jardim el Dourado'=>'15.00',
				'Joao Paulo'=>'30.00',
				'Jurere'=>'45.00',
				'Janaina'=>'25.00',
				'Kobrassol'=>'15.00',
				'Lisboa'=>'10.00',
				'Palhoca(centro)'=>'17.00',
				'Ponta de baixo'=>'15.00',
				'Ponte do Imaruim'=>'15.00',
				'Pantanal'=>'25.00',
				'Rocado'=>'12.00',
				'Rio Tavares'=>'30.00',
				'Ratones'=>'45.00',
				'Serraria'=>'20.00',
				'Sertao do Imaruim'=>'15.00',
				'Saco grande'=>'30.00',
				'Santa Monica'=>'30.00',
				'Santo Antonio de Lisboa'=>'35.00',
				'Sao Sebastiao'=>'30.00',
				'Sao Pedro de Alcantara'=>'40.00',
				'Santo Amaro'=>'35.00',
				'Tapera'=>'45.00',
				'Trindade'=>'25.00',
				'Vila Formosa'=>'12.00',
				'Zanelato'=>'20.00');
		
		$result = Mage::getModel('shipping/rate_result');
		
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
		
		return $result;
	}
	
	
	
	public function getAllowedMethods()
	{
		return array($this->_code => $this->getConfigData('title'));
	}
	
}