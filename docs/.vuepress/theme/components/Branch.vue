<script>
export default {
  functional: true,
  props: {
    type: {
      type: String,
      default: 'tip'
    },
    text: String,
    vertical: {
      type: String,
      default: 'top'
    }
  },
  render (h, { props, slots }) {
    return h('span', {
      class: ['badge', props.type],
      style: {
        verticalAlign: props.vertical
      }
    }, props.text || slots().default)
  }
}
</script>

<style lang="stylus" scoped>
.badge
  display inline-block
  margin: 0 10px;
  border-radius 8px
  padding 0 6px
  color white
  background-color #42b983
  &.tip, &.green
    background-color $badgeTipColor
  &.error
    background-color $badgeErrorColor
  &.warning, &.warn, &.yellow
    background-color $badgeWarningColor
  & + &
    margin-left 5px
</style>
