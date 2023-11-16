<?php

namespace App\Controller;

use App\Repository\ResearchCentersRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ParentsController extends AbstractController
{
    #[Route('/Parents', name: 'app_parents', methods: ['GET'])]
    public function index(
        ResearchCentersRepository $researchCentersRepository,
        Request $request
        ): JsonResponse
    {
        // Vérifier si la chaîne de requête existe
        if ($request->query->count() > 0) {
            // Récupérer les paramètres de la chaîne de requête dans un tableau associatif
            $queryParams = $request->query->all();
            // Vérifie si l'identifiant du parent est présent dans les paramètres
            if (isset($queryParams['research_center_parent_id'])) {
                $parentId = $queryParams['research_center_parent_id'];
            }
            // Vérifie si l'identifiant de l'enfant est présent dans les paramètres
            if (isset($queryParams['research_center_child_id'])) {
                $childrenId = $queryParams['research_center_child_id'];
            }
        }

        $researchCentersArray = [];

        // retourner la liste tous les parents de l'enfant
        if (isset($parentId)) {
            $parent = $researchCentersRepository->find($parentId);
            $childrens = $parent->getChildrens();
            foreach ($childrens as $children) {
                $researchCentersArray[] = [
                    'research_center_parent_id' => $parentId,
                    'research_center_child_id' => $children->getId(),
                ];
            }

            // retourner la liste tous les enfants des parents
        } elseif (isset($childrenId)) {
            $children = $researchCentersRepository->find($childrenId);
            $parents = $children->getparents();
            foreach ($parents as $parent) {
                $researchCentersArray[] = [
                    'research_center_child_id' => $childrenId,
                    'research_center_parent_id' => $parent->getId(),
                ];
            }
            // retourner la liste tous les parents de l'enfant et tous les enfants des parents
        } else {
            $parents = $researchCentersRepository->findAll();
            foreach ($parents as $parent) {
                $childrens = $parent->getchildrens();
                $childrenId = [];
                foreach ($childrens as $children) {
                    if ($parent->getId() !== null) {
                        $parentId = $parent->getId();
                        $researchCentersArray[] = [
                            'research_center_parent_id' => $parentId,
                            'research_center_child_id' => $children->getId(),
                        ];
                    } else {
                        break;
                    }
                }
            }
        }
        $researchCentersJson = json_encode($researchCentersArray);
        return new JsonResponse($researchCentersJson, 200, [], true);
    }
}