import DetailField from './components/DetailField';
import FormField from './components/FormField';

Nova.booting((Vue, router) => {
  Vue.component('detail-dependency-container', DetailField);
  Vue.component('form-dependency-container', FormField);
});
