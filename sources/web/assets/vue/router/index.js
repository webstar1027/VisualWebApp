import Vue from 'vue';
import VueRouter from 'vue-router';
import store from '../store';
import SecurityAPI from '../api/security';

import Connexion from '../views/Connexion';
import PasswordConfirmation from '../views/PasswordConfirmation';
import ForgotPassword from '../views/ForgotPassword';
import rootSimulation from '../views/Simulation';
import Simulations from '../views/Simulation/Simulations/Simulations';
import TableauDeBord from "../views/Simulation/TableauDeBord";
import IndicesEtTaux from "../views/Simulation/components/IndicesEtTaux";
import Logements from "../views/Simulation/components/logements/Logements";
import Patrimoines from "../views/Simulation/components/logements/components/Patrimoines";
import Demolitions from "../views/Simulation/components/logements/components/demolitions/Demolitions";
import Cessions from "../views/Simulation/components/logements/components/cessions/Cessions";
import Travaux from "../views/Simulation/components/logements/components/travaux/Travaux";
import Operations from "../views/Simulation/components/logements/components/operations/Operations";
import Produits from "../views/Simulation/components/produits/Produits";
import Autres from "../views/Simulation/components/produits/components/Autres";
import Loyers from "../views/Simulation/components/produits/components/Loyers";
import Structure from "../views/Simulation/components/structure/Structure";
import PortageTresorerie from '../views/Simulation/components/structure/components/PortageTresorerie'
import FondDeRoulement from '../views/Simulation/components/structure/components/FondDeRoulement'
import Accession from "../views/Simulation/components/accession/Accession";
import AccessionPsla from "../views/Simulation/components/accession/components/psla/Psla";
import Lotissement from "../views/Simulation/components/accession/components/Lotissement";
import Charges from "../views/Simulation/components/charges/Charges";
import FoyersFraisDeStructure from "../views/Simulation/components/foyers/components/FraisDeStructure";
import AutresCouts from "../views/Simulation/components/accession/components/autresCouts/AutresCouts";
import PartageSetting from "../views/Simulation/components/partageSetting/partageSetting.vue";
import AutresCharges from "../views/Simulation/components/charges/components/AutresCharges";
import Annuites from "../views/Simulation/components/charges/components/Annuites";
import Maintenance from "../views/Simulation/components/charges/components/Maintenance";
import RisquesLocatifs from "../views/Simulation/components/charges/components/RisquesLocatifs";
import CgllsAncols from "../views/Simulation/components/charges/components/CgllsAncols";
import Foyers from "../views/Simulation/components/foyers/Foyers";
import PatrimoinesFoyers from '../views/Simulation/components/foyers/components/Patrimoines';
import DemolitionFoyers from '../views/Simulation/components/foyers/components/Demolitions';
import NouveauxFoyers from '../views/Simulation/components/foyers/components/Nouveaux';
import TravauxFoyers from '../views/Simulation/components/foyers/components/Travaux';
import CessionFoyers from '../views/Simulation/components/foyers/components/Cessions';
import TypesEmprunts from "../views/Simulation/components/TypesEmprunts";
import Hypotheses from "../views/Simulation/components/Hypotheses";
import ProfilsEvolutionLoyers from "../views/Simulation/components/ProfilsEvolutionLoyers";
import AutresActivites from "../views/Simulation/components/autresActivites/AutresActivites";
import Codifications from "../views/Simulation/components/autresActivites/components/Codifications";
import FraisDeStructure from "../views/Simulation/components/autresActivites/components/FraisDeStructure";
import Recapitluatif from "../views/Simulation/components/autresActivites/components/Recapitluatif.vue";
import ProduitsEtCharges from "../views/Simulation/components/autresActivites/components/ProduitsEtCharges.vue";
import ResultatComptable from "../views/Simulation/components/resultatComptable/ResultatComptable";
import Vacance from "../views/Simulation/components/logements/components/Vacance"
import DonneesDuResultat from "../views/Simulation/components/resultatComptable/components/DonneesDuResultat";
import ModelesAmortissement from "../views/Simulation/components/resultatComptable/components/ModelesAmortissement";

