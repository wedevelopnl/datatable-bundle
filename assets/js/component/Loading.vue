<template>
  <div v-if="loadingState" :class="fullScreen ? 'fullscreen_loading position-fixed loading' : 'loading position-absolute'">
    <BSpinner variant="primary" />
  </div>
</template>

<script>
import {BSpinner} from 'bootstrap-vue'

export default {
  name: 'Loading',
  components: {
    BSpinner
  },
  model: {
    prop: 'loading',
    event: 'loading'
  },
  props: {
    loading: {
      type: Boolean,
      default: false
    },
    fullScreen: {
      type: Boolean,
      default: false
    }
  },
  data () {
    return {
      loadingState: this.loading
    }
  },
  watch: {
    loading(val) {
      this.loadingState = val;
    },
    loadingState(val) {
      this.$emit('loading', val)
    }
  },
}
</script>

<style lang="scss" scoped>
.fullscreen_loading {
  width: 100vw;
  height: 100vh;
}

.loading {
  background-color: rgba(0, 0, 0, 0.1);
  inset: 0;
  z-index: 10;
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>
