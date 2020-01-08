<template>
    <div class="periodique">
        <el-carousel :autoplay="false" :loop="false" trigger="click" :height="`${setCarouselHeight()}px`" arrow="always">
            <el-carousel-item v-for="item in 5" :key="item">
                <div class="item-wrapper">
                    <div v-for="key in 12" :key="key" class="period-item">
                        <p v-if="getIteration(item, key) < 50" class="text-center m-0">{{parseInt(anneeDeReference) + getIteration(item, key)}}</p>
                        <p v-else class="text-center m-0">&nbsp;</p>
                        <el-input
                            v-for="(periodique, property) in value" :key="property"
                            type="text"
                            :placeholder="getIteration(item, key) < 50 ? '0' : null"
                            :class="{'is-error': getIteration(item, key) < 50 && !isFloat(periodique[getIteration(item, key)])}"
                            v-model="periodique[getIteration(item, key)]"
                            :disabled="disable[property][getIteration(item, key)] || getIteration(item, key) > 49"
                            @change="checkPeriodic(property)">
                        </el-input>
                    </div>
                    <div class="period-item">
                        <p class="m-0">&nbsp;</p>
                        <div v-for="(periodique, property) in value" class="reset-control">
                            <i class="el-icon-refresh" @click="resetFormPeriodique(property)"></i>
                        </div>
                    </div>
                </div>
                <div class="text-center mt-2">
                    <span v-if="periodiqueHasError" style="color: #f56c6c;font-size: 13px;">Seules les valeurs numériques sont autorisées</span>
                </div>
            </el-carousel-item>
        </el-carousel>
    </div>
</template>

<script>
import { isFloat, repeatPeriodic, checkPeriodic, initPeriodic, periodicFormatter } from '../../utils/inputs';

export default {
    name: "Periodique",
    props: ['anneeDeReference', 'value', 'options'],
    data() {
        return {
            periodiqueHasError: false,
            disable: {},
			toto : 0
        }
    },
    created() {
        const types = Object.keys(this.value);
        types.forEach(type => {
            this.disable[type] = initPeriodic(50, false);
        });
    },
    methods: {
        isFloat: isFloat,
        setCarouselHeight () {
            return Object.keys(this.value).length * 50 + 100;
        },
        getIteration(item, key) {
            return ((item - 1) * 11) + key - 1;
        },
        resetFormPeriodique(type) {
            if (!this.periodiqueHasError) {
                const newPerioques = repeatPeriodic(this.value[type]);
                this.value[type] = [];
                this.value[type] = newPerioques;
                this.updateValue(type);
                this.$emit('onChange', type);
            }
        },
        checkPeriodic(type) {
            // Format input value
            let newPeriodics = this.value[type];
            this.value[type] = [];
            this.value[type] = periodicFormatter(newPeriodics);

            // Check if periodiques has error
            let hasError = false;
            const periodiques = Object.values(this.value);
            periodiques.forEach(periodique => {
                if (!checkPeriodic(periodique)) {
                    hasError = true;
                }
            });

            if (hasError) {
                this.periodiqueHasError = true;
                this.$emit('hasError', true);
            } else {
                this.periodiqueHasError = false;
                this.$emit('hasError', false);
                this.updateValue(type);
            }

            this.$emit('onChange', type);
        },
        updateValue(type) {
            this.$emit('input', this.value);
        }
    },
    watch: {
        options(value) {
            if (value.disable) {
                this.disable = value.disable;
            }
        }
    }
}
</script>

<style>
    .periodique .item-wrapper {
        margin: 20px 60px 0 60px;
        display: table;
    }
    .periodique .period-item {
        width: 8.33%;
        font-size: 12px;
        padding: 0 5px;
        display: table-cell;
        text-align: center;
    }
    .periodique .period-item .el-input{
        margin-top: 10px;
    }
    .periodique .period-item .el-input__inner{
        padding: 0 3px;
        text-align: right;
    }
    .periodique .el-carousel__button {
        background-color: #2591eb;
        pointer-events: none;
    }
    .periodique .el-carousel__arrow i {
        color: #2591eb;
    }
    .periodique .carousel-head {
        height: 50px;
    }
    .periodique .reset-control {
        width: 40px;
        height: 50px;
        padding-top: 20px;
    }
    .periodique .el-icon-refresh {
        color: #2591eb;
        font-size: 20px;
        font-weight: bold;
        cursor: pointer;
    }
    .periodique .el-input.is-error input {
        border-color: #f56c6c;
    }
	.el-dialog .periodique .period-item .el-input {
		width: 45px;
	}
</style>
