<?php
	namespace Suphle\Request;

	use Suphle\Hydration\BaseInterfaceLoader;

	use Suphle\Adapters\Validators\LaravelValidator;

	use Suphle\Contracts\{ Config\AuthContract, Bridge\LaravelContainer, Database\OrmDialect};

	use Illuminate\Database\Capsule\Manager as Capsule;

	use Illuminate\Validation\{Factory, DatabasePresenceVerifier};
	
	use Illuminate\Translation\{FileLoader, Translator};

	use Illuminate\Filesystem\Filesystem;

	class ValidatorLoader extends BaseInterfaceLoader {

		private $laravelContainer, $ormDialect;

		public function __construct (LaravelContainer $laravelContainer, OrmDialect $ormDialect) {

			$this->laravelContainer = $laravelContainer;

			$this->ormDialect = $ormDialect;
		}

		public function bindArguments ():array {

			$client = $this->getValidationClient();

			$databaseManager = $this->ormDialect->getNativeClient()->getDatabaseManager();

			$client->setPresenceVerifier(new DatabasePresenceVerifier($databaseManager));

			return [

				"client" => $client
			];
		}

		private function getValidationClient ():Factory {

			$translator = new Translator(
				new FileLoader(new Filesystem, "lang"),

				"en"
			);

			return new Factory( $translator, $this->laravelContainer);
		}

		public function concreteName ():string {

			return LaravelValidator::class;
		}
	}
?>