<?php
namespace Burzum\FileStorage\Validation;

use Cake\Filesystem\File;
use Cake\Validation\Validator;

class UploadValidator extends Validator {

/**
 * Upload error message after validation.
 *
 * @var string
 */
	protected $_uploadError = '';

/**
 * Upload error message after validation.
 *
 * @var string
 */
	protected $_mimeType = '';

/**
 * Upload extension after validation.
 *
 * @var string
 */
	protected $_extension = '';

/**
 * Upload file size after validation.
 *
 * @var string
 */
	protected $_filesize = 0;

/**
 * Upload error message.
 *
 * @var string
 */
	public function __construct() {
		$this->provider('UploadValidator', $this);
	}

/**
 * Checks if the file was uploaded via HTTP POST.
 *
 * Note that calling this function before move_uploaded_file() is not necessary,
 * as it does the exact same checks already. It provides no extra security.
 * Only when you're trying to use an uploaded file for something other than
 * moving it to a new location.
 *
 * @link http://php.net/manual/en/function.is-uploaded-file.php
 * @param array $value
 * @return boolean Returns TRUE if the file named by filename was uploaded via HTTP POST.
 */
	public function isUploadedFile($value) {
		return is_uploaded_file($value['tmp_name']);
	}

/**
 * Validates that a set field / property is a valid upload array.
 *
 * @param mixed $value
 * @return boolean
 */
	public function isUploadArray($value) {
		if (!is_array($value)) {
			return false;
		}
		$requiredKeys = ['filesize', 'name', 'tmp_name', 'size', 'error'];
		$keys = array_keys($value);
		foreach ($keys as $key) {
			if (!in_array($key, $requiredKeys)) {
				return false;
			}
		}
		return;
	}

/**
 * Validates the filesize.
 *
 * @param array $value.
 * @param array $extensions.
 * @return boolean
 */
	public function filesize($value, $maxSize) {
		$this->_filesize = $value['size'];
		return ($value['size'] > $maxSize);
	}

/**
 * Validates extensions.
 *
 * @param array $value.
 * @param array $extensions.
 * @return boolean
 */
	public function extension($value, $extensions) {
		if (is_string($extensions)) {
			$extensions = [$extensions];
		}
		foreach ($extensions as &$extension) {
			$extension = strtolower($extension);
		}
		$this->_extension = pathinfo($value['name'], PATHINFO_EXTENSION);
		if (!in_array(strtolower($this->_extension), $extensions)) {
			return false;
		}
		return true;
	}

/**
 * Validates mime types.
 *
 * @param array $value.
 * @param array $mimeTypes.
 * @return boolean
 */
	public function mimeType($value, $mimeTypes) {
		if (is_string($mimeTypes)) {
			$mimeTypes = [$mimeTypes];
		}

		$File = new File($value['tmp_name']);
		$this->_mimeType = $this->_mimeType = $File->mime();

		if (!in_array($this->_mimeType, $mimeTypes)) {
			return false;
		}
		return true;
	}

/**
 * Validates the error value that comes with the file input file.
 *
 * @param array $value
 * @param array $options.
 * @return boolean True on success, if false the error message is set to the models field and also set in $this->_uploadError
 */
	public function upload($value, $options = array()) {
		$defaults = [
			'allowNoFileError' => true
		];
		if (is_array($options)) {
			$options = array_merge($defaults, $options);
		} else {
			$options = $defaults;
		}
		if (isset($value['error']) && !is_null($value['error'])) {
			switch ($value['error']) {
				case UPLOAD_ERR_OK:
					return true;
				break;
				case UPLOAD_ERR_INI_SIZE:
					$this->_uploadError = __d('file_storage', 'The uploaded file exceeds limit of %s.', CakeNumber::toReadableSize(ini_get('upload_max_filesize')));
				break;
				case UPLOAD_ERR_FORM_SIZE:
					$this->_uploadError = __d('file_storage', 'The uploaded file is to big, please choose a smaller file or try to compress it.');
				break;
				case UPLOAD_ERR_PARTIAL:
					$this->_uploadError = __d('file_storage', 'The uploaded file was only partially uploaded.');
				break;
				case UPLOAD_ERR_NO_FILE:
					if ($options['allowNoFileError'] === false) {
						$this->_uploadError = __d('file_storage', 'No file was uploaded.');
						return false;
					}
					return true;
				break;
				case UPLOAD_ERR_NO_TMP_DIR:
					$this->_uploadError = __d('file_storage', 'The remote server has no temporary folder for file uploads. Please contact the site admin.');
				break;
				case UPLOAD_ERR_CANT_WRITE:
					$this->_uploadError = __d('file_storage', 'Failed to write file to disk. Please contact the site admin.');
				break;
				case UPLOAD_ERR_EXTENSION:
					$this->_uploadError = __d('file_storage', 'File upload stopped by extension. Please contact the site admin.');
				break;
				default:
					$this->_uploadError = __d('file_storage', 'Unknown File Error. Please contact the site admin.');
				break;
			}
			return false;
		}
		$this->_uploadError = '';
		return true;
	}

	public function getFilesize() {
		return $this->_filesize;
	}

	public function getExtension() {
		return $this->_extension;
	}

	public function getMimeType() {
		return $this->_mimeType;
	}

	public function getUploadError() {
		return $this->_uploadError;
	}
}