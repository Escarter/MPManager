<template>
    <div>
        <div class="md-layout">
            <div class="md-layout-item md-size-100">
                <div style="float:right">
                    <md-field>
                        <label for="period">Period</label>
                        <md-select v-model="period" name="period" id="period" @md-selected="getPeriod">
                            <md-option value="Yesterday">Yesterday</md-option>
                            <md-option value="Same day last week">Same day last week</md-option>
                            <md-option value="Past 7 days">Past 7 days</md-option>
                            <md-option value="Past 30 days">Past 30 days</md-option>
                        </md-select>
                    </md-field>
                </div>

                <div class="row" v-if="loading === true">
                    <div class="col-sm-12 text-center">
                        <img src="https://loading.io/spinners/dash-ring/index.dash-ring-loading-icon.svg"/>
                    </div>
                </div>
            </div>
        </div>
        <div class="md-layout md-gutter" v-if="analyticsData">
            <box class="md-layout-item md-large-size-25 md-medium-size-50 md-small-size-100"
                 :center-text="true"
                 :color="[ '#26c6da','#00acc1']"
                 header-text="Incoming Transactions"
                 :header-text-color="'#dddddd'"
                 :sub-text="analyticsData.current.total + '/' + analyticsData.past.total"
                 :sub-text-color="'#e3e3e3'"
                 box-icon="plus"
                 :box-icon-color="'#578839'"
                 :additional-text="analyticsData.analytics.totalPercentage.percentage + '%' + analyticsPeriods[analyticsPeriod]"
            />

            <box class="md-layout-item md-large-size-25 md-medium-size-50 md-small-size-100"
                 :center-text="true"
                 :color="[ '#6eaa44','#578839']"
                 header-text="Confirmed"
                 :header-text-color="'#dddddd'"
                 :sub-text="analyticsData.current.confirmed + '/' +  analyticsData.past.confirmed"
                 :sub-text-color="'#e3e3e3'"
                 box-icon="check"
                 :box-icon-color="'#578839'"
                 :additional-text="analyticsData.analytics.confirmationPercentage.percentage + '%' + analyticsPeriods[analyticsPeriod]"
            />

            <box class="md-layout-item md-large-size-25 md-medium-size-50 md-small-size-100"
                 :center-text="true"
                 :color="[ '#ef5350','#e53935']"
                 header-text="Cancelled"
                 :header-text-color="'#dddddd'"
                 :sub-text="analyticsData.current.cancelled + '/' +  analyticsData.past.cancelled"
                 :sub-text-color="'#e3e3e3'"
                 box-icon="ban"
                 :box-icon-color="'#578839'"
                 :additional-text="analyticsData.analytics.cancelationPercentage.percentage + '%' + analyticsPeriods[analyticsPeriod]"
            />

            <box class="md-layout-item md-large-size-25 md-medium-size-50 md-small-size-100"
                 :center-text="true"
                 :color="[ '#ffa726','#fb8c00']"
                 header-text="Revenue"
                 :header-text-color="'#dddddd'"
                 :sub-text="readable(analyticsData.current.amount) +                                    appConfig.currency"
                 :sub-text-color="'#e3e3e3'"
                 box-icon="money-bill"
                 :box-icon-color="'#578839'"
                 :additional-text="analyticsData.analytics.amountPercentage.percentage + '%' + analyticsPeriods[analyticsPeriod]"
            />

            <div class="md-size-100 md-layout-item" v-if="analyticsData === null && loading ===false">
                <h5>There is not enough data to compare</h5>
            </div>
        </div>

        <widget
            :id="'transaction-list'"
            :title="'Transactions'"
            :paginator="transactions.paginator"
            :search="false"
            :subscriber="subscriber"
            :route_name="'/transactions'"
            :show_per_page="true"
        >
            <div class="md-layout md-gutter md-size-100" >
                <div class="transaction-list-grid md-layout-item md-size-20">
                    <filter-transaction @searchSubmit="filterTransaction"></filter-transaction>
                </div>
                <div  class="transaction-list-grid md-layout-item md-size-80">
                    <md-table style="width:100%;" md-card>
                        <md-table-row>
                            <md-table-head>
                                <md-icon>person</md-icon>
                                Service
                            </md-table-head>
                            <md-table-head>
                                <md-icon>phone</md-icon>
                                Sender
                            </md-table-head>
                            <md-table-head>
                                <md-icon>money</md-icon>
                                Amount
                            </md-table-head>
                            <md-table-head>Type</md-table-head>
                            <md-table-head>Message</md-table-head>
                            <md-table-head>
                                <md-icon>calendar_today</md-icon>
                                Sent Date
                            </md-table-head>
                            <md-table-head>
                                <md-icon>calendar_view_day</md-icon>
                                Confirmation/Cancellation to third party
                            </md-table-head>
                        </md-table-row>

                        <md-table-row
                            :class="transaction.status===1 ? 'active':'danger'"
                            v-for="transaction in transactions.list"
                            :key="transaction.id"
                            style="cursor:pointer"
                            @click="transactionDetail(transaction.id)"
                        >
                            <md-table-cell>
                                <img
                                    :src="transaction.service.startsWith('vodacom') ? 'http://micropowermanager.com/storage/icons/vodacom.png':
                        'https://upload.wikimedia.org/wikipedia/en/thumb/8/8d/Bharti_Airtel_Limited_logo.svg/361px-Bharti_Airtel_Limited_logo.svg.png'"
                                    alt
                                    style="max-height:18px;"
                                />
                            </md-table-cell>
                            <md-table-cell>{{transaction.sender}}</md-table-cell>
                            <md-table-cell>{{readable(transaction.amount) + appConfig.currency}}
                            </md-table-cell>
                            <md-table-cell>{{transaction.type}}</md-table-cell>
                            <md-table-cell>{{transaction.message}}</md-table-cell>
                            <md-table-cell>
                                <div v-if="transaction!=undefined">
                                    {{timeForHuman(transaction.sentDate)}}
                                    <small>{{transaction.sentDate}}</small>
                                </div>
                            </md-table-cell>
                            <md-table-cell>
                                <div v-if="transaction!=undefined">
                                    In {{timeDiffForHuman(transaction.sentDate, transaction.lastUpdate)}}
                                    seconds
                                </div>
                            </md-table-cell>
                        </md-table-row>
                    </md-table>
                </div>
            </div>
        </widget>
    </div>
    <!-- container div -->
