<template>
	<el-dialog class="modale-dialog" title="Nouveau Poduit" :visible.sync="visible" :close-on-click-modal="false" width="75%" center>
		<div>
			<el-row class="d-flex modale-form mb-5">
				<el-col>
					<span class="form-label">Numéro</span>
					<el-input class="outline" v-model="inputs.value1"></el-input>
				</el-col>
				<el-col>
					<span class="form-label">Libélle</span>
					<el-input class="outline" v-model="inputs.value2"></el-input>
				</el-col>
				<el-col>
					<span class="form-label">Nature du produit</span>
					<el-input class="outline" v-model="inputs.value3"></el-input>
				</el-col>
				<el-col>
					<span class="form-label">Taux d'évolution</span>
					<el-input class="outline" v-model="inputs.value4"></el-input>
				</el-col>
			</el-row>
			<el-row class="my-5">
				<carousel class="years-carousel" :items="12" :nav="false">
					<template slot="prev">
						<el-button size="small" class="prv custom-nav bg-black text-white">
							<md-icon>keyboard_arrow_left</md-icon>
						</el-button>
					</template>

					<div v-for="(y,i) in years" :key="i" class="d-flex align-items-center flex-column">
						<span class="d-block text-center mb-2 font-small font-weight-semibold">{{y}}</span>
						<el-input style="width:60px" size="small" icon class="border thin-border text-white"></el-input>
					</div>
					
					<template slot="next">
						<el-button size="small" class="nxt custom-nav bg-black text-white">
							<md-icon>keyboard_arrow_right</md-icon>
						</el-button>
					</template>
				</carousel>
			</el-row>
			<el-row class="mt-5 dialog-footer">
				<div class="d-flex justify-content-center">
					<el-button class="bg-light-blue">Annuler</el-button>
					<el-button class="bg-black text-white">Valider</el-button>
				</div>
			</el-row>
		</div>
	</el-dialog>
</template>
<script>
import carousel from "vue-owl-carousel";
export default {
	name: "Modale",
	props: ["visible", "title"],
	components: { carousel },
	data() {
		return {
			inputs: {
				value1: "1",
				value2: "168 083",
				value3: "selectionner un produit",
				value4: "0.00"
			},
			years: [
				"2000",
				"2001",
				"2002",
				"2003",
				"2004",
				"2005",
				"2006",
				"2007",
				"2008",
				"2009",
				"2010",
				"2010",
				"2011",
				"2012",
				"2013",
				"2014",
				"2015",
				"2016",
				"2017",
				"2018",
				"2019",
				"2020"
			]
		};
	},
	computed: {
		dialogTitle() {
			return this.title ? this.title : "Modale";
		}
	}
};
</script>
