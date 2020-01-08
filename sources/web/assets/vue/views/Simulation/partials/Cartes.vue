<template>
	<div>
		<h4 class="admin-content-title">{{titlePage}}</h4>
		<div class="h-100 d-flex align-items-center">
			<div class="w-100">
				<el-row class="d-flex carte-row justify-content-center carte-boxes">
					<div class="carte-box bg-white" v-for="card in cardsOne" :key="card.title" @click="navigate(card.path)">
						<div class="carte-box-header px-3">
							<h6 class="font-weight-normal m-0 text-white text-uppercase">{{card.title}}</h6>
						</div>
						<div class="carte-box-content pb-3 px-2">
							<div class="carte-box-item py-3">
								<el-button class="cb-item font-smaller" v-for="sousRubrique in card.sousRubrique" :key="sousRubrique.label" @click.stop="navigate(sousRubrique.path)">
									<span class="text-gray cb-item__label">{{sousRubrique.label}}</span>
									<span v-if="sousRubrique.total === 777" class="cb-item__value"></span>
                                    <span v-else-if="sousRubrique.total >= 0" class="cb-item__value">{{ sousRubrique.total }}</span>
                                    <div v-else class="spinner-border spinner-border-sm mt-2 text-primary" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
								</el-button>
							</div>
						</div>
					</div>
				</el-row>
				<el-row class="d-flex w-100 carte-row justify-content-center carte-boxes">
				<div class="carte-box bg-white" v-for="card in cardsTwo" :key="card.title" @click="navigate(card.path)">
						<div class="carte-box-header px-3">
							<h6 class="font-weight-normal m-0 text-white text-uppercase">{{card.title}}</h6>
						</div>
						<div class="carte-box-content pb-3 px-2">
							<div class="carte-box-item py-3">
								<el-button class="cb-item font-smaller" v-for="sousRubrique in card.sousRubrique" :key="sousRubrique.label" @click.stop="navigate(sousRubrique.path)">
									<span class="text-gray cb-item__label">{{sousRubrique.label}}</span>
									<span v-if="sousRubrique.total === 777" class="cb-item__value"> </span>
									<span v-else-if="sousRubrique.total >= 0" class="cb-item__value">{{ sousRubrique.total }}</span>
                                    <div v-else class="spinner-border spinner-border-sm mt-2 text-primary" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
								</el-button>
							</div>
						</div>
					</div>
				</el-row>
			</div>

			<el-button circle class="bg-black text-white material-icon-hover">
				<md-icon>build</md-icon>
			</el-button>
		</div>
	</div>
</template>
<script>
    export default {
        name: "Cartes",
        props: ["cardsOne", "cardsTwo","sousRubrique","titlePage"],
        methods: {
            navigate(path) {
                if (path) {
                    this.$router.push(path);
                }
            }
        }
    };
</script>
