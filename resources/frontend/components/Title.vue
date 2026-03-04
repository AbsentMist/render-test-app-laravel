<template>
    <h1 class="text-title mb-6">
        {{ texte }}
         <span class="block h-2 w-32 rounded-r-full mt-1" :style="{backgroundColor: couleur || '#fca5a5'}"></span>
    </h1>
</template>

<script>
export default{
    props: {
        texte: {
            type: String,
            required: true,
        },
        couleur: {
            type: String,
            required: false,
        }
    }
}
</script>