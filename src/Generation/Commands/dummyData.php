<?php
namespace Generation\Commands;

if(!file_exists(__DIR__ . '/../../../config.php')) {

  return false;
}
require_once(__DIR__  . '/../../../config.php');

  use Symfony\Component\Console\Input\InputInterface;
  use Symfony\Component\Console\Output\OutputInterface;
  use Symfony\Component\Console\Command\Command;
  use Symfony\Component\Console\Input\InputArgument;
  use Symfony\Component\Console\Input\InputOption;
  use Symfony\Component\Console\Formatter\OutputFormatterStyle;
  use Symfony\Component\Console\Question\Question;
  use Symfony\Component\Console\Helper;

class DummyData extends Command
{
    protected function configure() {

      $this->setName('app:dummy-data')
      ->addArgument('command_name', InputArgument::IS_ARRAY | InputArgument::REQUIRED, 'Please enter command name.');

    }

    protected function execute(InputInterface $input, OutputInterface $output) {

      $command = $input->getArgument('command_name')[0];
      if($command == 'create-product') {
        $this->createProduct($input , $output);
      } else if($command == 'create-customer') {
        $this->createUser($input , $output);
      } else if($command == 'create-category') {
        $this->createCategory($input , $output);
      } else if($command == 'create-order') {
        $this->createOrder($input , $output);
      } else {
        $output->writeln('Command not found');
      }
    }

    public function createProduct($input , $output) {
      $helper = $this->getHelper('question');
      $q1 = new Question("Product Name: ");
      $q2 = new Question("Model: ");
      $q3 = new Question("Product meta title: ");
      $q4 = new Question("Product Price: ");
      $q5 = new Question("Product Quantity: ");
      $q6 = new Question("No of Product: ");

      $data = array();
      $data['name'] = $name = $helper->ask($input, $output, $q1);
      $data['model'] = $model = $helper->ask($input, $output, $q2);
      $data['meta_title'] = $meta_title = $helper->ask($input, $output, $q3);
      $data['price'] = (int)$helper->ask($input, $output, $q4);
      $data['quantity'] = (int)$helper->ask($input, $output, $q5);
      $no_of_product = (int)$helper->ask($input, $output, $q6);
      if($this->validateProduct($data)) {
        $this->addProduct($data);
        for($i = 1; $i <= $no_of_product; $i++) {
          $data['name'] = $name . ' ' . $i;
          $data['model'] = $model . ' ' . $i;
          $data['meta_title'] = $meta_title . ' ' . $i;
          $this->addProduct($data);
        }
      } else {
        $output->writeln('Empty not allow..!');
        die();
      }
      $output->writeln($no_of_product . ' product have been created');
    }

    public function createUser($input , $output) {
      $helper = $this->getHelper('question');
      $q1 = new Question("Enter First Name: ");
      $q2 = new Question("Enter Last Name: ");
      $q3 = new Question("Enter Email: ");
      $q4 = new Question("Enter Telephone: ");
      $q5 = new Question("Enter Password: ");
      $q6 = new Question("Enter the number of customer ");

      $data = array();
      $data['first_name'] = $first_name = $helper->ask($input, $output, $q1);
      $data['last_name'] = $last_name = $helper->ask($input, $output, $q2);
      $data['email'] = $helper->ask($input, $output, $q3);
      $data['phone'] = $helper->ask($input, $output, $q4);
      $data['password'] = $helper->ask($input, $output, $q5);
      $no_of_customer = (int)$helper->ask($input, $output, $q6);
      if($this->validateUser($data)) {
        $this->addUser($data);
        for($i = 1; $i <= $no_of_customer; $i++) {
          $rand = rand(4,9999);
          $data['last_name'] = $last_name . ' ' . $i;
          $data['email'] = $first_name . $rand . '@gmail.com';
          $this->addUser($data);
        }
      } else {
        $output->writeln('Empty not allow..!');
        die();
      }
      $output->writeln($no_of_customer . ' customer have been created');
    }

