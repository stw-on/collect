<template>
  <svg class="loading-indicator" :class="{light}" viewBox="0 0 24 24">
    <circle cx="12" cy="12" r="12" class="circle" stroke-width="4px" fill="none" />
    <path d="M 12 0 A 12 12 0 0 1 24 12" stroke-width="4px" fill="none" class="rotating" />
  </svg>
</template>

<script>
  export default {
    name: 'loading-indicator',
    props: {
      light: {
        type: Boolean,
        default: false
      }
    }
  }
</script>

<style lang="scss" scoped>
  @keyframes rotate {
    from {
      transform: rotate(-45deg);
    }
    to {
      transform: rotate(315deg);
    }
  }

  .loading-indicator {
    width: 24px;
    height: 24px;
    position: relative;
    overflow: visible;
    stroke: black;
    stroke-opacity: 0.2;

    .rotating {
      animation: rotate 0.5s infinite;
      transform-origin: 50% 50%;
      stroke-opacity: 1;
    }

    &.light {
      stroke: white;
    }
  }
</style>
