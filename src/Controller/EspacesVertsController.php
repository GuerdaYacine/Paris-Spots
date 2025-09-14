<?php

namespace App\Controller;

use Symfony\UX\Map\Map;
use App\Form\EspaceType;
use Symfony\UX\Map\Point;
use Symfony\UX\Map\Marker;
use Symfony\UX\Map\InfoWindow;
use App\Service\EspaceVertService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class EspacesVertsController extends AbstractController
{
    #[Route('/espaces-verts', name: 'app_espaces_verts')]
    public function index(EspaceVertService $espacesService, PaginatorInterface $paginator, Request $request): Response
    {
        $form = $this->createForm(EspaceType::class, null, ['method' => 'GET']);
        $form->handleRequest($request);

        $espaces = $espacesService->getCleanedEspaces();

        $search = $request->query->get('q');
        if ($search) {
            $espaces = $espacesService->searchEspaces($espaces, $search);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $arrondissement = $form->get('arrondissement')->getData();
            $espaces = $espacesService->filterEspaces($espaces, $arrondissement);
        }


        $map = new Map('default');
        $map->center(new Point(48.866667, 2.333333))
            ->zoom(11);

        foreach ($espaces as $espace) {
            if (!empty($espace['geom_x_y'])) {
                $map->addMarker(new Marker(
                    position: new Point($espace['geom_x_y']['lat'], $espace['geom_x_y']['lon']),
                    title: $espace['nom_ev'],
                    infoWindow: new InfoWindow(
                        content: '<p>' . $espace['adresse_numero'] . ' ' . $espace['adresse_typevoie'] . ' ' . $espace['adresse_libellevoie'] . ' ' . $espace['adresse_codepostal'] . '</p>'
                    )
                ));
            }
        }


        $pagination = $paginator->paginate(
            $espaces,
            $request->query->getInt('page', 1),
            15
        );

        return $this->render('espaces_verts/index.html.twig', [
            'pagination' => $pagination,
            'nombreEspaceVert' => count($espaces),
            'form' => $form->createView(),
            'map' => $map,
        ]);
    }
}
