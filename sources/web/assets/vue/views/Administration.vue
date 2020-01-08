<template>
	<back-wrapper>
		<el-container>
			<el-main>
				<cartes></cartes>
			</el-main>
		</el-container>
	</back-wrapper>
</template>

<script>
import Cartes from './Administration/partials/Cartes'

export default {
	name: 'administration',
	components: { Cartes }
}
</script>