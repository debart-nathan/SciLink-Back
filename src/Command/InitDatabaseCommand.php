<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Entity\ResearchCenters;
use App\Entity\Locations;
use App\Entity\Personnels;
use App\Entity\Manages;
use App\Entity\Investors;
use App\Entity\Tutelles;
use App\Entity\Domains;

class InitDatabaseCommand extends Command
{
    protected static $defaultName = 'app:init-database';

    private $entityManager;
    private $entitiesCache = [];


    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Initialise la base de données avec des données pré-remplies.')
            ->setHelp('Cette commande vous permet de pré-remplir votre base de données...');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Lecture du fichier...'); // Reading the file...

        // Lire le fichier XLS
        $filename = __DIR__ . '/../../data/fr-esr-structures-recherche-publiques-actives.xls';
        $spreadsheet = IOFactory::load($filename);
        $worksheet = $spreadsheet->getActiveSheet();
        $rawData = $worksheet->toArray();

        $output->writeln('Prétraitement des données...'); // Preprocessing the data...

        // Créer une nouvelle barre de progression (50 unités)
        $progressBar = new ProgressBar($output, count($rawData));

        // Démarrer et afficher la barre de progression
        $progressBar->start();

        // Traiter les données
        $this->processData($rawData, $progressBar);

        // Assurer que la barre de progression est à 100%
        $progressBar->finish();

        $output->writeln('');
        $output->writeln('Insertion des données dans la base de données terminée.'); // Inserting data into the database is complete.

