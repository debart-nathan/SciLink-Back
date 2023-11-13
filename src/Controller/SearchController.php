<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ResearchersRepository;
use App\Repository\ResearchCentersRepository;
use App\Repository\InvestorsRepository;

class SearchController extends AbstractController
{
    private $researchersRepository;
    private $researchCentersRepository;
    private $investorsRepository;
    private $serializer;

    public function __construct(
        ResearchersRepository $researchersRepository,
        ResearchCentersRepository $researchCentersRepository,
        InvestorsRepository $investorsRepository,
        SerializerInterface $serializer
    ) {
        $this->researchersRepository = $researchersRepository;
        $this->researchCentersRepository = $researchCentersRepository;
        $this->investorsRepository = $investorsRepository;
        $this->serializer = $serializer;
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

        $results = [];

        if ($category) {
            switch ($category) {
                case 'searcher':
                    $results = $this->searchAndWrap($this->researchersRepository, $search, $data['searcher'] ?? [], 'searcher');
                    break;
                case 'research-center':
                    $results = $this->searchAndWrap($this->researchCentersRepository, $search, $data['research-center'] ?? [], 'research-center');
                    break;
                case 'investor':
                    $results = $this->searchAndWrap($this->investorsRepository, $search, $data['investor'] ?? [], 'investor');
                    break;
            }
        } else {
            $results = array_merge(
                $this->searchAndWrap($this->researchersRepository, $search, $data['searcher'] ?? [], 'searcher'),
                $this->searchAndWrap($this->researchCentersRepository, $search, $data['research-center'] ?? [], 'research-center'),
                $this->searchAndWrap($this->investorsRepository, $search, $data['investor'] ?? [], 'investor')
            );
        }

        usort($results, function ($a, $b) {
            return $b['score'] - $a['score'];
        });

        // Remove the score from the results before returning
        array_walk($results, function (&$item) {
            unset($item['score']);
        });
        
        return $this->json($results);
    }

    private function searchAndWrap($repository, $search, $additionalData, $category)
    {
        $results = $repository->search($search, $additionalData);
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
                'data' => $this->serializer->normalize($result, null, [
                    AbstractNormalizer::CIRCULAR_REFERENCE_LIMIT => 1,
                    AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
                        if (method_exists($object, 'getId')) {
                            return $object->getId();
                        }

                        return $object;
                    }
                ]),
                'score' => $score,
            ];
        }

        return $wrappedResults;
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
            if (strpos($attribute, $search) !== false) {
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
