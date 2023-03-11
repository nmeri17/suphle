<?php
	namespace Suphle\Services\DecoratorHandlers;

	use Suphle\Hydration\{Container, Structures\ObjectDetails};

	use Suphle\Contracts\Services\CallInterceptors\{ SystemModelEdit, ServiceErrorCatcher, MultiUserModelEdit};

	use Suphle\Exception\Explosives\DevError\InvalidImplementor;

	class CallInterceptorProxy extends BaseInjectionModifier {

		protected const CALL_HANDLERS = [

			MultiUserModelEdit::class => MultiUserEditHandler::class,

			SystemModelEdit::class => SystemModelEditHandler::class,

			ServiceErrorCatcher::class => ErrorCatcherHandler::class
		];

		public function __construct (

			protected readonly Container $container,

			protected readonly ObjectDetails $objectMeta
		) {

			//
		}

		public function examineInstance (object $concrete, string $caller):object {

			foreach ($this->attributesList as $attributeMeta) {

				$attribute = $attributeMeta->newInstance();

				$interfaceName = $attribute->interceptType;

				$this->ensureValidInterceptType(

					$concrete::class, $interfaceName
				);

				$handler = self::CALL_HANDLERS[$interfaceName];

				$concrete = $this->container->getClass($handler)

				->examineInstance($concrete, $caller);
			}

			return $concrete;
		}

		protected function ensureValidInterceptType (string $concreteName, string $interfaceName):void {

			$acceptedType = array_key_exists($interfaceName, self::CALL_HANDLERS);

			$isChild = $this->objectMeta->implementsInterface(

				$concreteName, $interfaceName
			);

			if (!($acceptedType && $isChild))

				throw new InvalidImplementor($interfaceName, $concreteName);
		}
	}
?>