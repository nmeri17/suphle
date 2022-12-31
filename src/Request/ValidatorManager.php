<?php
	namespace Suphle\Request;

	use Suphle\Contracts\Requests\{RequestValidator, FileInputReader};

	use Suphle\Routing\PathPlaceholders;

	class ValidatorManager {

		protected array $actionRules = [];

		public function __construct(protected readonly RequestValidator $validator, protected readonly PathPlaceholders $placeholderStorage, protected readonly PayloadStorage $payloadStorage, protected readonly FileInputReader $fileInputReader) {

			//
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