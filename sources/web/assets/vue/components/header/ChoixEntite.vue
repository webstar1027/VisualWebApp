<template>
	<div class="col">
		<el-select
			v-model="selectedEntite"
			placeholder="choose entite"
			@change="changeEntite"
		>
			<el-option
				v-for="item in entites"
				v-if="item.id != null"
				:key="item.id"
				:label="item.nom"
				:value="item.id"
			></el-option>
		</el-select>
	</div>
</template>
<script>
import store from "../../store"

const DEFAULT_SORT_COLUMN = "nom";
const DEFAULT_SORT_ORDER = "ASC";

export default {
	name: "ChoixEntite",
	store,
	data() {
		return {
			selectedEntite: null,
			entites: []
		}
	},
	created() {
		this.loadEntites();
	},
	methods: {
		loadEntites() {
			const utilisateurEmail = this.$store.getters["security/email"];
			const estAdministrateurCentral = this.$store.getters["security/estAdministrateurCentral"];
			this.$apollo
				.query({
					query: require("../../graphql/administration/entites/entites.gql"),
					variables: {
						limit: 100,
						offset: 0,
						utilisateur: estAdministrateurCentral ? null : utilisateurEmail,
						sortColumn: DEFAULT_SORT_COLUMN,
						sortOrder: DEFAULT_SORT_ORDER
					}
				})
				.then(response => {
					if (response.data && response.data.entites) {
						this.entites = response.data.entites.items;
						if (!estAdministrateurCentral) {
							const entite = this.$store.getters["choixEntite/getUserSelectedId"];
							if (entite != null) {
								this.selectedEntite = entite.id;
							} else {
								this.selectedEntite = this.entites[0].id;
								this.$store.dispatch('choixEntite/setEntiteId', this.entites[0]);
							}
						}
					}
				});
		},
		changeEntite(entiteID) {
			const entite = this.entites.find(item => item.id == entiteID);
			this.$store.dispatch('choixEntite/setEntiteId', entite)
		}
	}
};
</script>
