<?php

declare(strict_types=1);

namespace App\Services;

use App\Dao\DemolitionDao;
use App\Dao\DemolitionPeriodiqueDao;
use App\Dao\PatrimoineDao;
use App\Dao\SimulationDao;
use App\Dao\TypeEmpruntDemolitionDao;
use App\Exceptions\HTTPException;
use App\Model\Demolition;
use App\Model\Factories\DemolitionFactory;
use App\Model\Simulation;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Csv as ReaderCsv;
use PhpOffice\PhpSpreadsheet\Reader\Ods as ReaderOds;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as ReaderXlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Component\HttpFoundation\Request;
use TheCodingMachine\TDBM\ResultIterator;
use TheCodingMachine\TDBM\TDBMException;
use Throwable;
use function array_pop;
use function array_push;
use function array_values;
use function chr;
use function count;
use function end;
use function floatval;
use function in_array;
use function intval;
use function Safe\json_encode;
use function strval;

final class DemolitionService
{
    /** @var DemolitionDao */
    private $demolitionDao;

    /** @var PatrimoineDao */
    private $patrimoineDao;

    /** @var TypeEmpruntDemolitionDao */
    private $typeEmpruntDemolitionDao;
    /** @var DemolitionPeriodiqueDao */
    private $periodiqueDao;

    /** @var TypeEmpruntService */
    private $typeEmpruntService;

    /** @var SimulationDao */
    private $simulationDao;

    /** @var DemolitionFactory */
    private $factory;

    public function __construct(
        DemolitionDao $demolitionDao,
        TypeEmpruntDemolitionDao $typeEmpruntDemolitionDao,
        DemolitionPeriodiqueDao $periodiqueDao,
        TypeEmpruntService $typeEmpruntService,
        SimulationDao $simulationDao,
        DemolitionFactory $factory,
        PatrimoineDao $patrimoineDao
    ) {
        $this->demolitionDao = $demolitionDao;
        $this->typeEmpruntDemolitionDao = $typeEmpruntDemolitionDao;
        $this->periodiqueDao = $periodiqueDao;
        $this->typeEmpruntService = $typeEmpruntService;
        $this->simulationDao = $simulationDao;
        $this->factory = $factory;
        $this->patrimoineDao = $patrimoineDao;
    }

    /**
     * @throws HTTPException
     */
    public function save(Demolition $demolition): void
    {
        try {
            $this->demolitionDao->save($demolition);
        } catch (Throwable $e) {
            throw HTTPException::badRequest('Cette démolition existe déjà.', $e);
        }
    }

    /**
     * @throws TDBMException
     */
    public function remove(string $demolitionUUID): void
    {
        try {
            $demolition = $this->demolitionDao->getById($demolitionUUID);
            $this->demolitionDao->delete($demolition, true);
        } catch (Throwable $e) {
            throw HTTPException::badRequest('Cette démolition n\'existe déjà.', $e);
        }
    }

    /**
     * @throws HTTPException
     */
    public function removeTypeDempruntDemolition(string $typesEmpruntsUUID, string $demolitionUUID): void
    {
        $typeEmpruntDemolition = $this->typeEmpruntDemolitionDao->findByTypeEmpruntAndDemolition(
            $typesEmpruntsUUID,
            $demolitionUUID
        );

        if ($typeEmpruntDemolition === null) {
            throw HTTPException::badRequest('Ce type d\'emprunt démolition existe déjà.');
        }

        $this->typeEmpruntDemolitionDao->delete($typeEmpruntDemolition);
    }

    /**
     * @return Demolition[]|ResultIterator
     */
    public function findBySimulationAndType(string $simulationId, int $type): ResultIterator
    {
        return $this->demolitionDao->findBySimulationIDAndType($simulationId, $type);
    }

    /**
     * @return Demolition[]|ResultIterator
     */
    public function findBySimulationID(string $simulationId): ResultIterator
    {
        return $this->demolitionDao->findBySimulationID($simulationId);
    }

