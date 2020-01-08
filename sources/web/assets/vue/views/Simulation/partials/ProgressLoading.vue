<template>
    <div v-if="visible" class="progress-loading">
        <el-progress :percentage="percentage"></el-progress>
    </div>
</template>

<script>
export default {
    name: "ProgressLoading",
    props: ["visible", 'finish'],
    data() {
        return {
            step: 1,
            interval: 1000,
            percentage: 0,
        };
    },
    created() {
        this.init();
    },
    mounted() {
        this.init();
    },
    methods: {
        init() {
            this.percentage = 0;
        },
        increase() {
            setTimeout(() => {
                if (this.percentage < 100) {
                    this.percentage += this.step;
                    if (this.percentage < 95) {
                        this.increase();
                    }
                }
            }, 333);
        }
    },
    watch: {
        finish(value) {
            if (value) {
                this.percentage = 100;
            }
        },
        visible(value) {
            if (value) {
                this.increase();
            } else {
                this.init();
            }
        }
    }
};
</script>

<style>
    .progress-loading {
        width: 100%;
        height: 100%;
        opacity: 0.95;
        background: #ffffff;
        z-index: 999999;
        top: 0;
        position: absolute;
        left: 0;
    }
    .progress-loading .el-progress {
        top: 50%;
        width: 40%;
        margin: 0 auto;
    }
</style>
