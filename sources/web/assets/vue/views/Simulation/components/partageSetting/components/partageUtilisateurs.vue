<template>
	<el-row>
		<ApolloQuery
			:query="require('../../../../../graphql/administration/partagers/fetchPartage.gql')"
			:variables="{
				limit: limit,
				offset: offset,
        simulationID: simulationId
      }">
			<template slot-scope="{ result:{ loading, error, data }, isLoading }">
				<div v-if="error">Une erreur est survenue !</div>
				<span> Nombre de partage -
						<strong style="font-size: 24px;"> 
							{{ count(data) }}
						</strong>
				</span>
				<el-table	v-loading="isLoading === 1"
									ref="shareUserTable"
									:data="validData(data)"
									style="width: 100%">
					<el-table-column sortable="custom" type="index" width="100"></el-table-column>
					<el-table-column sortable="custom" prop="utilisateur.nom" label="Nom"></el-table-column>
					<el-table-column sortable="custom" prop="utilisateur.prenom" label="Prenom"></el-table-column>
					<el-table-column sortable="custom" prop="partageType" label="Type de Partage">
						<template slot-scope="scope">
								<div v-if="editedId === scope.row.id">
									<el-select v-model="shareType" placeholder="Choisir le type" @change="changeType(scope.row)">
										<el-option
								      v-for="item in options"
								      :key="item.value"
								      :label="item.label"
								      :value="item.value">
								    </el-option>
									</el-select>
								</div>
								<div v-else>
									<el-tooltip class="item" effect="dark" content="Vous pouvez changer le type de partage" placement="bottom">
										<span> {{ scope.row.partageType }}</span>
									</el-tooltip>
								</div>
						</template>
					</el-table-column>
					<el-table-column sortable="custom" prop="utilisateur.email" label="Email"></el-table-column>
					<el-table-column label="Action">
						<template slot-scope="scope">
							<div class="action-buttons" >
								<el-tooltip class="item" effect="dark" content="modifier" placement="left">
									<span @click="editPartage(scope.row)" class="pr-2">
										<md-icon style="font-size: 20px;">edit</md-icon>
									</span>
								</el-tooltip>
								<el-tooltip class="item" effect="dark" content="supprimer" placement="right">
									<span @click="removeShare(scope.row)">
										<md-icon style="font-size: 20px;">delete</md-icon>
									</span>
								</el-tooltip>
							</div>
						</template>
					</el-table-column>
				</el-table>
					<el-pagination	v-if="isLoading === 0"
													@current-change="handleCurrentChange"
													style="text-align: center"
													layout="prev, pager, next"
													:current-page="currentPage"
													:total="count(data)">
					</el-pagination>
			</template>
		</ApolloQuery>
	</el-row>
</template>

<script>
	export default {
		name: 'PartageUtilisateur',
		data() {
			return {
				simulationId: null,
				currentPage: null,
				offset: 0,
				limit: 6,
				typeEdit: false,
				shareType: null,
				options: [
					{ label: "Lecture Seule", value: "read_only" },
					{ label: "Collaboratif", 	value: "collobative" },
				],
				editedId: null
			}
		},
		created() {
			this.simulationId = this.$route.params.id;
		},
		methods: {
			validData(data) {
				if (!_.isNil(data)) {
					return data.fetchPartage.items;
				} else {
					return [];
				}
			},
			count(data) {
				if (!_.isNil(data)) {
					return data.fetchPartage.count;
				} else {
					return 0;
				}
			},
			handleCurrentChange(val) {
				this.currentPage = val;
				this.offset = this.limit * (val - 1);
			},
			removeShare(row) {
				this.$apollo
						.mutate({
							mutation: require("../../../../../graphql/administration/partagers/deletePartage.gql"),
							variables: {
								id:row.id
							}
						})
						.then(() => {
							this.$router.go(0);
						});
			},
			editPartage(row) {
				this.shareType = null;
				this.editedId = row.id;
			},
			changeType(row) {
				let partageType = null;

				switch(this.shareType) {
					case "read_only":
						partageType = "Lecture Seule";
						break;
					case "collobative":
						partageType = "Collaboratif";
						break;
				}

				this.$apollo.mutate({
					mutation: require("../../../../../graphql/administration/partagers/savePartage.gql"),
					variables: {
						id: row.id,
						simulationID: this.simulationId,
						utilisateurs: [],
						entites: [],
						owner: '',
						partagerType: partageType
					}
				}).then(() => {
					this.$router.go(0);
				});
			}
		}
	}
</script>

<style lang="scss" scoped>
	.el-button.is-round {
    border-radius: 25px;
    padding: 12px 12px;
	}
	.action-buttons {
		display: flex;

		span {
			cursor:pointer;
		}
	}
</style>