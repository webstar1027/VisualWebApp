import axios from 'axios';

export default {
    exportProduitAutres (simulationId) {
        return axios({
            method: 'GET',
            url: `/export-produit-autres/${simulationId}`,
            responseType: 'blob'
        })
    }
}
