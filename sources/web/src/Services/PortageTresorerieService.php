<?php

declare(strict_types=1);

namespace App\Services;

use App\Dao\PortageTresorerieDao;
use App\Dao\PortageTresorerieParametreDao;
use App\Dao\PortageTresoreriePeriodiqueDao;
use App\Dao\SimulationDao;
use App\Model\PortageTresorerie;
use App\Model\PortageTresorerieParametre;
use App\Model\PortageTresoreriePeriodique;
use App\Model\Simulation;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use TheCodingMachine\TDBM\ResultIterator;
use TheCodingMachine\TDBM\TDBMException;
use Throwable;
use function chr;
use function count;
use function intval;

class PortageTresorerieService
{
    /** @var PortageTresorerieDao */
    private $portageTresorerieDao;

    /** @var PortageTresoreriePeriodiqueDao */
    private $portageTresoreriePeriodiqueDao;

    /** @var PortageTresorerieParametreDao */
    private $portageTresorerieParametreDao;

    /** @var SimulationDao */
    private $simulationDao;

    public function __construct(
        PortageTresorerieDao $portageTresorerieDao,
        PortageTresoreriePeriodiqueDao $portageTresoreriePeriodiqueDao,
        PortageTresorerieParametreDao $portageTresorerieParametreDao,
        SimulationDao $simulationDao
    ) {
        $this->portageTresorerieDao = $portageTresorerieDao;
        $this->portageTresoreriePeriodiqueDao = $portageTresoreriePeriodiqueDao;
        $this->portageTresorerieParametreDao = $portageTresorerieParametreDao;
        $this->simulationDao = $simulationDao;
    }

    public function save(PortageTresorerie $portageTresorerie): void
    {
        $this->portageTresorerieDao->save($portageTresorerie);
    }

    /**
     * @throws TDBMException
     */
    public function createPortagesTresoreries(Simulation $simulationID): void
    {
        $this->createPortageTresorerie($simulationID, PortageTresorerie::PRELIMINAIRES_LOCATIFS, 'Portage moyen terme des investissements');
        $this->createPortageTresorerie($simulationID, PortageTresorerie::RESERVES_FRONTIERES, 'Portage moyen terme des investissements');
        $this->createPortageTresorerie($simulationID, PortageTresorerie::FINANCEMENT_EXTERNE, 'Portage moyen terme des investissements');
        $this->createPortageTresorerie($simulationID, PortageTresorerie::PROMOTION_ACCESSION, 'Portage moyen terme des investissements', true);
        $this->createPortageTresorerie($simulationID, PortageTresorerie::SOLDE_EMPLOIS_RESSOURCES_OPERATION, 'Portage court terme des investissements', true);
        $this->createPortageTresorerie($simulationID, PortageTresorerie::SUBVENTIONS_A_ENCAISSER_SUR_OPERATIONS_LOCATIFS, 'Portage court terme des investissements');
        $this->createPortageTresorerie($simulationID, PortageTresorerie::SOLDE_TVA_SUR_IMMOBILISATIONS_LOCATIVES, 'Portage court terme des investissements');
        $this->createPortageTresorerie($simulationID, PortageTresorerie::DETTES_DIVERSES_FOURNISSEURS_IMMOBILISATIONS, 'Portage court terme des investissements', true);
        $this->createPortageTresorerie($simulationID, PortageTresorerie::DETTES_CREANCES_EXPLOITATION, 'Autres variations potentiel financier');
        $this->createPortageTresorerie($simulationID, PortageTresorerie::DETTES_CREANCES_HORS_EXPLOITATION, 'Autres variations potentiel financier');
        $this->createPortageTresorerie($simulationID, PortageTresorerie::CONCOURS_BANCAIRES_COURANTS, 'Autres variations potentiel financier');

        $this->createPortageTresorerieParametre($simulationID);
    }

    private function createPortageTresorerie(Simulation $simulation, string $nom, string $type, ?bool $estParametre = null): void
    {
        $portageTresorerie = new PortageTresorerie($simulation, $nom, $type);
        if ($estParametre === true) {
            $portageTresorerie->setEstParametre(true);
        }
        $this->portageTresorerieDao->save($portageTresorerie);
        $this->createPortageTresoreriePeriodique($portageTresorerie);
    }

