import './bootstrap';
import htmx from 'htmx.org';
import Plotly from 'plotly.js-dist';
import Toast from './toast.js';

window.htmx = htmx;

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
