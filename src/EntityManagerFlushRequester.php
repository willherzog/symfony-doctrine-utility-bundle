<?php

namespace WHSymfony\WHDoctrineUtilityBundle;

/**
 * @author Will Herzog <willherzog@gmail.com>
 */
final class EntityManagerFlushRequester
{
	private array $entityClasses = [];

	/**
	 * Request a Doctrine entity manager flush for a specific entity class.
	 *
	 * @param string $entityClass The fully-qualified class name of a Doctrine entity
	 */
	public function addFlushRequestForEntity(string $entityClass): void
	{
		if( !in_array($entityClass, $this->entityClasses, true) ) {
			$this->entityClasses[] = $entityClass;
		}
	}

	/**
	 * @internal
	 */
	public function getEntityClasses(): array
	{
		return $this->entityClasses;
	}
}
