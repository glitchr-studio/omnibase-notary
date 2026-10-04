<?php

namespace Base\Notary\Controller\Client;

use Base\Notary\Repository\AreaRepository;
use Base\Notary\Service\Record;
use Base\Office\Repository\OfficeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * The notarial regime's public pages: the fields of practice, the tariff
 * (regulated: the page explains it and links to the official text, with the
 * office's discounts and its fees for what the tariff does not cover), and
 * the consumer mediator of the notariat.
 */
class NotaryController extends AbstractController
{
    public function __construct(private readonly OfficeRepository $offices, private readonly Record $record)
    {
    }

    #[Route('/domaines', name: 'notary_areas', methods: ['GET'])]
    public function areas(AreaRepository $areas): Response
    {
        return $this->render('@Notary/client/areas.html.twig', ['areas' => $areas->findActive(), 'office' => $this->offices->findMain()]);
    }

    #[Route('/domaines/{slug}', name: 'notary_area', methods: ['GET'], requirements: ['slug' => '[a-z0-9\-]+'])]
    public function area(string $slug, AreaRepository $areas): Response
    {
        $area = $areas->findOneActiveBySlug($slug) ?? throw $this->createNotFoundException();

        return $this->render('@Notary/client/area.html.twig', ['area' => $area, 'areas' => $areas->findActive(), 'office' => $this->offices->findMain()]);
    }

    #[Route('/tarifs', name: 'notary_tariff', methods: ['GET'])]
    public function tariff(): Response
    {
        return $this->render('@Notary/client/tariff.html.twig', [
            'office' => $this->offices->findMain(),
            'record' => $this->record,
            'tariff' => $this->getParameter('notary.tariff'),
            'mediator' => $this->getParameter('notary.mediator'),
        ]);
    }

    #[Route('/mediation', name: 'notary_mediation', methods: ['GET'])]
    public function mediation(): Response
    {
        return $this->render('@Notary/client/mediation.html.twig', [
            'office' => $this->offices->findMain(),
            'record' => $this->record,
            'mediator' => $this->getParameter('notary.mediator'),
        ]);
    }
}
