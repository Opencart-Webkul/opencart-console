<?php
//
// Command line tool for installing opencart
// Original Author: Vineet Naik <vineet.naik@kodeplay.com> <naikvin@gmail.com>
// Updated and maintained by OpenCart
// (Currently tested on linux only)
//
// Usage:
//
//   Normal Install
//
//   php cli_install.php install --username    admin
//                               --email       email@example.com
//                               --password    password
//                               --cloud       0
//                               --http_server http://localhost/opencart/
//                               --db_driver   mysqli
//                               --db_hostname localhost
//                               --db_username root
//                               --db_password pass
//                               --db_database opencart
//								 --db_port     3306
//                               --db_prefix   oc_
//
//   Cloud Install
//
//   php cli_install.php install --username admin
//                               --email    email@example.com
//                               --password password
//                               --cloud    1
//

ini_set('display_errors', 1);

error_reporting(E_ALL);

// DIR
define('DIR_OPENCART', str_replace('\\', '/', realpath(dirname(__FILE__) . '/../')) . '/');
define('DIR_APPLICATION', DIR_OPENCART . 'install/');
define('DIR_SYSTEM', DIR_OPENCART . '/system/');
define('DIR_IMAGE', DIR_OPENCART . '/image/');
define('DIR_STORAGE', DIR_SYSTEM . 'storage/');
define('DIR_LANGUAGE', DIR_APPLICATION . 'language/');
define('DIR_TEMPLATE', DIR_APPLICATION . 'view/template/');
define('DIR_CONFIG', DIR_SYSTEM . 'config/');
define('DIR_CACHE', DIR_SYSTEM . 'storage/cache/');
define('DIR_DOWNLOAD', DIR_SYSTEM . 'storage/download/');
define('DIR_LOGS', DIR_SYSTEM . 'storage/logs/');
define('DIR_MODIFICATION', DIR_SYSTEM . 'storage/modification/');
define('DIR_SESSION', DIR_SYSTEM . 'storage/session/');
define('DIR_UPLOAD', DIR_SYSTEM . 'storage/upload/');

// Startup
require_once(DIR_SYSTEM . 'startup.php');

// Registry
$registry = new Registry();

// Loader
$loader = new Loader($registry);
$registry->set('load', $loader);

// Request
$registry->set('request', new Request());

// Response
$response = new Response();
$response->addHeader('Content-Type: text/plain; charset=utf-8');
$registry->set('response', $response);

set_error_handler(function($code, $message, $file, $line, array $errcontext) {
	// error was suppressed with the @-operator
	if (error_reporting() === 0) {
		return false;
	}

	throw new ErrorException($message, 0, $code, $file, $line);
});

class ControllerCliInstall extends Controller {
	public function index() {
		if (isset($this->request->server['argv'])) {
			$argv = $this->request->server['argv'];
		} else {
			$argv = array();
		}

		// Just displays the path to the file
		$script = array_shift($argv);

		// Get the arguments passed with the command
		$command = array_shift($argv);

		switch ($command) {
			case 'install':
				$output = $this->install($argv);
				break;
			case 'usage':
			default:
				$output = $this->usage($argv);
				break;
		}

		$this->response->setOutput($output);
	}

