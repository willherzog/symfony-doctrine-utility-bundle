<?php

namespace WHSymfony\WHDoctrineUtilityBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\Migrations\Version\MigrationFactory;
use Doctrine\ORM\EntityManagerInterface;

use WHSymfony\WHDoctrineUtilityBundle\DependencyInjection\EntityManagerAwareInterface;

/**
 * @author Will Herzog <willherzog@gmail.com>
 */
class EntityManagerAwareMigrationFactory implements MigrationFactory
{
	public function __construct(
		protected readonly MigrationFactory $migrationFactory,
		protected readonly EntityManagerInterface $entityManager
	) {}

	public function createVersion(string $migrationClassName): AbstractMigration
	{
		$migration = $this->migrationFactory->createVersion($migrationClassName);

		if( $migration instanceof EntityManagerAwareInterface ) {
			$migration->setEntityManager($this->entityManager);
		}

		return $migration;
	}
}
