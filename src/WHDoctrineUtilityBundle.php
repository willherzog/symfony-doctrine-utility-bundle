<?php

namespace WHSymfony\WHDoctrineUtilityBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\{ContainerBuilder,ContainerInterface};
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service_locator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

use WHDoctrine\Hydration\SimplifiedArrayHydrator;
use WHDoctrine\Type\NullableArrayType;

use WHSymfony\WHDoctrineUtilityBundle\HttpKernel\EventListener\KernelResponseListener;
use WHSymfony\WHDoctrineUtilityBundle\Migrations\EntityManagerAwareMigrationFactory;

/**
 * @author Will Herzog <willherzog@gmail.com>
 */
class WHDoctrineUtilityBundle extends AbstractBundle
{
	/** @deprecated */
	public const REQUEST_ATTR_FLUSH_REQUIRED = 'wh_doctrine_flush_required';
	/** @deprecated */
	public const REQUEST_ATTR_ENTITY_MANAGER = 'wh_doctrine_entity_manager';

	protected string $extensionAlias = 'wh_doctrine';

	public function configure(DefinitionConfigurator $definition): void
	{
		$definition->rootNode()
			->children()
				->booleanNode('enable_kernel_response_listener')
					->defaultTrue()
					->info('Whether WHDoctrineUtilityBundle\'s kernel response listener should be enabled.')
				->end()
				->booleanNode('enable_entity_manager_aware_migrations')
					->defaultTrue()
					->info('Whether to enable a decorator of the default Doctrine Migrations factory service which can inject the Doctrine entity manager into migration classes (ignored if your project does not have "doctrine/migrations" as a dependency).')
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

		if( $builder->hasExtension('doctrine_migrations') ) {
			$configs = $builder->getExtensionConfig($this->extensionAlias);
			$migrationServicesPrepended = false;

			foreach( array_reverse($configs) as $config ) {
				if( $config['enable_entity_manager_aware_migrations'] ?? false && !$migrationServicesPrepended ) {
					$container->extension('doctrine_migrations', [
						'services' => [
							'Doctrine\Migrations\Version\MigrationFactory' => EntityManagerAwareMigrationFactory::class
						]
					]);

					$migrationServicesPrepended = true;
				}
			}
		}

		$container->extension('monolog', [
			'channels' => ['whdoctrine']
		]);
	}

	public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
	{
		if( $config['enable_kernel_response_listener'] ) {
			$container->services()
				->set('whdoctrine.entity_manager_flush_requester', EntityManagerFlushRequester::class)
				->alias(EntityManagerFlushRequester::class, 'whdoctrine.entity_manager_flush_requester')

				->set('whdoctrine.kernel_response_listener', KernelResponseListener::class)
					->args([
						service('doctrine'),
						service('whdoctrine.entity_manager_flush_requester'),
						service_locator(['logger' => service('monolog.logger.whdoctrine')->ignoreOnInvalid()])
					])
					->tag('kernel.event_listener', ['event' => 'kernel.response'])
			;
		}

		if( $config['enable_entity_manager_aware_migrations'] ) {
			$container->services()
				->set('whdoctrine.entity_manager_aware.migration_factory', EntityManagerAwareMigrationFactory::class)
					->decorate('doctrine.migrations.migrations_factory', invalidBehavior: ContainerInterface::IGNORE_ON_INVALID_REFERENCE)
					->args([
						service('whdoctrine.entity_manager_aware.migration_factory.inner'),
						service('doctrine.orm.entity_manager'),
						service_locator(['logger' => service('monolog.logger.whdoctrine')->ignoreOnInvalid()])
					])
			;
		}
	}
}
