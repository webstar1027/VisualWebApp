<template>
	<el-dialog
		class="modale-dialog admin-modal"
		title="Partage d'une simulation"
		:visible.sync="visible"
		:close-on-click-modal="false"
		width="75%"
		center
	>
		<div>
			<p class="font-weight-medium">
				<span class="font-small">Vous allez partager</span>
				<span class="font-large">5</span>
				<span class="font-small">simulations</span>
			</p>
		</div>

		<el-row class="d-flex justify-content-center">
			<div class="col-sm-12 col-md-4">
				<span class="form-label">Type de partage</span>
				<el-select class="border" v-model="values.one" placeholder="Type de partage">
					<el-option v-for="(item,i) in options" :key="i" :label="item.label" :value="item.value"></el-option>
				</el-select>
			</div>
		</el-row>
		<el-row class="mt-5">
			<div class="col-sm-12 col-md-4">
				<span class="form-label">Rechercher par entite</span>
				<el-select class="outline" v-model="values.two" placeholder="Selectionnez une entite">
					<el-option v-for="(item,i) in options" :key="i" :label="item.label" :value="item.value"></el-option>
				</el-select>
			</div>
			<div class="col-sm-12 col-md-4">
				<span class="form-label">Rechercher par ensemble</span>
				<el-select class="outline" v-model="values.three" placeholder="Selectionnez une entite">
					<el-option v-for="(item,i) in options" :key="i" :label="item.label" :value="item.value"></el-option>
				</el-select>
			</div>
			<div class="col-sm-12 col-md-4">
				<span class="form-label">Rechercher par utilisateurs</span>
				<el-autocomplete
					class="outline"
					:class="{'active': queryActive}"
					popper-class="modal-autocomplete"
					v-model="selectedQuery"
					:fetch-suggestions="modalQuerySearch"
					placeholder="Rechercher par utilisateurs"
					@select="handleSelect"
				>
					<i class="el-icon-close el-input__icon" slot="suffix"></i>
				</el-autocomplete>
			</div>
		</el-row>
		<el-row class="mt-4">
			<p class="font-weight-medium">
				<span class="font-small">Vous allez partager</span>
				<span class="font-large">5</span>
				<span class="font-small">simulations</span>
			</p>
		</el-row>
		<el-row class="mt-3">
			<el-table :data="table.data" style="width: 100%">
				<el-table-column type="selection" width="55"></el-table-column>
				<el-table-column prop="nom" label="Nom" sortable></el-table-column>
				<el-table-column prop="email" label="Email" sortable></el-table-column>
				<el-table-column prop="title" label="Title" sortable></el-table-column>
			</el-table>
		</el-row>
		<el-row class="mt-5 dialog-footer">
			<div class="d-flex justify-content-center">
				<el-button class="bg-light-blue">Annuler</el-button>
				<el-button type="primary">Partage</el-button>
			</div>
		</el-row>
	</el-dialog>
</template>
<script>
export default {
	name: "Modal",
	props: ["visible"],
	data() {
		return {
			table: {
				data: [
					{
						nom: "John Doe",
						email: "johndoe@gmail.com",
						title: "Entite Test"
					},
					{
						nom: "John Doe",
						email: "johndoe@gmail.com",
						title: "Entite Test"
					},
					{
						nom: "John Doe",
						email: "johndoe@gmail.com",
						title: "Entite Test"
					}
				]
			},
			options: [
				{ label: "Option One", value: "opt-1" },
				{ label: "Option Two", value: "opt-2" },
				{ label: "Option Three", value: "opt-3" }
			],
			values: {
				one: null
			},
			selectedQuery: null,
			queryActive: false
		};
	},
	methods: {
		handleSelect() {},
		handleClick() {},
		modalQuerySearch() {}
	}
};
</script>
