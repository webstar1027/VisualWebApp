<template>
	<el-container class="main-app" id="app">
		<Header></Header>
		<div class="d-flex h-100 bg-primary">
			<div class="aside bg-primary text-white h-100 flex-shrink-0" width="250px">
				<BackSidebar></BackSidebar>
			</div>
			<div class="d-flex flex-column main-content flex-grow-1">
				<slot name="breadcrumb"></slot>
				<div class="flex-grow-1 px-3">
					<slot></slot>
				</div>
				<Footer></Footer>
			</div>
		</div>
	</el-container>
</template>
<script>
	import Header from "../layouts/Header";
	import Footer from "../layouts/Footer";
	import BackSidebar from "../layouts/BackSidebar.vue";
	import Breadcrumbs from "../partials/Breadcrumbs";

	export default {
		name: "BackWrapper",
		components: {
			Header,
			Footer,
			BackSidebar,
			Breadcrumbs
		}
	};
</script>
