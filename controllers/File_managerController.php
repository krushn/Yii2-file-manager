<?php

namespace app\controllers;

use Yii;
use app\models\File_manager;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\data\Pagination;
use yii\widgets\LinkPager;
use yii\web\UploadedFile;

define('HTTP_CATALOG',  Yii::getAlias('@web').'/');   
define('DIR_IMAGE', Yii::$app->basePath. '/upload/'); 

class File_managerController extends Controller {

	public function actionIndex() {
		
		$request = Yii::$app->request;

		$server = HTTP_CATALOG;
		
		if ($request->get('filter_name')) {
			$filter_name = rtrim(str_replace('*', '', $request->get('filter_name')), '/');
		} else {
			$filter_name = null;
		}

		// Make sure we have the correct directory
		if ($request->get('directory')) {
			$directory = rtrim(DIR_IMAGE . '/' . str_replace('*', '', $request->get('directory')), '/');
		} else {
			$directory = DIR_IMAGE;
		}

		if ($request->get('page')) {
			$page = $request->get('page');
		} else {
			$page = 1;
		}

		$directories = array();
		$files = array();

		$data['images'] = array();

		// Get directories
		$directories = glob($directory . '/' . $filter_name . '*', GLOB_ONLYDIR);

		if (!$directories) {
			$directories = array();
		}

		// Get files
		$files = glob($directory . '/' . $filter_name . '*.{jpg,jpeg,png,gif,JPG,JPEG,PNG,GIF}', GLOB_BRACE);

		if (!$files) {
			$files = array();
		}

		// Merge directories and files
		$images = array_merge($directories, $files);

		// Get total number of files and directories
		$image_total = count($images);

		// Split the array based on current page number and max number of items per page of 10
		$images = array_splice($images, ($page - 1) * 16, 16);

		foreach ($images as $image) {
			$name = str_split(basename($image), 14);

			if (is_dir($image)) {

				$name = implode(' ', $name);

				if($name == 'cache')
					continue;

				$url = '';

				if ($request->get('target')) {
					$url .= '&target=' . $request->get('target');
				}

				if ($request->get('thumb')) {
					$url .= '&thumb=' . $request->get('thumb');
				}

				$data['images'][] = array(
					'thumb' => '',
					'name'  => $name,
					'type'  => 'directory',
					'path'  => substr($image, strlen(utf8_decode(DIR_IMAGE))),
					'href'  => Url::to(['file_manager/index', 'directory' => urlencode(substr($image, strlen(utf8_decode(DIR_IMAGE . '/')))) . $url])
				);
			} elseif (is_file($image)) {
				$data['images'][] = array(
					'thumb' => File_manager::resize(substr($image, strlen(utf8_decode(DIR_IMAGE))), 100, 100),
					'name'  => implode(' ', $name),
					'type'  => 'image',
					'path'  => substr($image, strlen(utf8_decode(DIR_IMAGE))),
					'href'  => $server . '/../../upload/' . substr($image, strlen(utf8_decode(DIR_IMAGE)))
				);
			}
		}

		if ($request->get('directory')) {
			$data['directory'] = urlencode($request->get('directory'));
		} else {
			$data['directory'] = '';
		}

		$data['filter_name'] = $request->get('filter_name');
		$data['target'] = $request->get('target');
		$data['thumb'] = $request->get('thumb');
		
		// Parent
		$url = ['file_manager/index'];

		if ($request->get('directory')) {
			$pos = strrpos($request->get('directory'), '/');

			if ($pos) {
				$url['directory'] = urlencode(substr($request->get('directory'), 0, $pos));
			}
		}

		if ($request->get('target')) {
			$url['target'] = $request->get('target');
		}

		if ($request->get('thumb')) {
			$url['thumb'] = $request->get('thumb');
		}

		$data['parent'] = Url::to($url);

		// Refresh
		$url = ['file_manager/index'];

		if ($request->get('directory')) {
			$url['directory'] = urlencode($request->get('directory'));
		}

		if ($request->get('target')) {
			$url['target'] = $request->get('target');
		}

		if ($request->get('thumb')) {
			$url['thumb'] = $request->get('thumb');
		}

		$data['refresh'] = Url::to($url);

		$url = '';

		if ($request->get('directory')) {
			$url .= '&directory=' . urlencode(html_entity_decode($request->get('directory'), ENT_QUOTES, 'UTF-8'));
		}

		if ($request->get('filter_name')) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($request->get('filter_name'), ENT_QUOTES, 'UTF-8'));
		}

		if ($request->get('target')) {
			$url .= '&target=' . $request->get('target');
		}

		if ($request->get('thumb')) {
			$url .= '&thumb=' . $request->get('thumb');
		}

		$data['pagination'] = LinkPager::widget([
		    'pagination' => new Pagination(['totalCount' => $image_total])
		]);
		
		return $this->renderPartial('index', $data);
	}

	public function actionUpload() {
		
		$request = Yii::$app->request;

		$json = array();

		// Make sure we have the correct directory
		if ($request->get('directory')) {
			$directory = DIR_IMAGE . '/' . $request->get('directory'). '/';
		} else {
			$directory = DIR_IMAGE;
		}

		// Check its a directory
		if (!is_dir($directory)) {
			$json['error'] = 'Directory not found!';
		}

		if (!$json) {

			$model = new File_manager();

	        $model->images = UploadedFile::getInstances($model, 'images');

            if (!$model->upload($directory)) {
            	$json['error'] = 'Error: Something goes wrong!';
            }
		}

		if (!$json) {
			$json['success'] = 'Success: File uploaded successfully!';
		}

		Yii::$app->response->format = 'json';
		return $json;
	}

	public function actionFolder() {

		$request = Yii::$app->request;

		$json = array();

		// Make sure we have the correct directory
		if ($request->get('directory')) {
			$directory = rtrim(DIR_IMAGE . '/' . $request->get('directory'), '/');
		} else {
			$directory = DIR_IMAGE;
		}

		// Check its a directory
		if (!is_dir($directory) || substr(str_replace('\\', '/', realpath($directory)), 0, strlen(DIR_IMAGE)) != DIR_IMAGE) {
			//$json['error'] = 'Error: Not valid directory!';
		}

		if ($request->isPost) {
			
			// Sanitize the folder name
			$folder = basename(html_entity_decode($request->post('folder'), ENT_QUOTES, 'UTF-8'));

			// Validate the filename length
			if ((strlen(utf8_decode($folder)) < 3) || (strlen(utf8_decode($folder)) > 128)) {
				$json['error'] = 'Error: Filename should be between 3 to 128!';
			}

			// Check if directory already exists or not
			if (is_dir($directory . '/' . $folder)) {
				$json['error'] = 'Error: Already exists!';
			}
		}

		if (!isset($json['error'])) {
			mkdir($directory . '/' . $folder, 0777);
			chmod($directory . '/' . $folder, 0777);

			@touch($directory . '/' . $folder . '/' . 'index.html');

			$json['success'] = 'Success: Directory created successfully!';
		}

		Yii::$app->response->format = 'json';
		return $json;
	}

	public function actionDelete() {

		$request = Yii::$app->request;
			
		$json = array();

		if ($request->post('path')) {
			$paths = $request->post('path');
		} else {
			$paths = array();
		}

		// Loop through each path to run validations
		foreach ($paths as $path) {
			// Check path exsists
			if ($path == DIR_IMAGE) {
				$json['error'] = 'Error on delete!';
				break;
			}
		}

		if (!$json) {
			// Loop through each path
			foreach ($paths as $path) {
				$path = rtrim(DIR_IMAGE . $path, '/');

				// If path is just a file delete it
				if (is_file($path)) {
					unlink($path);

				// If path is a directory beging deleting each file and sub folder
				} elseif (is_dir($path)) {
					$files = array();

					// Make path into an array
					$path = array($path . '*');

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

			$json['success'] = 'Success: deleted successfully!';
		}

		Yii::$app->response->format = 'json';
		return $json;
	}
}