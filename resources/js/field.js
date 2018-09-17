Nova.booting((Vue, router) => {
    Vue.component('detail-nova-dependency-container', require('./components/DetailField'));
    Vue.component('form-nova-dependency-container', require('./components/FormField'));
})
