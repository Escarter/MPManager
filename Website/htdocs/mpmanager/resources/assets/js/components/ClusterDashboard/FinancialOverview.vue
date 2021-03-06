<template>
    <widget
            :title="'Finance Overview  (Period : ' +periodText + ')'"
            :id="'clusters-finance-overview'"
            button
            button-text="Set Period"
            button-color="red"
            :callback="showPeriod"

    >
        <div v-if="setPeriod"
             style="position: absolute; top: 0; right: 0; z-index: 9999; padding: 15px; background-color: white; border:1px solid #ccc;">
            <p>Select a period for income data</p>
            <h5>From</h5>
            <datepicker
                    class="datepicker-right"
                    monday-first
                    minimum-view="day"
                    :disabledDates="disabled"
                    @selected="dateSelectedFrom"
                    maximum-view="year"/>
            <h5> To</h5>
            <datepicker
                    class="datepicker-right"
                    monday-first
                    minimum-view="day"
                    @selected="dateSelectedTo"
                    :disabledDates="disabled"
                    maximum-view="year"/>
            <div style="margin-top: 5px;">
                <button style="width:100%;" class="btn btn-primary" @click="getClusterFinancialData">Send</button>
            </div>
        </div>

        <div class="md-layout md-gutter" style="padding: 10px">
            <!-- donut chart-->
            <div class="md-layout-item "
                 :class="lineChartFullScreen? 'md-size-100' : 'md-size-35' "
                 v-if="financialData">
                <md-card>
                    <md-card-header>
                        <md-card-header-text>
                            Revenue Line
                        </md-card-header-text>
                        <md-menu md-size="big" md-direction="bottom-end">
                            <md-button class="md-icon-button" md-menu-trigger
                                       @click="maximize('lineChartFullScreen')">
                                <md-icon>fullscreen</md-icon>
                            </md-button>

                        </md-menu>
                    </md-card-header>
                    <md-card-content>
                        <GChart
                                type="LineChart"
                                :data="financialDataChart('line')"
                                :options="chartOptions"
                                :resizeDebounce="500"
                                ref="gChart"
                                :events="chartEvents"
                        />
                    </md-card-content>
                </md-card>
            </div>
            <!-- Column Chart -->
            <div class="md-layout-item"
                 v-if="financialData"
                 :class="barChartFullScreen? 'md-size-100' : 'md-size-35' ">
                <md-card>
                    <md-card-header>
                        <md-card-header-text>
                            Bar Chart
                        </md-card-header-text>
                        <md-menu md-size="big" md-direction="bottom-end">
                            <md-button class="md-icon-button" md-menu-trigger
                                       @click="maximize('barChartFullScreen')">
                                <md-icon>fullscreen</md-icon>
                            </md-button>

                        </md-menu>
                    </md-card-header>
                    <md-card-content>
                        <GChart
                                type="ColumnChart"
                                :data="financialDataChart('column')"
                                :options="chartOptions"
                                :resizeDebounce="500"
                                ref="gChart"
                                :events="chartEvents"
                        />
                    </md-card-content>
                </md-card>
            </div>

            <div class="md-layout-item md-size-30"
                 v-if="financialData"
                 :class="donutChartFullScreen? 'md-size-100' : 'md-size-30' ">
                <md-card>
                    <md-card-header>
                        <md-card-header-text>
                            Percentiles of the Revenue
                        </md-card-header-text>
                        <md-menu md-size="big" md-direction="bottom-end">
                            <md-button class="md-icon-button" md-menu-trigger
                                       @click="maximize('donutChartFullScreen')">
                                <md-icon>fullscreen</md-icon>
                            </md-button>

                        </md-menu>
                    </md-card-header>
                    <md-card-content>
                        <GChart
                                type="PieChart"
                                :data="financialDataChart('column')"
                                :options="chartOptions"
                                :resizeDebounce="500"
                                ref="gChart"
                                :events="chartEvents"
                        />
                    </md-card-content>
                </md-card>

            </div>
        </div>


    </widget>


</template>

