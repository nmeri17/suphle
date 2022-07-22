<?php
	namespace Suphle\Request;

	use Suphle\Contracts\Requests\{RequestValidator, FileInputReader};

	use Suphle\Routing\PathPlaceholders;

	class ValidatorManager {

		private $placeholderStorage, $validator, $payloadStorage,

		$fileInputReader, $actionRules = [];

		public function __construct (

			RequestValidator $validator, PathPlaceholders $placeholderStorage,

			PayloadStorage $payloadStorage, FileInputReader $fileInputReader
		) {

			$this->validator = $validator;

			$this->placeholderStorage = $placeholderStorage;

			$this->payloadStorage = $payloadStorage;

			$this->fileInputReader = $fileInputReader;
		}

		public function validationErrors ():iterable {

			$mergedPayload = array_merge(
				$this->placeholderStorage->getAllSegmentValues(),

				$this->payloadStorage->fullPayload(),

				$this->fileInputReader->getFileObjects()
			);

			$this->validator->validate($mergedPayload, $this->actionRules);

			return $this->validator->getErrors();
		}

		public function isValidated (): bool {

			return empty($this->validationErrors());
		}

		public function setActionRules (array $rules):void {

			$this->actionRules = $rules;
		}
	}
?>