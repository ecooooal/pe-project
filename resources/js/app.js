import './bootstrap';
import htmx from 'htmx.org';
import Plotly from 'plotly.js-dist-min'
import Toast from './toast.js';
window.global ||= window;

window.htmx = htmx;
htmx.config.getCacheBusterParam = true

window.addEventListener('DOMContentLoaded', () => {
 const serverToast = document.getElementById('server-toast');
  if (serverToast) {
    try {
      const toastData = JSON.parse(serverToast.textContent);
      Toast.show(toastData);
    } catch (e) {
      console.error('Invalid toast JSON:', e);
    }
  }
});
