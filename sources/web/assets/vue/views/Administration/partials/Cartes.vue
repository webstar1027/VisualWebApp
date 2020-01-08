<template>
    <div class="h-100 d-flex align-items-center">
        <div class="w-100">
            <el-row class="d-flex carte-row justify-content-center carte-boxes">
                <div class="carte-box bg-white" @click="navigateToMenu('utilisateurs')">
                    <div class="carte-box-header px-3">
                        <h6 class="font-weight-normal m-0 text-white text-uppercase">Liste des utilisateurs</h6>
                    </div>
                    <div class="carte-box-content pb-3 px-2">
                        <md-icon class>people</md-icon>
                    </div>
                </div>
                <div class="carte-box bg-white" @click="navigateToMenu('entites')">
                    <div class="carte-box-header px-3">
                        <h6 class="font-weight-normal m-0 text-white text-uppercase">Liste des entités</h6>
                    </div>
                    <div class="carte-box-content pb-3 px-2">
                        <md-icon class>fiber_manual_record</md-icon>
                    </div>
                </div>
                <div class="carte-box bg-white" @click="navigateToMenu('ensembles')">
                    <div class="carte-box-header px-3">
                        <h6 class="font-weight-normal m-0 text-white text-uppercase">Liste des ensembles</h6>
                    </div>
                    <div class="carte-box-content pb-3 px-2">
                        <md-icon class>fiber_smart_record</md-icon>
                    </div>
                </div>
            </el-row>
            <el-row v-if="isSuperAdmin" class="d-flex carte-row justify-content-center carte-boxes">
                <div class="carte-box bg-white" @click="navigateToMenu('roles')">
                    <div class="carte-box-header px-3">
                        <h6 class="font-weight-normal m-0 text-white text-uppercase">Liste des rôles</h6>
                    </div>
                    <div class="carte-box-content pb-3 px-2">
                        <md-icon class>verified_user</md-icon>
                    </div>
                </div>
                <div class="carte-box bg-white" @click="navigateToMenu('fusion-entites')">
                    <div class="carte-box-header px-3">
                        <h6 class="font-weight-normal m-0 text-white text-uppercase">Fusion d’entités</h6>
                    </div>
                    <div class="carte-box-content pb-3 px-2">
                        <md-icon class>group_work</md-icon>
                    </div>
                </div>
            </el-row>
        </div>
    </div>
</template>
<script>
export default {
    name: "Cartes",
    computed: {
            isSuperAdmin() {
                return this.$store.getters["security/estAdministrateurCentral"];
            }
        },
    methods:{
        navigateToMenu(rubrique){
            this.$router.push('/gestion/' + rubrique);
        }
    }
};
</script>
