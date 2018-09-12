<?php
/**
* Edit By Tomas Tang
* 2018/9/12
* @Huawei, Shenzhen, Guangdong, China
* This script help users register customer(just for basic information) account programmly.
* It can be used in two mode:
* 1. regiter gigantic amount of customer accounts one time with a file named customerdata.txt which include informtion that an account need.
* 2. register a signle customer account defaultly, The default customer's information have been saved in $customer_default. 
*/

/**
* COPY ./register.php $INSTALL_DIR/app/ #in dockerfile
* COPY ./customerdata.txt $INSTALL_DIR/app/     #in dockerfile
* php -f app/register.php              #in bin/install
*/

/**
* The format we write information in the file-data.txt.
* We save the data as ".cvs" format wich means we use comma "," divide data, and each customer's information hold in one line.
* The form of the customer's account is writed below:
* first name,last name,email,password,phone number,country,city,post code,street
* the first four are essential, others can be default, and the email cannot be repeated.
*/

$customer_default=array("hello","world","helloworld@huawei.com","password",'1234567','CN','深圳','518000','龙岗区坂田街道');
						
function RegisterCustomer($customer_info)
{
	require_once('Mage.php');
	$websiteId = Mage::app()->getWebsite()->getId();
	$store = Mage::app()->getStore();
 
	$customer = Mage::getModel("customer/customer");
	$customer   ->setWebsiteId($websiteId)
            ->setStore($store)
            ->setFirstname($customer_info[0])
            ->setLastname($customer_info[1])
            ->setEmail($customer_info[2])
            ->setPassword($customer_info[3]);
 
	try{
		$customer->save();
	}
	catch (Exception $e) {
		Zend_Debug::dump($e->getMessage());
	}

	$address = Mage::getModel("customer/address");
	$address->setCustomerId($customer->getId())
			->setFirstname($customer->getFirstname())
			->setLastname($customer->getLastname())
			->setCountryId($customer_info[5])
			//->setRegionId('1') //state/province, only needed if the country is USA
			->setPostcode($customer_info[7])
			->setCity($customer_info[6])
			->setTelephone($customer_info[4])
			->setStreet($customer_info[8])
			->setIsDefaultBilling('1')
			->setIsDefaultShipping('1')
			->setSaveInAddressBook('1');
 
	try{
		$address->save();
	}
	catch (Exception $e) {
		Zend_Debug::dump($e->getMessage());
	}
}

//$DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
//echo "I'm doing it!";
if(file_exists('customerdata.txt')){
	try{
		$file_array = file('customerdata.txt');//取到文件数组
		foreach ($file_array as $value) {
			$information=str_getcsv($value);
			$num=count($information);
			if($num<4){
				continue;
			}
			if($num<9){
				for($i=$num;$i<9;$i++){
					$information[$i]=$customer_default[$i];
				}
			}
			RegisterCustomer($information);
		}
	}
	catch (Exception $e) {
		Zend_Debug::dump($e->getMessage());
	}
}
else{
	RegisterCustomer($customer_default);
}