import AdministrationWrapper from "../views/Administration/AdministrationWrapper";
import Administration from '../views/Administration';
import Utilisateurs from '../views/Administration/Utilisateurs';
import Utilisateur from '../views/Administration/Utilisateur';
import Entites from '../views/Administration/Entites';
import Entite from '../views/Administration/Entite';
import Ensembles from '../views/Administration/Ensembles';
import Ensemble from '../views/Administration/Ensemble';
import Roles from "../views/Administration/Roles";
import Role from "../views/Administration/Role";
import InvitationEnsemble from '../views/Administration/InvitationEnsemble';
import InvitationEntite from '../views/Administration/InvitationEntite';
import FusionEntites from '../views/Administration/FusionEntites';
import Ccmi from '../views/Simulation/components/accession/components/Ccmi'
import Vefa from '../views/Simulation/components/accession/components/Vefa/Vefa';

Vue.use(VueRouter);

let router = new VueRouter({
    mode: 'history',
    routes: [

        {
            path: '/connexion',
            component: Connexion,
        },
        {
            path: '/deconnexion',
            name: 'logout'
        },
        {
            path: '/forgot-password',
            component: ForgotPassword,
        },
        {
            path: '/confirmation/:token',
            component: PasswordConfirmation,
        },
        {
            path: '/',
            component: rootSimulation,
            meta: { requiresAuth: true },
            children: [
                { path: '', redirect: 'simulations' },
                { path: 'simulations', component: Simulations, name: 'simulations' },
                { path: 'simulation/:id', component: TableauDeBord },

                // Activites
                // logement
                { path: 'simulation/:id/logements', component: Logements },
                { path: 'simulation/:id/logements/patrimoines', component: Patrimoines },
                { path: 'simulation/:id/logements/demolitions', component: Demolitions },
                { path: 'simulation/:id/logements/cessions', component: Cessions },
                { path: 'simulation/:id/logements/travaux', component: Travaux },
                { path: 'simulation/:id/logements/operations', component: Operations },
                // Foyers
                { path: 'simulation/:id/foyers', component: Foyers },
                { path: 'simulation/:id/foyers/patrimoines', component: PatrimoinesFoyers },
                { path: 'simulation/:id/foyers/demolitions', component: DemolitionFoyers},
                { path: 'simulation/:id/foyers/travaux', component: TravauxFoyers },
                { path: 'simulation/:id/foyers/nouveaux', component: NouveauxFoyers },
                { path: 'simulation/:id/foyers/cessions', component: CessionFoyers },
                { path: 'simulation/:id/foyers/frais-de-structure', component: FoyersFraisDeStructure },
                // accession
                { path: 'simulation/:id/accession', component: Accession },

                { path: 'simulation/:id/accession/ccmi', component: Ccmi },
                { path: 'simulation/:id/accession/psla', component: AccessionPsla },
                { path: 'simulation/:id/accession/lotissement', component: Lotissement },
                { path: 'simulation/:id/accession/vefa', component: Vefa },
                { path: 'simulation/:id/accession/autres-couts', component: AutresCouts },
                // autres activites
                { path: 'simulation/:id/autres-activites', component: AutresActivites },
                { path: 'simulation/:id/autres-activites/codifications', component: Codifications },
                { path: 'simulation/:id/autres-activites/frais-de-structure', component: FraisDeStructure },
                { path: 'simulation/:id/autres-activites/produits-et-charges', component: ProduitsEtCharges},
                { path: 'simulation/:id/autres-activites/recapitulatifs', component: Recapitluatif},

                // vacance
                { path: 'simulation/:id/logements/vacance', component: Vacance},

                // Données financières
                // structure
                { path: 'simulation/:id/structure', component: Structure },
                { path: 'simulation/:id/structure/fond-roulement', component: FondDeRoulement },
                { path: 'simulation/:id/structure/portage-tresorerie', component: PortageTresorerie },

                // charges
                { path: 'simulation/:id/charges', component: Charges },
                { path: 'simulation/:id/charges/autres-charges', component: AutresCharges },
                { path: 'simulation/:id/charges/risques-locatifs', component: RisquesLocatifs },
                { path: 'simulation/:id/charges/annuites', component: Annuites },
                { path: 'simulation/:id/charges/maintenance', component: Maintenance },
                { path: 'simulation/:id/charges/cglls-ancols', component: CgllsAncols },
                // produits
                { path: 'simulation/:id/produits', component: Produits},
                { path: 'simulation/:id/produits/autres', component: Autres },
                { path: 'simulation/:id/produits/loyers', component: Loyers },
                // resultat comptable
                { path: 'simulation/:id/resultat-comptable', component: ResultatComptable },
                { path: 'simulation/:id/resultat-comptable/donnees-du-resultat', component: DonneesDuResultat },
                { path: 'simulation/:id/resultat-comptable/modeles-amortissement', component: ModelesAmortissement },

                // Indice et taux
                { path: 'simulation/:id/indices-taux', component: IndicesEtTaux },

                // Paramètres d'activités
                // Hypothese liées aux investissements
                { path: 'simulation/:id/hypotheses', component: Hypotheses },
                // Types d'emprunts
                { path: 'simulation/:id/types-emprunts', component: TypesEmprunts, name:"typeEmprunt" },
                // Profil d'évolution loyers
                { path: 'simulation/:id/profils-evolution-loyers', component: ProfilsEvolutionLoyers, name:"profilEvolutionLoyer" },
                // Partagee Setting
                { path: 'simulation/:id/partage-parametres', component: PartageSetting },
            ],
        },
        {
            path: '/administration',
            component: Administration ,
            meta: { requiresAuth: true, requiresAdmin: true }
        },
        {
            path: '/gestion',
            component: AdministrationWrapper,
            meta: { requiresAuth: true, requiresAdmin: true },
            children: [
                { path: '', redirect: 'utilisateurs' },

                { path: 'utilisateurs', component: Utilisateurs },
                { path: 'utilisateur/:id?', component: Utilisateur},

                { path: 'entites', component: Entites },
                { path: 'entite/:id?', component: Entite},
                { path: 'entite/:id/invitation', component: InvitationEntite },

                { path: 'ensembles', component: Ensembles },
                { path: 'ensemble/:id?', component: Ensemble },
                { path: 'ensemble/:id/invitation', component: InvitationEnsemble },

                { path: 'roles', component: Roles},
                { path: 'role/:id?', component: Role},

                { path: 'fusion-entites', component: FusionEntites}
            ],
        },
    ]
});

