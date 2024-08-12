<?php

namespace WHSymfony\WHDoctrineUtilityBundle\HttpKernel\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

use WHSymfony\WHDoctrineUtilityBundle\EntityManagerFlushRequester;

/**
 * @author Will Herzog <willherzog@gmail.com>
 */
class KernelResponseListener implements ServiceSubscriberInterface
{
	static public function getSubscribedServices(): array
	{
		return ['logger' => LoggerInterface::class];
	}

	public function __construct(
		private readonly ManagerRegistry $managerRegistry,
		private readonly EntityManagerFlushRequester $flushRequester,
		private readonly ContainerInterface $locator
	) {}

	public function __invoke(ResponseEvent $event): void
	{
		if( !$event->isMainRequest() || $event->getResponse()->isClientError() || $event->getResponse()->isServerError() ) {
			return;
		}

		$entityClasses = $this->flushRequester->getEntityClasses();
		$entityManagersFlushed = [];

		if( count($entityClasses) > 0 ) {
			$logger = $this->locator->has('logger') ? $this->locator->get('logger') : null;
		}

		foreach( $entityClasses as $entityClass ) {
			$entityManager = $this->managerRegistry->getManagerForClass($entityClass);

			if( !$entityManager instanceof EntityManagerInterface ) {
				$logger?->notice('No associated Doctrine entity manager found for entity class.', [
					'event' => KernelEvents::RESPONSE,
					'entity' => $entityClass
				]);

				continue;
			}

			if( !in_array($entityManager, $entityManagersFlushed) ) {
				if( !$entityManager->isOpen() ) {
					$logger?->info(sprintf('Doctrine entity manager is closed (cannot flush).'), [
						'event' => KernelEvents::RESPONSE,
						'entity' => $entityClass
					]);
				} elseif( $entityManager->getUnitOfWork()->size() === 0 ) {
					$logger?->info(sprintf('Doctrine entity manager has no managed entities (i.e. nothing to be "flushed").'), [
						'event' => KernelEvents::RESPONSE,
						'entity' => $entityClass
					]);
				} else {
					$entityManager->flush();

					$logger?->info('Called ->flush() on Doctrine entity manager.', [
						'event' => KernelEvents::RESPONSE,
						'entity' => $entityClass
					]);
				}

				$entityManagersFlushed[] = $entityManager;
			}
		}
	}
}
