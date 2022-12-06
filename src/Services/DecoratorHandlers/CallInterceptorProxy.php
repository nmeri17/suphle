<?php
	namespace Suphle\Services\DecoratorHandlers;

	use Suphle\Hydration\{Container, Structures\ObjectDetails};

	use Suphle\Services\Decorators\InterceptsCalls;

	use Suphle\Contracts\Services\Decorators\{ SystemModelEdit, ServiceErrorCatcher, MultiUserModelEdit};

	use Suphle\Services\DecoratorHandlers\{SystemModelEditHandler, ErrorCatcherHandler, MultiUserEditHandler};

	use Suphle\Exception\Explosives\Generic\InvalidImplementor;

	class CallInterceptorProxy extends BaseInjectionModifier {

		protected const CALL_HANDLERS = [

			MultiUserModelEdit::class => MultiUserEditHandler::class,

			SystemModelEdit::class => SystemModelEditHandler::class,

			ServiceErrorCatcher::class => ErrorCatcherHandler::class
		];

		public function __construct (

			private readonly Container $container,

			protected readonly ObjectDetails $objectMeta
		) {

			//
		}

		public function examineInstance (object $concrete, string $caller):object {

			foreach ($this->attributesList as $attributeMeta) {

				$attribute = $attributeMeta->newInstance();

				$interfaceName = $attribute->interceptType;

				if (
					!$this->objectMeta->implementsInterface(

						$concrete::class, $interfaceName
					) ||

					!array_key_exists($interfaceName, self::CALL_HANDLERS)
				)

					throw new InvalidImplementor($interfaceName, $concrete::class);

				$handler = self::CALL_HANDLERS[$interfaceName];

				$concrete = $this->container->getClass($handler)

				->examineInstance($concrete, $caller);
			}
		}
	}
?>