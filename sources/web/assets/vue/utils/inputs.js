import {evaluate} from 'mathjs';

function isFloat (val) {
    return val ? !(!/^\d+\.\d+$/.test(val) && !/^\d+$/.test(val)) : true
}

function mathInput (val) {
    if ( /([-+]?[ 0-9]*\.?[0-9]+[ \/\+\-\*])+([-+]?[ 0-9]*\.?[0-9]+)/g.test(val)) {
        return evaluate(val);
    } else if (val && isFloat(val)) {
        return parseFloat(val);
    } else if (!val && val !== 0) {
        return null;
    }
    return val;
}

function periodicFormatter (data) {
    for (let i = 0; i < data.length; i++) {
        if (/\t/.test(data[i])){
            let split = data[i].split("\t");
            for (let j = 0; j < split.length; j++) {
                data[i + j] =  mathInput(split[j]);
            }
        }
        else {
            data[i] = mathInput(data[i]);
        }
    }
    return data;
}

function periodicObjectFormatter (data) {
    for (let i = 0; i < data.length; i++) {
        if (/\t/.test(data[i].valeur)){
            let split = data[i].valeur.split("\t");
            for (let j = 0; j < split.length; j++) {
                data[i + j].valeur =  mathInput(split[j]);
            }
        }
        else {
            data[i].valeur = mathInput(data[i].valeur);
        }
    }
}

function initPeriodic (count=50, value=null) {
    let items = [];
    for (let i = 0; i < count; i++) {
        items.push(value);
    }
    return items;
}

function initObjectPeriodic (count=50, value=null) {
    let items = [];
    for (let i = 0; i < count; i++) {
        items.push({
            iteration: i + 1,
            valeur: value
        });
    }
    return {items};
}

function repeatPeriodic (data) {
    let lastIndex = 0;
    const values = data.filter((item, index) => {
        if (item !== 0 && item !== null) {
            lastIndex = index;
            return true;
        }
        return false;
    });

    if (values.length > 0) {
        const lastValue = values[values.length-1];
        for (let i = 0; i < data.length; i++) {
            if (i > lastIndex) {
                data[i] = lastValue;
            }
        }
    }
    return data;
}

function repeatObjectPeriodic (data) {
    let lastIndex = 0;
    const values = data.filter((item, index) => {
        if (item.valeur !== 0 && item.valeur !== null) {
            lastIndex = index;
            return true;
        }
        return false;
    });

    if (values.length > 0) {
        const lastValue = values[values.length-1].valeur;
        for (let i = 0; i < data.length; i++) {
            if (i > lastIndex) {
                data[i].valeur = lastValue;
            }
        }
    }
    return data;
}

function checkObjectPeriodic (inputs) {
    inputs = inputs.map((item) => {
        return item.valeur === null ? true : isFloat(item.valeur);
    });
    return !inputs.includes(false);
}

function checkPeriodic (inputs) {
    inputs = inputs.map((item) => {
        return item === null ? true : isFloat(item);
    });
    return !inputs.includes(false);
}

function checkAllPeriodics (allPeriodics) {
    let isValid = true;
    Object.keys(allPeriodics).forEach((item) =>{
        if (!checkPeriodic(allPeriodics[item])) {
            isValid = false;
        }
    });
    return isValid;
}

export {
    isFloat,
    mathInput,
    periodicFormatter,
    periodicObjectFormatter,
    initPeriodic,
    initObjectPeriodic,
    repeatPeriodic,
    repeatObjectPeriodic,
    checkObjectPeriodic,
    checkPeriodic,
    checkAllPeriodics
}