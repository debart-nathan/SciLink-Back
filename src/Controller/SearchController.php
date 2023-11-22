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
        $totalCount = $totalCountSearcher + $totalCountResearchCenter + $totalCountInvestor;

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
                    break;
            }
        } else {

            $results = array_merge(
                $this->searchAndWrap(
                    $this->researchersRepository,
                    $search,
                    $data['searcher'] ?? [],
                    'searcher',
                    $offset,
                    $limit
                ),
                $this->searchAndWrap(
                    $this->researchCentersRepository,
                    $search,
                    $data['research-center'] ?? [],
                    'research-center',
                    $offset,
                    $limit
                ),
                $this->searchAndWrap(
                    $this->investorsRepository,
                    $search,
                    $data['investor'] ?? [],
                    'investor',
                    $offset,
                    $limit
                )
            );
        }

        usort($results, function ($a, $b) {
            return $b['score'] - $a['score'];
        });
        $results = array_slice($results, 0, $limit);

        // Remove the score from the results before returning
        array_walk($results, function (&$item) {
            unset($item['score']);
        });

        return $this->json([
            'results' => $results,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $totalCount, // You may need to adjust this based on the total number of results
            ],
        ]);
    }

    private function searchAndWrap($repository, $search, $additionalData, $category, $offset, $limit)
    {

        $results = $repository->search($search, $additionalData, $offset, $limit);
        $wrappedResults = [];

        // Define the fields to be tested for each category
        $fieldsToTest = [];
        switch ($category) {
            case 'searcher':
                $fieldsToTest = ['user.user_name', 'user.first_name', 'user.last_name'];
                break;
            case 'research-center':
                $fieldsToTest = ['libelle', 'sigle'];
                break;
            case 'investor':
                $fieldsToTest = ['name', 'sigle'];
                break;
        }

        foreach ($results as $result) {
            $score = $this->calculateScore($result, $search, $fieldsToTest);
            $wrappedResults[] = [
                'category' => $category,
                'data' => $this->createDataArray($result, $category),
                'score' => $score,
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

    private function calculateScore($item, $search, $fieldsToTest)
    {
        $maxScore = 0;


        // Iterate over the specified fields
        foreach ($fieldsToTest as $field) {
            // Split the field string into parent and child parts
            $parts = explode('.', $field);
            $attribute = $item;
            foreach ($parts as $part) {
                $getter = 'get' . ucfirst($part);
                if (method_exists($attribute, $getter)) {
                    $attribute = $attribute->$getter();
                } else {
                    // If the getter doesn't exist, skip to the next field
                    continue 2;
                }
            }

            $score = 0;

            // Check if the attribute value contains the search string
            if ($attribute !== null && $search !== null && strpos($attribute, $search) !== false) {
                // Calculate the proportion of the search string in the attribute value
                $proportion = strlen($search) / strlen($attribute);
                $score += $proportion;

                // Give bonus points if the search string is at the beginning of the attribute value
                if (strpos($attribute, $search) === 0) {
                    $score += 0.1; // Adjust this value as needed
                }
            }

            // Update maxScore if this field's score is higher
            if ($score > $maxScore) {
                $maxScore = $score;
            }
        }

        return $maxScore;
    }
}
