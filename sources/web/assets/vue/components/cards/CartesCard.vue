<template>
	<div class="carte-box bg-white">
		<div class="carte-box-header px-3">
			<h6 class="font-weight-normal m-0 text-white text-uppercase">{{title}}</h6>
		</div>
		<div class="carte-box-content pb-3 px-2">
			<div class="carte-box-item py-3">
				<el-button class="cb-item font-smaller">
					<span class="text-gray cb-item__label">Logements familiaux</span>
					<span class="cb-item__value">4563</span>
				</el-button>
				<el-button class="cb-item font-smaller">
					<span class="text-gray cb-item__label">Logements familiaux</span>
					<span class="cb-item__value">4563</span>
				</el-button>
				<el-button class="cb-item font-smaller">
					<span class="text-gray cb-item__label">Logements familiaux</span>
					<span class="cb-item__value">4563</span>
				</el-button>
			</div>
		</div>
	</div>
</template>
<script>
export default {
	name: "CartesCard",
	props: ["title"],

};
</script>
