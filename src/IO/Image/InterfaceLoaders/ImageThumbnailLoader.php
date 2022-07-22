<?php
	namespace Suphle\IO\Image\InterfaceLoaders;

	use Suphle\Hydration\BaseInterfaceLoader;

	use Suphle\Adapters\Image\Optimizers\ImagineClient;

	class ImageThumbnailLoader extends BaseInterfaceLoader {

		public function afterBind ( $initialized):void {

			$initialized->setupClient();
		}

		public function concreteName ():string {

			return ImagineClient::class;
		}
	}
?>