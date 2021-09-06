<template>
  <BModal class="modal-z" v-model="modalState">
    <template #modal-header="{ close }">
      <span class="align-middle text-primary">
        {{ title }}
      </span>
      <b-button variant="white" color="black" @click="closeModal">
        <i class="far fa-times fa-fw"></i>
      </b-button>
    </template>
    <div v-html="content"></div>
    <template #modal-footer="{ ok, cancel, hide }">
      <b-button
          :variant="buttonVariant(btn)"
          @click="handleBehavior(btn)"
          v-for="(btn, index) in buttons" :key="index">
        {{ btn.label }}
      </b-button>
    </template>
  </BModal>
</template>

<script>
import {BButton, BIcon, BModal} from 'bootstrap-vue'

export default {
  name: 'Modal',
  components: {
    BModal,
    BButton,
    BIcon
  },
  model: {
    prop: 'modal',
    event: 'modal'
  },
  props: {
    modal: {
      type: Boolean,
      default: false
    },
    name: {
      type: String,
      required: true
    },
    title: {
      type: String,
      required: true
    },
    content: {
      type: String,
      required: false
    },
    buttons: {
      type: Array,
      required: true
    },
    confirmUrl: {
      type: String,
      default: ''
    }
  },
  data () {
    return {
      modalState: this.modal
    }
  },
  watch: {
    modal(val) {
      this.modalState = val;
    },
    modalState(val) {
      this.$emit('modal', val)
    }
  },
  methods: {
    closeModal() {
      this.$emit('closeModal', this.modalState);
    },
    buttonVariant(btn) {
      if (!btn.cssClass) {
        return 'btn';
      }
      return btn.cssClass;
    },
    handleBehavior(button) {
      const { behavior } = button;
      switch (behavior) {
        case 'close':
          this.modalState = false;
          break;
        case 'confirm':
          this.$emit('confirm', {
            ...button,
            modalState: this.modalState,
            url: this.confirmUrl
          });
          break;
        default:
          this.modalState = false;
      }
    },
  },
}
</script>