	public function install($argv) {
		// Options
		$option = array(
			'username'    => 'admin',
			'cloud'       => 0,
			'db_driver'   => 'mysqli',
			'db_hostname' => 'localhost',
			'db_password' => '',
			'db_port'     => '3306',
			'db_prefix'   => 'oc_'
		);

		// Turn args into an array
		for ($i = 0; $i < count($argv); $i++) {
			if (substr($argv[$i], 0, 2) == '--') {
				$key = substr($argv[$i], 2);

				// If the next line also starts with -- we need to fill in a null value for the current one
				if (isset($argv[$i + 1]) && substr($argv[$i + 1], 0, 2) != '--') {
					$option[$key] = $argv[$i + 1];

					// Skip the counter by 2
					$i++;
				} else {
					$option[$key] = '';
				}
			}
		}

		// Command line is sending true and false as strings so used 1 or 0 instead.
		if ($option['cloud']) {
			$cloud = 1;
		} else {
			$cloud = 0;
		}

		// Cloud Install
		if (!$cloud) {
			$required = array(
				'username',    // Already set
				'email',
				'password',
				'cloud',       // Already set
				'http_server',
				'db_driver',   // Already set
				'db_hostname',
				'db_username', // Already set
				'db_password', // Already set
				'db_database',
				'db_port',     // Already set
				'db_prefix'    // Already set
			);
		} else {
			$required = array(
				'username', // Already set
				'email',
				'password',
				'cloud'     // Already set
			);
		}

		// Validation
		$missing = array();

		foreach ($required as $value) {
			if (!array_key_exists($value, $option)) {
				$missing[] = $value;
			}
		}

		if (count($missing)) {
			return 'ERROR: Following inputs were missing or invalid: ' . implode(', ', $missing)  . "\n";
		}

		// Pre-installation check
		$error = '';

		if (version_compare(phpversion(), '7.0.0', '<')) {
			$error .= 'ERROR: You need to use PHP7+ or above for OpenCart to work!' . "\n";
		}

		if (!ini_get('file_uploads')) {
			$error .= 'ERROR: file_uploads needs to be enabled!' . "\n";
		}

		if (ini_get('session.auto_start')) {
			$error .= 'ERROR: OpenCart will not work with session.auto_start enabled!' . "\n";
		}

		if (!extension_loaded('mysqli')) {
			$error .= 'ERROR: MySQLi extension needs to be loaded for OpenCart to work!' . "\n";
		}

		if (!extension_loaded('gd')) {
			$error .= 'ERROR: GD extension needs to be loaded for OpenCart to work!' . "\n";
		}

		if (!extension_loaded('curl')) {
			$error .= 'ERROR: CURL extension needs to be loaded for OpenCart to work!' . "\n";
		}

		if (!function_exists('openssl_encrypt')) {
			$error .= 'ERROR: OpenSSL extension needs to be loaded for OpenCart to work!' . "\n";
		}

		if (!extension_loaded('zlib')) {
			$error .= 'ERROR: ZLIB extension needs to be loaded for OpenCart to work!' . "\n";
		}

		if (!is_file(DIR_OPENCART . 'config.php')) {
			$error .= 'ERROR: config.php does not exist. You need to rename config-dist.php to config.php!' . "\n";
		} elseif (!is_writable(DIR_OPENCART . 'config.php')) {
			$error .= 'ERROR: config.php needs to be writable for OpenCart to be installed!' . "\n";
		} elseif (!is_file(DIR_OPENCART . 'admin/config.php')) {
			$error .= 'ERROR: admin/config.php does not exist. You need to rename admin/config-dist.php to admin/config.php!' . "\n";
		} elseif (!is_writable(DIR_OPENCART . 'admin/config.php')) {
			$error .= 'ERROR: admin/config.php needs to be writable for OpenCart to be installed!' . "\n";
		}

		if ($error) {
			$output  = 'ERROR: Pre-installation check failed: ' . "\n";
			$output .= $error . "\n\n";

			return $output;
		}

		// Pre-installation check
		$error = '';

		if ((utf8_strlen($option['username']) < 3) || (utf8_strlen($option['username']) > 20)) {
			$error .= 'ERROR: Username must be between 3 and 20 characters!' . "\n";
		}

		if ((utf8_strlen($option['email']) > 96) || !filter_var($option['email'], FILTER_VALIDATE_EMAIL)) {
			$error .= 'ERROR: E-Mail Address does not appear to be valid!' . "\n";
		}

		// If not cloud then we validate the password
		if (!$cloud) {
			$password = html_entity_decode($option['password'], ENT_QUOTES, 'UTF-8');

			if ((utf8_strlen($password) < 3) || (utf8_strlen($password) > 20)) {
				$error .= 'ERROR: Password must be between 4 and 20 characters!' . "\n";
			}
		} elseif (!$option['password']) {
			$error .= 'ERROR: Password hash required!' . "\n";
		}

		if ($error) {
			$output  = 'ERROR: Validation failed: ' . "\n";
			$output .= $error . "\n\n";

			return $output;
		}

		// Make sure there is a SQL file to load sample data
		$file = DIR_APPLICATION . 'opencart.sql';

		if (!is_file($file)) {
			return 'ERROR: Could not load SQL file: ' . $file;
		}

		if (!$cloud) {
			$db_driver   = html_entity_decode($option['db_driver'], ENT_QUOTES, 'UTF-8');
			$db_hostname = html_entity_decode($option['db_hostname'], ENT_QUOTES, 'UTF-8');
			$db_username = html_entity_decode($option['db_username'], ENT_QUOTES, 'UTF-8');
			$db_password = html_entity_decode($option['db_password'], ENT_QUOTES, 'UTF-8');
			$db_database = html_entity_decode($option['db_database'], ENT_QUOTES, 'UTF-8');
			$db_port     = $option['db_port'];
			$db_prefix   = $option['db_prefix'];
		} else {
			$db_driver   = getenv('DB_DRIVER', true);
			$db_hostname = getenv('DB_HOSTNAME', true);
			$db_username = getenv('DB_USERNAME', true);
			$db_password = getenv('DB_PASSWORD', true);
			$db_database = getenv('DB_DATABASE', true);
			$db_port     = getenv('DB_PORT', true);
			$db_prefix   = getenv('DB_PREFIX', true);
		}

		try {
			// Database
			$db = new \DB($db_driver, $db_hostname, $db_username, $db_password, $db_database, $db_port);
		} catch (ErrorException $e) {
			return 'Error: Could not make a database link using ' . $db_username . '@' . $db_hostname . '!' . "\n";
		}

		// Set up Database structure
		$this->load->helper('db_schema');

		$tables = db_schema();

		foreach ($tables as $table) {
			$table_query = $db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . $db_database . "' AND TABLE_NAME = '" . $db_prefix . $table['name'] . "'");

			if ($table_query->num_rows) {
				$db->query("DROP TABLE `" . $db_prefix . $table['name'] . "`");
			}

			$sql = "CREATE TABLE `" . $db_prefix . $table['name'] . "` (" . "\n";

			foreach ($table['field'] as $field) {
				$sql .= "  `" . $field['name'] . "` " . $field['type'] . (!empty($field['not_null']) ? " NOT NULL" : "") . (isset($field['default']) ? " DEFAULT '" . $db->escape($field['default']) . "'" : "") . (!empty($field['auto_increment']) ? " AUTO_INCREMENT" : "") . ",\n";
			}

			if (isset($table['primary'])) {
				$primary_data = array();

				foreach ($table['primary'] as $primary) {
					$primary_data[] = "`" . $primary . "`";
				}

				$sql .= "  PRIMARY KEY (" . implode(",", $primary_data) . "),\n";
			}

			if (isset($table['index'])) {
				foreach ($table['index'] as $index) {
					$index_data = array();

					foreach ($index['key'] as $key) {
						$index_data[] = "`" . $key . "`";
					}

					$sql .= "  KEY `" . $index['name'] . "` (" . implode(",", $index_data) . "),\n";
				}
			}

			$sql = rtrim($sql, ",\n") . "\n";
			$sql .= ") ENGINE=" . $table['engine'] . " CHARSET=" . $table['charset'] . " COLLATE=" . $table['collate'] . ";\n";

			$db->query($sql);
		}

