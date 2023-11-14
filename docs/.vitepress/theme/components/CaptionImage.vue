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

<script setup lang="ts">
import { withBase } from "vitepress";

interface Props {
    href?: string;
    src: string;
    caption?: string;
}

const props = withDefaults(defineProps<Props>(), { href: "", caption: "" });

const src = withBase(props.src);
</script>
