<script>
    import { Line, mixins } from "vue-chartjs";
    const { reactiveProp } = mixins;

    export default {
        extends: Line,
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
                        xAxes: [
                            {
                                ticks: {
                                    min: 0,
                                    stepSize: 1,
                                    display: false,
                                },
                                gridLines:{
                                    drawBorder: false,
                                    color: 'rgba(0,0,250,.1)'
                                }

                            }
                        ],
                        yAxes: [
                            {
                                ticks: {
                                    display: false,
                                },
                                gridLines: {
                                    display: false,
                                    drawBorder: false,
                                }
                            }
                        ]
                    },
                    legend:{
                        display: false,
                    },
                    responsive: true,
                    maintainAspectRatio: false,
                }
            };
        }
    };
</script>