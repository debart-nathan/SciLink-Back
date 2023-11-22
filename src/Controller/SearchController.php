<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ResearchersRepository;
use App\Repository\ResearchCentersRepository;
use App\Repository\InvestorsRepository;
use App\Security\Voter\ContactVoter;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SearchController extends AbstractController
{
    private $researchersRepository;
    private $researchCentersRepository;
    private $investorsRepository;
    private $tokenStorage;
    private $contactVoter;

    public function __construct(
        ResearchersRepository $researchersRepository,
        ResearchCentersRepository $researchCentersRepository,
        InvestorsRepository $investorsRepository,
        TokenStorageInterface $tokenStorage,
        ContactVoter $contactVoter
    ) {
        $this->researchersRepository = $researchersRepository;
        $this->researchCentersRepository = $researchCentersRepository;
        $this->investorsRepository = $investorsRepository;
        $this->tokenStorage = $tokenStorage;
        $this->contactVoter = $contactVoter;
    }

    #[Route('/search', name: 'search', methods: ['GET', 'POST'])]
    public function index(Request $request): JsonResponse
    {
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
        } else {
            $data = $request->query->all();
        }
        $category = $data['category'] ?? null;
        $search = $data['search'] ?? null;

        // Get page and limit from data, default to 1 and 10 respectively if not provided
        $page = $data['page'] ?? 1;
        $limit = $data['limit'] ?? 10;

        // Calculate the offset based on the page number and limit
        $offset = ($page - 1) * $limit;
        $results = [];
        $totalCount = 0;

        // Implement this method in your repository
        $totalCountInvestor = $this->investorsRepository
            ->getTotalCount($search, $data['investor'] ?? []);
        $totalCountResearchCenter = $this->researchCentersRepository
            ->getTotalCount($search, $data['research-center'] ?? []);
        $totalCountSearcher = $this->researchersRepository
            ->getTotalCount($search, $data['searcher'] ?? []);
        $totalCount =  0;

        if ($category) {
            switch ($category) {
                case 'searcher':
                    $results = $this->searchAndWrap(
                        $this->researchersRepository,
                        $search,
                        $data['searcher'] ?? [],
                        'searcher',
                        $offset,
                        $limit

                    );
                    $totalCount = $totalCountSearcher;
                    break;
                case 'research-center':
                    $results = $this->searchAndWrap(
                        $this->researchCentersRepository,
                        $search,
                        $data['research-center'] ?? [],
                        'research-center',
                        $offset,
                        $limit

                    );
                    $totalCount = $totalCountResearchCenter;
                    break;
                case 'investor':
                    $results = $this->searchAndWrap(
                        $this->investorsRepository,
                        $search,
                        $data['investor'] ?? [],
                        'investor',
                        $offset,
                        $limit
                    );
                    $totalCount =  $totalCountInvestor;
                    break;
            }
        } else {
            $offsetRemaining = $offset;
            $limitRemaining = $limit;

            $offsetResearchers = min($totalCountSearcher, intdiv($offsetRemaining, 3));
            $offsetRemaining -= $offsetResearchers;
            $limitResearchers = min($totalCountSearcher - $offsetResearchers, intdiv($limitRemaining, 3));
            $limitRemaining -= $limitResearchers;

            $offsetResearchCenter = min($totalCountResearchCenter, intdiv($offsetRemaining, 2));
            $offsetRemaining -= $offsetResearchCenter;
            $limitResearchCenter = min($totalCountResearchCenter - $offsetResearchCenter, intdiv($limitRemaining, 2));
            $limitRemaining -= $limitResearchCenter;

            $offsetInvestor = min($totalCountInvestor, $offsetRemaining);
            $limitInvestor = min($totalCountInvestor - $offsetInvestor, $limitRemaining);

            $results = array_merge(
                $this->searchAndWrap(
                    $this->researchersRepository,
                    $search,
                    $data['searcher'] ?? [],
                    'searcher',
                    $offsetResearchers,
                    $limitResearchers
                ),
                $this->searchAndWrap(
                    $this->researchCentersRepository,
                    $search,
                    $data['research-center'] ?? [],
                    'research-center',
                    $offsetResearchCenter,
                    $limitResearchCenter
                ),
                $this->searchAndWrap(
                    $this->investorsRepository,
                    $search,
                    $data['investor'] ?? [],
                    'investor',
                    $offsetInvestor,
                    $limitInvestor
                )
            );
            $totalCount = $totalCountSearcher + $totalCountResearchCenter + $totalCountInvestor;
        }



        return $this->json([
            'results' => $results,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $totalCount,
            ],
        ]);
    }

    private function searchAndWrap($repository, $search, $additionalData, $category, $offset, $limit)
    {

        $results = $repository->search($search, $additionalData, $offset, $limit);
        $wrappedResults = [];



        foreach ($results as $result) {
            $wrappedResults[] = [
                'category' => $category,
                'data' => $this->createDataArray($result, $category),

            ];
        }

        return $wrappedResults;
    }
    private function createDataArray($object, $category)
    {


        switch ($category) {
            case 'searcher':
                $token = $this->tokenStorage->getToken();
                $privacySecurity = false;
                $user = $object->getUser();
                if ($token) {
                    /** @var Users $loginUser */
                    $loginUser = $token->getUser();
                    $privacySecurity = (
                        // vérifie que l'utilisateur connecté est l'utilisateur de la donné
                        (($loginUser->getId() === $user->getId())) ||
                        // vérifie que l'utilisateur connecté a une relation accepté avec l’utilisateur de la donné
                        $this->contactVoter->voteOnAttribute('HAS_ACCEPTED_CONTACT', $user, $token)

                    );
                }
                return [
                    "profil" => [
                        "id" => $object->getId(),
                    ],
                    "user" => [
                        'id' => $user->getId(),
                        'user_name' => $user->getUserName(),
                        'first_name' => $user->getFirstName(),
                        'last_name' => $user->getLastName(),
                        'email' => $privacySecurity ? $user->getEmail() : null,
                    ]
                ];
            case 'research-center':
                return [
                    'id' => $object->getId(),
                    'label' => $object->getLibelle(),
                    'sigle' => $object->getSigle(),
                    'founding_year' => $object->getFoundingYear(),
                    'is_active' => $object->isIsActive(),
                    'website' => $object->getWebsite(),
                    'fiche_msr' => $object->getFicheMsr()
                ];
            case 'investor':
                return [
                    'id' => $object->getId(),
                    'name' => $object->getName(),
                    'sigle' => $object->getSigle(),
                    'type' => $object->getType(),
                    'label' => $object->getLabel()
                ];
            default:
                throw new \Exception("Unknown category: $category");
        }
    }
}
