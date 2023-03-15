<?php

namespace App\EventSubscriber;

use App\Entity\Header;
use App\Entity\Product;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class EasyAdminSubscriber implements EventSubscriberInterface
{
    private $appKernel;

    public function __construct(KernelInterface $appKernel)
    {
        $this->appKernel = $appKernel;
    }

    public static function getSubscribedEvents()
    {
        return [
            BeforeEntityPersistedEvent::class => ['setIllustration'],
            BeforeEntityUpdatedEvent::class => ['updateIllustration']
        ];
    }

    public function uploadIllustration($event, $entityName)
    {
        $entity = $event->getEntityInstance();
        $file = $_FILES[$entityName]['name']['illustration'];
        $files = "";
        foreach ($file as $value) {
            $files .= (string)$value;
        }
        $entity->setIllustration($files);

    }

    public function updateIllustration(BeforeEntityUpdatedEvent $event)
    {
        if (!($event->getEntityInstance() instanceof Product) && !($event->getEntityInstance() instanceof Header)) {
            return;
        }

        $reflexion = new \ReflectionClass($event->getEntityInstance());
        $entityName = $reflexion->getShortName();

        if ($_FILES[$entityName]['name']['illustration'] != ''){
            $this->uploadIllustration($event, $entityName);
        }
    }

    public function setIllustration(BeforeEntityPersistedEvent $event)
    {
        $reflexion = new \ReflectionClass($event->getEntityInstance());
        $entityName = $reflexion->getShortName();

        if (!($event->getEntityInstance() instanceof Product) && !($event->getEntityInstance() instanceof Header)) {
            return;
        }

        $this->uploadIllustration($event, $entityName);

    }
}