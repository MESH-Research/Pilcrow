<template>
    <a v-if="href" :href="href">
        <figure>
            <img :src="src" />
            <figcaption>
                <slot name="caption">{{ caption }}</slot>
            </figcaption>
        </figure>
    </a>
    <figure v-else>
        <img :src="src" />
        <figcaption>
            <slot name="caption">{{ caption }}</slot>
        </figcaption>
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
            type: String,
            default: ""
        }
    }
};
</script>
