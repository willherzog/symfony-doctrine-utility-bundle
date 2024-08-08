<?php

namespace WHSymfony\WHDoctrineUtilityBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service_locator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

use WHDoctrine\Hydration\SimplifiedArrayHydrator;
use WHDoctrine\Type\NullableArrayType;

use WHSymfony\WHDoctrineUtilityBundle\HttpKernel\EventListener\KernelResponseListener;

/**
 * @author Will Herzog <willherzog@gmail.com>
 */
class WHDoctrineUtilityBundle extends AbstractBundle
{
	public const REQUEST_ATTR_FLUSH_REQUIRED = 'wh_doctrine_flush_required';
	public const REQUEST_ATTR_ENTITY_MANAGER = 'wh_doctrine_entity_manager';

	protected string $extensionAlias = 'wh_doctrine';

	public function configure(DefinitionConfigurator $definition): void
	{
		$definition->rootNode()
			->children()
				->booleanNode('enable_kernel_response_listener')
					->defaultFalse()
					->info('Whether WHDoctrineUtilityBundle\'s kernel response listener should be enabled.')
				->end()
			->end()
		;
	}

	public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
	{
		$container->extension('doctrine', [
			'dbal' => [
				'types' => [
					'array_nullable' => NullableArrayType::class
				]
			],
			'orm' => [
				'hydrators' => [
					'simplified_array' => SimplifiedArrayHydrator::class
				]
			]
		]);

		$container->extension('monolog', [
			'channels' => ['whdoctrine']
		]);
	}

	public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
	{
		if( $config['enable_kernel_response_listener'] ) {
			$container->services()
				->set('whdoctrine.kernel_response_listener', KernelResponseListener::class)
					->args([
						service('doctrine'),
						service_locator(['logger' => service('logger')->ignoreOnInvalid()])
					])
					->tag('kernel.event_listener', ['event' => 'kernel.response'])
					->tag('monolog.logger', ['channel' => 'whdoctrine'])
			;
		}
	}
}