    public function validateUser($data) {
      $status = true;
      if(empty($data['first_name']) || empty($data['last_name']) || empty($data['email']) || empty($data['phone']) || empty($data['password'])) {
        $status = false;
      }
      return $status;
    }

    public function validateProduct($data) {
      $status = true;
      if(empty($data['name']) || empty($data['model']) || empty($data['meta_title'])) {
        $status = false;
      }
      return $status;
    }

    public function createCategory($input , $output) {
      $helper = $this->getHelper('question');
      $q1 = new Question("Category Name: ");
      $q2 = new Question("category meta title: ");
      $q3 = new Question("No of Category: ");

      $data = array();
      $data['name'] = $name = $helper->ask($input, $output, $q1);
      $data['meta_title'] = $meta_title = $helper->ask($input, $output, $q2);
      $no_of_category = (int)$helper->ask($input, $output, $q3);
      if($this->validateCategory($data)) {
        $this->addCategory($data);
        for($i = 1; $i <= $no_of_category; $i++) {
          $rand = rand(4,9999);
          $data['name'] = $name . ' ' . $rand;
          $data['meta_title'] = $meta_title . ' ' . $rand;
          $this->addCategory($data);
        }
      } else {
        $output->writeln('Empty not allow..!');
        die();
      }
      $output->writeln($no_of_category . ' Category have been created');
    }

    public function validateCategory($data) {
      $status = true;
      if(empty($data['name']) || empty($data['meta_title'])) {
        $status = false;
      }
      return $status;
    }

    public function createOrder($input , $output) {
      $data = array();
      $helper = $this->getHelper('question');
      $q1 = new Question("No of Orders: ");
      $no_of_orders = (int)$helper->ask($input, $output, $q1);
      $data = $this->getDefaultOrder();

      for ($i = 0; $i < $no_of_orders; $i++) {
        $data['invoice_prefix'] = 'INV-2019-' . $i;
        $this->addOrder($data);
      }
      $output->writeln($no_of_orders . ' Order have been created');
    }

    public function addUser($data) {
      $conn = $this->connObject();
      $query = "INSERT INTO `" . DB_PREFIX . "customer` SET customer_group_id = '" . $this->getSetting('config_customer_group_id') . "' ,store_id = '" . $this->getSetting('config_store_id') . "', language_id = '" . $this->getSetting('config_language_id') . "',firstname ='" . $this->escape($data['first_name']) . "' , lastname = '" . $this->escape($data['last_name']) . "' , email = '" . $this->escape($data['email']) . "' , telephone = '" . $this->escape($data['phone']) . "', fax = 'test', salt = '" . $this->escape($salt = $this->token(9)) . "' , password = '" . $this->escape(sha1($salt . sha1($salt . sha1($data['password'])))) . "', custom_field = 'test', ip = '111.11.11.11', safe = '1', token = 'test', `code` = 'test', status = '1', date_added = NOW()";
      $result = mysqli_query($conn,$query);
      return $conn->insert_id;
    }

    public function addProduct($data) {
      $conn = $this->connObject();

      $query = "INSERT INTO " . DB_PREFIX . "product SET model = '" . $this->escape($data['model']) . "', sku = 'test', upc = 'test', ean = 'test', jan = 'test', isbn = 'test', mpn = 'test', location = 'test', quantity = '" . (int)$data['quantity'] . "', tax_class_id = '9', stock_status_id = '7', date_available = NOW(), manufacturer_id = '0', price = '" . (float)$data['price'] . "', `status` = '1', date_added = NOW(), date_modified = NOW()";
      mysqli_query($conn,$query);
      $product_id = $conn->insert_id;

      $query = "INSERT INTO " . DB_PREFIX . "product_description SET product_id = '" . (int)$product_id . "', language_id = '1', name = '" . $this->escape($data['name']) . "', description = '" . $this->escape($data['name']) . "', tag = 'test', meta_title = '" . $this->escape($data['meta_title']) . "', meta_description = '" . $this->escape($data['meta_title']) . "', meta_keyword = '" . $this->escape($data['meta_title']) . "'";
      $result = mysqli_query($conn,$query);
    }

