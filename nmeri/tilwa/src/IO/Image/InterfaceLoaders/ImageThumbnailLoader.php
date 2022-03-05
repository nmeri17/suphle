<?php
	namespace Tilwa\IO\Image\InterfaceLoaders;

	use Tilwa\Hydration\BaseInterfaceLoader;

	use Tilwa\Adapters\Image\Optimizers\ImagineClient;

	class ImageThumbnailLoader extends BaseInterfaceLoader {

		public function afterBind ( $initialized):void {

			$initialized->setupClient();
		}

		public function concrete ():string {

			return ImagineClient::class;
		}
	}
?>