router.beforeEach((to, from, next) => {
    if (to.name === 'logout') {
        return SecurityAPI.logout();
    }
    if(to.name === 'simulations' || to.path.includes('administration') || to.path.includes('gestion')) {
        store.dispatch('choixEntite/setDisable', false);
    } else {
        store.dispatch('choixEntite/setDisable', true);
    }
    if (store.getters['security/estAdministrateurCentral']) {
        store.dispatch('choixEntite/setDisable', true);
    }
    if (to.matched.some(record => record.meta.requiresAuth)) {
        // this route requires auth, check if logged in
        // if not, redirect to login page.
        if (store.getters['security/isAuthenticated']) {
            if (to.matched.some(record => record.meta.requiresAdmin)) {
                if (store.getters['security/isReferent'] || store.getters['security/estAdministrateurCentral'] || store.getters['security/estAdministrateurSimulation']) {
                    if (to.path.includes('roles') || to.path.includes('role') || to.path.includes('fusion-entites')) {
                        if (store.getters['security/estAdministrateurCentral'] || store.getters['security/estAdministrateurSimulation']) {
                            next();
                        } else {
                            next({
                                path: '/simulations',
                            });
                        }
                    } else {
                        next();
                    }
                } else {
                    next({
                        path: '/simulations',
                    });
                }
            } else {
                next(); // make sure to always call next()!
            }
        } else {
            next({
                path: '/connexion',
                query: { redirect: to.fullPath }
            });
        }
    } else {
        next(); // make sure to always call next()!
    }
});

export default router;