    private function createPortageTresoreriePeriodique(PortageTresorerie $portageTresorerie): void
    {
        $numberIterations = PortageTresoreriePeriodique::NUMBER_OF_ITERATIONS;
        if ($portageTresorerie->getEstParametre()) {
            $numberIterations = 1;
        }
        for ($i=1; $i<= $numberIterations; $i++) {
            $portageTresoreriePeriodique = new PortageTresoreriePeriodique($portageTresorerie, $i);

            $portageTresoreriePeriodique->setValeur(null);
            $this->portageTresoreriePeriodiqueDao->save($portageTresoreriePeriodique);
        }
    }

    private function createPortageTresorerieParametre(Simulation $simulation): void
    {
        $portageTresorerieParametre = new PortageTresorerieParametre($simulation);
        $this->portageTresorerieParametreDao->save($portageTresorerieParametre);
    }

    public function saveParametre(string $simulationId, ?float $soldeEmplois, ?float $detteFournisseurs, ?float $promotionAccession, ?float $tauxInteretFinancement, ?float $tauxInteretConcours): ?PortageTresorerieParametre
    {
        if (empty($simulationId)) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Erreur sur la simulation ou le nombre pondéré.');
        }
        $portageTresorerieParametre = $this->portageTresorerieParametreDao->findOneBySimulationId($simulationId);
        if ($portageTresorerieParametre === null) {
            // Create a new one if it doesn't exist
            try {
                $simulation = $this->simulationDao->getById($simulationId);
            } catch (Throwable $e) {
                throw new HttpException(Response::HTTP_NOT_FOUND, "La simulation n'existe pas.", $e);
            }
            $portageTresorerieParametre = new PortageTresorerieParametre($simulation);
        } else {
            // Updating the existing one
            $portageTresorerieParametre->setSoldeEmplois($soldeEmplois);
            $portageTresorerieParametre->setDetteFournisseurs($detteFournisseurs);
            $portageTresorerieParametre->setPromotionAccession($promotionAccession);
            $portageTresorerieParametre->setTauxInteretFinancement($tauxInteretFinancement);
            $portageTresorerieParametre->setTauxInteretConcours($tauxInteretConcours);
        }
        $this->portageTresorerieParametreDao->save($portageTresorerieParametre);

