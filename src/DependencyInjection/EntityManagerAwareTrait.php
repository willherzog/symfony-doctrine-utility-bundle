<?php

namespace WHSymfony\WHDoctrineUtilityBundle\DependencyInjection;

use Doctrine\ORM\EntityManagerInterface;

/**
 * @author Will Herzog <willherzog@gmail.com>
 */
trait EntityManagerAwareTrait
{
	/** @var EntityManagerInterface */
	protected $entityManager;

	public function setEntityManager(EntityManagerInterface $entityManager): void
	{
		$this->entityManager = $entityManager;
	}
}
