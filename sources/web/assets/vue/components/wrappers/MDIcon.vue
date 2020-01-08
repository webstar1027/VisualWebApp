<template>
    <i class="material-icons"><slot></slot></i>
</template>
<script>
export default {
    name:'MDIcon',
}
</script>
<style lang="scss" scoped>
.material-icons{
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
