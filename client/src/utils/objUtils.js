export const mapObject = (defs, obj) => {
  if (typeof obj == 'undefined' || obj === null) {
    return defs;
  }
  const result = {};
  Object.entries(defs).forEach(([key, value]) => {
    if (typeof value === 'object' && !Array.isArray(value)) {
      result[key] = mapObject(value, obj[key]);
    } else {
      result[key] = obj?.[key] ?? value;
    }
  });
  return result;
};

export default {
    mapObject
}