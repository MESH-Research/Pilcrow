export const mapObject = (defs, obj) => {
    const result = {};
    Object.entries(defs).forEach(([key, value]) => {
      if (typeof value === 'object' && !Array.isArray(value)) {
        console.log(value);
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