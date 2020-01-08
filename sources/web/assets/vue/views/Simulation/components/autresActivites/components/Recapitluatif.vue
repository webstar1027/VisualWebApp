<template>
	<div class="recapitluatif">
		<el-card>
			<el-row>
				<span> Récapitulatif des activités </span>
			</el-row>
			<el-row class="mt-3" v-loading="loading">
				<el-table	
						:data="recapList"
						style="width: 100%">
						<el-table-column sortable fixed column-key="activite" prop="libelle" width="170" label="Libellé"></el-table-column>
						<el-table-column v-for="column in recapColumns"
							sortable
							align="center"
							:key="column.prop"
							:prop="column.prop"
							:label="column.label">
						</el-table-column>
				</el-table>
			</el-row>
		</el-card>
	</div>
</template>

<script>
	export default {
		name: 'Recapitluatif',
		data() {
			return {
				recapColumns: [],
				simulationId: null,
				recapList: [],
				produitCharges: [],
				anneeDeReference:null,
				loading: false
			}
		},
		created() {
		 let simulationId = _.isNil(this.$route.params.id) ? null : this.$route.params.id;

			if (_.isNil(simulationId)) {
					return;
			}

			this.simulationId = simulationId;
			this.loading = true;

			this.getAnneeDeReference();
			this.getProduitsCharges();
		},
		methods: {			
			getProduitsCharges() {
				this.$apollo.query({
					query: require('../../../../../graphql/simulations/produit-charge/produitCharges.gql'),
					variables: {
						simulationId: this.simulationId
					}
				}).then((res) => {
					if (res.data.produitCharges.items.length > 0) {
						const produitCharges = res.data.produitCharges.items.map(item => {
							let periodiques = [];

							item.produitsChargesPeriodique.items.forEach(periodique => {
									periodiques[periodique.iteration - 1] = periodique.value;
							});

							return {
								id: item.id,
								libelle: item.libelle,
								indexation: item.indexation,
								type: item.type,
								tauxDevolution: item.tauxDevolution,
								activite: item.codification.activite,
								produitsChargesPeriodique: periodiques
							}
						});

						this.produitCharges = produitCharges;
						// Calculate the recap list.
						this.getRecapList(produitCharges);
					} else {
						this.loading = false;
					}
				});
			},
			getRecapList(data) {
				data.forEach(group => {
					let recapList = [];

					group.produitsChargesPeriodique.forEach((item, index) => {
						if (!recapList[index]) {
							recapList[index] = 0;
						}

						recapList[index] += item;
					});

					let itemData = {
						libelle: group.libelle,
						recapList
					}
					this.recapList.push(itemData);
				});

				// Get total sum.
				let recapList = [];
				data.forEach(group => {
					group.produitsChargesPeriodique.forEach((item, index) => {
						if (!recapList[index]) {
							recapList[index] = 0;
						}

						recapList[index] += item;
					});
				});

				let totalItem = {
					libelle: 'total',
					recapList
				}

				this.recapList.push(totalItem);
				this.loading = false;
			},
			getAnneeDeReference() {
				this.$apollo.query({
					query: require('../../../../../graphql/simulations/simulation.gql'),
					variables: {
							simulationID: this.simulationId
					}
				}).then((res) => {
					if (res.data && res.data.simulation) {
						this.anneeDeReference = res.data.simulation.anneeDeReference;
						this.setTableColumns();
					}
				});
			},
			setTableColumns() {
				this.recapColumns = [];

				for (var i = 0; i < 50; i++) {
					this.recapColumns.push({
						label: (parseInt(this.anneeDeReference) + i).toString(),
						prop: `recapList[${i}]`
					});
				}
			},
		}
	}
</script>