    public function addCategory($data) {
      $conn = $this->connObject();
      $query = "INSERT INTO " . DB_PREFIX . "category SET parent_id = '0', `top` = '" . (isset($data['top']) ? (int)$data['top'] : 0) . "', `column` = '0', `sort_order` = '0', status = '1', date_modified = NOW(), date_added = NOW()";
      mysqli_query($conn,$query);
		  $category_id = $conn->insert_id;

      $query = "INSERT INTO " . DB_PREFIX . "category_description SET category_id = '" . (int)$category_id . "', language_id = '1', name = '" . $this->escape($data['name']) . "', description = '" . $this->escape($data['name']) . "', meta_title = '" . $this->escape($data['meta_title']) . "', meta_description = '" . $this->escape($data['meta_title']) . "', meta_keyword = '" . $this->escape($data['meta_title']) . "'";
      mysqli_query($conn,$query);

      $query = "INSERT INTO `" . DB_PREFIX . "category_path` SET `category_id` = '" . (int)$category_id . "', `path_id` = '" . (int)$category_id . "', `level` = '0'";
      mysqli_query($conn,$query);
    }

    public function addOrder($data) {
      $conn = $this->connObject();
      $query = "INSERT INTO `" . DB_PREFIX . "order` SET invoice_prefix = '" . $this->escape($data['invoice_prefix']) . "', store_id = '" . (int)$data['store_id'] . "', store_name = '" . $this->escape($data['store_name']) . "', store_url = '" . $this->escape($data['store_url']) . "', customer_id = '" . (int)$data['customer_id'] . "', customer_group_id = '" . (int)$data['customer_group_id'] . "', firstname = '" . $this->escape($data['firstname']) . "', lastname = '" . $this->escape($data['lastname']) . "', email = '" . $this->escape($data['email']) . "', telephone = '" . $this->escape($data['telephone']) . "', fax = '" . $this->escape($data['fax']) . "', custom_field = '" . $this->escape(isset($data['custom_field']) ? json_encode($data['custom_field']) : '') . "', payment_firstname = '" . $this->escape($data['payment_firstname']) . "', payment_lastname = '" . $this->escape($data['payment_lastname']) . "', payment_company = '" . $this->escape($data['payment_company']) . "', payment_address_1 = '" . $this->escape($data['payment_address_1']) . "', payment_address_2 = '" . $this->escape($data['payment_address_2']) . "', payment_city = '" . $this->escape($data['payment_city']) . "', payment_postcode = '" . $this->escape($data['payment_postcode']) . "', payment_country = '" . $this->escape($data['payment_country']) . "', payment_country_id = '" . (int)$data['payment_country_id'] . "', payment_zone = '" . $this->escape($data['payment_zone']) . "', payment_zone_id = '" . (int)$data['payment_zone_id'] . "', payment_address_format = '" . $this->escape($data['payment_address_format']) . "', payment_custom_field = '" . $this->escape(isset($data['payment_custom_field']) ? json_encode($data['payment_custom_field']) : '') . "', payment_method = '" . $this->escape($data['payment_method']) . "', payment_code = '" . $this->escape($data['payment_code']) . "', shipping_firstname = '" . $this->escape($data['shipping_firstname']) . "', shipping_lastname = '" . $this->escape($data['shipping_lastname']) . "', shipping_company = '" . $this->escape($data['shipping_company']) . "', shipping_address_1 = '" . $this->escape($data['shipping_address_1']) . "', shipping_address_2 = '" . $this->escape($data['shipping_address_2']) . "', shipping_city = '" . $this->escape($data['shipping_city']) . "', shipping_postcode = '" . $this->escape($data['shipping_postcode']) . "', shipping_country = '" . $this->escape($data['shipping_country']) . "', shipping_country_id = '" . (int)$data['shipping_country_id'] . "', shipping_zone = '" . $this->escape($data['shipping_zone']) . "', shipping_zone_id = '" . (int)$data['shipping_zone_id'] . "', shipping_address_format = '" . $this->escape($data['shipping_address_format']) . "', shipping_custom_field = '" . $this->escape(isset($data['shipping_custom_field']) ? json_encode($data['shipping_custom_field']) : '') . "', shipping_method = '" . $this->escape($data['shipping_method']) . "', shipping_code = '" . $this->escape($data['shipping_code']) . "', comment = '" . $this->escape($data['comment']) . "', total = '" . (float)$data['total'] . "', order_status_id = '" . (int)$data['order_status_id'] . "', affiliate_id = '" . (int)$data['affiliate_id'] . "', commission = '" . (float)$data['commission'] . "', marketing_id = '" . (int)$data['marketing_id'] . "', tracking = '" . $this->escape($data['tracking']) . "', language_id = '" . (int)$data['language_id'] . "', currency_id = '" . (int)$data['currency_id'] . "', currency_code = '" . $this->escape($data['currency_code']) . "', currency_value = '" . (float)$data['currency_value'] . "', ip = '" . $this->escape($data['ip']) . "', forwarded_ip = '" .  $this->escape($data['forwarded_ip']) . "', user_agent = '" . $this->escape($data['user_agent']) . "', accept_language = '" . $this->escape($data['accept_language']) . "', date_added = NOW(), date_modified = NOW()";
      mysqli_query($conn , $query);
  		$order_id = $conn->insert_id;
      $product = $data['order_product'];

      $query = "INSERT INTO " . DB_PREFIX . "order_product SET order_id = '" . (int)$order_id . "', product_id = '" . (int)$product['product_id'] . "', name = '" . $this->escape($product['name']) . "', model = '" . $this->escape($product['model']) . "', quantity = '" . (int)$product['quantity'] . "', price = '" . (float)$product['price'] . "', total = '" . (float)$product['total'] . "', tax = '" . (float)$product['tax'] . "', reward = '" . (int)$product['reward'] . "'";
      mysqli_query($conn , $query);

      foreach($data['order_total'] as $total) {
        $query = "INSERT INTO " . DB_PREFIX . "order_total SET order_id = '" . (int)$order_id . "', code = '" . $this->escape($total['code']) . "', title = '" . $this->escape($total['title']) . "', `value` = '" . (float)$total['value'] . "', sort_order = '" . (int)$total['sort_order'] . "'";
        mysqli_query($conn , $query);
      }

      $history = $data['order_history'];

      $query = "INSERT INTO " . DB_PREFIX . "order_history SET order_id = '" . (int)$order_id . "', order_status_id = '" . (int)$history['order_status_id'] . "', notify = '" . (int)$history['notify'] . "', comment = '" . $this->escape($history['comment']) . "', date_added = NOW()";
      mysqli_query($conn , $query);

    }

