<template>
  <div v-if="paginator">
    <vue-grid justify="around">
      <vue-cell width="4of12">
        <div
          class="col-xs-12 hidden-xs"
          :class="show_per_page === true ? 'col-sm-4 col-lg-5':'col-sm-6 col-lg-6'"
        >
          <div
            class="dataTables_info"
            id="datatable_col_reorder_info2"
            role="status"
            aria-live="polite"
          >
            Showing {{paginator.from}} to {{paginator.to}}
            of {{paginator.totalEntries}} entries
          </div>
        </div>
      </vue-cell>

      <vue-cell style="float:right" width="4of12">
        <div class="col-sm-2 col-lg-1 col-xs-6" v-if="show_per_page===true">
          <div
            class="dataTables_info"
            id="datatable_col_reorder_info"
            role="status"
            aria-live="polite"
          >
            Per Page
            <select name="per_page" id="per_page" @change="defaultItemsPerPage">
              <option value="15">15</option>
              <option value="25">25</option>
              <option value="30">30</option>
              <option value="50">50</option>
              <option value="100">100</option>
              <option value="200">200</option>
              <option value="300">300</option>
            </select>
          </div>
        </div>
      </vue-cell>

      <vue-cell width="4of12">
        <div class="col-sm-6 col-xs-12">
          <div
            class="dataTables_paginate paging_simple_numbers"
            id="datatable_col_reorder_paginate"
          >
            <ul class="pagination pagination-sm">
              <li
                :class="paginator.currentPage>1 ? 'paginate_button previous' :' paginate_button previous disabled'"
                id="datatable_col_reorder_previous"
              >
                <a
                  v-if="!loading"
                  href="javascript:void(0);"
                  aria-controls="datatable_col_reorder"
                  data-dt-idx="0"
                  tabindex="0"
                  @click="loadPage(--paginator.currentPage)"
                >Previous</a>
                <a href="javascript:void(0);" disabled="disabled" v-else>Previous</a>
              </li>
              <li
                v-for="(page, index) in paginator.totalPage"
                :key="index"
                :class="page===paginator.currentPage?' active':''"
                v-if="paginator.currentPage - index <4 && paginator.currentPage - index  > 0 "
              >
                
                  <a
                    v-if="(index < paginator.currentPage+2) && index > paginator.currentPage-4"
                    href="javascript:void(0);"
                    @click="loadPage(page)"
                  >{{page}}</a>

                  <a v-else-if="index === (2+ paginator.currentPage)">...</a>
                  <a
                    v-else-if="(index > Math.abs(paginator.totalPage -3)) "
                    href="javascript:void(0);"
                    @click="loadPage(page)"
                  >{{page}}</a>
              
              </li>
              <li
                :class="(paginator.currentPage < paginator.totalPage ? 'paginate_button next':'paginate_button next disabled')"
                id="datatable_col_reorder_next"
              >
                <a
                  v-if="!loading"
                  href="javascript:void(0);"
                  aria-controls="datatable_col_reorder"
                  data-dt-idx="8"
                  tabindex="0"
                  @click="loadPage(++paginator.currentPage)"
                >Next</a>
                <a href="javascript:void(0);" v-else>Next</a>
              </li>
            </ul>
            <!-- <span v-if="loading">Loading new page</span> -->
          </div>
        </div>
      </vue-cell>
    </vue-grid>
  </div>
</template>

<script>
import { Paginator } from "../classes/paginator";
import { EventBus } from "./eventbus";

export default {
  name: "Paginate",
  props: {
    paginatorReference: Paginator,
    callback: {},
    subscriber: String,
    route_name: String,
    show_per_page: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      loading: false,
      currentFrom: 0,
      currentTo: 0,
      total: 0,
      currentPage: 0,
      totalPages: 0,
      paginator: this.paginatorReference,
      term: {},
      threeDots: false,
      perPage: 15
    };
  },
  mounted() {
    //load the first page

    if (this.$route.params.page_number !== undefined) {
      this.loadPage(this.$route.params.page_number);
    } else {
      this.loadPage(1);
    }
    EventBus.$on("loadPage", this.eventLoadPage);
  },
  destroyed() {
    this.paginator = null;
    console.log("paginator destroyed");
  },
  methods: {
    eventLoadPage(paginator, term = {}) {
      this.term = term;
      let totalPage = this.paginator.totalPage;
      this.paginator = paginator;
      this.paginator.totalPage = totalPage;
      this.loadPage(1);
    },
    defaultItemsPerPage(data) {
      this.paginator.perPage = data.target.value;
       
      this.loadPage(this.paginator.currentPage);
    },

    defaultCallback(data = null) {
      console.log("default callback with", data);
    },
    loadPage(pageNumber = 1) {
      if (this.loading) {
        return;
      }

      this.loading = true;
      this.paginator.loadPage(pageNumber, this.term).then(response => {
        if (this.route_name !== undefined && !this.route_name.includes('/page/1')) {
          //TODO: düzelt
          
          this.$router.push(this.route_name + "/page/" + pageNumber).catch(err => {console.log("test111")});
          //this.loading = false;
        }
        this.loading =false;
        EventBus.$emit("pageLoaded", this.subscriber, response.data);
      });
    }
  },
  computed: {
    _callBack: function() {
      if (this.callback === undefined) return this.defaultCallback;
      return this.callback;
    }
  }
};
</script>

