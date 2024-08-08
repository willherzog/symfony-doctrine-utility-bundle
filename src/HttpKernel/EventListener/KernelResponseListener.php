<?php

namespace WHSymfony\WHDoctrineUtilityBundle\HttpKernel\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

use WHSymfony\WHDoctrineUtilityBundle\WHDoctrineUtilityBundle as BundleConstants;

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
		private readonly ContainerInterface $locator
	) {}

	public function __invoke(ResponseEvent $event): void
	{
		if( !$event->isMainRequest() ) {
			return;
		}

		$request = $event->getRequest();

		if(
			$request->attributes->has(BundleConstants::REQUEST_ATTR_FLUSH_REQUIRED)
			&& $request->attributes->getBoolean(BundleConstants::REQUEST_ATTR_FLUSH_REQUIRED)
		) {
			if( $request->attributes->has(BundleConstants::REQUEST_ATTR_ENTITY_MANAGER) ) {
				$entityManager = $request->attributes->get(BundleConstants::REQUEST_ATTR_ENTITY_MANAGER);

				if( !$entityManager instanceof EntityManagerInterface ) {
					throw new \UnexpectedValueException(sprintf(
						'The request attribute "%s" has a value set but it is not an instance of %s.',
						BundleConstants::REQUEST_ATTR_ENTITY_MANAGER,
						EntityManagerInterface::class
					));
				}

				$usingNonDefaultManager = true;
			} else {
				$entityManager = $this->managerRegistry->getManager();

				// ManagerRegistry::getManager() returns an instance of ObjectManager, but that instance doesn't necessarily
				// also implement EntityManagerInterface
				if( !$entityManager instanceof EntityManagerInterface ) {
					return;
				}

				$usingNonDefaultManager = false;
			}

			if( $entityManager->isOpen() ) {
				$entityManager->flush();

				if( $this->locator->has('logger') ) {
					$this->locator->get('logger')->info('Called ->flush() on Doctrine entity manager for kernel.response event.', [
						'using_non_default_manager' => $usingNonDefaultManager
					]);
				}
			}
		}
	}
}
