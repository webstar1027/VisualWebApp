<template>
    <div>
        <el-row :gutter="20" type="flex" justify="space-between">
            <el-col :lg="8" :xl="6">
                <p class="admin-content-title">Projection {{beginProjection}} - {{endProjection}}</p>
            </el-col>
            <el-col v-if="sliderVisible" :lg="11" :xl="13">
                <el-slider
                    v-model="range"
                    range
                    show-stops
                    :format-tooltip="getSliderLabel"
                    @change="changeRange"
                    :max="51">
                </el-slider>
            </el-col>
            <el-col v-if="!sliderVisible" :md="5" class="text-right">
                <el-button type="primary" @click="sliderVisible=true">Gérer la projection</el-button>
            </el-col>
            <el-col v-if="sliderVisible" :md="5" class="text-right">
                <el-button type="warning" @click="sliderVisible=false">Cacher la barre de la projection</el-button>
            </el-col>
        </el-row>
        <el-row v-loading="!loaded" class="d-flex justify-content-center">
            <el-col :md="22" class="tableau-de-bord">
                <draggable
                    :list="draggableList1"
                    class="d-flex"
                    v-bind="dragOptions"
                    group="line"
                    @start="isDragging = true"
                    @end="isDragging = false"
                    @update="changePosition(0)">
                    <transition-group>
                        <el-col :md="6" v-for="e in draggableList1" :key="e.id">
                            <TabCard :title="e.title">
                                <div class="d-flex mb-3">
                                    <h4 class="m-0 text-dark">{{calculChart(e.title).first}}</h4>
                                    <div class="ml-auto text-right">
                                        <h4 class="m-0 text-dark">{{calculChart(e.title).last}}</h4>
                                        <h5 class="m-0 text-success">{{calculChart(e.title).growth}}%</h5>
                                    </div>
                                </div>
                                <Chart2 v-if="e.title=='Patrimoine'" :chart-data="patrimoineData" :styles="styles.chart2"></Chart2>
                                <Chart2 v-else-if="e.title=='Annuites'" :chart-data="annuitesData" :styles="styles.chart2"></Chart2>
                                <Chart2 v-else-if="e.title=='Maintenance travaux'" :chart-data="maintenanceData" :styles="styles.chart2"></Chart2>
                                <Chart2 v-else :chart-data="fonctionnementData" :styles="styles.chart2"></Chart2>
                            </TabCard>
                        </el-col>
                    </transition-group>
                </draggable>

                <el-row>
                    <el-col :md="12">
                        <draggable
                            :list="draggableList2"
                            class="d-flex"
                            v-bind="dragOptions"
                            group="bar"
                            draggable=".drag-item"
                            @change="update">
                            <transition-group>
                                <el-row v-for="e in draggableList2" :key="e.id" :class="{'drag-item': e.title !== 'doughnut'}">
                                    <TabCard v-if="e.title=='Autofinancement net'" :title="e.title">
                                        <Chart1 :chart-data="autofinancementData" :styles="styles.chart1"></Chart1>
                                    </TabCard>
                                    <TabCard v-else-if="e.title=='Potentiel financier'" :title="e.title">
                                        <Chart1 :chart-data="financierData" :styles="styles.chart1"></Chart1>
                                    </TabCard>
                                    <TabCard v-else-if="e.title=='Fond de roulement Long Terme'" :title="e.title">
                                        <Chart1 :chart-data="roulementData" :styles="styles.chart1"></Chart1>
                                    </TabCard>
                                    <draggable
                                        v-else-if="e.title=='doughnut'"
                                        :list="draggableList4"
                                        class="d-flex"
                                        v-bind="dragOptions"
                                        group="doughnut"
                                        @update="changePosition(2)">
                                        <transition-group>
                                            <el-col :md="12" v-for="item in draggableList4" :key="item.id">
                                                <TabCard :title="item.title">
                                                    <Chart3 v-if="item.title=='Nature des investissements' && loaded" :labels="investissementsData.labels" :datasets="investissementsData.data" :styles="styles.chart1"></Chart3>
                                                    <Chart3 v-if="item.title=='Plan de financement' && loaded" :labels="financementData.labels" :datasets="financementData.data" :styles="styles.chart1"></Chart3>
                                                    <div class="text-center doughnut-text font-family-2">
                                                        <h6 class="font-weight-bold ">2 842 601</h6>
                                                        <small class="d-block text-muted font-weight-light font-smallest">
                                                        {{item.title === 'Nature des investissements' ? 'Investissement sur la période' : ''}}
                                                        </small>
                                                    </div>
                                                </TabCard>
                                            </el-col>
                                        </transition-group>
                                    </draggable>
                                </el-row>
                            </transition-group>
                        </draggable>
                    </el-col>
                    <el-col :md="12">
                        <draggable
                            :list="draggableList3"
                            class="d-flex"
                            v-bind="dragOptions"
                            group="bar"
                            @change="update">
                            <transition-group>
                                <div v-for="(e, index) in draggableList3" :key="e.id">
                                    <TabCard :title="e.title" :class="{'mt-3': index === 1}">
                                        <Chart1 v-if="e.title=='Autofinancement net'" :chart-data="autofinancementData" :styles="styles.chart1"></Chart1>
                                        <Chart1 v-else-if="e.title=='Potentiel financier'" :chart-data="financierData" :styles="styles.chart1"></Chart1>
                                        <Chart1 v-else :chart-data="roulementData" :styles="styles.chart1"></Chart1>
                                    </TabCard>
                                </div>
                            </transition-group>
                        </draggable>
                    </el-col>
                </el-row>
            </el-col>
        </el-row>
    </div>