    public function exportDemolition(int $type, Worksheet $sheet, string $simulationId): Worksheet
    {
        $simulation = $this->simulationDao->getById($simulationId);
        $anneeDeReference = $simulation->getAnneeDeReference();

        switch ($type) {
            case Demolition::TYPE_NON_IDENTIFIEE:
                $writeData = [];
                $headers = [
                    'N°',
                    'Nom catégorie',
                    'Conventionné',
                    'Surface moyenne par logement en m²',
                    'Loyer mensuel € / m² / logt',
                    'Réduction TFPB€ / lgt démoli',
                    'Réduction de GE € / lgt démoli',
                    'Réduction de maintenance courante € / lgt démoli',
                    'Convention ANRU',
                ];

                for ($i = 0; $i < 50; $i++) {
                    array_push($headers, 'Nb logts démolis ' . (intval($anneeDeReference) + $i));
                }

                array_push($headers, 'Indexation à l\'ICC');

                for ($i = 0; $i < 50; $i++) {
                    array_push($headers, 'Coût moyen de la démolition en K€ / lgt ' . (intval($anneeDeReference) + $i));
                }

                for ($i = 0; $i < 50; $i++) {
                    array_push($headers, 'Rembourst CRD en K€ / lgt ' . (intval($anneeDeReference) + $i));
                }

                for ($i = 0; $i < 50; $i++) {
                    array_push($headers, 'Coûts annexes démolition en K€ / lgt ' . (intval($anneeDeReference) + $i));
                }

                array_push($headers, 'Quotité de fonds propres investis');
                array_push($headers, 'Quotité subventions Etat finançant la démolition et le CRD');
                array_push($headers, 'Quotité subvention ANRU en %');
                array_push($headers, 'Quotité subvention EPCI / Commune en %');
                array_push($headers, 'Quotité subvention département en %');
                array_push($headers, 'Quotité subvention région en %');
                array_push($headers, 'Quotité subvention collecteuren %');
                array_push($headers, 'Quotité autres subventions en %');
                array_push($headers, 'Total Emprunt');
                array_push($headers, 'Numéro d\'emprunt');
                array_push($headers, 'Quotité emprunt');
                array_push($headers, 'Nombre d\'années d\'amortissement financier résiduelles');
                array_push($writeData, $headers);

                $demolitions = $this->findBySimulationAndType($simulationId, $type);
                $typeEmpruntsDemolitionNumber = 0;

                foreach ($demolitions as $demolition) {
                    $demolitionPeriodiques = $demolition->getDemolitionPeriodique();
                    $typeEmpruntDemolitions = $demolition->getTypeEmpruntDemolition();

                    $row = [];
                    $totalEmprunt = 0;

                    if (count($typeEmpruntDemolitions) !== 0) {
                        $typeEmpruntsDemolitionNumber += count($typeEmpruntDemolitions);

                        foreach ($typeEmpruntDemolitions as $typeEmpruntDemolition) {
                            $totalEmprunt += floatval($typeEmpruntDemolition->getQuotiteEmprunt());
                        }

                        foreach ($typeEmpruntDemolitions as $key => $value) {
                            $row = [];
                            if ($key === 0) {
                                array_push($row, $demolition->getNGroupe());
                                array_push($row, $demolition->getNomCategorie());
                                array_push($row, $demolition->getLogementsConventionees() === true ? 'Oui' : 'Non');
                                array_push($row, $demolition->getSurfaceMoyenne());
                                array_push($row, $demolition->getLoyerMensuel());
                                array_push($row, $demolition->getTfpb());
                                array_push($row, $demolition->getGrosEntretien());
                                array_push($row, $demolition->getMaintenanceCourante());
                                array_push($row, $demolition->getConventionAnru() === true ? 'Oui' : 'Non');

                                foreach ($demolitionPeriodiques as $demolitionPeriodique) {
                                    array_push($row, $demolitionPeriodique->getNombreLogements());
                                }

                                array_push($row, $demolition->getIndexationIcc() === true ? 'Oui' : 'Non');

                                foreach ($demolitionPeriodiques as $demolitionPeriodique) {
                                    array_push($row, $demolitionPeriodique->getCoutMoyen());
                                }

                                foreach ($demolitionPeriodiques as $demolitionPeriodique) {
                                    array_push($row, $demolitionPeriodique->getRemboursement());
                                }

                                foreach ($demolitionPeriodiques as $demolitionPeriodique) {
                                    array_push($row, $demolitionPeriodique->getCoutAnnexes());
                                }

                                array_push($row, $demolition->getFoundsPropres());
                                array_push($row, $demolition->getSubventionsEtat());
                                array_push($row, $demolition->getSubventionsAnru());
                                array_push($row, $demolition->getSubventionsEpci());
                                array_push($row, $demolition->getSubventionsDepartement());
                                array_push($row, $demolition->getSubventionsRegion());
                                array_push($row, $demolition->getSubventionsCollecteur());
                                array_push($row, $demolition->getAutresSubventions());
                                array_push($row, 'SUM');
                                array_push($row, $value->getTypesEmprunts()->getNumero());
                                array_push($row, $value->getQuotiteEmprunt());
                                array_push($row, $demolition->getNombreAnneesAmortissements());
                            } else {
                                array_push($row, $demolition->getNGroupe());
                                array_push($row, $demolition->getNomCategorie());

                                for ($i = 0; $i < 217; $i++) {
                                    array_push($row, null);
                                }

                                array_push($row, $value->getTypesEmprunts()->getNumero());
                                array_push($row, $value->getQuotiteEmprunt());
                                array_push($row, null);
                            }
                            array_push($writeData, $row);
                        }
                    } else {
                        $typeEmpruntsDemolitionNumber++;

                        array_push($row, $demolition->getNGroupe());
                        array_push($row, $demolition->getNomCategorie());
                        array_push($row, $demolition->getLogementsConventionees() === true ? 'Oui' : 'Non');
                        array_push($row, $demolition->getSurfaceMoyenne());
                        array_push($row, $demolition->getLoyerMensuel());
                        array_push($row, $demolition->getTfpb());
                        array_push($row, $demolition->getGrosEntretien());
                        array_push($row, $demolition->getMaintenanceCourante());
                        array_push($row, $demolition->getConventionAnru() === true ? 'Oui' : 'Non');

                        foreach ($demolitionPeriodiques as $demolitionPeriodique) {
                            array_push($row, $demolitionPeriodique->getNombreLogements());
                        }

                        array_push($row, $demolition->getIndexationIcc() === true ? 'Oui' : 'Non');

                        foreach ($demolitionPeriodiques as $demolitionPeriodique) {
                            array_push($row, $demolitionPeriodique->getCoutMoyen());
                        }

                        foreach ($demolitionPeriodiques as $demolitionPeriodique) {
                            array_push($row, $demolitionPeriodique->getRemboursement());
                        }

                        foreach ($demolitionPeriodiques as $demolitionPeriodique) {
                            array_push($row, $demolitionPeriodique->getCoutAnnexes());
                        }

                        array_push($row, $demolition->getFoundsPropres());
                        array_push($row, $demolition->getSubventionsEtat());
                        array_push($row, $demolition->getSubventionsAnru());
                        array_push($row, $demolition->getSubventionsEpci());
                        array_push($row, $demolition->getSubventionsDepartement());
                        array_push($row, $demolition->getSubventionsRegion());
                        array_push($row, $demolition->getSubventionsCollecteur());
                        array_push($row, $demolition->getAutresSubventions());
                        array_push($row, null);
                        array_push($row, null);
                        array_push($row, null);
                        array_push($row, $demolition->getNombreAnneesAmortissements());
                        array_push($writeData, $row);
                    }
                }

                $sheet->setTitle('demolitions_nonidentifiees');
                $sheet->setCellValue('A1', 'Démolitions non identifiées');
                $sheet->fromArray($writeData, null, 'A2', true);
                $sheet->getRowDimension(2)->setRowHeight(50);

                for ($i = 1; $i <= 500; $i++) {
                    $column = $this->columnLetter($i);
                    $sheet->getColumnDimension($column)->setWidth(25);
                    $sheet->getStyle($column . '2')->getAlignment()->setWrapText(true);

                    if ($column === 'HN') {
                        break;
                    }
                }

                for ($i = 3; $i < $typeEmpruntsDemolitionNumber + 1; $i++) {
                    $value = $this->getCellValue($sheet, $i, 'HK');

                    if ($value !== 'SUM') {
                        continue;
                    }

                    $lastRow = 0;

                    for ($j = $i + 1; $j < $typeEmpruntsDemolitionNumber + 1; $j++) {
                        $nextValue = $this->getCellValue($sheet, $j, 'HK');

                        if ($nextValue === 'SUM') {
                            $lastRow = $j - 1;
                            break;
                        }

                        if ($j !== $typeEmpruntsDemolitionNumber) {
                            continue;
                        }

                        $lastRow = $typeEmpruntsDemolitionNumber + 1;
                    }

                    if ($lastRow === 0) {
                        $sheet->setCellValue(
                            'HK' . $i,
                            '=HM' . $i
                        );
                    } else {
                        $sheet->setCellValue(
                            'HK' . $i,
                            '=SUM(HM' . $i . ':HM' . $lastRow . ')'
                        );
                    }
                }

                $sheet->getStyle('A1')->getFont()->setBold(true);
                $sheet->getStyle('A2:HN2')->getFont()->setBold(true);
                $sheet->getStyle('A2:HN' . ($typeEmpruntsDemolitionNumber+2))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle('A2:HN' . ($typeEmpruntsDemolitionNumber+2))->getFont()->setSize(10);
                $sheet->getStyle('A2:HN' . ($typeEmpruntsDemolitionNumber+2))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                break;
            default:
                $writeData = [];
                $nonSumColumns = [1, 2, 3, 4, 5, 6, 7,10, 11, 12, 13, 14, 27, 28, 29];
                $headers = [
                    'N° groupe',
                    'N° sous-groupe',
                    'Information',
                    'Nom du groupe',
                    'N° tranche',
                    'Nom de la tranche',
                    'Convention ANRU',
                    'Surface démolie',
                    'Nombre de logements',
                    'Date démolition',
                    'Indexé ICC',
                    'Réduction TFPB',
                    'Réduction MC',
                    'Réduction de GE',
                    'Coûts Démolition',
                    'Remb. de C.R.D',
                    'Coûts annexes',
                    'Remb. de Subventions',
                    'Fonds propres',
                    'Subventions d\'Etat',
                    'Subventions ANRU',
                    'Subventions EPCI / Commune',
                    'Subventions département',
                    'Subventions Région',
                    'Subventions collecteur',
                    'Autres subventions',
                    'Total emprunts',
                    'Numéro d\'emprunt',
                    'Date d\'emprunt',
                    'Montant',
                ];

                for ($i = 0; $i < 50; $i++) {
                    array_push($headers, 'Economie d\'annuités - part capital' . (intval($anneeDeReference) + $i));
                }

                for ($i = 0; $i < 50; $i++) {
                    array_push($headers, 'Economie d\'annuités - part intérêts' . (intval($anneeDeReference) + $i));
                }

                array_push($writeData, $headers);

                $demolitions = $this->findBySimulationAndType($simulationId, Demolition::TYPE_IDENTIFIEE);
                $typeEmpruntsDemolitionNumber = 0;

                foreach ($demolitions as $demolition) {
                    $demolitionPeriodiques = $demolition->getDemolitionPeriodique();
                    $typeEmpruntDemolitions = $demolition->getTypeEmpruntDemolition();
                    $row = [];

                    if (count($typeEmpruntDemolitions) !== 0) {
                        $typeEmpruntsDemolitionNumber += count($typeEmpruntDemolitions);

                        foreach ($typeEmpruntDemolitions as $key => $value) {
                            $row = [];

                            if ($key === 0) {
                                array_push($row, $demolition->getNGroupe());
                                array_push($row, $demolition->getNSousGroupe());
                                array_push($row, $demolition->getInformation());
                                array_push($row, $demolition->getNomGroupe());
                                array_push($row, $demolition->getNumero());
                                array_push($row, $demolition->getNomTranche());
                                array_push($row, $demolition->getConventionAnru() === true ? 'Oui' : 'Non');
                                array_push($row, $demolition->getSurfaceDemolie());
                                array_push($row, $demolition->getNombreLogementDemolis());
                                array_push($row, $demolition->getDateDemolution());
                                array_push($row, $demolition->getIndexationIcc() === true ? 'Oui' : 'Non');
                                array_push($row, $demolition->getTfpb());
                                array_push($row, $demolition->getGrosEntretien());
                                array_push($row, $demolition->getMaintenanceCourante());
                                array_push($row, $demolition->getCoutDemolution());
                                array_push($row, $demolition->getRemboursementCrd());
                                array_push($row, $demolition->getCoutAnnexes());
                                array_push($row, $demolition->getRemboursementSubventions());
                                array_push($row, $demolition->getFoundsPropres());
                                array_push($row, $demolition->getSubventionsEtat());
                                array_push($row, $demolition->getSubventionsAnru());
                                array_push($row, $demolition->getSubventionsEpci());
                                array_push($row, $demolition->getSubventionsDepartement());
                                array_push($row, $demolition->getSubventionsRegion());
                                array_push($row, $demolition->getSubventionsCollecteur());
                                array_push($row, $demolition->getAutresSubventions());
                                array_push($row, 'SUM');
                                array_push($row, $value->getTypesEmprunts()->getNumero());
                                array_push($row, $value->getDatePremiere());
                                array_push($row, $value->getMontant());

                                foreach ($demolitionPeriodiques as $demolitionPeriodique) {
                                    array_push($row, $demolitionPeriodique->getPartCapital());
                                }

                                foreach ($demolitionPeriodiques as $demolitionPeriodique) {
                                    array_push($row, $demolitionPeriodique->getPartInterets());
                                }
                            } else {
                                array_push($row, $demolition->getNGroupe());
                                array_push($row, $demolition->getNSousGroupe());

                                for ($i = 1; $i <= 25; $i++) {
                                    array_push($row, null);
                                }

                                array_push($row, $value->getTypesEmprunts()->getNumero());
                                array_push($row, $value->getDatePremiere());
                                array_push($row, $value->getMontant());

                                for ($i = 1; $i <= 100; $i++) {
                                    array_push($row, null);
                                }
                            }
                            array_push($writeData, $row);
                        }
                    } else {
                        $typeEmpruntsDemolitionNumber++;
                        array_push($row, $demolition->getNGroupe());
                        array_push($row, $demolition->getNSousGroupe());
                        array_push($row, $demolition->getInformation());
                        array_push($row, $demolition->getNomGroupe());
                        array_push($row, $demolition->getNumero());
                        array_push($row, $demolition->getNomTranche());
                        array_push($row, $demolition->getConventionAnru() === true ? 'Oui' : 'Non');
                        array_push($row, $demolition->getSurfaceDemolie());
                        array_push($row, $demolition->getNombreLogementDemolis());
                        array_push($row, $demolition->getDateDemolution());
                        array_push($row, $demolition->getIndexationIcc() === true ? 'Oui' : 'Non');
                        array_push($row, $demolition->getTfpb());
                        array_push($row, $demolition->getGrosEntretien());
                        array_push($row, $demolition->getMaintenanceCourante());
                        array_push($row, $demolition->getCoutDemolution());
                        array_push($row, $demolition->getRemboursementCrd());
                        array_push($row, $demolition->getCoutAnnexes());
                        array_push($row, $demolition->getRemboursementSubventions());
                        array_push($row, $demolition->getFoundsPropres());
                        array_push($row, $demolition->getSubventionsEtat());
                        array_push($row, $demolition->getSubventionsAnru());
                        array_push($row, $demolition->getSubventionsEpci());
                        array_push($row, $demolition->getSubventionsDepartement());
                        array_push($row, $demolition->getSubventionsRegion());
                        array_push($row, $demolition->getSubventionsCollecteur());
                        array_push($row, $demolition->getAutresSubventions());
                        array_push($row, null);
                        array_push($row, null);
                        array_push($row, null);
                        array_push($row, null);

                        foreach ($demolitionPeriodiques as $demolitionPeriodique) {
                            array_push($row, $demolitionPeriodique->getPartCapital());
                        }

                        foreach ($demolitionPeriodiques as $demolitionPeriodique) {
                            array_push($row, $demolitionPeriodique->getPartInterets());
                        }

                        array_push($writeData, $row);
                    }
                }

                $totalRow = ['total'];

                for ($i = 1; $i <= 130; $i++) {
                    array_push($totalRow, null);
                }

                array_push($writeData, $totalRow);
                $sheet->setTitle('demolitions_identifiees');
                $sheet->setCellValue('A1', 'Démolitions identifiées');
                $sheet->fromArray($writeData, null, 'A2', true);
                $sheet->getRowDimension(2)->setRowHeight(50);

                for ($i = 1; $i <= 130; $i++) {
                    $column = $this->columnLetter($i);
                    $sheet->getColumnDimension($column)->setWidth(20);
                    $sheet->getStyle($column . '2')->getAlignment()->setWrapText(true);

                    if (in_array($i, $nonSumColumns)) {
                        continue;
                    }

                    if ($typeEmpruntsDemolitionNumber === 0) {
                        $sheet->setCellValue(
                            $column . (count($demolitions) + 3),
                            '=SUM(' . $column . '3:' . $column . (count($demolitions) + 2) . ')'
                        );
                    } else {
                        $sheet->setCellValue(
                            $column . ($typeEmpruntsDemolitionNumber+3),
                            '=SUM(' . $column . '3:' . $column . ($typeEmpruntsDemolitionNumber + 2) . ')'
                        );
                    }
                }

                for ($i = 3; $i < $typeEmpruntsDemolitionNumber + 3; $i++) {
                    $value = $this->getCellValue($sheet, $i, 'AA');

                    if ($value !== 'SUM') {
                        continue;
                    }

                    $lastRow = 0;

                    for ($j = $i + 1; $j < $typeEmpruntsDemolitionNumber + 3; $j++) {
                        $nextValue = $this->getCellValue($sheet, $j, 'AA');

                        if ($nextValue === 'SUM') {
                            $lastRow = $j - 1;
                            break;
                        }

                        if ($j !== $typeEmpruntsDemolitionNumber + 2) {
                            continue;
                        }

                        $lastRow = $typeEmpruntsDemolitionNumber + 2;
                    }

                    if ($lastRow === 0) {
                        $sheet->setCellValue(
                            'AA' . $i,
                            '=AD' . $i
                        );
                    } else {
                        $sheet->setCellValue(
                            'AA' . $i,
                            '=SUM(AD' . $i . ':AD' . $lastRow . ')'
                        );
                    }
                }

                $sheet->getStyle('A1')->getFont()->setBold(true);
                $sheet->getStyle('A2:DZ2')->getFont()->setBold(true);

                $sheet->getStyle('A2:DZ' . ($typeEmpruntsDemolitionNumber+3))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle('A2:DZ' . ($typeEmpruntsDemolitionNumber+3))->getFont()->setSize(10);
                $sheet->getStyle('A2:DZ' . ($typeEmpruntsDemolitionNumber+3))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                break;
        }

        return $sheet;
    }

