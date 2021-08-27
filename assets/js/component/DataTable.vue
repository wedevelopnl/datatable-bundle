<template>
  <div>
    <div class="d-flex flex-wrap align-items-center">
      <div class="d-flex align-items-center flex-shrink-0 mb-3">
        <b-form-select v-model="perPage" size="sm" class="form-control form-control-sm flex-shrink-0 items-per-page">
          <b-form-select-option value="25">25</b-form-select-option>
          <b-form-select-option value="50">50</b-form-select-option>
          <b-form-select-option value="100">100</b-form-select-option>
        </b-form-select>
        <span class="ml-1 d-none d-sm-block">{{ config.resultsPerPageText }}</span>
      </div>
      <div class="d-flex align-items-center mb-3 ml-2 mr-2">
        <a href="#" class="font-lg data-table-options-toggle" @click.prevent="showOptions = !showOptions">
          <i class="far fa-cog fa-fw"></i>
        </a>
      </div>
      <div class="d-flex align-items-center mb-3 ml-2 mr-2" v-if="this.isExportable">
        <a href="#" class="font-lg data-table-export" v-on:click="exportTable" :title=config.exportTableText>
          <i class="fas fa-download"></i>
        </a>
      </div>
      <div class="data-table-options" :class="{active: showOptions}">
        <b-form-checkbox v-for="column in this.columns" v-if="column.label.length" v-model="visibleColumns[column.key]" :key="column.key" :disabled="visibleColumns.length === 1 && column.visible" inline>
          {{ column.label }}
        </b-form-checkbox>
      </div>
    </div>
    <b-table
        ref="table"
        striped
        hover
        no-local-sorting
        responsive
        sticky-header="calc(100vh - 350px)"
        show-empty
        :empty-text="this.config.noResultsText"
        :empty-filtered-text="this.config.noResultsText"
        :fields="onlyVisibleColumns"
        :items="fetchRows"
        :api-url="this.config.apiUrl"
        :filter="filter"
        :current-page="currentPage"
        :per-page="perPage"
        :sort-by.sync="sortBy"
        :sort-desc.sync="sortDesc"
        :busy="isLoading"
        @filtered="onFiltered"
    >
      <template #table-busy>
        <div class="text-center text-danger my-2">
          <b-spinner class="align-middle"></b-spinner>
          <strong>Loading...</strong>
        </div>
      </template>
      <template slot="top-row" slot-scope="{ fields }">
        <b-td v-for="field in fields" :key="field.key" :sticky-column="field.stickyColumn">
          <template v-if="field.filterable">
            <template v-if="field.filterType === 'text'">
              <b-form-input debounce="200" v-model="filter[field.key]" :placeholder="field.label" autocomplete="off"></b-form-input>
            </template>
            <template v-if="field.filterType === 'smart_choice'">
              <b-form-input :list="'list-' + field.key" v-model="filter[field.key]" debounce="200" :placeholder="field.label" onfocus="this.value = ''" onchange="this.blur()"></b-form-input>

              <datalist :id="'list-' + field.key">
                <option v-for="value in field.filterOptions.choices">{{ value }}</option>
              </datalist>
            </template>
            <template v-if="field.filterType === 'simple_choice' || field.filterType === 'bool'">
              <b-form-select v-model="filter[field.key]">
                <b-form-select-option :value="null">{{ field.label }}</b-form-select-option>
                <b-form-select-option v-for="(value, key) in field.filterOptions.choices" :key="key" :value="key">{{ value }}</b-form-select-option>
              </b-form-select>
            </template>
          </template>
        </b-td>
      </template>
      <template #cell()="data">
        <template v-if="data.field.type === 'twig' || data.field.type === 'bool'">
          <div v-html="data.value"></div>
        </template>
        <template v-else>
          {{ data.value }}
        </template>
      </template>
    </b-table>
    <div class="d-flex">
      <b-pagination
          v-model="currentPage"
          class="mb-3 ml-auto"
          :total-rows="totalRows"
          :per-page="perPage"
          prev-text="<"
          next-text=">"
          first-number
          last-number
      >
        <template #prev-text><i class="far fa-chevron-left" title="Vorige"></i></template>
        <template #next-text><i class="far fa-chevron-right" title="Volgende"></i></template>
      </b-pagination>
    </div>
    <Modal
        @confirm="confirm"
        @closeModal="closeEvent"
        v-for="(modal, index) in config.modals"
        :key="index"
        :name="modal.name"
        :title="modal.title"
        :content="selectedContent"
        :confirmUrl="confirmUrl"
        :buttons="modal.buttons"
        v-model="allModals[index].selected"/>
    <b-modal ref="export-modal" id="export-modal" :title=config.exportSuccessTitle>
      <p class="my-4">{{ config.exportSuccessBody }}</p>
      <template #modal-footer="{ ok, cancel, hide }">
        <b-button variant="primary" @click="ok">
          Ok
        </b-button>
      </template>
    </b-modal>
  </div>
</template>

<script>
import {BFormCheckbox, BFormInput, BFormSelect, BFormSelectOption, BPagination, BTable, BTd, BModal, BSpinner, BButton} from "bootstrap-vue";
import Modal from './Modal.vue'
import axios from "axios";

