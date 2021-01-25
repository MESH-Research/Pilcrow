<template>
    <a v-if="href" :href="href">
        <figure>
            <img :src="src" />
            <figcaption v-text="caption" />
        </figure>
    </a>
    <figure v-else>
        <img :src="src" />
        <figcaption v-text="caption" />
    </figure>
</template>

<script>
export default {
    name: "CaptionImage",
    props: {
        href: {
            type: String,
            default: ""
        },
        src: {
            type: String
        },
        caption: {
            type: String
        }
    }
};
</script>
