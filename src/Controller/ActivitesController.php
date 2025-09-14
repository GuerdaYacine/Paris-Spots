<?php

namespace App\Controller;

use Symfony\UX\Map\Map;
use Symfony\UX\Map\Point;
use App\Form\ActiviteType;
use Symfony\UX\Map\Marker;
use Symfony\UX\Map\InfoWindow;
use App\Service\ActiviteService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;

final class ActivitesController extends AbstractController
{
    #[Route('/activites', name: 'app_activites')]
    public function index(
        ActiviteService $activitesService,
        PaginatorInterface $paginator,
        HttpFoundationRequest $request
    ): Response {
        $form = $this->createForm(ActiviteType::class, null, [
            'method' => 'GET'
        ]);
        $form->handleRequest($request);

        $activites = $activitesService->getCleanedActivites();

        $search = $request->query->get('q');
        if ($search) {
            $activites = $activitesService->searchActivites($activites, $search);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $prix          = $form->get('prix')->getData();
            $arrondissement = $form->get('arrondissement')->getData();
            $activites = $activitesService->filterActivites($activites, $prix, $arrondissement);
        }

        $map = new Map('default');
        $map->center(new Point(48.866667, 2.333333))
            ->zoom(11);

        foreach ($activites as $activite) {
            if (!empty($activite['lat_lon'])) {
                $map->addMarker(new Marker(
                    position: new Point($activite['lat_lon']['lat'], $activite['lat_lon']['lon']),
                    title: $activite['title'],
                    infoWindow: new InfoWindow(
                        content: '<p>' . htmlspecialchars($activite['lead_text']) . '</p>'
                    )
                ));
            }
        }

        $pagination = $paginator->paginate(
            $activites,
            $request->query->getInt('page', 1),
            15,
        );

        return $this->render('activites/index.html.twig', [
            'pagination' => $pagination,
            'nombreActivites' => count($activites),
            'form' => $form->createView(),
            'map' => $map,
        ]);
    }
}
