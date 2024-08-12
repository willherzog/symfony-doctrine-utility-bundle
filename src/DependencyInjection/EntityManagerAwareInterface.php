<?php

namespace WHSymfony\WHDoctrineUtilityBundle\DependencyInjection;

use Doctrine\ORM\EntityManagerInterface;

/**
 * @author Will Herzog <willherzog@gmail.com>
 */
interface EntityManagerAwareInterface
{
	public function setEntityManager(EntityManagerInterface $entityManager);
}
