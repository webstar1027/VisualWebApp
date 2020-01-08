import Vue from 'vue';
import App from './App';
import apolloProvider from './graphql';
import router from './router';
import store from './store';
import ElementUI from 'element-ui';
import locale from 'element-ui/lib/locale/lang/fr';
import './theme/index.scss';
import multiCascader from 'multi-cascader-base-ele';
import Toasted from 'vue-toasted';

Vue.use(ElementUI, { locale });
Vue.use(multiCascader);
Vue.use(Toasted);

require('./sass/main.scss')
require('material-design-icons/iconfont/material-icons.css')

import FrontWrapper from "./components/wrappers/Front";
import BackWrapper from "./components/wrappers/Back";
import MDIcon from "./components/wrappers/MDIcon";
Vue.component('front-wrapper', FrontWrapper)
Vue.component('back-wrapper', BackWrapper)
Vue.component('md-icon', MDIcon)

Vue.config.productionTip = false

new Vue({
    template: '<App/>',
    components: { App },
    apolloProvider,
    router,
    store,
}).$mount('#app');