</template>


<script>

    import {
        Transaction,
        Transactions
    } from '../../classes/transaction/transaction'
    import { timing } from '../../mixins/timing'
    import { currency } from '../../mixins/currency'
    import { EventBus } from '../../shared/eventbus'
    import Widget from '../../shared/widget'
    import FilterTransaction from './FilterTransaction'
    import Box from '../Box'

    export default {
        name: 'transactionList',
        mixins: [timing, currency],
        components: { Box, FilterTransaction, Widget },
        data () {
            return {
                period: 'Yesterday',
                filter: [],
                loading: true,
                subscriber: 'transactrionList', //its needed for the pagination
                tab: 'all',
                transactions: new Transactions(),
                paginator: null,
                analyticsData: null,
                analyticsPeriod: null,

                analyticsPeriods: [
                    'Yesterday',
                    'Same day last week',
                    'Past 7 days',
                    'Past 30 days'
                ],
                bcd: {
                    Home: {
                        href: '/'
                    },
                    Transactions: {
                        href: null
                    }
                }
            }
        },

        mounted () {
            this.loadAnalytics()
            pageSetUp()
            this.getPeriod()
            EventBus.$emit('bread', this.bcd)
            EventBus.$on('pageLoaded', this.reloadList)
            EventBus.$on('searching', this.searching)
            EventBus.$on('end_searching', this.endSearching)

            if (Object.keys(this.$store.getters.search).length) {
                console.log('Search term is set', this.$store.getters.search)
            } else {
                console.log('search term is empty')
            }
        },
        beforeDestroy () {
            EventBus.$off('pageLoaded', this.reloadList)
            EventBus.$off('searching', this.searching)
            EventBus.$off('end_searching', this.endSearching)
        },

        watch: {
            analyticsPeriod () {
                this.analyticsData = null
                this.loadAnalytics()
            }
        },
        methods: {
            filterTransaction (filterData) {
                let data = {}
                for (let i in filterData) {
                    if (filterData[i] === null) {
                        continue
                    }
                    data[i] = filterData[i]
                }

                this.$store.state.search = data
                this.filter = data

                this.transactions.searchAdvanced(data)
                //this.transactions.paginator = new Paginator(resources.transactions.searchAdvanced + data)
            },
            reloadList (sub, data) {
                if (sub !== this.subscriber) return
                this.transactions.updateList(data)
            },
            transactionDetail (id) {
                this.$router.push({ path: '/transactions/' + id })
            },

            loadAnalytics () {
                this.loading = true
                this.analyticsPeriod =
                    this.analyticsPeriod === null ? 0 : this.analyticsPeriod

                axios
                    .get(resources.transactions.analytics + this.analyticsPeriod)
                    .then(response => {
                        let data = response.data
                        this.loading = false

                        if (data.success === false) {
                            this.analyticsData = null
                            return
                        }
                        this.analyticsData = data
                    })
            },

            searching (searchTerm) {
                this.transactions.search(searchTerm)
            },
            endSearching () {
                this.transactions.showAll()
            },

            getPeriod (period = 'Yesterday') {

                switch (period) {
                    case 'Yesterday':
                        this.analyticsPeriod = 0
                        break

                    case 'Same day last week':
                        this.analyticsPeriod = 1
                        break

                    case 'Past 7 days':
                        this.analyticsPeriod = 2
                        break

                    case 'Past 30 days':
                        this.analyticsPeriod = 3
                        break

                    default:
                        break
                }

                this.loadAnalytics()
            }
        }
    }
</script>

<style scoped>
    .box {
        border-right: 2px solid #6d7f94;
        padding-left: 45px;
        color: #6d7f94;
    }

    .information {
        font-size: 2.5rem;
        margin: 0.5rem 0;
    }

    .information.green {
        color: #0dba9a;
    }

    .information.red {
        color: #ba0f0d;
    }

    .information > small {
        font-size: 1.5rem;
    }

    .sub-information > .green {
        color: #61c7b3;
    }

    .sub-information > .red {
        color: #ba0f0d;
    }

    .header {
        clear: both;
    }

    .card-list {
        display: -webkit-inline-box !important;
        width: 100%;
    }

    .card-list-item {
        width: 25% !important;
    }

    .card-list-item-content {
        width: 100% !important;
    }

    .transaction-list-grid {
        padding: 1rem;
    }
</style>