    public function connObject() {
      $conn = mysqli_connect(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD,DB_DATABASE);
     return $conn;
    }

    public function escape($param) {
      return $param;
    }

    public function getSetting($key) {
      $conn = $this->connObject();
      $store_id = 0;
      $code = "config";
      $setting_data = array();
  		$query = "SELECT * FROM " . DB_PREFIX . "setting WHERE store_id = '" . (int)$store_id . "' AND `code` = '" . $this->escape($code) . "'";

      $results = mysqli_query($conn,$query);

  		foreach ($results as $result) {
  			if (!$result['serialized']) {
  				$setting_data[$result['key']] = $result['value'];
  			} else {
  				$setting_data[$result['key']] = json_decode($result['value'], true);
  			}
  		}
  		return isset($setting_data[$key]) && $setting_data[$key] ? $setting_data[$key] : 0 ;
  	}

    function token($length = 32) {
    	$string = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    	$max = strlen($string) - 1;
    	$token = '';
    	for ($i = 0; $i < $length; $i++) {
    		$token .= $string[mt_rand(0, $max)];
    	}
    	return $token;
    }

    public function getDefaultOrder() {

      $cust['first_name'] = 'Jhon';
      $cust['last_name'] = 'Doe';
      $cust['email'] = 'jhondoe' . rand(4,9999) . '@webkul.com';
      $cust['phone'] = '1234567891';
      $cust['password'] = 'test';
      $customer_id = $this->addUser($cust);
      $conn = $this->connObject();
      $query = "SELECT * FROM `" . DB_PREFIX . "customer` WHERE customer_id = '". (int)$customer_id . "'";
      $result = mysqli_query($conn , $query);
      $customer = mysqli_fetch_array($result);
      $data['invoice_no'] = 0;
      $data['invoice_prefix'] = 'INV-2019-00';
      $data['store_id'] = 0;
      $data['store_name'] = 'Your Store';
      $data['store_url'] = 'http://akhileshkumar.com/d1/';
      $data['customer_id'] = $customer['customer_id'];
      $data['customer_group_id'] =$customer['customer_group_id'];
      $data['firstname'] = $customer['firstname'];
      $data['lastname'] = $customer['lastname'];
      $data['email'] = $customer['email'];
      $data['telephone'] = $customer['telephone'];
      $data['fax'] = '';
      $data['payment_firstname'] = $customer['firstname'];
      $data['payment_lastname'] = $customer['lastname'];
      $data['payment_company'] = '';
      $data['payment_address_1'] = 'Noida';
      $data['payment_address_2'] = '';
      $data['payment_city'] = 'Noida';
      $data['payment_postcode'] = '110092';
      $data['payment_country'] = 'India';
      $data['payment_country_id'] = 99;
      $data['payment_zone'] = 'Uttar Pradesh';
      $data['payment_zone_id'] = 1505;
      $data['payment_address_format'] = '';
      $data['payment_method'] = 'Bank Transfer';
      $data['payment_code'] = 'bank_transfer';
      $data['shipping_firstname'] = '';
      $data['shipping_lastname'] = '';
      $data['shipping_company'] = '';
      $data['shipping_address_1'] = '';
      $data['shipping_address_2'] = '';
      $data['shipping_city'] = '';
      $data['shipping_postcode'] = '';
      $data['shipping_country'] = '';
      $data['shipping_country_id'] = 0;
      $data['shipping_zone'] = '';
      $data['shipping_zone_id'] = 0;
      $data['shipping_address_format'] = '';
      $data['shipping_method'] = '';
      $data['shipping_code'] = '';
      $data['comment'] = '';
      $data['total'] = 500.0000;
      $data['order_status_id'] = 5;
      $data['affiliate_id'] = 0;
      $data['commission'] = 0.0000;
      $data['marketing_id'] = 0;
      $data['tracking'] = '';
      $data['language_id'] = 1;
      $data['currency_id'] = 2;
      $data['currency_code'] = 'USD';
      $data['currency_value'] = 1.00000000;
      $data['ip'] = '192.168.15.165';
      $data['forwarded_ip'] = '';
      $data['user_agent'] = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36';
      $data['accept_language'] = 'en-GB,en-US;q=0.9,en;q=0.8';

  		$history['order_status_id'] = 5;
      $history['notify'] = 1;
      $history['comment'] = 'Bank Transfer Instructions test Your order will not ship until we receive payment';

      $data['order_history'] = $history;

      $product['product_id'] = 43;
      $product['name'] = 'MacBook';
      $product['model'] = 'Product 16';
      $product['quantity'] = 1;
      $product['price'] = 500.0000;
      $product['total'] = 500.0000;
      $product['tax'] = 0.0000;
      $product['reward'] = 600;

  		$data['order_product'] = $product;

      $orderTotal[] = array(
  			'code' => 'sub_total',
  	    'title' => 'Sub-Total',
  	    'value' => 500.0000,
  	    'sort_order' => 1
  		);

  		$orderTotal[] = array(
  			'code' => 'total',
  	    'title' => 'Total',
  	    'value' => 500.0000,
  	    'sort_order' => 9
  		);

  		$data['order_total'] = $orderTotal;

      return $data;
    }
}
