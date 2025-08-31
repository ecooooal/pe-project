window.Toast = (() => {
  const container_id = 'toast-container';

  let container = document.getElementById(container_id);
  if (!container) {
    container = document.createElement('div');
    container.id = container_id;
    container.className = 'fixed top-4 right-4 flex flex-col gap-2 z-[9999]';
    document.body.appendChild(container);
  }

  function show({status, message, type = 'default'}, duration = 5000) {    
    const toast = document.createElement('div');

    const base_class = 'text-sm font-semibold px-4 py-3 rounded-lg shadow-lg opacity-0 pointer-events-auto select-none transition-opacity duration-300';
    const type_class = {
      success: 'bg-green-900 text-gray-50',
      error: 'bg-red-900 text-gray-50',
      warning: 'bg-yellow-900 text-red-50',
      info: 'bg-blue-900 text-gray-50',
      default: 'bg-gray-900 text-gray-50'
    };

    toast.className = `${base_class} ${type_class[type] || type_class.info}`;
    toast.innerHTML = `<div>${status}</div><div class="text-xs">${message}</div>`;


    container.appendChild(toast);

    requestAnimationFrame(() => {
      toast.classList.remove('opacity-0');
      toast.classList.add('opacity-100');
    });

    // Remove toast after duration with fade-out
    setTimeout(() => {
      toast.classList.remove('opacity-100');
      toast.classList.add('opacity-0');

      toast.addEventListener('transitionend', () => {
        toast.remove();
      });
    }, duration);
  }

  return { show };
})();


export default Toast;