</template>
<script>
    import draggable from 'vuedraggable'
    import TabCard from "../../components/cards/TabCard";
    import Chart1 from "./charts/Chart1";
    import Chart2 from "./charts/Chart2";
    import Chart3 from "./charts/Chart3";

    export default {
        name: "TableauDeBord",
        components: {
            draggable,
            TabCard,
            Chart1,
            Chart2,
            Chart3,
        },
        data() {
            return {
                draggableList1: [
                    {id: 1, title: 'Patrimoine'},
                    {id: 2, title: 'Annuites'},
                    {id: 3, title: 'Maintenance travaux'},
                    {id: 4, title: 'Coût de fonctionnement locatif'},
                ],
                draggableList2: [
                    {id: 1, title: 'Autofinancement net'},
                    {id: 2, title: 'doughnut'},
                ],
                draggableList3: [
                    {id: 3, title: 'Potentiel financier'},
                    {id: 4, title: 'Fond de roulement Long Terme'},
                ],
                draggableList4: [
                    {id: 1, title: 'Nature des investissements'},
                    {id: 2, title: 'Plan de financement'},
                ],
                simulationID: null,
                anneeDeReference: 0,
                sliderVisible: false,
                range: [0, 51],
                styles: {
                    chart1: {
                        height: "200px",
                        position: "relative"
                    },
                    chart2: {
                        height: "120px",
                        position: "relative"
                    }
                },
                defaultLineChartData: {
                    labels: [],
                    datasets: [
                        {
                            label: "",
                            borderColor: "#4990EF",
                            pointBackgroundColor: "#4990EF",
                            pointBorderColor: "white",
                            pointBorderWidth: "1",
                            borderWidth: 2,
                            backgroundColor: "transparent",
                            data: []
                        },
                    ]
                },
                defaultBarChartData: {
                    labels: [],
                    datasets: [
                        {
                            label: "",
                            borderColor: "#4990EF",
                            pointBackgroundColor: "#4990EF",
                            pointBorderColor: "white",
                            pointBorderWidth: 7,
                            pointRadius: 7,
                            borderWidth: 2,
                            backgroundColor: "transparent",
                            data: []
                        },
                    ]
                },
                beginProjection: 0,
                endProjection: 0,
                loaded: false,
                labels: [],
                patrimoineData: {},
                annuitesData: {},
                maintenanceData: {},
                fonctionnementData: {},
                autofinancementData: {},
                financierData: {},
                roulementData: {},
                investissementsData: {},
                financementData: {},
                patrimoine: [],
                annuites: [],
                maintenance: [],
                fonctionnement: [],
                autofinancement: [],
                financier: [],
                roulement: [],
                investissements: {},
                financement: {}
            };
        },
        created() {
            const simulationID = _.isNil(this.$route.params.id) ? null : this.$route.params.id;
            if (_.isNil(simulationID)) {
                return;
            }
            this.simulationID = simulationID;

            this.getSimulation();
            this.getTableauDeBord();
        },
        computed: {
            dragOptions() {
                return {
                    animation: 500,
                    group: "description",
                    disabled: false,
                    ghostClass: "ghost"
                };
            }
        },
        methods: {
            getSimulation() {
                this.$apollo.query({
                    query: require('../../graphql/simulations/simulation.gql'),
                    variables: {
                        simulationID: this.simulationID
                    }
                }).then((res) => {
                    if (res.data && res.data.simulation) {
                        this.anneeDeReference = res.data.simulation.anneeDeReference;
                    }
                });
            },
            getTableauDeBord() {
                this.$apollo.query({
                    query: require('../../graphql/simulations/tableauDeBord/tableauDeBord.gql'),
                    variables: {
                        simulationId: this.simulationID
                    }
                }).then((res) => {
                    if (res.data && res.data.tableauDeBord) {
                        this.uuid = res.data.tableauDeBord.uuid;
                        this.beginProjection = res.data.tableauDeBord.beginProjection;
                        this.endProjection = res.data.tableauDeBord.endProjection;

                        this.labels = this.getLabels(this.beginProjection, this.endProjection);

                        res.data.tableauDeBord.tableauDeBordPeriodique.items.forEach(item => {
                            const position = item.tableauDeBordParam.position;
                            const composant = item.tableauDeBordParam.composant;
                            switch(position) {
                                case 1: this.draggableList1[0].title = composant; break;
                                case 2: this.draggableList1[1].title = composant; break;
                                case 3: this.draggableList1[2].title = composant; break;
                                case 4: this.draggableList1[3].title = composant; break;
                                case 5: this.draggableList2[0].title = composant; break;
                                case 6: this.draggableList3[0].title = composant; break;
                                case 7: this.draggableList3[1].title = composant; break;
                                case 8: this.draggableList4[0].title = composant; break;
                                case 9: this.draggableList4[1].title = composant; break;
                                default: break;
                            }

                            switch(composant) {
                                case 'Patrimoine': this.patrimoine.push(item.value); break;
                                case 'Annuites': this.annuites.push(item.value); break;
                                case 'Maintenance travaux': this.maintenance.push(item.value); break;
                                case 'Coût de fonctionnement locatif': this.fonctionnement.push(item.value); break;
                                case 'Autofinancement net': this.autofinancement.push(item.value); break;
                                case 'Potentiel financier': this.financier.push(item.value); break;
                                case 'Nature des investissements': this.investissements[item.iteration] = item.value; break;
                                case 'Plan de financement': this.financement[item.iteration] = item.value; break;
                                case 'Fond de roulement Long Terme': this.roulement.push(item.value); break;
                                default: break;
                            }
                        });

                        // Set range
                        this.range = [parseInt(this.beginProjection) - parseInt(this.anneeDeReference), parseInt(this.endProjection) - parseInt(this.anneeDeReference)];
                        this.getChartData();
                        this.investissementsData = this.getDoughnutChartData(this.investissements);
                        this.financementData = this.getDoughnutChartData(this.financement);

                        this.loaded = true;
                    }
                });
            },
            getLabels(beginProjection, endProjection) {
                let labels = [];
                for (var i = 0; i <= (endProjection - beginProjection); i++) {
                    labels.push((beginProjection + i).toString());
                }
                return labels;
            },
            getPeriodique(data) {
                let periodiques = [];
                data.forEach(periodique => {
                    periodiques[periodique.iteration - 1] = parseFloat(periodique.value);
                });
                return periodiques;
            },
            getSliderLabel(value) {
                return parseInt(this.anneeDeReference) + value;
            },
            getChartData() {
                this.beginProjection = parseInt(this.anneeDeReference) + this.range[0];
                this.endProjection = parseInt(this.anneeDeReference) + this.range[1];
                this.labels = this.getLabels(this.beginProjection, this.endProjection);

                this.patrimoineData = this.getLineChartData("Evolution du patrimoine", this.patrimoine);
                this.annuitesData = this.getLineChartData("Annuité en % des loyers", this.annuites);
                this.maintenanceData = this.getLineChartData("Maintenance", this.maintenance);
                this.fonctionnementData = this.getLineChartData("Coût de fonctionnement locatif", this.fonctionnement);
                this.autofinancementData = this.getBarChartData("Composant", this.autofinancement);
                this.financierData = this.getBarChartData("Composant", this.financier);
                this.roulementData = this.getBarChartData("Composant", this.roulement);
            },
            changeRange() {
                this.getChartData();
                this.saveData();
            },
            saveData(composant='', position=0) {
                this.$apollo.mutate({
                        mutation: require('../../graphql/simulations/tableauDeBord/saveTableauDeBord.gql'),
                        variables: {
                            tableauDeBord: {
                                simulationId: this.simulationID,
                                uuid: this.uuid,
                                beginProjection: this.beginProjection,
                                endProjection: this.endProjection,
                                composant: composant,
                                position: position
                            }
                        }
                    });
            },
            getLineChartData(label, data) {
                let defaultData = {...this.defaultLineChartData};
                defaultData.labels = this.labels;
                defaultData.datasets[0].label = label;
                defaultData.datasets[0].data = data.slice(this.range[0], this.range[1] + 1);
                return defaultData;
            },
            getBarChartData(label, data) {
                let defaultData = {...this.defaultBarChartData};
                defaultData.labels = this.labels;
                defaultData.datasets[0].label = label;
                defaultData.datasets[0].data = data.slice(this.range[0], this.range[1] + 1);
                return defaultData;
            },
            getDoughnutChartData(data) {
                return {
                    labels: Object.keys(data),
                    data: Object.values(data)
                };
            },
            calculChart(type) {
                let data;
                switch(type) {
                    case 'Patrimoine': data = this.patrimoine; break;
                    case 'Annuites': data = this.annuites; break;
                    case 'Maintenance travaux': data = this.maintenance; break;
                    case 'Coût de fonctionnement locatif': data = this.fonctionnement; break;
                    default: break;
                }
                const rangedData = data.slice(this.range[0], this.range[1] + 1);
                const first = rangedData[0];
                const last = rangedData[rangedData.length -1];
                let growth = 0;
                if (first !== 0) {
                    growth = ((last > first) ? '+' : '') + Math.round((last - first) / first * 100);
                } else {
                    growth = 0;
                }

                return {
                    first,
                    last,
                    growth
                };
            },
            update(evt) {
                const draggableList2 = [...this.draggableList2];
                const draggableList3 = [...this.draggableList3];
                let totalList = draggableList2.concat(draggableList3);
                if (evt.hasOwnProperty('added') && draggableList2.length === 3) {
                    const element = evt.added.element;
                    this.draggableList2 = [element, {id: 2, title: 'doughnut'}];
                    this.draggableList3 = totalList.filter(item => !(item.title === 'doughnut' || item.title === element.title));
                }
                if (evt.hasOwnProperty('added') && draggableList2.length === 1) {
                    if (evt.added.newIndex === 0 || evt.added.newIndex === 2) {
                        this.draggableList2 = [draggableList3[1], {id: 2, title: 'doughnut'}];
                    } else {
                        this.draggableList2 = [draggableList3[0], {id: 2, title: 'doughnut'}];
                    }
                    this.draggableList3 = totalList.filter(item => {
                        let isExist = false;
                        this.draggableList2.forEach(e => {
                            if (e.title === item.title) {
                                isExist = true;
                            }
                        })
                        return !isExist;
                    });
                }

                this.changePosition(1);
            },
            changePosition(type) {
                switch(type) {
                    case 0:
                        this.draggableList1.forEach((item, index) => {
                            this.saveData(item.title, index + 1);
                        }); break;
                    case 1:
                        this.saveData(this.draggableList2[0].title, 5);
                        this.draggableList3.forEach((item, index) => {
                            this.saveData(item.title, index + 6);
                        }); break;
                    case 2:
                        this.draggableList4.forEach((item, index) => {
                            this.saveData(item.title, index + 8);
                        }); break;
                    default: break;
                }
            }
        },
    };
</script>

<style>
    .tableau-de-bord .d-flex > span {
        width: 100%;
    }
    .ghost {
        opacity: 0.5;
        background: #c8ebfb;
    }
</style>
