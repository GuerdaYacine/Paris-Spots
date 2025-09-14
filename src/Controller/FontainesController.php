<?php

namespace App\Controller;

use Symfony\UX\Map\Map;
use Symfony\UX\Map\Point;
use App\Form\FontaineType;
use Symfony\UX\Map\Marker;
use Symfony\UX\Map\InfoWindow;
use App\Service\FontaineService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class FontainesController extends AbstractController
{
    #[Route('/fontaines', name: 'app_fontaines')]
    public function index(FontaineService $fontainesService, PaginatorInterface $paginator, Request $request): Response
    {
        $form = $this->createForm(FontaineType::class, null, ['method' => 'GET']);
        $form->handleRequest($request);

        $fontaines = $fontainesService->getCleanedFontaines();


        if ($form->isSubmitted() && $form->isValid()) {
            $arrondissement = $form->get('arrondissement')->getData();
            $disponibleOnly = $form->get('disponibilite')->getData();

            $fontaines = $fontainesService->filterFontaines($fontaines, $arrondissement, $disponibleOnly);
        }

        $map = new Map('default');
        $map->center(new Point(48.866667, 2.333333))
            ->zoom(11);

        foreach ($fontaines as $fontaine) {
            if (!empty($fontaine['geo_point_2d'])) {
                $map->addMarker(new Marker(
                    position: new Point($fontaine['geo_point_2d']['lat'], $fontaine['geo_point_2d']['lon']),
                    title: $fontaine['commune'],
                    infoWindow: new InfoWindow(
                        content: '<p> Disponible : ' . $fontaine['dispo'] . '</p>'
                    )
                ));
            }
        }

        $pagination = $paginator->paginate(
            $fontaines,
            $request->query->getInt('page', 1),
            15
        );

        return $this->render('fontaines/index.html.twig', [
            'pagination' => $pagination,
            'nombreFontaine' => count($fontaines),
            'form' => $form->createView(),
            'map' => $map,
        ]);
    }
}
