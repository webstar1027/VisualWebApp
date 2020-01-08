<template>
	<el-table class="admin-table" :data="data" style="width: 100%">
		<el-table-column type="selection"></el-table-column>

		<el-table-column
			v-for="(f,i) in fields"
			:prop="f.prop"
			:label="f.label"
			:sortable="f.sortable"
			:fixed="f.fixed"
			:key="i"
		></el-table-column>

		<el-table-column prop="information" label="Informations">
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
		</el-table-column>

		<el-table-column prop="actions">
			<template>
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
