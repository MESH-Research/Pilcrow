<template>
  <div v-if="showBanner"  class="local-dev-banner">
    <span>📂 {{ dirName }}</span>
    <a
      href="/graphiql"
      target="_blank"
      rel="noopener"
      class="graphiql-link"
      title="Open GraphiQL"
      aria-label="Open GraphiQL"
    >
      <svg
        class="graphiql-icon"
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 400 400"
        aria-hidden="true"
      >
        <path
          fill="currentColor"
          d="M57.468 302.66l-14.376-8.3 160.15-277.38 14.376 8.3zm-17.668-30.46h320.3v16.6H39.8z"
        />
        <path
          fill="currentColor"
          d="M206.348 374.026l-160.21-92.5 8.3-14.376 160.21 92.5zm139.174-241.08l-160.21-92.5 8.3-14.376 160.21 92.5z"
        />
        <path
          fill="currentColor"
          d="M54.482 132.883l-8.3-14.375 160.21-92.5 8.3 14.376zm288.086 169.78L182.418 25.283l14.376-8.3 160.15 277.38zM52.5 107.5h16.6v185H52.5zm278.4 0h16.6v185h-16.6z"
        />
        <path
          fill="currentColor"
          d="M203.522 367l-7.25-12.558 139.34-80.45 7.25 12.557z"
        />
        <path
          fill="currentColor"
          d="M369.5 297.9c-9.6 16.7-31 22.4-47.7 12.8-16.7-9.6-22.4-31-12.8-47.7 9.6-16.7 31-22.4 47.7-12.8 16.8 9.7 22.5 31 12.8 47.7zM90.9 137c-9.6 16.7-31 22.4-47.7 12.8-16.7-9.6-22.4-31-12.8-47.7 9.6-16.7 31-22.4 47.7-12.8 16.7 9.7 22.4 31 12.8 47.7zM30.5 297.9c-9.6-16.7-3.9-38 12.8-47.7 16.7-9.6 38-3.9 47.7 12.8 9.6 16.7 3.9 38-12.8 47.7-16.8 9.6-38.1 3.9-47.7-12.8zM309.1 137c-9.6-16.7-3.9-38 12.8-47.7 16.7-9.6 38-3.9 47.7 12.8 9.6 16.7 3.9 38-12.8 47.7-16.7 9.6-38.1 3.9-47.7-12.8zM200 395.8c-19.3 0-34.9-15.6-34.9-34.9 0-19.3 15.6-34.9 34.9-34.9 19.3 0 34.9 15.6 34.9 34.9 0 19.2-15.6 34.9-34.9 34.9zm0-321.8c-19.3 0-34.9-15.6-34.9-34.9 0-19.3 15.6-34.9 34.9-34.9 19.3 0 34.9 15.6 34.9 34.9 0 19.3-15.6 34.9-34.9 34.9z"
        />
      </svg>
    </a>
  </div>
</template>

<script setup lang="ts">
const appRoot = process.env.LANDO_APP_ROOT as string | undefined
const dirName = appRoot?.split("/").pop() ?? null
const showBanner = appRoot !== undefined && dirName !== 'pilcrow'
</script>

<style scoped lang="sass">
.local-dev-banner
  position: absolute
  top: 0
  left: 50%
  transform: translateX(-50%)
  background: rgb(0, 0, 0)
  color: rgba(255, 255, 255)
  font-size: 13px
  padding: 3px 8px
  border-radius: 0 0 4px 4px
  z-index: 9999
  pointer-events: none
  box-shadow: 0 0 1px 1px #fff, 0 0 0 2px #000
  display: flex
  align-items: center
  gap: 8px

.graphiql-link
  pointer-events: auto
  display: inline-flex
  align-items: center
  color: inherit
  opacity: 0.6
  transition: opacity 120ms ease

  &:hover,
  &:focus-visible
    opacity: 1

.graphiql-icon
  width: 13px
  height: 13px
  display: block
</style>
