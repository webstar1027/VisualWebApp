<script>
	import { Doughnut } from "vue-chartjs";
	export default {
		extends: Doughnut,
		props: ['labels', 'datasets'],
		mounted() {
			this.renderChart(
				this.data,
				this.options
				//
			);
		},
		data() {
			return {
				data: {
					labels: this.labels,
					datasets: [
						{
							data: this.datasets,
                            backgroundColor: ["#1968f9", "#91aabb", "#0592e6", "#4e89c4"],
						}
					],
				},
				options: {
					responsive: true,
                    maintainAspectRatio: false,
                    cutoutPercentage: 70,
					animation: {
					},
					legend: {
						display: false
					}
				}
			};
		}
	};
</script>