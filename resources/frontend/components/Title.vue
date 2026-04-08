<template>
    <h1 class="text-title mb-6">
        {{ texte }}
         <span class="block h-2 w-32 rounded-r-full mt-1" :style="{backgroundColor: couleur || '#fca5a5'}"></span>
    </h1>
</template>

<script>
/**
 * @fileoverview Composant Title.
 * @description En-tête de section réutilisable pour harmoniser les titres des écrans.
 * @remarks Ce composant encapsule le style de titre principal avec un soulignement coloré
 * configurable pour conserver une identité visuelle homogène.
 */
export default{
    name: 'Title',
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