    public function getCellValue(Worksheet $sheet, int $i, string $column): ?string
    {
        /** @var Cell $cell */
        $cell = $sheet->getCell($column . $i);

        return $cell->getValue();
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

    /**
     *  @param mixed[] $data
     *
     *  @return mixed[]
     */
    public function getTypeEmpruntsData(array $data, int $type, string $target): array
    {
        $result = [];
        $numero = $data[0][0];
        $item = [];

        switch ($type) {
            case 1:
                foreach ($data as $key => $value) {
                    if ($target === 'numero') {
                        if ($value[0] === $numero) {
                            if (in_array($value[219], $item)) {
                                return ['wrong numero'];
                            }

                            array_push($item, $value[219]);
                        } else {
                            array_push($result, $item);
                            $numero = $value[0];
                            $item = [];
                            array_push($item, $value[219]);
                        }
                    }

                    if ($target === 'quotite') {
                        if ($value[0] === $numero) {
                            array_push($item, $value[220]);
                        } else {
                            array_push($result, $item);
                            $numero = $value[0];
                            $item = [];
                            array_push($item, $value[220]);
                        }
                    }

                    if (end($data) !== $value) {
                        continue;
                    }

                    array_push($result, $item);
                }
                break;

            default:
                foreach ($data as $key => $value) {
                    if ($target === 'numero') {
                        if ($value[0] === $numero) {
                            if (in_array($value[27], $item)) {
                                return ['wrong numero'];
                            }

                            array_push($item, $value[27]);
                        } else {
                            array_push($result, $item);
                            $numero = $value[0];
                            $item = [];
                            array_push($item, $value[27]);
                        }
                    }

                    if ($target === 'montant') {
                        if ($value[0] === $numero) {
                            array_push($item, $value[29]);
                        } else {
                            array_push($result, $item);
                            $numero = $value[0];
                            $item = [];
                            array_push($item, $value[29]);
                        }
                    }

                    if (end($data) !== $value) {
                        continue;
                    }

                    array_push($result, $item);
                }
                break;
        }

        return $result;
    }

    public function importDemolition(int $type, Request $request, string $simulationId): string
    {
        $file = $request->files->get('file');
        $extension = $file->getclientOriginalExtension();

        switch ($extension) {
            case 'ods':
                $reader = new ReaderOds();
                break;
            case 'xlsx':
                $reader = new ReaderXlsx();
                break;
            case 'csv':
                $reader = new ReaderCsv();
                break;
            default:
                throw HTTPException::badRequest('Extension invalide');
        }

        if ($file) {
            $spreadsheet = IOFactory::load($file);
            $changedIds = [];

            switch ($type) {
                case Demolition::TYPE_NON_IDENTIFIEE:
                    $isNonidentifees = false;

                    foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                        $worksheetTitle = $worksheet->getTitle();

                        if ($worksheetTitle !== 'demolitions_nonidentifiees') {
                            continue;
                        }

                        $isNonidentifees = true;
                    }

                    if ($isNonidentifees === false) {
                        throw HTTPException::badRequest('Il n\'y a pas de feuille correcte, veuillez vérifier à nouveau.');
                    }

                    $data['nonidentifiees'] = [
                        'columnNames' => [],
                        'columnValues' => [],
                    ];

                    foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                        $worksheetTitle = $worksheet->getTitle();

                        if ($worksheetTitle !== 'demolitions_nonidentifiees') {
                            continue;
                        }

                        foreach ($worksheet->getRowIterator() as $row) {
                            $rowIndex = $row->getRowIndex();

                            if ($rowIndex < 2) {
                                continue;
                            }

                            $cellIterator = $row->getCellIterator();
                            $cellIterator->setIterateOnlyExistingCells(true);

                            foreach ($cellIterator as $cell) {
                                if ($rowIndex < 2) {
                                    continue;
                                }

                                if ($rowIndex === 2) {
                                    $data['nonidentifiees']['columnNames'][] = $cell->getCalculatedValue();
                                } else {
                                    $data['nonidentifiees']['columnValues'][$rowIndex][] = $cell->getCalculatedValue();
                                }
                            }
                        }
                    }

                    $data['nonidentifiees']['columnValues'] = array_values($data['nonidentifiees']['columnValues']);
                    $saveData = [];
                    $temp = '';

                    foreach ($data['nonidentifiees']['columnValues'] as $item) {
                        if ($temp === $item[0]) {
                            continue;
                        }

                        array_push($saveData, $item);
                        $temp = $item[0];
                    }

                    $typeEmpruntsNumeros = $this->getTypeEmpruntsData($data['nonidentifiees']['columnValues'], 1, 'numero');

                    if ($typeEmpruntsNumeros[0] === 'wrong numero') {
                        throw HTTPException::badRequest('Le numéro répété est détecté. S\'il vous plaît vérifier le numéro');
                    }

                    $typeEmpruntsQuotiteEmprunts = $this->getTypeEmpruntsData($data['nonidentifiees']['columnValues'], 1, 'quotite');

                    foreach ($saveData as $key => $value) {
                        if (count($typeEmpruntsNumeros) > 0) {
                            for ($i = 0; $i < count($typeEmpruntsNumeros[$key]); $i++) {
                                if (! isset($typeEmpruntsNumeros[$key][$i])) {
                                    continue;
                                }

                                $typeEmpts = $this->typeEmpruntService->fetchByNumero(strval($typeEmpruntsNumeros[$key][$i]));

                                if (count($typeEmpts) === 0) {
                                    throw HTTPException::badRequest('Il n\'y a pas de tels emprunts de type.');
                                }
                            }
                        }

                        $valueArray = ['Oui', 'Non'];

                        if (! in_array($value[2], $valueArray) || ! in_array($value[8], $valueArray)) {
                            throw HTTPException::badRequest('La valeur doit etre Non ou Oui');
                        }
                    }

                    $newNonIdentifees = [];
                    foreach ($saveData as $key => $value) {
                        $typeEmprunts = [];
                        $nombreLogements = [];
                        $coutMoyen = [];
                        $remboursement = [];
                        $coutAnnexes = [];

                        if (count($typeEmpruntsNumeros) !== 0) {
                            for ($i = 0; $i < count($typeEmpruntsNumeros[$key]); $i++) {
                                if (! isset($typeEmpruntsNumeros[$key][$i])) {
                                    continue;
                                }

                                $typeEmpts = $this->typeEmpruntService->fetchByNumero(strval($typeEmpruntsNumeros[$key][$i]));
                                $typeEmprunt = [
                                    'id' => $typeEmpts[0]->getId() ,
                                    'quotiteEmprunt' => $typeEmpruntsQuotiteEmprunts[$key][$i],
                                ];

                                array_push($typeEmprunts, json_encode($typeEmprunt));
                            }
                        }

                        for ($i = 9; $i <= 221; $i++) {
                            if ($i < 59) {
                                array_push($nombreLogements, $value[$i]);
                            }

                            if ($i !== 59 && $i < 110) {
                                array_push($coutMoyen, $value[$i]);
                            }

                            if ($i >= 110 && $i < 160) {
                                array_push($remboursement, $value[$i]);
                            }

                            if ($i < 160 || $i >= 210) {
                                continue;
                            }

                            array_push($coutAnnexes, $value[$i]);
                        }

                        $oldDemolition = $this->demolitionDao->findOneByNGroupe($simulationId, intval($value[0]), 1);

                        try {
                            if (count($oldDemolition) > 0) {
                                $oldIdentifiee = $this->factory->createDemolition(
                                    $oldDemolition[0]->getId(),
                                    $simulationId,
                                    intval($value[0]),
                                    null,
                                    null,
                                    null,
                                    null,
                                    null,
                                    $value[8] === 'Oui',
                                    null,
                                    null,
                                    null,
                                    $value[59] === 'Oui',
                                    null,
                                    null,
                                    $value[211],
                                    null,
                                    null,
                                    $value[210],
                                    $value[212],
                                    $value[213],
                                    $value[214],
                                    $value[215],
                                    $value[216],
                                    $value[217],
                                    null,
                                    $value[5],
                                    $value[7],
                                    $value[6],
                                    strval($value[1]),
                                    $value[3],
                                    $value[4],
                                    $value[2] === 'Oui',
                                    intval($value[221]),
                                    null,
                                    null,
                                    1,
                                    count($typeEmprunts) === 0 ? null : $typeEmprunts,
                                    json_encode([
                                        'nombre_logements' => $nombreLogements,
                                        'cout_moyen' => $coutMoyen,
                                        'remboursement' => $remboursement,
                                        'cout_annexes' => $coutAnnexes,
                                    ])
                                );

                                $this->save($oldIdentifiee);
                                $changedIds[] = $oldDemolition[0]->getId();
                            } else {
                                $identifiee = $this->factory->createDemolition(
                                    null,
                                    $simulationId,
                                    intval($value[0]),
                                    null,
                                    null,
                                    null,
                                    null,
                                    null,
                                    $value[8] === 'Oui',
                                    null,
                                    null,
                                    null,
                                    $value[59] === 'Oui',
                                    null,
                                    null,
                                    $value[211],
                                    null,
                                    null,
                                    $value[210],
                                    $value[212],
                                    $value[213],
                                    $value[214],
                                    $value[215],
                                    $value[216],
                                    $value[217],
                                    null,
                                    $value[5],
                                    $value[7],
                                    $value[6],
                                    strval($value[1]),
                                    $value[3],
                                    $value[4],
                                    $value[2] === 'Oui',
                                    intval($value[221]),
                                    null,
                                    null,
                                    1,
                                    count($typeEmprunts) === 0 ? null : $typeEmprunts,
                                    json_encode([
                                        'nombre_logements' => $nombreLogements,
                                        'cout_moyen' => $coutMoyen,
                                        'remboursement' => $remboursement,
                                        'cout_annexes' => $coutAnnexes,
                                    ])
                                );

                                $this->save($identifiee);
                                $changedIds[] = $identifiee->getId();
                            }
                        } catch (Throwable $e) {
                            throw HTTPException::badRequest('Impossible d\'importer des données', $e);
                        }
                    }

                    $allIdentifiees = $this->findBySimulationAndType($simulationId, Demolition::TYPE_NON_IDENTIFIEE);

                    foreach ($allIdentifiees as $_identifiee) {
                        if (in_array($_identifiee->getId(), $changedIds)) {
                            continue;
                        }

                        $this->remove($_identifiee->getId());
                    }
                    break;

                default:
                    $isIdentifees = false;

                    foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                        $worksheetTitle = $worksheet->getTitle();

                        if ($worksheetTitle !== 'demolitions_identifiees') {
                            continue;
                        }

                        $isIdentifees = true;
                    }

                    if ($isIdentifees === false) {
                        throw HTTPException::badRequest('Il n\'y a pas de feuille correcte, veuillez vérifier à nouveau.');
                    }

                    $data['demolitions_identifiees'] = [
                        'columnNames' => [],
                        'columnValues' => [],
                    ];

                    foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                        $worksheetTitle = $worksheet->getTitle();

                        if ($worksheetTitle !== 'demolitions_identifiees') {
                            continue;
                        }

                        foreach ($worksheet->getRowIterator() as $row) {
                            $rowIndex = $row->getRowIndex();

                            if ($rowIndex < 2) {
                                continue;
                            }

                            $cellIterator = $row->getCellIterator();
                            $cellIterator->setIterateOnlyExistingCells(true);

                            foreach ($cellIterator as $cell) {
                                if ($rowIndex === 2) {
                                    $data['demolitions_identifiees']['columnNames'][] = $cell->getCalculatedValue();
                                } else {
                                    $data['demolitions_identifiees']['columnValues'][$rowIndex][] = $cell->getCalculatedValue();
                                }
                            }
                        }
                    }