export default {
  name: 'DataTable',
  props: ['config'],
  components: {BTable, BTd, BPagination, BFormInput, BFormSelect, BFormSelectOption, BFormCheckbox, BModal, BSpinner, BButton, Modal},
  data() {
    return {
      name: null,
      isExportable: false,
      isLoading: false,
      columns: [],
      visibleColumns: {},
      currentPage: 1,
      totalRows: 0,
      perPage: 25,
      filter: {},
      showOptions: false,
      sortBy: null,
      sortDesc: false,
      allModals: [],
      selectedContent: null,
      confirmUrl: null,
    }
  },
  watch: {
    visibleColumns: {
      deep: true,
      handler(visibleColumns) {
        let preferences = this.getPreferences();
        preferences.visibleColumns = visibleColumns;
        this.setPreferences(preferences);
      }
    },
  },
  created() {
    const urlSearchParams = new URLSearchParams(window.location.search);
    const queryParameters = Object.fromEntries(urlSearchParams.entries());

    this.name = this.config.name;
    this.isExportable = this.config.isExportable;
    this.columns = this.config.columns;

    const preferences = this.getPreferences();
    this.visibleColumns = {...this.visibleColumns, ...preferences.visibleColumns};

    for (const column of this.columns) {
      if (column.filterable) {
        this.$set(this.filter, column.key, queryParameters[column.key] ? queryParameters[column.key] : null);
      }

      if (!Object.keys(preferences.visibleColumns).includes(column.key)) {
        this.$set(this.visibleColumns, column.key, column.visible);
      }
    }

    this.initializeModals();
  },
  computed: {
    onlyVisibleColumns() {
      return this.columns.filter(column => this.visibleColumns[column.key]);
    }
  },
  methods: {
    confirm: function (action) {
      const { table } = this.$refs;
      const { url, method } = action;
      const promise = axios({
        url,
        method,
        headers: {
          'Accept': 'application/json'
        }
      });
      return promise.then(() => {
        table.refresh();
        this.closeModal(action.modalState);
      }).catch(error => {
        this.closeModal(action.modalState)
      })
    },
    initializeModals() {
      this.config.modals.forEach(modal => {
        Object.assign(modal, { selected: false });
        this.allModals.push({ name: modal.name, selected: modal.selected });
      })
    },
    closeEvent(modalState) {
      this.closeModal(modalState)
    },
    closeModal(modalState) {
      for (let i = 0, j = this.config.modals.length; i < j; i++) {
        this.allModals[i].selected = !modalState;
      }
    },
    onFiltered(filteredItems) {
      this.$nextTick(() => {
        this.addModalListeners(this.config.modals, filteredItems);
      })
    },
    addModalListeners(modals, filteredItems) {
      modals.forEach((modal, modalIndex) => {
        const tableActions = document.querySelectorAll( `[data-wbmn-modal="${modal.name}"]`);
        tableActions.forEach((action, actionIndex) => {
          const listener = (event) => {
            event.stopImmediatePropagation();
            event.preventDefault();
            this.allModals[modalIndex].selected = !this.allModals[modalIndex].selected;
            this.selectedContent = filteredItems[actionIndex][`modal.${modal.name}`];
            this.confirmUrl = action.dataset.wbmnModalConfirmUrl;
          }
          action.addEventListener('click', listener);
        })
      })
    },
    fetchRows: async function (ctx) {
      const promise = axios.get(ctx.apiUrl, {
        headers: {
          'Accept': 'application/json'
        },
        params: {
          dataTable: this.name,
          page: ctx.currentPage,
          size: ctx.perPage,
          filter: JSON.stringify(this.filter),
          sortBy: ctx.sortBy,
          sortDirection: ctx.sortDesc ? 'DESC' : 'ASC'
        }
      })

      return promise.then(response => {
        this.totalRows = response.data.total
        const rows = response.data.rows
        return rows || []
      })
    },

    initPreferences() {
      const preferences = {
        visibleColumns: this.columns
            .map(column => { return { [column.key]: column.visible }})
            .reduce((acc, column) => { return {...acc, ...column} })
      };

      this.setPreferences(preferences);

      return preferences;
    },

    getPreferences() {
      let preferences = localStorage[`DataTable_${this.name}`];
      if (typeof preferences === 'undefined') {
        return this.initPreferences();
      }

      return JSON.parse(preferences);
    },

    setPreferences(preferences) {
      localStorage[`DataTable_${this.name}`] = JSON.stringify(preferences);
    },

    exportTable() {
      this.isLoading = true;
      axios.post(this.config.apiUrl, {
        dataTableExport: true,
        dataTable: this.name,
        filter: JSON.stringify(this.filter),
        columns: JSON.stringify(this.visibleColumns),
        sortBy: this.sortBy ?? '',
        sortDirection: this.sortDesc ? 'DESC' : 'ASC'
      }).then(response => {
        this.$refs['export-modal'].show()
        this.isLoading = false;
        return response;
      }).catch(() => {
        this.isLoading = false;
      });
    }
  }
}
</script>
