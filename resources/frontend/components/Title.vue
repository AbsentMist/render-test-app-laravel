<template>
    <h1 class="text-title mb-6">
        {{ texte }}
         <span class="block h-2 w-32 bg-red-300 rounded-r-full mt-1"></span>
    </h1>
</template>

<script>
export default{
    props: {
        texte: {
            type: String,
            required: true,
        }
    }
}
</script>