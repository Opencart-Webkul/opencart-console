<?php
class ControllerCommonFileManager extends Controller {
	public function index() {
		$this->load->language('common/filemanager');

		// Make sure we have the correct directory
		if (isset($this->request->get['directory'])) {
			$directory = DIR_IMAGE . 'catalog/' . html_entity_decode($this->request->get['directory'], ENT_QUOTES, 'UTF-8') . '/';
		} else {
			$directory = DIR_IMAGE . 'catalog/';
		}

		if (isset($this->request->get['filter_name'])) {
			$filter_name = basename(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		} else {
			$filter_name = '';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$data['directories'] = array();

		// Get directories
		$directories = glob($directory . '*', GLOB_ONLYDIR);

		if ($directories) {
			// Split the array based on current page number and max number of items per page of 10
			$images = array_splice($directories, ($page - 1) * 16, 16);

			foreach ($images as $image) {
				if (substr(str_replace('\\', '/', realpath($image)), 0, utf8_strlen(DIR_IMAGE . 'catalog')) == DIR_IMAGE . 'catalog') {
					$name = basename($image);

					$url = '';

					if (isset($this->request->get['target'])) {
						$url .= '&target=' . $this->request->get['target'];
					}

					if (isset($this->request->get['thumb'])) {
						$url .= '&thumb=' . $this->request->get['thumb'];
					}

					if (isset($this->request->get['ckeditor'])) {
						$url .= '&ckeditor=' . $this->request->get['ckeditor'];
					}

					$data['directories'][] = array(
						'name' => $name,
						'path' => utf8_substr($image, utf8_strlen(DIR_IMAGE)),
						'type' => 'directory',
						'href' => $this->url->link('common/filemanager', 'user_token=' . $this->session->data['user_token'] . '&directory=' . urlencode(utf8_substr($image, utf8_strlen(DIR_IMAGE . 'catalog/'))) . $url)
					);
				}
			}
		}

		$this->load->model('tool/image');

		$data['images'] = array();

		$files = glob($directory . $filter_name . '*.{jpg,jpeg,png,gif,JPG,JPEG,PNG,GIF}', GLOB_BRACE);

		if ($files) {
			// Split the array based on current page number and max number of items per page of 10
			$images = array_splice($files, ($page - 1) * 16, 16 - count($data['images']));

			foreach ($images as $image) {
				if (substr(str_replace('\\', '/', realpath($image)), 0, utf8_strlen(DIR_IMAGE . 'catalog')) == DIR_IMAGE . 'catalog') {
					$name = basename($image);

					$data['images'][] = array(
						'thumb' => $this->model_tool_image->resize(utf8_substr($image, utf8_strlen(DIR_IMAGE)), 136, 136),
						'name'  => $name,
						'path'  => utf8_substr($image, utf8_strlen(DIR_IMAGE)),
						'href'  => HTTP_CATALOG . 'image/' . utf8_substr($image, utf8_strlen(DIR_IMAGE))
					);
				}
			}
		}

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->get['directory'])) {
			$data['directory'] = urldecode($this->request->get['directory']);
		} else {
			$data['directory'] = '';
		}

		if (isset($this->request->get['filter_name'])) {
			$data['filter_name'] = $this->request->get['filter_name'];
		} else {
			$data['filter_name'] = '';
		}

		// Return the target ID for the file manager to set the value
		if (isset($this->request->get['target'])) {
			$data['target'] = $this->request->get['target'];
		} else {
			$data['target'] = '';
		}

		// Return the thumbnail for the file manager to show a thumbnail
		if (isset($this->request->get['thumb'])) {
			$data['thumb'] = $this->request->get['thumb'];
		} else {
			$data['thumb'] = '';
		}

		if (isset($this->request->get['ckeditor'])) {
			$data['ckeditor'] = $this->request->get['ckeditor'];
		} else {
			$data['ckeditor'] = '';
		}

		// Parent
		$url = '';

		if (isset($this->request->get['directory'])) {
			$pos = strrpos($this->request->get['directory'], '/');

			if ($pos) {
				$url .= '&directory=' . urlencode(substr($this->request->get['directory'], 0, $pos));
			}
		}

		if (isset($this->request->get['target'])) {
			$url .= '&target=' . $this->request->get['target'];
		}

		if (isset($this->request->get['thumb'])) {
			$url .= '&thumb=' . $this->request->get['thumb'];
		}

		if (isset($this->request->get['ckeditor'])) {
			$url .= '&ckeditor=' . $this->request->get['ckeditor'];
		}

		$data['parent'] = $this->url->link('common/filemanager', 'user_token=' . $this->session->data['user_token'] . $url);

		// Refresh
		$url = '';

		if (isset($this->request->get['directory'])) {
			$url .= '&directory=' . urlencode(html_entity_decode($this->request->get['directory'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}
		
		if (isset($this->request->get['target'])) {
			$url .= '&target=' . $this->request->get['target'];
		}

		if (isset($this->request->get['thumb'])) {
			$url .= '&thumb=' . $this->request->get['thumb'];
		}

		if (isset($this->request->get['ckeditor'])) {
			$url .= '&ckeditor=' . $this->request->get['ckeditor'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['refresh'] = $this->url->link('common/filemanager', 'user_token=' . $this->session->data['user_token'] . $url);

		$url = '';

		if (isset($this->request->get['directory'])) {
			$url .= '&directory=' . urlencode(html_entity_decode($this->request->get['directory'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['target'])) {
			$url .= '&target=' . $this->request->get['target'];
		}

		if (isset($this->request->get['thumb'])) {
			$url .= '&thumb=' . $this->request->get['thumb'];
		}

		if (isset($this->request->get['ckeditor'])) {
			$url .= '&ckeditor=' . $this->request->get['ckeditor'];
		}

		// Get total number of files and directories
		$data['pagination'] = $this->load->controller('common/pagination', array(
			'total' => count(array_merge((array)$directories, (array)$files)),
			'page'  => $page,
			'limit' => 16,
			'url'   => $this->url->link('common/filemanager', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}')
		));

		$this->response->setOutput($this->load->view('common/filemanager', $data));
	}

	public function upload() {
		$this->load->language('common/filemanager');

		$json = array();

		// Check user has permission
		if (!$this->user->hasPermission('modify', 'common/filemanager')) {
			$json['error'] = $this->language->get('error_permission');
		}

		// Make sure we have the correct directory
		if (isset($this->request->get['directory'])) {
			$directory = DIR_IMAGE . 'catalog/' . html_entity_decode($this->request->get['directory'], ENT_QUOTES, 'UTF-8') . '/';
		} else {
			$directory = DIR_IMAGE . 'catalog/';
		}

		// Check its a directory
		if (!is_dir($directory) || substr(str_replace('\\', '/', realpath($directory)), 0, strlen(DIR_IMAGE . 'catalog')) != DIR_IMAGE . 'catalog') {
			$json['error'] = $this->language->get('error_directory');
		}

		if (!$json) {
			// Check if multiple files are uploaded or just one
			$files = array();

			if (!empty($this->request->files['file']['name']) && is_array($this->request->files['file']['name'])) {
				foreach (array_keys($this->request->files['file']['name']) as $key) {
					$files[] = array(
						'name'     => $this->request->files['file']['name'][$key],
						'type'     => $this->request->files['file']['type'][$key],
						'tmp_name' => $this->request->files['file']['tmp_name'][$key],
						'error'    => $this->request->files['file']['error'][$key],
						'size'     => $this->request->files['file']['size'][$key]
					);
				}
			}

			foreach ($files as $file) {
				if (is_file($file['tmp_name'])) {
					// Sanitize the filename
					$filename = preg_replace('[/\\?%*:|"<>]', '', basename(html_entity_decode($file['name'], ENT_QUOTES, 'UTF-8')));

					// Validate the filename length
					if ((utf8_strlen($filename) < 4) || (utf8_strlen($filename) > 255)) {
						$json['error'] = $this->language->get('error_filename');
					}

					// Allowed file extension types
					$allowed = array(
						'jpg',
						'jpeg',
						'gif',
						'png'
					);

					if (!in_array(utf8_strtolower(utf8_substr(strrchr($filename, '.'), 1)), $allowed)) {
						$json['error'] = $this->language->get('error_filetype');
					}

					// Allowed file mime types
					$allowed = array(
						'image/jpeg',
						'image/pjpeg',
						'image/png',
						'image/x-png',
						'image/gif'
					);

					if (!in_array($file['type'], $allowed)) {
						$json['error'] = $this->language->get('error_filetype');
					}

					// Return any upload error
					if ($file['error'] != UPLOAD_ERR_OK) {
						$json['error'] = $this->language->get('error_upload_' . $file['error']);
					}
				} else {
					$json['error'] = $this->language->get('error_upload');
				}

				if (!$json) {
					move_uploaded_file($file['tmp_name'], $directory . $filename);
				}
			}
		}

		if (!$json) {
			$json['success'] = $this->language->get('text_uploaded');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function folder() {
		$this->load->language('common/filemanager');

		$json = array();

		// Check user has permission
		if (!$this->user->hasPermission('modify', 'common/filemanager')) {
			$json['error'] = $this->language->get('error_permission');
		}

		// Make sure we have the correct directory
		if (isset($this->request->get['directory'])) {
			$directory = DIR_IMAGE . 'catalog/' . html_entity_decode($this->request->get['directory'], ENT_QUOTES, 'UTF-8') . '/';
		} else {
			$directory = DIR_IMAGE . 'catalog/';
		}

		// Check its a directory
		if (!is_dir($directory) || substr(str_replace('\\', '/', realpath($directory)), 0, strlen(DIR_IMAGE . 'catalog')) != DIR_IMAGE . 'catalog') {
			$json['error'] = $this->language->get('error_directory');
		}

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			// Sanitize the folder name
			$folder = preg_replace('[/\\?%*:|"<>]', '', basename(html_entity_decode($this->request->post['folder'], ENT_QUOTES, 'UTF-8')));

			// Validate the filename length
			if ((utf8_strlen($folder) < 3) || (utf8_strlen($folder) > 128)) {
				$json['error'] = $this->language->get('error_folder');
			}

			// Check if directory already exists or not
			if (is_dir($directory . '/' . $folder)) {
				$json['error'] = $this->language->get('error_exists');
			}
		}

		if (!$json) {
			mkdir($directory . '/' . $folder, 0777);
			chmod($directory . '/' . $folder, 0777);

			@touch($directory . '/' . $folder . '/' . 'index.html');

			$json['success'] = $this->language->get('text_directory');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function delete() {
		$this->load->language('common/filemanager');

		$json = array();

		// Check user has permission
		if (!$this->user->hasPermission('modify', 'common/filemanager')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (isset($this->request->post['path'])) {
			$paths = $this->request->post['path'];
		} else {
			$paths = array();
		}

		// Loop through each path to run validations
		foreach ($paths as $path) {
			// Convert any html encoded characters.
			$path = html_entity_decode($path, ENT_QUOTES, 'UTF-8');

			// Check path exists
			if (($path == DIR_IMAGE . 'catalog') || (substr(str_replace('\\', '/', realpath(DIR_IMAGE . $path)), 0, strlen(DIR_IMAGE . 'catalog')) != DIR_IMAGE . 'catalog')) {
				$json['error'] = $this->language->get('error_delete');

				break;
			}
		}

		if (!$json) {
			// Loop through each path
			foreach ($paths as $path) {
				$path = rtrim(DIR_IMAGE . html_entity_decode($path, ENT_QUOTES, 'UTF-8'), '/');

				// If path is just a file delete it
				if (is_file($path)) {
					unlink($path);

				// If path is a directory begin deleting each file and sub folder
				} elseif (is_dir($path)) {
					$files = array();

					// Make path into an array
					$path = array($path);

					// While the path array is still populated keep looping through
					while (count($path) != 0) {
						$next = array_shift($path);

						foreach (glob($next) as $file) {
							// If directory add to path array
							if (is_dir($file)) {
								$path[] = $file . '/*';
							}

							// Add the file to the files to be deleted array
							$files[] = $file;
						}
					}

					// Reverse sort the file array
					rsort($files);

					foreach ($files as $file) {
						// If file just delete
						if (is_file($file)) {
							unlink($file);

						// If directory use the remove directory function
						} elseif (is_dir($file)) {
							rmdir($file);
						}
					}
				}
			}

			$json['success'] = $this->language->get('text_delete');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}