		// Setup database data
		$lines = file($file, FILE_IGNORE_NEW_LINES);

		if ($lines) {
			$sql = '';

			$start = false;

			foreach ($lines as $line) {
				if (substr($line, 0, 12) == 'INSERT INTO ') {
					$sql = '';

					$start = true;
				}

				if ($start) {
					$sql .= $line;
				}

				if (substr($line, -2) == ');') {
					$db->query(str_replace("INSERT INTO `oc_", "INSERT INTO `" . $db_prefix, $sql));

					$start = false;
				}
			}

			$db->query("SET CHARACTER SET utf8");

			$db->query("SET @@session.sql_mode = 'MYSQL40'");

			$db->query("DELETE FROM `" . $db_prefix . "user` WHERE user_id = '1'");

			// If cloud we do not need to hash the password as we will be passing the password hash
			if (!$cloud) {
				$password = password_hash(html_entity_decode($option['password'], ENT_QUOTES, 'UTF-8'), PASSWORD_DEFAULT);
			} else {
				$password = $option['password'];
			}

			$db->query("INSERT INTO `" . $db_prefix . "user` SET user_id = '1', user_group_id = '1', username = '" . $db->escape($option['username']) . "', salt = '', password = '" . $db->escape($password) . "', firstname = 'John', lastname = 'Doe', email = '" . $db->escape($option['email']) . "', status = '1', date_added = NOW()");

			$db->query("DELETE FROM `" . $db_prefix . "setting` WHERE `key` = 'config_email'");
			$db->query("INSERT INTO `" . $db_prefix . "setting` SET `code` = 'config', `key` = 'config_email', value = '" . $db->escape($option['email']) . "'");

			$db->query("DELETE FROM `" . $db_prefix . "setting` WHERE `key` = 'config_encryption'");
			$db->query("INSERT INTO `" . $db_prefix . "setting` SET `code` = 'config', `key` = 'config_encryption', value = '" . $db->escape(token(1024)) . "'");

			$db->query("UPDATE `" . $db_prefix . "product` SET `viewed` = '0'");

			$db->query("INSERT INTO `" . $db_prefix . "api` SET username = 'Default', `key` = '" . $db->escape(token(256)) . "', status = 1, date_added = NOW(), date_modified = NOW()");

			$last_id = $db->getLastId();

			$db->query("DELETE FROM `" . $db_prefix . "setting` WHERE `key` = 'config_api_id'");
			$db->query("INSERT INTO `" . $db_prefix . "setting` SET `code` = 'config', `key` = 'config_api_id', value = '" . (int)$last_id . "'");

			// set the current years prefix
			$db->query("UPDATE `" . $db_prefix . "setting` SET `value` = 'INV-" . date('Y') . "-00' WHERE `key` = 'config_invoice_prefix'");
		}

