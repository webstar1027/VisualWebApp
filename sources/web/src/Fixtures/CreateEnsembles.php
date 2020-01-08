<?php

declare(strict_types=1);

namespace App\Fixtures;

use App\Dao\EnsembleDao;
use App\Dao\EntiteDao;
use App\Dao\UtilisateurDao;
use App\Model\Ensemble;
use App\Model\Entite;
use App\Model\Utilisateur;
use Cake\Chronos\Chronos;
use RuntimeException;

final class CreateEnsembles implements Fixture
{
    public const NOM_1 = 'Ensemble 1';
    public const DESCRIPTION_1 = 'Description de l\'ensemble n°1';

    public const NOM_2 = 'Ensemble 2';
    public const DESCRIPTION_2 = 'Description de l\'ensemble n°2';

    public const NOM_3 = 'Ensemble 3';
    public const DESCRIPTION_3 = 'Description de l\'ensemble n°3';

    /** @var Utilisateur */
    private $adminCentral;

    /** @var UtilisateurDao */
    private $utilisateurDao;
    /** @var EnsembleDao */
    private $ensembleDao;
    /** @var EntiteDao */
    private $entiteDao;

    public function __construct(UtilisateurDao $utilisateurDao, EnsembleDao $ensembleDao, EntiteDao $entiteDao)
    {
        $this->utilisateurDao = $utilisateurDao;
        $this->ensembleDao = $ensembleDao;
        $this->entiteDao = $entiteDao;
    }

    public function up(): void
    {
        $utilisateur = $this->utilisateurDao->findOneByEmail(CreateUtilisateurs::ADMIN_CENTRAL_EMAIL);
        if (empty($utilisateur)) {
            throw new RuntimeException('The admin user does not exist!');
        } else {
            $this->adminCentral = $utilisateur;
        }

        $this->createEnsemble(self::NOM_1, self::DESCRIPTION_1, CreateEntites::CODE_1, [CreateEntites::CODE_1, CreateEntites::CODE_2, CreateEntites::CODE_3, CreateEntites::CODE_4]);
        $this->createEnsemble(self::NOM_2, self::DESCRIPTION_2, CreateEntites::CODE_3, [CreateEntites::CODE_3, CreateEntites::CODE_4, CreateEntites::CODE_6, CreateEntites::CODE_7]);
        $this->createEnsemble(self::NOM_3, self::DESCRIPTION_3, CreateEntites::CODE_7, [CreateEntites::CODE_2, CreateEntites::CODE_3, CreateEntites::CODE_6]);
    }

    /**
     * @param string[] $codesEntites
     */
    private function createEnsemble(string $nom, string $description, string $codeEntiteReferente, array $codesEntites): void
    {
        $ensemble = new Ensemble(
            $this->adminCentral,
            $nom,
            Chronos::now()
        );

        $ensemble->setDescription($description);

        if ($nom === self::NOM_1) {
            foreach ($codesEntites as $codeEntite) {
                /** @var Entite $entite */
                $entite = $this->entiteDao->findOneByCode($codeEntite);
                $ensemble->addEntiteByEnsemblesEntites($entite);
            }
            /** @var Entite $entiteReferente */
            $entiteReferente = $this->entiteDao->findOneByCode($codeEntiteReferente);
            $ensemble->addEntiteByReferentsEnsembles($entiteReferente);
        } elseif ($nom === self::NOM_2) {
            foreach ($codesEntites as $codeEntite) {
                /** @var Entite $entite */
                $entite = $this->entiteDao->findOneByCode($codeEntite);
                $ensemble->addEntiteByEnsemblesEntites($entite);
            }
            /** @var Entite $entiteReferente */
            $entiteReferente = $this->entiteDao->findOneByCode($codeEntiteReferente);
            $ensemble->addEntiteByReferentsEnsembles($entiteReferente);
        } elseif ($nom === self::NOM_3) {
            foreach ($codesEntites as $codeEntite) {
                /** @var Entite $entite */
                $entite = $this->entiteDao->findOneByCode($codeEntite);
                $ensemble->addEntiteByEnsemblesEntites($entite);
            }
            /** @var Entite $entiteReferente */
            $entiteReferente = $this->entiteDao->findOneByCode($codeEntiteReferente);
            $ensemble->addEntiteByReferentsEnsembles($entiteReferente);
        }

        $this->ensembleDao->save($ensemble);
    }

    public function getName(): string
    {
        return 'Create Ensembles';
    }
}
