import Vue from "vue"
import { getCurrentInstance } from "@vue/composition-api"

export function onHook(name, callback) {
  const vm = getCurrentInstance()
  const merge = Vue.config.optionMergeStrategies[name]

  if (vm && merge) {
    const prototype = Object.getPrototypeOf(vm.proxy.$options)
    prototype[name] = merge(vm.proxy.$options[name], callback)
    return () => {
      delete prototype[name]
    }
  }
  return () => {}
}

export function offHook(name) {
  const vm = getCurrentInstance()
  const merge = Vue.config.optionMergeStrategies[name]

  if (vm && merge) {
    const prototype = Object.getPrototypeOf(vm.proxy.$options)
    if (prototype[name]) {
      delete prototype[name]
    }
  }
}

export function onBeforeRouteUpdate(callback) {
  return onHook("beforeRouteUpdate", callback)
}

export function onBeforeRouteLeave(callback) {
  return onHook("beforeRouteLeave", callback)
}

export function clearBeforeRouteUpdate() {
  return offHook("beforeRouteUpdate")
}

export function clearBeforeRouteLeave() {
  return offHook("beforeRouteLeave")
}
