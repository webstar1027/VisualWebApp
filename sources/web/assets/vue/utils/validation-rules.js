const customValidator = {
    // Use rules when you need specifics rules (ex: you need taux validation but not need to be a required field)
    rules: {
        required: { required: true, message: 'Ce champs est obligatoire', trigger: 'blur' },
        requiredNoWhitespaces: { required: true, whitespace: true, message: 'Ce champs est obligatoire', trigger: 'blur' },
        positiveInt: { pattern: /^\d{1,9}$/, message: 'Ce champs est invalide', trigger: 'blur' },
        positiveDouble: { pattern: /^\d{1,12}(\.\d{1,10})?$/, message: 'Ce champs est invalide', trigger: 'blur' },
        taux: { pattern: /^(?:100|(?:\d{2}|[0-9])(?:\.\d{1,3})?)$/, message: 'Le taux doit être compris entre 0 et 100', trigger: 'blur' },
        maxVarchar: {max: 255 , message: 'Ce champs ne peut excéder 255 caractères', trigger: 'blur'},
        maxLongtext: {max: 100000 , message: 'Ce champs excède le nombre de caractères maximum autorisé', trigger: 'blur'},
        phone: {pattern: /^\+?([0-9]{2})\)?[-. ]?([0-9]{4})[-. ]?([0-9]{4})$/, message: 'Merci de renseigner un numéro de téléphone', trigger: 'blur'},
        email: {type: 'email', message: 'Le format de l\'adresse e-mail est invalide', trigger: 'blur'},
        siren: {required: true, pattern:/[0-9\s]{11}/, message: 'Merci de renseigner un SIREN valide', trigger: 'blur'},
        codeEntite: {required: true, pattern:/[A-Z0-9]{5}/, message: 'Merci de renseigner un code valide', trigger: 'blur'}
    },
    // Preset validators as required
    preset: {
        taux: [
            { required: true, message: 'Ce champs est obligatoire', trigger: 'blur' },
            { pattern: /^(?:(0|100)|(?:\d{2}|[0-9])(?:\.\d{1,3})?)$/, message: 'Le taux doit être compris entre 0 et 100', trigger: 'change' }
        ],
        differe: [
            { required: true, message: 'Ce champs est obligatoire', trigger: 'blur' },
            { pattern: /^\d{1,3}$/, message: 'Ce champs est invalide', trigger: 'blur' }
        ],
        number: {
            positiveInt: [
                { required: true, message: 'Ce champs est obligatoire', trigger: 'blur' },
                { pattern: /^\d{1,7}$/, message: 'Ce champs est invalide', trigger: 'blur' }
            ],
            positiveDouble: [
                { required: true, message: 'Ce champs est obligatoire', trigger: 'blur' },
                { pattern: /^\d{1,12}(\.\d{1,10})?$/, message: 'Ce champs est invalide', trigger: 'blur' }
            ],
            positiveDoubleNotRequired: [
                { pattern: /^\d{1,12}(\.\d{1,10})?$/, message: 'Ce champs est invalide', trigger: 'blur' }
            ]
        },
    },

    getRule: (name, event, message) => {
        if (name) {
            let rule = customValidator.rules[name];
            event = typeof event !== 'undefined' ? event : 'blur'
            rule.trigger = event
            if (typeof message !== 'undefined') {
                rule.message = message
            }
            return Object.assign({}, rule);
        }
        return null
    },
    getPreset(name) {
        /** This method can get only 3 depth level of an object
         * example -> name =  number.positiveInt.somethingElse (maximum)
         * Refactor this one if you need more depth
         **/
        if (name) {
            if (customValidator.preset[name]) {
                return customValidator.preset[name]
            } else if (name.includes('.')) {
                let arr = name.split('.')
                if (arr.length && arr.length <= 3) {
                    return arr.length === 2 ? customValidator.preset[arr[0]][arr[1]] : customValidator.preset[arr[0]][arr[1]][arr[2]]
                }
            }
        }
        return null
    }
};

export default customValidator;
