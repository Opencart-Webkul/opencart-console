<?php
class ModelUpgrade1001 extends Model {
	public function upgrade() {
		// order_recurring
		$this->db->query("ALTER TABLE `" . DB_PREFIX . "order_recurring` CHANGE `status` `status` tinyint(4) NOT NULL AFTER `trial_price`");

		// order_recurring
		$query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "order_recurring' AND COLUMN_NAME = 'created'");

		if ($query->num_rows) {
			$query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "order_recurring' AND COLUMN_NAME = 'date_added'");

			if ($query->num_rows) {
				$this->db->query("UPDATE `" . DB_PREFIX . "order_recurring` SET `date_added` = `created` WHERE `date_added` IS NULL or `date_added` = ''");
				$this->db->query("ALTER TABLE `" . DB_PREFIX . "order_recurring` DROP `created`");
			} else {
				$this->db->query("ALTER TABLE `" . DB_PREFIX . "order_recurring` CHANGE `created` `date_added` datetime NOT NULL AFTER `status`");
			}
		}

		// order_recurring
		$query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "order_recurring' AND COLUMN_NAME = 'profile_id'");

		if ($query->num_rows) {
			$query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "order_recurring' AND COLUMN_NAME = 'recurring_id'");

			if ($query->num_rows) {
				$this->db->query("UPDATE `" . DB_PREFIX . "order_recurring` SET `recurring_id` = `profile_id` WHERE `recurring_id` IS NULL OR `recurring_id` = ''");

				$this->db->query("ALTER TABLE `" . DB_PREFIX . "order_recurring` DROP `profile_id`");
			} else {
				$this->db->query("ALTER TABLE `" . DB_PREFIX . "order_recurring` CHANGE `profile_id` `recurring_id` int(11) NOT NULL");
			}
		}

		// order_recurring
		$query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "order_recurring' AND COLUMN_NAME = 'profile_name'");

		if ($query->num_rows) {
			$query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "order_recurring' AND COLUMN_NAME = 'recurring_name'");

			if ($query->num_rows) {
				$this->db->query("UPDATE `" . DB_PREFIX . "order_recurring` SET `recurring_name` = `profile_name` WHERE `recurring_name` IS NULL or `recurring_name` = ''");
				$this->db->query("ALTER TABLE `" . DB_PREFIX . "order_recurring` DROP `profile_name`");
			} else {
				$this->db->query("ALTER TABLE `" . DB_PREFIX . "order_recurring` CHANGE `profile_name` `recurring_name` varchar(255) NOT NULL");
			}
		}

		// order_recurring
		$query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "order_recurring' AND COLUMN_NAME = 'profile_description'");

		if ($query->num_rows) {
			$query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "order_recurring' AND COLUMN_NAME = 'recurring_description'");

			if ($query->num_rows) {
				$this->db->query("UPDATE `" . DB_PREFIX . "order_recurring` SET `recurring_description` = `profile_description` WHERE `recurring_description` IS NULL or `recurring_description` = ''");
				$this->db->query("ALTER TABLE `" . DB_PREFIX . "order_recurring` DROP `profile_description`");
			} else {
				$this->db->query("ALTER TABLE `" . DB_PREFIX . "order_recurring` CHANGE `profile_description` `recurring_description` varchar(255) NOT NULL");
			}
		}



		// order
		$query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "order' AND COLUMN_NAME = 'shipping_custom_field'");

		if (!$query->num_rows) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "order` ADD `shipping_custom_field` TEXT NOT NULL AFTER `shipping_address_format`");
		}

		// banner
		$query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "banner_image' AND COLUMN_NAME = 'sort_order'");

		if (!$query->num_rows) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "banner_image` ADD `sort_order` INT(3) NOT NULL AFTER `image`");
		}

		// setting
		$query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "setting' AND COLUMN_NAME = 'group'");

		if ($query->num_rows) {
			// Leave code if already there and just drop group
			$query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "setting' AND COLUMN_NAME = 'code'");

			if ($query->num_rows) {
				$this->db->query("UPDATE `" . DB_PREFIX . "setting` SET `code` = `group` WHERE `code` IS NULL or `code` = ''");
				$this->db->query("ALTER TABLE `" . DB_PREFIX . "setting` DROP `group`");
			} else {
				$this->db->query("ALTER TABLE `" . DB_PREFIX . "setting` CHANGE `group` `code` varchar(128) NOT NULL");
			}
		}
		
		// tags
		$query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "product_tag'");

		if ($query->num_rows) {
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "language");

			foreach ($query->rows as $language) {
				// Get old tags
				$query = $this->db->query("SELECT p.product_id, GROUP_CONCAT(DISTINCT pt.tag order by pt.tag ASC SEPARATOR ',') as tags FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_tag pt ON (p.product_id = pt.product_id) WHERE pt.language_id = '" . (int)$language['language_id'] . "' GROUP BY p.product_id");

				if ($query->num_rows) {
					foreach ($query->rows as $row) {
						$this->db->query("UPDATE " . DB_PREFIX . "product_description SET tag = '" . $this->db->escape(strtolower($row['tags'])) . "' WHERE product_id = '" . (int)$row['product_id'] . "' AND language_id = '" . (int)$language['language_id'] . "'");
						$this->db->query("DELETE FROM " . DB_PREFIX . "product_tag WHERE product_id = '" . (int)$row['product_id'] . "' AND language_id = '" . (int)$language['language_id'] . "'");
					}
				}
			}
		}

		// Update the config.php by adding a DIR_MODIFICATION
		if (is_file(DIR_OPENCART . 'config.php')) {
			$files = glob(DIR_OPENCART . '{config.php,admin/config.php}', GLOB_BRACE);

			foreach ($files as $file) {
				if (!is_writable($file)) {
					exit(json_encode(array('error' => 'File is read only. Please adjust and try again: ' . $file)));
				}

				$upgrade = true;

				$lines = file($file);

				foreach ($lines as $line) {
					if (strpos($line, 'DIR_MODIFICATION') !== false) {
						$upgrade = false;

						break;
					}
				}

				if ($upgrade) {
					$output = '';

					foreach ($lines as $line_id => $line) {
						if (strpos($line, 'DIR_LOGS') !== false) {
							$new_line = "define('DIR_MODIFICATION', '" . str_replace("\\", "/", DIR_SYSTEM) . 'modification/' . "');";
							$output .= $new_line . "\n";
							$output .= $line;
						} else {
							$output .= $line;
						}
					}

					file_put_contents($file, $output);
				}
			}

			// Update the config.php by adding a DIR_UPLOAD
			foreach ($files as $file) {
				if (!is_writable($file)) {
					exit(json_encode(array('error' => 'File is read only. Please adjust and try again: ' . $file)));
				}

				$upgrade = true;

				$lines = file($file);

				foreach ($lines as $line) {
					if (strpos($line, 'DIR_UPLOAD') !== false) {
						$upgrade = false;

						break;
					}
				}

				if ($upgrade) {
					$output = '';

					foreach ($lines as $line_id => $line) {
						if (strpos($line, 'DIR_LOGS') !== false) {
							$new_line = "define('DIR_UPLOAD', '" . str_replace("\\", "/", DIR_SYSTEM) . 'upload/' . "');";
							$output .= $new_line . "\n";
							$output .= $line;
						} else {
							$output .= $line;
						}
					}

					file_put_contents($file, $output);
				}
			}

			// Update the config.php to change mysql to mysqli
			foreach ($files as $file) {
				if (!is_writable($file)) {
					exit(json_encode(array('error' => 'File is read only. Please adjust and try again: ' . $file)));
				}

				$upgrade = false;

				$lines = file($file);

				foreach ($lines as $line) {
					if (strpos($line, "define('DB_DRIVER', 'mysql'") !== false) {
						$upgrade = true;

						break;
					}
				}

				if ($upgrade) {
					$output = '';

					foreach ($lines as $line_id => $line) {
						if (strpos($line, "'mysql'") !== false) {
							$new_line = "define('DB_DRIVER', 'mysqli');";
							$output .= $new_line . "\n";
						} else {
							$output .= $line;
						}
					}

					file_put_contents($file, $output);
				}
			}
		}
	}
}