<template>
	<div class="loader" :class="{'is-active':show}">
		<img src="@/assets/logo-white.svg" class="loading-logo" alt srcset>

		<el-row class="d-block">
			<el-progress :percentage="percentage" :show-text="false"></el-progress>
		</el-row>

		<span
			class="d-block text-center font-smaller mt-3 loader-text"
			:class="{'active':active}"
		>Chargement...</span>
	</div>
</template>
<script>
import { setInterval, clearInterval } from "timers";

export default {
	name: "Loader",
	props: ["show"],
	data() {
		return {
			percentage: 0,
			active: false
		};
	},
	methods:{
		hideModal(){},
		showModal(){},

	},
	mounted() {
		this.active = true;
		setInterval(
			function() {
				if (this.percentage != 100) {
					this.percentage++;
				} else {
					clearInterval();
					this.active = false;
				}
			}.bind(this),
			100
		);
	}
};
</script>
