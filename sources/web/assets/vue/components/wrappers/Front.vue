<template>
	<el-container class="main-app" id="app">
		<Header></Header>
		<main class="d-flex h-100 bg-primary">
			<div v-if="isRoute('simulations')" class="aside aside-homepage text-white flex-shrink-0">
				<VoidSideBar></VoidSideBar>
			</div>
			<div v-else class="aside text-white flex-shrink-0">
				<FrontSideBar></FrontSideBar>
			</div>
			<div class="d-flex flex-column main-content flex-grow-1">
				<Breadcrumbs></Breadcrumbs>
				<div class="flex-grow-1 px-3">
					<slot></slot>
				</div>
				<Footer></Footer>
			</div>
		</main>
	</el-container>
</template>
<script>
	import Header from "../layouts/Header";
	import Footer from "../layouts/Footer";
	import FrontSideBar from "../layouts/FrontSidebar";
	import VoidSideBar from "../layouts/VoidSidebar";
	import Breadcrumbs from "../partials/Breadcrumbs";

	export default {
		name: "FrontWrapper",
		components: {
			Header,
			Footer,
			FrontSideBar,
			VoidSideBar,
			Breadcrumbs
		},
		computed:{
			routeArr(){
				return this.$route.path.split('/')
			}
		},
		methods: {
			isRoute(route){
				return this.routeArr.includes(route)
			}
		}
	};
</script>
