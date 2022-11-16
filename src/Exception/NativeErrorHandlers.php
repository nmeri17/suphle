<?php
	namespace Suphle\Exception;

	use ErrorException;

	class NativeErrorHandlers {

		public function silentErrorToException ():void {

			set_error_handler(function ($errCode, $errMessage, $errFile, $errLine) {

				throw new ErrorException($errMessage, 0, $errCode, $errFile, $errLine);
			});
		}
	}
?>