                    $data['demolitions_identifiees']['columnValues'] = array_values($data['demolitions_identifiees']['columnValues']);
                    array_pop($data['demolitions_identifiees']['columnValues']);

                    $saveData = [];
                    $temp = '';

                    foreach ($data['demolitions_identifiees']['columnValues'] as $item) {
                        if ($temp === $item[0]) {
                            continue;
                        }

                        array_push($saveData, $item);
                        $temp = $item[0];
                    }

                    $typeEmpruntsNumeros = $this->getTypeEmpruntsData($data['demolitions_identifiees']['columnValues'], 0, 'numero');

                    if ($typeEmpruntsNumeros[0] === 'wrong numero') {
                        throw HTTPException::badRequest('Le numéro répété est détecté. S\'il vous plaît vérifier le numéro.');
                    }

                    $typeEmpruntsMontants = $this->getTypeEmpruntsData($data['demolitions_identifiees']['columnValues'], 0, 'montant');

                    foreach ($saveData as $key => $value) {
                        if (count($typeEmpruntsNumeros) > 0) {
                            for ($i = 0; $i < count($typeEmpruntsNumeros[$key]); $i++) {
                                if (! isset($typeEmpruntsNumeros[$key][$i])) {
                                    continue;
                                }

                                $typeEmpts = $this->typeEmpruntService->fetchByNumero(strval($typeEmpruntsNumeros[$key][$i]));

                                if (count($typeEmpts) === 0) {
                                    throw HTTPException::badRequest('Il n\'y a pas de tels emprunts de type.');
                                }
                            }
                        }

                        $valueArray = ['Oui', 'Non'];

                        if (! in_array($value[6], $valueArray)) {
                            throw HTTPException::badRequest('La valeur doit etre Non ou Oui');
                        }

                        $patrimoine = $this->patrimoineDao->findOneByNGroupe($simulationId, intval($value[0]));

                        if (count($patrimoine) === 0) {
                            throw HTTPException::badRequest('Patrimoine N°groupe ' . $value[0] . '  n\'existe pas.');
                        }
                    }