        return Command::SUCCESS;
    }

    private function processData(array $rawData, ProgressBar $progressBar)
    {
        // Ignorer la première ligne (noms des colonnes)
        array_shift($rawData);

        // Préparer la relation parent-enfant
        $relationships = [];

        // Boucle à travers les données
        foreach ($rawData as $row) {
            // Gérer ResearchCenter
            // Colonnes: numero_national_de_structure, libelle, sigle, annee_de_creation, site_web, fiche_rnsr
            $researchCenter = $this->handleEntity(ResearchCenters::class, ['id' => $row[0]], [
                'id' => $row[0],
                'libelle' => $row[1],
                'sigle' => $row[2],
                'founding_year' => $row[3],
                'is_active' => true,
                'website' =>  filter_var($row[7], FILTER_VALIDATE_URL) ?: null,
                'fiche_msr' => $row[33],
            ]);

            // Enregistrer la relation parent-enfant
            $relationships[] = [
                'center' => $researchCenter,
                'parents' => explode(';', $row[23]),
                'children' => explode(';', $row[24]),
            ];

            // Gérer Location
            // Colonnes: adresse, code_postal, commune
            if ($row[8] || $row[9] || $row[10]) {
                $location = $this->handleEntity(Locations::class, ['address' => $row[8], 'postal_code' => $row[9], 'commune' => $row[10]], [
                    'address' => $row[8],
                    'postal_code' => $row[9],
                    'commune' => $row[10],
                ]);

                // Définir l'emplacement pour le centre de recherche
                $researchCenter->setLocated($location);
            }

            // Gérer les entités Personnels et Manages
            // Colonnes: nom_du_responsable, prenom_du_responsable, titre_du_responsable
            $personnelNames = explode(';', $row[11]);
            $personnelFirstNames = explode(';', $row[12]);
            $personnelTitles = explode(';', $row[13]);

            // Effacer les anciennes relations
            foreach ($researchCenter->getManages() as $oldManage) {
                $this->entityManager->remove($oldManage);
            }

            for ($i = 0; $i < count($personnelNames); $i++) {
                $personnel = $this->handleEntity(Personnels::class, ['first_name' => $personnelFirstNames[$i], 'last_name' => $personnelNames[$i]], [
                    'first_name' => $personnelFirstNames[$i],
                    'last_name' => $personnelNames[$i],
                ]);

                $manage = $this->handleEntity(Manages::class, ['personnel' => $personnel->getId(), 'researchCenter' => $researchCenter->getId()], [
                    'personnel' => $personnel,
                    'researchCenter' => $researchCenter,
                    'grade' => $personnelTitles[$i],
                ]);
                $researchCenter->addManage($manage);
            }

            // Gérer les entités Investors et Tutelles
            // Colonnes: tutelles, sigles_des_tutelles, nature_de_tutelle, uai_des_tutelles, siret_des_tutelles,```
            $investorNames = explode(';', $row[15]);
            $investorSigles = explode(';', $row[16]);
            $investorNatures = explode(';', $row[18]);
            $tutelleUais = explode(';', $row[19]);
            $tutelleSirets = explode(';', $row[20]);
            $tutelleTypes = explode(';', $row[22]);
            $investorLabels = explode(';', $row[14]);

            // Effacer les anciennes relations
            foreach ($researchCenter->getTutelles() as $oldTutelle) {
                $this->entityManager->remove($oldTutelle);
            }

            for ($i = 0; $i < count($investorLabels); $i++) {
                $investor = $this->handleEntity(Investors::class, ['name' => $investorNames[$i], 'sigle' => $investorSigles[$i]], [
                    'name' => $investorNames[$i],
                    'sigle' => $investorSigles[$i],
                    'nature' => $investorNatures[$i],
                    'type' => "Organism",
                    'label' => $investorLabels[$i]
                ]);

                $tutelle = $this->handleEntity(Tutelles::class, ['investor' => $investor->getId(), 'researchCenter' => $researchCenter->getId()], [
                    'investor' => $investor,
                    'researchCenter' => $researchCenter,
                    'uai' => $tutelleUais[$i],
                    'type' => $tutelleTypes[$i],
                    'siret' => $tutelleSirets[$i],
                ]);
            }

            // Gérer l'entité Domains
            // Colonnes: code_domaine_scientifique, domaine_scientifique
            $domainIds = explode(';', $row[29]);
            $domainNames = explode(';', $row[30]);

            // Effacer les anciennes relations
            foreach ($researchCenter->getDomains() as $oldDomain) {
                $researchCenter->removeDomain($oldDomain);
            }

            for ($i = 0; $i < count($domainNames); $i++) {
                $domain = $this->handleEntity(Domains::class, ['id' => $domainIds[$i]], [
                    'id' => $domainIds[$i],
                    'name' => $domainNames[$i],
                ]);

                // Ajouter le Domaine au ResearchCenter
                $researchCenter->addDomain($domain);
            }

            $progressBar->advance();
        }

        // ajouter la relation parent-enfant
        foreach ($relationships as $relationship) {
            $researchCenter = $relationship['center'];
            foreach ($researchCenter->getParents() as $oldParent) {
                $researchCenter->removeParent($oldParent);
            }
            foreach ($researchCenter->getChildrens() as $oldChild) {
                $researchCenter->removeChildren($oldChild);
            }

            foreach ($relationship['parents'] as $parentId) {
                $parentCacheKey = ResearchCenters::class . ':' . http_build_query(['id' => $parentId]);

                if (isset($this->entitiesCache[$parentCacheKey])) {
                    $parent = $this->entitiesCache[$parentCacheKey];
                } else {
                    $parent = $this->entityManager->getRepository(ResearchCenters::class)->findOneBy(['id' => $parentId]);
                }

                if ($parent) {
                    $researchCenter->addParent($parent);
                }
            }

            foreach ($relationship['children'] as $childId) {
                $childCacheKey = ResearchCenters::class . ':' . http_build_query(['id' => $childId]);

                if (isset($this->entitiesCache[$childCacheKey])) {
                    $child = $this->entitiesCache[$childCacheKey];
                } else {
                    $child = $this->entityManager->getRepository(ResearchCenters::class)->findOneBy(['id' => $childId]);
                }

                if ($child) {
                    $researchCenter->addChildren($child);
                }
            }
        }


        // Appliquer les modifications à la base de données
        $this->entityManager->flush();
    }

    private function handleEntity(string $entityClass, $criteria, array $data)
    {

        $cacheKey = $entityClass . ':' . http_build_query($criteria);

        // Vérifier si l'entité est dans le cache
        if (isset($this->entitiesCache[$cacheKey])) {
            $entity = $this->entitiesCache[$cacheKey];
        } else {
            // Si l'entité n'est pas dans le cache, la récupérer de la base de données
            $entity = $this->entityManager->getRepository($entityClass)->findOneBy($criteria);

            // Si l'entité n'existe pas dans la base de données, en créer une nouvelle
            if (!$entity) {
                $entity = new $entityClass();
            }

            // Stocker l'entité dans le cache
            $this->entitiesCache[$cacheKey] = $entity;
        }
        foreach ($data as $property => $value) {
            $property = lcfirst(str_replace('_', '', ucwords($property, '_')));
            $setter = 'set' . ucfirst($property);
            if (method_exists($entity, $setter)) {
                $entity->$setter($value);
            }
        }

        $this->entityManager->persist($entity);
        return $entity;
    }
}