<style scoped lang="scss">
.pagination {
  color: #ac2925;
  list-style: none;
  display: flex;

  li {
    list-style: none;
    display: inline-flex;
    padding: 5px;
    margin: 1px;
    background-color: #f7f7f7;
  }

  .active {
    background-color: #dddddd;
  }
}
.dataTables_info {
  padding-top: 9px;
  font-size: 13px;
  font-weight: 700;
  font-style: italic;
  color: #969696;
}
.dataTables_paginate {
  float: right;
  margin: 0;
}
.dataTables_paginate ul.pagination {
  margin: 2px 0;
  white-space: nowrap;
}
.dataTables_paginate {
  float: right;
  margin: 0;
}
.pagination {
  display: inline-flex;
  padding-left: 0;
  margin: 18px 0;
  border-radius: 2px;
}
.pagination > li {
  display: inline;
}
.pagination > li > a,
.pagination > li > span {
  position: relative;
  float: left;
  padding: 6px 12px;
  line-height: 1.42857143;
  text-decoration: none;
  color: #3276b1;
  background-color: #fff;
  border: 1px solid #ddd;
  margin-left: -1px;
}
.pagination > li:first-child > a,
.pagination > li:first-child > span {
  margin-left: 0;
  border-bottom-left-radius: 2px;
  border-top-left-radius: 2px;
}
.pagination > li:last-child > a,
.pagination > li:last-child > span {
  border-bottom-right-radius: 2px;
  border-top-right-radius: 2px;
}
.pagination > li > a:focus,
.pagination > li > a:hover,
.pagination > li > span:focus,
.pagination > li > span:hover {
  z-index: 2;
  color: #214e75;
  background-color: #eee;
  border-color: #ddd;
}
.pagination > .active > a,
.pagination > .active > a:focus,
.pagination > .active > a:hover,
.pagination > .active > span,
.pagination > .active > span:focus,
.pagination > .active > span:hover {
  z-index: 3;
  color: #fff;
  background-color: #3276b1;
  border-color: #3276b1;
  cursor: default;
}
.pagination > .disabled > a,
.pagination > .disabled > a:focus,
.pagination > .disabled > a:hover,
.pagination > .disabled > span,
.pagination > .disabled > span:focus,
.pagination > .disabled > span:hover {
  color: #999;
  background-color: #fff;
  border-color: #ddd;
  cursor: not-allowed;
}
.pagination-lg > li > a,
.pagination-lg > li > span {
  padding: 10px 16px;
  font-size: 17px;
  line-height: 1.33;
}
.pagination-lg > li:first-child > a,
.pagination-lg > li:first-child > span {
  border-bottom-left-radius: 3px;
  border-top-left-radius: 3px;
}
.pagination-lg > li:last-child > a,
.pagination-lg > li:last-child > span {
  border-bottom-right-radius: 3px;
  border-top-right-radius: 3px;
}
.pagination-sm > li > a,
.pagination-sm > li > span {
  padding: 5px 10px;
  font-size: 12px;
  line-height: 1.5;
}
.pagination-sm > li:first-child > a,
.pagination-sm > li:first-child > span {
  border-bottom-left-radius: 2px;
  border-top-left-radius: 2px;
}
.pagination-sm > li:last-child > a,
.pagination-sm > li:last-child > span {
  border-bottom-right-radius: 2px;
  border-top-right-radius: 2px;
}
.pagination.pagination-alt > li > a {
  box-shadow: none;
  -moz-box-shadow: none;
  -webkit-box-shadow: none;
  border: none;
  margin-left: -1px;
}
.pagination.pagination-alt > li:first-child > a {
  padding-left: 0;
}
.pagination > li > a,
.pagination > li > span {
  box-shadow: inset 0 -2px 0 rgba(0, 0, 0, 0.05);
  -moz-box-shadow: inset 0 -2px 0 rgba(0, 0, 0, 0.05);
  -webkit-box-shadow: inset 0 -2px 0 rgba(0, 0, 0, 0.05);
}
</style>
