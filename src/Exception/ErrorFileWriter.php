<?php
	namespace Suphle\Exception;

	use Throwable;

	class ErrorFileWriter {

		private $uncatchableFile, $catchableFile;

		/**
		 * @param $uncatchableFile Full file paths
		*/
		public function __construct (string $uncatchableFile, string $catchableFile) {

			$this->uncatchableFile = $uncatchableFile;

			$this->catchableFile = $catchableFile;
		}

		public function setUncatchableHandlers ():self {

			$newLine = ",\n";

			set_error_handler(function($errno, $errmsg) use ($newLine) {

				file_put_contents(

					$this->uncatchableFile, $errmsg . $newLine,

					FILE_APPEND
				);
			});

			set_exception_handler(function ($exception) use ($newLine) {

				file_put_contents(

					$this->uncatchableFile,

					$exception->getMessage() . $newLine, FILE_APPEND
				);
			});

			return $this;
		}

		public function attemptOperation (callable $action) {
		
			try {

				return $action();

				restore_error_handler();

				restore_exception_handler();
			}
			catch (Throwable $exception) {

				file_put_contents(

					$this->catchableFile, $exception->getMessage()
				);
			}
		}
	}
?>