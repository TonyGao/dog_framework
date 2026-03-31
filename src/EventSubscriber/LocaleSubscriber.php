<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LocaleSubscriber implements EventSubscriberInterface
{
    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        if (!$event->isMainRequest()) {
            return;
        }

        // Check if a custom locale is set in the query parameters
        if ($locale = $request->query->get('_locale')) {
            $request->setLocale($locale);
            // Save to session
            try {
                $request->getSession()->set('_locale', $locale);
            } catch (\Exception $e) {
                // Session might not be enabled or started
            }
        } else {
            // Otherwise use the locale from the session
            try {
                if ($request->hasPreviousSession()) {
                    $locale = $request->getSession()->get('_locale');
                    if ($locale) {
                        $request->setLocale($locale);
                    }
                }
            } catch (\Exception $e) {
                // Session issue
            }
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // must be registered before (i.e. with a higher priority than) the default Locale listener
            KernelEvents::REQUEST => [['onKernelRequest', 20]],
        ];
    }
}