                    foreach ($saveData as $key => $value) {
                        $typeEmprunts = [];
                        $partCapital = [];
                        $partInterets = [];

                        if (count($typeEmpruntsNumeros) > 0) {
                            for ($i = 0; $i < count($typeEmpruntsNumeros[$key]); $i++) {
                                if (! isset($typeEmpruntsNumeros[$key][$i])) {
                                    continue;
                                }

                                $typeEmpts = $this->typeEmpruntService->fetchByNumero(strval($typeEmpruntsNumeros[$key][$i]));
                                $typeEmprunt = [
                                    'id' => $typeEmpts[0]->getId(),
                                    'montant' => $typeEmpruntsMontants[$key][$i],
                                    'datePremiere' => $value[28],
                                ];

                                array_push($typeEmprunts, json_encode($typeEmprunt));
                            }
                        }

                        for ($i = 30; $i <= 129; $i++) {
                            if ($i < 80) {
                                array_push($partCapital, $value[$i]);
                            } else {
                                array_push($partInterets, $value[$i]);
                            }
                        }

                        $oldDemolition = $this->demolitionDao->findOneByNGroupe($simulationId, intval($value[0]), 0);

                        try {
                            if (count($oldDemolition) > 0) {
                                $oldIdentifee = $this->factory->createDemolition(
                                    $oldDemolition[0]->getId(),
                                    $simulationId,
                                    intval($value[0]),
                                    intval($value[1]),
                                    strval($value[2]),
                                    strval($value[3]),
                                    intval($value[4]),
                                    strval($value[5]),
                                    $value[6] === 'Oui',
                                    $value[7],
                                    intval($value[8]),
                                    $value[9],
                                    $value[10] === 'Oui',
                                    $value[14],
                                    $value[15],
                                    $value[19],
                                    $value[16],
                                    $value[17],
                                    $value[18],
                                    $value[20],
                                    $value[21],
                                    $value[22],
                                    $value[23],
                                    $value[24],
                                    $value[25],
                                    $value[29],
                                    $value[11],
                                    $value[13],
                                    $value[12],
                                    null,
                                    null,
                                    null,
                                    null,
                                    null,
                                    null,
                                    null,
                                    0,
                                    count($typeEmprunts) === 0 ? null : $typeEmprunts,
                                    json_encode(['part_capital' => $partCapital, 'part_interets' => $partInterets])
                                );

                                $this->save($oldIdentifee);
                                $changedIds[] = $oldDemolition[0]->getId();
                            } else {
                                $identifee = $this->factory->createDemolition(
                                    null,
                                    $simulationId,
                                    intval($value[0]),
                                    intval($value[1]),
                                    strval($value[2]),
                                    strval($value[3]),
                                    intval($value[4]),
                                    strval($value[5]),
                                    $value[6] === 'Oui',
                                    $value[7],
                                    intval($value[8]),
                                    $value[9],
                                    $value[10] === 'Oui',
                                    $value[14],
                                    $value[15],
                                    $value[19],
                                    $value[16],
                                    $value[17],
                                    $value[18],
                                    $value[20],
                                    $value[21],
                                    $value[22],
                                    $value[23],
                                    $value[24],
                                    $value[25],
                                    $value[29],
                                    $value[11],
                                    $value[13],
                                    $value[12],
                                    null,
                                    null,
                                    null,
                                    null,
                                    null,
                                    null,
                                    null,
                                    0,
                                    count($typeEmprunts) === 0 ? null : $typeEmprunts,
                                    json_encode(['part_capital' => $partCapital, 'part_interets' => $partInterets])
                                );

                                $this->save($identifee);
                                $changedIds[] = $identifee->getId();
                            }
                        } catch (Throwable $e) {
                            throw HTTPException::badRequest('Impossible d\'importer des données', $e);
                        }
                    }

