import enUS from "./en-US.json"

export default {
  "en-US": enUS,
  copy: new Proxy(
    {},
    {
      get(object, key) {
        return key
      }
    }
  )
}