        return $portageTresorerieParametre;
    }

    /**
     * @return PortageTresorerie[]|ResultIterator
     */
    public function findBySimulationAndType(string $simulationId, string $type): ResultIterator
    {
        return $this->portageTresorerieDao->findBySimulationAndType($simulationId, $type);
    }

    public function exportPortageTresorerie(Worksheet $sheet, string $simulationId): Worksheet
    {
        $simulation = $this->simulationDao->getById($simulationId);
        $anneeDeReference = $simulation->getAnneeDeReference();

        $headers = [null];
        $moyenTermeData = [];
        $courtTermeData = [];
        $tresorerieData = [];
        $totalRow = ['Total'];

        for ($i = 0; $i < 50; $i++) {
            $headers[] = intval($anneeDeReference) + $i;
            $totalRow[] = 0;
        }

        $moyenTermeData[] = $headers;
        $courtTermeData[] = $headers;
        $tresorerieData[] = $headers;

        $moyenTermeData = $this->fetchPortageTresorerie($moyenTermeData, $simulationId, 'Portage moyen terme des investissements');
        $courtTermeData = $this->fetchPortageTresorerie($courtTermeData, $simulationId, 'Portage court terme des investissements');
        $tresorerieData = $this->fetchPortageTresorerie($tresorerieData, $simulationId, 'Autres variations potentiel financier');

        $moyenTermeData[] = $totalRow;
        $courtTermeData[] = $totalRow;
        $tresorerieData[] = $totalRow;

        $sheet->setTitle('portage_tresorerie');
        $sheet->setCellValue('A1', 'Portage et trésorerie');
        $sheet->setCellValue('A2', 'Portage moyen terme des investissements');
        $sheet->setCellValue('A11', 'Portage court terme des investissements. ');
        $sheet->setCellValue('A19', 'Trésorerie du cycle d\'exploitation');

        $sheet->fromArray($moyenTermeData, null, 'A3', true);
        $sheet->fromArray($courtTermeData, null, 'A12', true);
        $sheet->fromArray($tresorerieData, null, 'A20', true);

        $sheet->getStyle('A1:A25')->getFont()->setBold(true);
        $sheet->getStyle('B3:AY3')->getFont()->setBold(true);
        $sheet->getStyle('B12:AY12')->getFont()->setBold(true);
        $sheet->getStyle('B20:AZ20')->getFont()->setBold(true);

        $sheet->getStyle('A3:AY25')->getFont()->setSize(10);
        $sheet->getStyle('A11')->getFont()->setSize(11);
        $sheet->getStyle('A19')->getFont()->setSize(11);
        $sheet->getStyle('A1:AY25')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getColumnDimension('A')->setwidth(50);

        $sheet->getStyle('B3:AY3')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A4:AY9')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('B12:AY12')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A13:AY17')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('B20:AY20')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A21:AY25')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        for ($i = 2; $i <= count($headers); $i++) {
            $column = $this->columnLetter($i);
            $sheet->setCellValue(
                $column . '9',
                '=SUM(' . $column . '4:' . $column . '8)'
            );

            $sheet->setCellValue(
                $column . '17',
                '=SUM(' . $column . '13:' . $column . '16)'
            );

            $sheet->setCellValue(
                $column . '25',
                '=SUM(' . $column . '21:' . $column . '24)'
            );
        }

        return $sheet;
    }

    /**
     *  @param mixed[] $writeData
     *
     *  @return mixed[]
     */
    public function fetchPortageTresorerie(array $writeData, string $simulationId, string $type): array
    {
        $portageTresoreries = $this->findBySimulationAndType($simulationId, $type);

        foreach ($portageTresoreries as $portageTresorerie) {
            $row = [];
            $row[] = $portageTresorerie->getNom();
            $periodiques = $portageTresorerie->getPortageTresoreriePeriodique();

            foreach ($periodiques as $periodique) {
                $row[] = $periodique->getValeur();
            }

            $writeData[] = $row;
        }

        return $writeData;
    }

    public function columnLetter(int $c): string
    {
        if ($c <= 0) {
            return '';
        }

        $letter = '';

        while ($c !== 0) {
            $p = ($c - 1) % 26;
            $c = intval(($c - $p) / 26);
            $letter = chr(65 + $p) . $letter;
        }

        return $letter;
    }

    public function clonePortageTresorerie(Simulation $newSimulation, Simulation $oldSimulation): void
    {
        $objects = $this->portageTresorerieDao->findBySimulation($oldSimulation);
        foreach ($objects as $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $this->save($newObject);
            foreach ($object->getPortageTresoreriePeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setPortageTresorerie($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->portageTresoreriePeriodiqueDao->save($newPeriodique);
            }
        }
    }

    public function fusionPortageTresorerie(Simulation $newSimulation, Simulation $oldSimulation1, Simulation $oldSimulation2): void
    {
        $portageTresorerieParametre1 = $this->portageTresorerieParametreDao->findOneBySimulationId($oldSimulation1->getId());
        $portageTresorerieParametre2 = $this->portageTresorerieParametreDao->findOneBySimulationId($oldSimulation2->getId());
        if (! empty($portageTresorerieParametre1) && ! empty($portageTresorerieParametre2)) {
            $portageTresorerieParametre = new PortageTresorerieParametre($newSimulation);
            $portageTresorerieParametre->setSoldeEmplois($portageTresorerieParametre1->getSoldeEmplois() + $portageTresorerieParametre2->getSoldeEmplois());
            $portageTresorerieParametre->setDetteFournisseurs($portageTresorerieParametre1->getDetteFournisseurs() + $portageTresorerieParametre2->getDetteFournisseurs());
            $portageTresorerieParametre->setPromotionAccession($portageTresorerieParametre1->getPromotionAccession() + $portageTresorerieParametre2->getPromotionAccession());
            $this->portageTresorerieParametreDao->save($portageTresorerieParametre);
        }

        $objects1 = $this->portageTresorerieDao->findBySimulation($oldSimulation1);
        $objects2 = $this->portageTresorerieDao->findBySimulation($oldSimulation2);
        foreach ($objects1 as $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $this->save($newObject);
            foreach ($object->getPortageTresoreriePeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setPortageTresorerie($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->portageTresoreriePeriodiqueDao->save($newPeriodique);
            }
        }

        foreach ($objects2 as $object) {
            $portageTresorerie = $this->portageTresorerieDao->findOneBySimulationAndNom($newSimulation, $object->getNom());
            if (empty($portageTresorerie)) {
                continue;
            }

            foreach ($portageTresorerie->getPortageTresoreriePeriodique() as $periodique) {
                $oldPortageTresoreriePeriodique = $this->portageTresoreriePeriodiqueDao->findOneByPortageTresorerieIDAndIterartion($object->getId(), $periodique->getIteration());
                $periodique->setValeur($periodique->getValeur() + $oldPortageTresoreriePeriodique->getValeur());
                $this->portageTresoreriePeriodiqueDao->save($periodique);
            }
        }
    }
}