                    $demolitions = $this->findBySimulationAndType($simulationId, Demolition::TYPE_IDENTIFIEE);

                    foreach ($demolitions as $demolition) {
                        if (in_array($demolition->getId(), $changedIds)) {
                            continue;
                        }

                        $this->remove($demolition->getId());
                    }
                    break;
            }
        }

        if ($type === Demolition::TYPE_NON_IDENTIFIEE) {
            return 'Démolitions Non identifiées importé';
        }

        return 'Démolitions Identifiées importé';
    }

    public function cloneDemolition(Simulation $newSimulation, Simulation $oldSimulation): void
    {
        $objects = $this->demolitionDao->findBySimulation($oldSimulation);
        foreach ($objects as $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $this->save($newObject);
            foreach ($object->getDemolitionPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setDemolition($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
            foreach ($object->getTypeEmpruntDemolition() as $typeEmpruntObject) {
                $newTypeEmprunt = clone $typeEmpruntObject;
                $newTypeEmprunt->setDemolition($newObject);
                $this->typeEmpruntDemolitionDao->save($newTypeEmprunt);
            }
        }
    }

    public function fusionDemolition(Simulation $newSimulation, Simulation $oldSimulation1, Simulation $oldSimulation2): void
    {
        $identifees1 = $this->demolitionDao->findBySimulationIDAndType($oldSimulation1->getId(), 0);
        $nonidentifees1 = $this->demolitionDao->findBySimulationIDAndType($oldSimulation1->getId(), 1);

        $identifees2 = $this->demolitionDao->findBySimulationIDAndType($oldSimulation2->getId(), 0);
        $nonidentifees2 = $this->demolitionDao->findBySimulationIDAndType($oldSimulation2->getId(), 1);

        foreach ($identifees1 as $key => $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $newObject->setNGroupe($key + 1);
            $this->save($newObject);

            foreach ($object->getDemolitionPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setDemolition($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
            foreach ($object->getTypeEmpruntDemolition() as $typeEmpruntObject) {
                $newTypeEmprunt = clone $typeEmpruntObject;
                $newTypeEmprunt->setDemolition($newObject);
                $this->typeEmpruntDemolitionDao->save($newTypeEmprunt);
            }
        }
        foreach ($nonidentifees1 as $key => $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $newObject->setNGroupe($key + 1);
            $this->save($newObject);

            foreach ($object->getDemolitionPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setDemolition($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
            foreach ($object->getTypeEmpruntDemolition() as $typeEmpruntObject) {
                $newTypeEmprunt = clone $typeEmpruntObject;
                $newTypeEmprunt->setDemolition($newObject);
                $this->typeEmpruntDemolitionDao->save($newTypeEmprunt);
            }
        }

        foreach ($identifees2 as $key => $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $newObject->setNGroupe(count($identifees1) + $key + 1);
            $this->save($newObject);

            foreach ($object->getDemolitionPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setDemolition($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
            foreach ($object->getTypeEmpruntDemolition() as $typeEmpruntObject) {
                $newTypeEmprunt = clone $typeEmpruntObject;
                $newTypeEmprunt->setDemolition($newObject);
                $this->typeEmpruntDemolitionDao->save($newTypeEmprunt);
            }
        }
        foreach ($nonidentifees2 as $key => $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $newObject->setNGroupe(count($nonidentifees1) + $key + 1);
            $this->save($newObject);

            foreach ($object->getDemolitionPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setDemolition($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
            foreach ($object->getTypeEmpruntDemolition() as $typeEmpruntObject) {
                $newTypeEmprunt = clone $typeEmpruntObject;
                $newTypeEmprunt->setDemolition($newObject);
                $this->typeEmpruntDemolitionDao->save($newTypeEmprunt);
            }
        }
    }
}