		// Cloud Install
		if (!$cloud) {
			// Write config files
			$output = '<?php' . "\n";
			$output .= '// HTTP' . "\n";
			$output .= 'define(\'HTTP_SERVER\', \'' . $option['http_server'] . '\');' . "\n\n";

			$output .= '// HTTPS' . "\n";
			$output .= 'define(\'HTTPS_SERVER\', \'' . $option['http_server'] . '\');' . "\n\n";

			$output .= '// DIR' . "\n";
			$output .= 'define(\'DIR_APPLICATION\', \'' . addslashes(DIR_OPENCART) . 'catalog/\');' . "\n";
			$output .= 'define(\'DIR_SYSTEM\', \'' . addslashes(DIR_OPENCART) . 'system/\');' . "\n";
			$output .= 'define(\'DIR_IMAGE\', \'' . addslashes(DIR_OPENCART) . 'image/\');' . "\n";
			$output .= 'define(\'DIR_STORAGE\', DIR_SYSTEM . \'storage/\');' . "\n";
			$output .= 'define(\'DIR_LANGUAGE\', DIR_APPLICATION . \'language/\');' . "\n";
			$output .= 'define(\'DIR_TEMPLATE\', DIR_APPLICATION . \'view/theme/\');' . "\n";
			$output .= 'define(\'DIR_CONFIG\', DIR_SYSTEM . \'config/\');' . "\n";
			$output .= 'define(\'DIR_CACHE\', DIR_STORAGE . \'cache/\');' . "\n";
			$output .= 'define(\'DIR_DOWNLOAD\', DIR_STORAGE . \'download/\');' . "\n";
			$output .= 'define(\'DIR_LOGS\', DIR_STORAGE . \'logs/\');' . "\n";
			$output .= 'define(\'DIR_MODIFICATION\', DIR_STORAGE . \'modification/\');' . "\n";
			$output .= 'define(\'DIR_SESSION\', DIR_STORAGE . \'session/\');' . "\n";
			$output .= 'define(\'DIR_UPLOAD\', DIR_STORAGE . \'upload/\');' . "\n\n";

			$output .= '// DB' . "\n";
			$output .= 'define(\'DB_DRIVER\', \'' . addslashes($option['db_driver']) . '\');' . "\n";
			$output .= 'define(\'DB_HOSTNAME\', \'' . addslashes($option['db_hostname']) . '\');' . "\n";
			$output .= 'define(\'DB_USERNAME\', \'' . addslashes($option['db_username']) . '\');' . "\n";
			$output .= 'define(\'DB_PASSWORD\', \'' . addslashes($option['db_password']) . '\');' . "\n";
			$output .= 'define(\'DB_DATABASE\', \'' . addslashes($option['db_database']) . '\');' . "\n";
			$output .= 'define(\'DB_PREFIX\', \'' . addslashes($option['db_prefix']) . '\');' . "\n";
			$output .= 'define(\'DB_PORT\', \'' . addslashes($option['db_port']) . '\');' . "\n";

			$file = fopen(DIR_OPENCART . 'config.php', 'w');

			fwrite($file, $output);

			fclose($file);

			$output = '<?php' . "\n";
			$output .= '// HTTP' . "\n";
			$output .= 'define(\'HTTP_SERVER\', \'' . $option['http_server'] . 'admin/\');' . "\n";
			$output .= 'define(\'HTTP_CATALOG\', \'' . $option['http_server'] . '\');' . "\n";

			$output .= '// HTTPS' . "\n";
			$output .= 'define(\'HTTPS_SERVER\', \'' . $option['http_server'] . 'admin/\');' . "\n";
			$output .= 'define(\'HTTPS_CATALOG\', \'' . $option['http_server'] . '\');' . "\n";

			$output .= '// DIR' . "\n";
			$output .= 'define(\'DIR_APPLICATION\', \'' . addslashes(DIR_OPENCART) . 'admin/\');' . "\n";
			$output .= 'define(\'DIR_SYSTEM\', \'' . addslashes(DIR_OPENCART) . 'system/\');' . "\n";
			$output .= 'define(\'DIR_IMAGE\', \'' . addslashes(DIR_OPENCART) . 'image/\');' . "\n";
			$output .= 'define(\'DIR_STORAGE\', DIR_SYSTEM . \'storage/\');' . "\n";
			$output .= 'define(\'DIR_CATALOG\', \'' . addslashes(DIR_OPENCART) . 'catalog/\');' . "\n";
			$output .= 'define(\'DIR_LANGUAGE\', DIR_APPLICATION . \'language/\');' . "\n";
			$output .= 'define(\'DIR_TEMPLATE\', DIR_APPLICATION . \'view/template/\');' . "\n";
			$output .= 'define(\'DIR_CONFIG\', DIR_SYSTEM . \'config/\');' . "\n";
			$output .= 'define(\'DIR_CACHE\', DIR_STORAGE . \'cache/\');' . "\n";
			$output .= 'define(\'DIR_DOWNLOAD\', DIR_STORAGE . \'download/\');' . "\n";
			$output .= 'define(\'DIR_LOGS\', DIR_STORAGE . \'logs/\');' . "\n";
			$output .= 'define(\'DIR_MODIFICATION\', DIR_STORAGE . \'modification/\');' . "\n";
			$output .= 'define(\'DIR_SESSION\', DIR_STORAGE . \'session/\');' . "\n";
			$output .= 'define(\'DIR_UPLOAD\', DIR_STORAGE . \'upload/\');' . "\n\n";

			$output .= '// DB' . "\n";
			$output .= 'define(\'DB_DRIVER\', \'' . addslashes($option['db_driver']) . '\');' . "\n";
			$output .= 'define(\'DB_HOSTNAME\', \'' . addslashes($option['db_hostname']) . '\');' . "\n";
			$output .= 'define(\'DB_USERNAME\', \'' . addslashes($option['db_username']) . '\');' . "\n";
			$output .= 'define(\'DB_PASSWORD\', \'' . addslashes($option['db_password']) . '\');' . "\n";
			$output .= 'define(\'DB_DATABASE\', \'' . addslashes($option['db_database']) . '\');' . "\n";
			$output .= 'define(\'DB_PREFIX\', \'' . addslashes($option['db_prefix']) . '\');' . "\n";
			$output .= 'define(\'DB_PORT\', \'' . addslashes($option['db_port']) . '\');' . "\n\n";

			$output .= '// OpenCart API' . "\n";
			$output .= 'define(\'OPENCART_SERVER\', \'https://www.opencart.com/\');';

			$file = fopen(DIR_OPENCART . 'admin/config.php', 'w');

			fwrite($file, $output);

			fclose($file);
		}

		// Return success message
		$output  = 'SUCCESS! OpenCart successfully installed on your server' . "\n";
		$output .= 'Store link: ' . $option['http_server'] . "\n";
		$output .= 'Admin link: ' . $option['http_server'] . 'admin/' . "\n\n";

		return $output;
	}

	public function usage() {
		$option = implode(' ', array(
			'--username',
			'admin',
			'--email',
			'email@example.com',
			'--password',
			'password',
			'--http_server',
			'http://localhost/opencart/',
			'--cloud',
			'0',
			'--db_driver',
			'mysqli',
			'--db_hostname',
			'localhost',
			'--db_username',
			'root',
			'--db_password',
			'pass',
			'--db_database',
			'opencart',
			'--db_port',
			'3306',
			'--db_prefix',
			'oc_'
		));

		$output  = 'Usage:' . "\n";
		$output .= '======' . "\n\n";
		$output .= 'php cli_install.php install ' . $option . "\n\n";

		return $output;
	}
}

// Controller
$controller = new ControllerCliInstall($registry);
$controller->index();

// Output
$response->output();