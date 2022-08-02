<?php
	namespace Suphle\Request;

	trait SanitizesIntegerInput {

		protected function allInputToPositive (array $incomingData):array {

			foreach ($incomingData as $key => $value)

				if (is_numeric($value))

					$incomingData[$key] = $this->positiveIntValue($value);

			return $incomingData;
		}

		protected function positiveIntValue (string $value):int {

			$intValue = intval($value);

			if ($intValue < 0) $intValue = abs($intValue);

			return $intValue;
		}
	}
?>