<script>
  import Widget from '../../shared/widget'
  import { resources } from '../../resources'
  import Datepicker from 'vuejs-datepicker'
  import moment from 'moment'

  export default {
    name: 'FinancialOverview',
    components: { Datepicker, Widget },
    props: {
      clusterId: {
        type: String,
        default: '1'
      }
    },
    data () {
      return {
        lineChartFullScreen: false,
        barChartFullScreen: false,
        donutChartFullScreen: false,

        periodText: '2019.01.01 - Today',
        period: {},
        setPeriod: false,
        clicks: 0, //to detect a double click on a chart
        financialData: null,
        chartOptions: {
          chart: {
            title: 'Customer Payment Flow',
            subtitle: 'Sales, Expenses, and Profit: 2014-2017',
          },
        },
        chartEvents: {
          select: () => {
          },
          click: () => {
            this.clicks++
            let parent = this
            setTimeout(function () {
              if (parent.clicks >= 2) {
                parent.chartType = parent.toggleChartType()

              }
              parent.clicks = 0
            }, 250)
          },
        },
        disabled: {
          customPredictor:
            function (date) {
              let today = new Date()
              let minDate = new Date('2018-01-01')
              // disables the date if it is a multiple of 5
              if (date > today || date < minDate) {
                return true
              }
            }
        },
      }
    },
    mounted () {
      this.getClusterFinancialData()
    },
    methods: {
      showPeriod () {
        this.setPeriod = !this.setPeriod
      },
      getClusterFinancialData () {
        axios.post(resources.clusters.detail + this.clusterId + '/revenue', {
          'period': 'monthly',
          'startDate': this.period.from,
          'endDate': this.period.to
        }).then((response) => {
          this.financialData = response.data.data
          if (this.period.from)
            this.periodText = this.period.from + ' - ' + this.period.to
          this.setPeriod = false

        })
      },
      financialDataChart (type) {
        let data = []
        if (type === 'column') {
          return this.columnChartData()
        } else if (type === 'line') {
          return this.lineChartData()
        }
        return data
      },
      /**
       * Generates data array for line chart
       */
      lineChartData () {
        let data = []
        data.push(['Period'])

        let citiesCount = this.financialData.cities.length
        if (citiesCount === 0) {
          return
        }
        data[0] = this.insertCityNames(citiesCount, data[0])

        let periods = this.financialData.cities[0].revenue
        for (let p in periods) {
          data.push(this.getPeriodicData(citiesCount, p))
        }
        return data
      },
      /**
       * Generates data array for column and donut chart
       */
      columnChartData () {
        let data = []
        data.push(['Mini Grid Name', 'Revenue'])

        for (let i in this.financialData.cities) {
          let cD = this.financialData.cities[i]
          let totalRevenue = 0
          for (let revenue in cD.revenue) {
            totalRevenue += cD.revenue[revenue].revenue
          }
          data.push([cD.name, totalRevenue])

        }

        let total = data.slice(1, data.length)
        let sum = total.reduce((prev, total) => { return prev + total[1]}, 0)
        let data_for_box = {
          'sum': sum, 'period': this.periodText
        }
        this.$emit('complete', data_for_box)
        return data
      },
      /**
       * Inserts the cluster names to the given data array and returns it
       * @param count
       * @param data
       */
      insertCityNames
        (count, data) {
        for (let i = 0; i < count; i++) {
          data.push(this.financialData.cities[i].name)
        }
        return data
      },
      /**
       *
       * @param count the length of clusters
       * @param periodName current selected Period
       * @returns array
       */
      getPeriodicData (count, periodName) {
        let data = []

        data.push(periodName)
        for (let i = 0; i < count; i++) {
          data.push(this.financialData.cities[i].revenue[periodName].revenue)
          //data.push(this.financialData[i].period[periodName].revenue)
        }
        return data
      },

      dateSelectedFrom (date) {
        this.setDate(date, 'from')
      },
      dateSelectedTo (date) {
        this.setDate(date, 'to')
      },
      setDate (dateData, target) {
        let date = moment(dateData)
        if (target === 'from') {
          this.period.from = date.format('YYYY-MM-DD')
        } else {
          this.period.to = date.format('YYYY-MM-DD')
        }
      },
      maximize (data) {
        //eval('this.data = !this.data')
        if (data === 'lineChartFullScreen') {
          this.lineChartFullScreen = !this.lineChartFullScreen
        } else if (data === 'barChartFullScreen') {
          this.barChartFullScreen = !this.barChartFullScreen
        } else if (data === 'donutChartFullScreen') {
          this.donutChartFullScreen = !this.donutChartFullScreen
        }
        window.dispatchEvent(new Event('resize'))
      }
    },
  }
</script>

<style>
    .datepicker-right .vdp-datepicker__calendar {
        right: 0;
    }
</style>
