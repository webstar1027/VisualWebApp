import moment from "moment";

function getIncremental (data, property) {
    if (data.length > 0) {
        const max = data.reduce(function(prev, current) {
            return (prev[property] > current[property]) ? prev : current
        });
        return max[property] + 1;
    } else {
        return 1;
    }
}

function dateFormatter (date) {
    return date ? moment.utc(String(date)).format("MM/YYYY") : null;
}

async function updateData (query, simulationId) {
    await query.fetchMore({
        variables: { simulationId },
        updateQuery: (prev, { fetchMoreResult }) => {
            return fetchMoreResult
        }
    })
}

function groupBy(list, keyGetter) {
    const map = new Map();
    list.forEach((item) => {
         const key = keyGetter(item);
         const collection = map.get(key);
         if (!collection) {
             map.set(key, [item]);
         } else {
             collection.push(item);
         }
    });
    return map;
}

function getFloat(value) {
    return value? parseFloat(value): 0;
}

async function updateDataByType (query, simulationId, type) {
    await query.fetchMore({
        variables: {
            simulationId,
            type
        },
        updateQuery: (prev, { fetchMoreResult }) => {
            return fetchMoreResult
        }
    })
}

function getYearsFromCurrent (start, end) {
    let currentYear = new Date().getFullYear()
    let listYear = [];
    currentYear = currentYear - start;
    for (let i = currentYear; i <= currentYear + end; i++) {
        listYear.push({
            value: i,
            label: i
        });
    }
    return listYear;
}

function getCount (data, type) {
    let value = 0;
    if (data && data[type]) {
        value = data[type].count;
    }
    return value;
}

function getItems (data, type) {
    let value = [];
    if (data && data[type]) {
        value = data[type].items;
    }
    return value;
}


export {
    getIncremental,
    dateFormatter,
    updateData,
    groupBy,
    getFloat,
    updateDataByType,
    getYearsFromCurrent,
    getCount,
    getItems
}
