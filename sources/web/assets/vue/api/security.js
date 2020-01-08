import axios from 'axios';

export default {
    login (login, password) {
        return axios.post(
            '/api/security/login',
            {
                username: login,
                password: password
            }
        );
    },
    logout () {
        return axios.post('/api/security/logout');
    },
    confirmPassword (token, password) {
        return axios.post(
            '/api/security/token',
            {
                token: token,
                password: password
            }
        );
    },
    resetPassword (email) {
        return axios.post(
            '/api/security/reset-password',
            {
                email: email
            }
        );
    }
}
