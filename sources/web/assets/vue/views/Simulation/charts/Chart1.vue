
<script>
    import { Bar, mixins } from "vue-chartjs";
    const { reactiveProp } = mixins;

    export default {
        extends: Bar,
        mixins: [reactiveProp],
        mounted() {
            this.renderChart(
                this.chartData,
                this.options
            );
        },
        data() {
            return {
                options: {
                    scales: {
                        yAxes: [
                            {
                                ticks: {
                                },
                                gridLines: {
                                    drawBorder: false,
                                    color: "rgba(0,0,0,.05)"
                                }
                            }
                        ],
                        xAxes: [
                            {
                                ticks: {
                                    display: false
                                },
                                gridLines: {
                                    display: false,
                                    drawBorder: false
                                }
                            }
                        ]
                    },
                    legend: {
                        display: false
                    },
                    responsive: true,
                    maintainAspectRatio: false
                }
            };
        }
    };
</script>