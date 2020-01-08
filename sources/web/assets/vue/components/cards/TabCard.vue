<template>
	<div class="m-2 shadow tableau-card">
		<div class="d-flex p-2">
			<h6 class="m-0 font-family-2 font-weight-semibold">{{title ? title : 'title'}}</h6>
			<div class="ml-auto">
				<slot name="title-end"></slot>
			</div>
		</div>
		<div class="p-3 content">
			<slot></slot>
		</div>
	</div>
</template>
<script>
// import { Line } from "vue-chartjs";
export default {
	// extends: Line,
	name: "TabCard",
	props: ["title"],
	data() {
		return {
			chartData: {
				labels: ["x1", "x2", "x3", "x4", "x5"],
				datasets: [
					{
						label: "One",
						backgroundColor: "#000",
						data: ["20", "30", "37", "32", "42"]
					},
					{
						label: "Two",
						backgroundColor: "#eee",
						data: ["20", "30", "37", "32", "42"]
					}
				]
			}
		};
	},
	mounted() {
		// this.renderChart(this.chartData);
	}
};
</script>
