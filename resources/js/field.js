import DetailField from './components/DetailField';
import FormField from './components/FormField';

Nova.booting((Vue, router) => {
  Vue.component('detail-nova-dependency-container', DetailField);
  Vue.component('form-nova-dependency-container', FormField);
});
