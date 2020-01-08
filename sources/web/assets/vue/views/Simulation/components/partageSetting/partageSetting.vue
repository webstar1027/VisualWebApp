<template>
    <el-card class="box-card">
        <el-checkbox v-model="isPublic" @change="changeLock">Rendre la simulation publique</el-checkbox>
        <el-tabs v-model="activeName">
            <el-tab-pane v-for="(item, i) in tabs"
                         :key="i"
                         type="border-card"
                         :label="item.name"
                         :name="item.value">
                <component :is="item.component"></component>
            </el-tab-pane>
      </el-tabs>
    </el-card>
</template>

<script>
    import PartageUtilisateurs from './components/partageUtilisateurs.vue'
    export default {
        name: 'PartagerSetting',
        components:{
            PartageUtilisateurs
        },
        data() {
            return {
                tabs: [
                    { name:'Partage',   value:'share', component:PartageUtilisateurs},
                    { name:'Tableau de bord', value:'second'},
                    { name:'ThirdTab',  value:'third'},
                ],
                activeName: 'share',
                isPublic: false,
            }
        },
        created() {
            const simulationID = _.isNil(this.$route.params.id) ? null : this.$route.params.id;
            if (_.isNil(simulationID)) {
                return;
            }
            this.simulationID = simulationID;
            this.getSimulation();
        },
        methods: {
            getSimulation() {
                this.$apollo
                    .query({
                        query: require("../../../../graphql/simulations/simulation.gql"),
                        variables: {
                            simulationID: this.simulationID
                        }
                    })
                    .then(response => {
                        if (response.data && response.data.simulation) {
                            this.isPublic = response.data.simulation.verrouillePar === null;
                        }
                    });
            },
            changeLock(value) {
                this.$apollo
                    .mutate({
                        mutation: require("../../../../graphql/simulations/updateSimulationLock.gql"),
                        variables: {
                            simulationId: this.simulationID,
                            locked: !this.isPublic
                        }
                    })
                    .then(response => {
                        this.$message({
                            type: "success",
                            message: this.isPublic ? "La simulation est publique" : "La simulation est privée"
                        });
                    });
            }
        }
    }
</script>