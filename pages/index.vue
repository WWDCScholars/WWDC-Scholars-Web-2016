<template lang="pug">
.container-outer
  .tab-bar-container
    navigation-tab-bar.form-color-purple
      nuxt-link(
        v-for="(link, year) in yearLinks",
        :to="link",
        :key="year"
      ) {{ year }}
  .container-fluid.color-purple
    .loading-scholars(v-if="$fetchState.pending && currentScholars.length < 1") #[i Gathering the brightest minds from around the world]&nbsp;&nbsp;🤓
    .scholars-list(v-else-if="currentScholars.length > 0")
      scholar-thumbnail(
        v-for="scholar in currentScholars",
        :scholar.once="scholar",
        :key="scholar.recordName"
      )
    .no-scholars(v-else) #[i Unfortunately there are no Scholars to show yet]&nbsp;&nbsp;😭
</template>

<script lang="ts">
import { Component, Vue } from 'nuxt-property-decorator'
import { namespace } from 'vuex-class'
import { Scholar, WWDCYear } from '@wwdcscholars/cloudkit'
import { NavigationTabBar, ScholarThumbnail } from '~/components'

import { name as yearsName } from '~/store/years'
const Years = namespace(yearsName)
import { name as scholarsName } from '~/store/scholars'
const Scholars = namespace(scholarsName)

@Component({
  components: {
    NavigationTabBar,
    ScholarThumbnail
  }
})
export default class PageIndex extends Vue {
  @Years.State('years')
  allYears!: { [recordName: string]: WWDCYear }

  @Years.Getter('sortedKeys')
  sortedYearKeys!: string[]

  @Scholars.State('scholars')
  allScholars!: { [recordName: string]: Scholar }

  get latestYear(): WWDCYear {
    const keys = this.sortedYearKeys
    return this.allYears[keys[keys.length - 1]]
  }

  get currentYear(): WWDCYear {
    if (this.$route.params.year) {
      return this.allYears[`WWDC ${this.$route.params.year}`]
    }

    return this.latestYear
  }

  get yearLinks(): { [year: string]: object } {
    return this.sortedYearKeys.reduce((acc, key) => {
      const record = this.allYears[key]
      const params = record === this.latestYear ? {} : { year: record.year }
      acc[record.year] = {
        name: 'scholars-year',
        params
      }
      return acc
    }, {})
  }

  get currentScholars(): Scholar[] {
    if (!this.currentYear) return []
    const currentYearRecordName = this.currentYear.recordName
    return Object.values(this.allScholars)
      .filter(scholar => {
        return scholar.wwdcYearsApproved && scholar.wwdcYearsApproved.some(yearReference => yearReference.recordName === currentYearRecordName)
      })
      .sort((lhs, rhs) => lhs.givenName.localeCompare(rhs.givenName))
  }

  validate({ params }): boolean {
    if (!params.year) return true
    const year = params.year
    return /^\d{4}$/.test(year)
  }

  async fetch() {
    await this.$store.dispatch('years/queryYears')
    const years = this.$store.state.years.years

    let year: WWDCYear

    if (this.$route.params.year) {
      year = years[`WWDC ${this.$route.params.year}`]
    } else {
      const keys = this.$store.getters['years/sortedKeys']
      year = years[keys[keys.length - 1]]
    }
    if (!year) {
      if (process.server) {
        this.$nuxt.context.res.statusCode = 404
      }

      throw new Error('The requested year could not be found in our database.')
    }

    await this.$store.dispatch('scholars/queryScholars', year)
  }
}
</script>

<style lang="sass" scoped>
.scholars-list
  margin-top: 30px
  display: grid
  grid-template-columns: repeat(auto-fill, 160px)
  grid-auto-rows: 160px
  grid-gap: 15px
  justify-content: center

  .scholar-thumbnail
    display: block

.no-scholars, .loading-scholars
  margin-top: 30px
  text-align: center
  color: $sch-gray0
</style>
