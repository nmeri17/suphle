<?php
	namespace Suphle\File;

	trait SetsExecutionPath {

		protected string $executionPath;

		public function setExecutionPath (string $path):void {

			$this->executionPath = $path;
		}
	}
?>