<?php

namespace WHSymfony\WHDoctrineUtilityBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\Migrations\Version\MigrationFactory;
use Doctrine\ORM\EntityManagerInterface;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

use Symfony\Contracts\Service\ServiceSubscriberInterface;

use WHSymfony\WHDoctrineUtilityBundle\DependencyInjection\EntityManagerAwareInterface;

/**
 * @author Will Herzog <willherzog@gmail.com>
 */
class EntityManagerAwareMigrationFactory implements MigrationFactory, ServiceSubscriberInterface
{
	static public function getSubscribedServices(): array
	{
		return ['logger' => LoggerInterface::class];
	}

	public function __construct(
		protected readonly MigrationFactory $migrationFactory,
		protected readonly EntityManagerInterface $entityManager,
		private readonly ContainerInterface $locator
	) {}

	public function createVersion(string $migrationClassName): AbstractMigration
	{
		$migration = $this->migrationFactory->createVersion($migrationClassName);

		if( $migration instanceof EntityManagerAwareInterface ) {
			$migration->setEntityManager($this->entityManager);

			if( $this->locator->has('logger') ) {
				$this->locator->get('logger')->info(sprintf('Injected Doctrine entity manager dependency into migration class "%s".', $migrationClassName));
			}
		}

		return $migration;
	}
}
