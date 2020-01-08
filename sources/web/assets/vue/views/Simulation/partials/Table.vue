<template>
	<el-table :data="data" style="width: 100%">
		<el-table-column type="selection"></el-table-column>

		<el-table-column
			v-for="(f,i) in fields"
			:prop="f.prop"
			:label="f.label"
			:sortable="f.sortable"
			:fixed="f.fixed"
			:key="i"
		></el-table-column>

		<!-- <el-table-column prop="code" label="Code" sortable fixed></el-table-column>
		<el-table-column prop="simulation_mere" label="Simulation mere" sortable></el-table-column>
		<el-table-column prop="nom_simulation" label="Nom simulation" sortable></el-table-column>
		<el-table-column prop="entite" label="Entite" sortable></el-table-column>
		<el-table-column prop="anee_de_ref" label="Anee de ref" sortable></el-table-column>
		<el-table-column prop="date_de_modification" label="Date de modification" sortable></el-table-column>
		<el-table-column prop="modifie_par" label="Modifie par" sortable></el-table-column>-->

		<el-table-column prop="information" label="Informations" fixed="right">
			<template slot-scope="scope">
				<div class="d-flex btn-icons">
					<el-button round class="p-0 text-black">
						<md-icon>lock</md-icon>
					</el-button>
					<el-button round class="p-0 text-black">
						<md-icon>share</md-icon>
					</el-button>
					<el-button round class="p-0">
						<md-icon>merge_type</md-icon>
					</el-button>
				</div>
			</template>
		</el-table-column>

		<el-table-column prop="actions" fixed="right">
			<template slot-scope="scope">
				<div class="d-flex btn-icons-bg">
					<el-button class="round bg-black text-white has-notif" round>
						<md-icon>message</md-icon>
					</el-button>
					<el-button class="bg-primary text-white" round>
						<md-icon>library_books</md-icon>
					</el-button>
					<el-button class="bg-dark text-white" round>
						<md-icon>delete</md-icon>
					</el-button>
				</div>
			</template>
		</el-table-column>
	</el-table>
</template>
<script>
export default {
	name: "Table",
	props: ["fields", "data"],
	data() {
		return {};
	}
};
</script>
